<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl;
    protected $model;
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash-latest');
    }

    /**
     * Process report message and extract incident data
     */
    public function processReportMessage(string $message, array $context, array $incidentData, array $conversationHistory): array
    {
        try {
            $language = $this->detectLanguage($message, $conversationHistory);
            if (empty($context['established_language'])) {
                $context['established_language'] = $language;
            } else {
                $language = $context['established_language']; // Use established language
            }
            $language = $this->ensureLanguageConsistency($context, $language);
            $this->debugLanguageDetection($message, $language);

            $hasImage = isset($context['image_uploaded']) && $context['image_uploaded'];
            $isImageOnly = isset($context['image_only']) && $context['image_only'];
            $conversationStage = $context['conversation_stage'] ?? 'collecting';

            // Handle follow-up responses to image analysis
            if (isset($context['awaiting_user_response']) && $context['awaiting_user_response'] && !$hasImage) {
                return $this->handleImageFollowupResponse($message, $language, $context, $incidentData);
            }

            // Handle first image upload
            if ($hasImage) {
                if (empty($conversationHistory) || !isset($context['first_image_processed'])) {
                    return $this->handleFirstImageUpload($language, $incidentData, $context);
                } else {
                    return $this->handleAdditionalImageEvidence($message, $language, $context, $incidentData);
                }
            }

            // Handle text response after first image
            if (!$hasImage && isset($context['first_image_processed']) && !isset($context['incident_details_provided'])) {
                return $this->processIncidentDetailsAfterImage($message, $language, $context, $incidentData);
            }

            // If this is a follow-up text after image processing, clear image flags and continue normally
            if (!$hasImage && isset($context['awaiting_user_response'])) {
                $context['awaiting_user_response'] = false;
                $context['conversation_stage'] = 'collecting';
            }

            // Auto-fill user data if logged in
            $incidentData = $this->autoFillUserData($incidentData);
            
            // Ensure conversationHistory is an array to prevent errors
            if (!is_array($conversationHistory)) {
                $conversationHistory = [];
            }

            // FIXED: Handle photo response when conversation_stage is 'awaiting_photo_response'
            if ($conversationStage === 'awaiting_photo_response') {
                return $this->handlePhotoResponse($message, $language, $context, $incidentData);
            }

            // Check conversation stage for pending cancellation confirmation
            if ($conversationStage === 'pending_cancellation') {
                if ($this->isConfirmingCancellation($message, $language)) {
                    Log::info('User confirmed cancellation', [
                        'message' => $message,
                        'language' => $language,
                        'context' => $context
                    ]);
                    
                    return [
                        'reply' => $this->getCancellationMessage($language),
                        'incident_data' => [],
                        'context' => [
                            'conversation_stage' => 'cancelled',
                            'language_detected' => $language,
                            'cancelled_at' => now()->toDateTimeString()
                        ],
                        'is_complete' => false,
                        'is_cancelled' => true,
                        'missing_fields' => [],
                        'language' => $language,
                        'confidence' => 1.0
                    ];
                } else {
                    return [
                        'reply' => $this->getContinueReportMessage($language),
                        'incident_data' => $incidentData,
                        'context' => array_merge($context ?? [], [
                            'conversation_stage' => 'collecting',
                            'language_detected' => $language
                        ]),
                        'is_complete' => false,
                        'is_cancelled' => false,
                        'missing_fields' => $this->getMissingRequiredFields($incidentData),
                        'language' => $language,
                        'confidence' => 0.9
                    ];
                }
            }

            // Check for initial cancellation intent
            if ($this->detectCancellationIntent($message, $language)) {
                return [
                    'reply' => $this->getCancellationConfirmationMessage($language),
                    'incident_data' => $incidentData,
                    'context' => array_merge($context, [
                        'conversation_stage' => 'pending_cancellation',
                        'language_detected' => $language,
                        'established_language' => $language,  
                        'cancellation_requested_at' => now()->toDateTimeString()
                    ]),
                    'is_complete' => false,
                    'is_cancelled' => false,
                    'missing_fields' => $this->getMissingRequiredFields($incidentData),
                    'language' => $language,
                    'confidence' => 1.0
                ];
            }

            // Build conversation context for Gemini
            $conversationContext = $this->buildConversationContext($conversationHistory, $context, $incidentData);
            
            // Create system prompt based on language
            $systemPrompt = $this->buildSystemPrompt($language, $incidentData);
            
            // Prepare Gemini request
            $prompt = $this->buildPrompt($systemPrompt, $conversationContext, $message);
            
            // Call Gemini API
            $response = $this->callGeminiAPI($prompt);
            
            // Parse Gemini response
            $parsedResponse = $this->parseGeminiResponse($response, $language, $incidentData);
            
            // Extract and update incident data
            $updatedIncidentData = $this->extractIncidentData($message, $parsedResponse, $incidentData, $language);
            
            // Determine missing fields and completion status
            $missingFields = $this->getMissingRequiredFields($updatedIncidentData);
            $isComplete = empty($missingFields);
            
            // Handle completion - ask for photo before saving
            if ($isComplete && $conversationStage !== 'awaiting_photo_response') {
                return $this->askForPhoto($language, $context, $updatedIncidentData);
            }

            // Generate appropriate response for continuing collection
            $reply = $this->generateResponse($parsedResponse, $updatedIncidentData, $missingFields, $language, $isComplete);

            return [
                'reply' => $reply,
                'incident_data' => $updatedIncidentData,
                'context' => array_merge($context, [
                    'last_intent' => $parsedResponse['intent'] ?? 'report_incident',
                    'conversation_stage' => 'collecting',
                    'language_detected' => $language,
                    'established_language' => $language,
                ]),
                'is_complete' => false,
                'missing_fields' => $missingFields,
                'language' => $language,
                'confidence' => $parsedResponse['confidence'] ?? 0.8
            ];
            
        } catch (\Exception $e) {
            Log::error('Gemini processing error: ' . $e->getMessage());
            
            $fallbackLanguage = $context['language_detected'] ?? 'english';

            return [
                'reply' => $this->getErrorMessage($context['language_detected'] ?? 'mixed'),
                'incident_data' => $incidentData,
                'context' => $context,
                'is_complete' => false,
                'missing_fields' => $this->getMissingRequiredFields($incidentData),
                'language' => $language,
                'established_language' => $language,
                'confidence' => 0.1
            ];
        }
    }

        /**
     * Handle additional image evidence - FIXED VERSION
     */
     private function handleAdditionalImageEvidence(string $message, string $language, array $context, array $incidentData): array
    {
        $incidentData = $this->autoFillUserData($incidentData);
        
        //Only handle as additional image evidence if there's actually a NEW image
        // and we have existing conversation history
        if (!isset($context['image_uploaded']) || !$context['image_uploaded']) {
            // No new image, process as normal text message
            return $this->processNormalTextMessage($message, $language, $context, $incidentData);
        }

        // Check if this is the first response after initial image upload
        if (isset($context['first_image_processed']) && !isset($context['incident_details_provided'])) {
            // User is providing incident details after first image
            return $this->processIncidentDetailsAfterImage($message, $language, $context, $incidentData);
        }

        // This is truly additional image evidence
        $messages = [
            'english' => "Thank you for the additional image evidence. This will be included in your incident report. Is there anything else about this incident you'd like to add or clarify?",
            'tagalog' => "Salamat po sa karagdagang larawan na evidence. Isasama po ito sa incident report ninyo. May iba pa po bang gusto ninyong idagdag o linawin tungkol sa incident na ito?",
            'mixed' => "Salamat po sa additional image evidence. Isasama po ito sa incident report ninyo. May iba pa po bang gusto ninyong i-add or clarify about this incident?"
        ];

        return [
            'reply' => $messages[$language] ?? $messages['mixed'],
            'incident_data' => $incidentData,
            'context' => array_merge($context, [
                'awaiting_user_response' => true,
                'additional_image_processed' => true,
                'conversation_stage' => 'collecting'
            ]),
            'is_complete' => false,
            'missing_fields' => $this->getMissingRequiredFields($incidentData),
            'language' => $language,
            'confidence' => 0.9
        ];
    }

    /**
     * Process incident details provided after initial image upload
     */
    private function processIncidentDetailsAfterImage(string $message, string $language, array $context, array $incidentData): array
    {
        // Extract incident information from the user's description
        $updatedIncidentData = $this->extractIncidentData($message, [], $incidentData, $language);
        
        // Update description with the user's input
        if (!empty($message)) {
            $updatedIncidentData['description'] = isset($updatedIncidentData['description']) 
                ? $updatedIncidentData['description'] . ' | ' . $message 
                : $message;
        }

        // Mark that incident details have been provided
        $updatedContext = array_merge($context, [
            'incident_details_provided' => true,
            'conversation_stage' => 'collecting',
            'awaiting_user_response' => false
        ]);

        // Determine missing fields and next steps
        $missingFields = $this->getMissingRequiredFields($updatedIncidentData);
        
        if (empty($missingFields)) {
            // All required info collected, ask for photo (or complete if image already provided)
            return $this->askForPhoto($language, $updatedContext, $updatedIncidentData);
        }

        // Generate response for missing information
        $reply = $this->getNextQuestionMessage($missingFields, $language, $updatedIncidentData);

        return [
            'reply' => $reply,
            'incident_data' => $updatedIncidentData,
            'context' => $updatedContext,
            'is_complete' => false,
            'missing_fields' => $missingFields,
            'language' => $language,
            'confidence' => 0.8
        ];
    }
    /**
     * Process normal text message (not image-related)
     */
    private function processNormalTextMessage(string $message, string $language, array $context, array $incidentData): array
    {
        // Clear image-related flags from context
        $cleanContext = $context;
        unset($cleanContext['image_uploaded']);
        unset($cleanContext['awaiting_user_response']);
        
        // Continue with normal processing
        $cleanContext['conversation_stage'] = 'collecting';
        
        // Extract incident information
        $updatedIncidentData = $this->extractIncidentData($message, [], $incidentData, $language);
        
        // Determine missing fields
        $missingFields = $this->getMissingRequiredFields($updatedIncidentData);
        
        if (empty($missingFields)) {
            return $this->askForPhoto($language, $cleanContext, $updatedIncidentData);
        }

        $reply = $this->getNextQuestionMessage($missingFields, $language, $updatedIncidentData);

        return [
            'reply' => $reply,
            'incident_data' => $updatedIncidentData,
            'context' => $cleanContext,
            'is_complete' => false,
            'missing_fields' => $missingFields,
            'language' => $language,
            'confidence' => 0.8
        ];
    }
            /**
         * Ask for photo after all required information is collected
         */
        private function askForPhoto(string $language, array $context, array $incidentData): array
        {
            if (isset($context['has_image']) && $context['has_image']) {
                // Skip photo question and complete the report
                return [
                    'reply' => $this->getCompletionMessage($language, $incidentData),
                    'incident_data' => $incidentData,
                    'context' => array_merge($context, [
                        'conversation_stage' => 'saved',
                        'language_detected' => $language,
                        'completed_at' => now()->toDateTimeString()
                    ]),
                    'is_complete' => true,
                    'missing_fields' => [],
                    'language' => $language,
                    'confidence' => 1.0
                ];
            }
    
            $photoPrompts = [
                'english' => "Perfect! I have all the required information for your incident report. 
        Would you like to attach any photos to help document this incident? Photos can be very helpful for:
        📸 Showing the scene or location
        🏥 Documenting injuries (if appropriate)
        📋 Evidence of damage or hazards
        📱 Any other relevant visual information
        You can either:
        ✅ Upload a photo now by attaching it to your next message
        ❌ Type 'No' or 'Skip' if you don't have photos to add
        What would you prefer?",

                'tagalog' => "Perfect po! Nakompleto ko na po ang lahat ng kinakailangang impormasyon para sa incident report ninyo.
        Gusto po ba ninyong mag-attach ng mga larawan para ma-document ang incident na ito? Nakakatulong po ang mga larawan para sa:
        📸 Pagpapakita ng lugar o scene
        🏥 Pagdodokumento ng mga sugat (kung appropriate)
        📋 Evidence ng damage o hazards  
        📱 Anumang relevant visual information
        Pwede po kayong:
        ✅ Mag-upload ng photo ngayon by attaching it sa next message ninyo
        ❌ I-type ang 'hindi' o 'skip' kung walang photos na idadagdag
        Ano po ang gusto ninyo?",

                'mixed' => "Perfect po! Nakompleto ko na ang lahat ng required info para sa incident report ninyo.

        Gusto po ba ninyong mag-attach ng photos para ma-document yung incident? Photos are very helpful po para sa:
        📸 Showing the scene or location
        🏥 Documenting injuries (if appropriate po)
        📋 Evidence ng damage or hazards
        📱 Any other relevant visual info

        You can either po:
        ✅ Upload photo now by attaching it sa next message ninyo  
        ❌ Type 'No' or 'Skip' if walang photos na i-add

        What would you prefer po?"
            ];

            return [
                'reply' => $photoPrompts[$language] ?? $photoPrompts['mixed'],
                'incident_data' => $incidentData,
                'context' => array_merge($context, [
                    'conversation_stage' => 'awaiting_photo_response',
                    'language_detected' => $language,
                    'ready_to_save' => true
                ]),
                'is_complete' => false, // Not complete until photo response is handled
                'missing_fields' => [],
                'language' => $language,
                'confidence' => 1.0
            ];
        }

            /**
    * Handle user response about photo upload
    */
    private function handlePhotoResponse(string $message, string $language, array $context, array $incidentData): array
    {
        $message = strtolower(trim($message));
        
        // Check if user declined photo upload
        $declinePatterns = ['no', 'hindi', 'skip', 'wag na', 'ayaw', 'none', 'walang photo', 'walang larawan'];
        $isDeclined = false;
        
        foreach ($declinePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                $isDeclined = true;
                break;
            }
        }
        
        if ($isDeclined) {
            // User declined photo, proceed to save - FIXED: Set is_complete to true
            return [
                'reply' => $this->getCompletionMessage($language, $incidentData),
                'incident_data' => $incidentData,
                'context' => array_merge($context, [
                    'conversation_stage' => 'saved',
                    'language_detected' => $language,
                    'completed_at' => now()->toDateTimeString()
                ]),
                'is_complete' => true, 
                'missing_fields' => [],
                'language' => $language,
                'confidence' => 1.0
            ];
        }
        
        // User wants to upload photo or asking questions
        $waitingMessages = [
            'english' => "Great! Please attach your photo(s) to your next message. I'll analyze them and add them to your incident report. 

If you're having trouble uploading, just type 'skip' and I'll save the report without photos.",
            
            'tagalog' => "Ayos po! Pakiattach po ang inyong larawan sa susunod ninyong message. I-analyze ko po ito at idadagdag sa incident report ninyo.

Kung may problema sa pag-upload, i-type lang po ang 'skip' at ise-save ko na ang report nang walang photos.",
            
            'mixed' => "Great po! Please attach yung photos ninyo sa next message. I'll analyze them at idadagdag sa incident report ninyo.

If may problem sa pag-upload, just type 'skip' lang at ise-save ko na yung report without photos."
        ];
        
        return [
            'reply' => $waitingMessages[$language] ?? $waitingMessages['mixed'],
            'incident_data' => $incidentData,
            'context' => $context, // Keep same context
            'is_complete' => false,
            'missing_fields' => [],
            'language' => $language,
            'confidence' => 0.9
        ];
    }



    /**
     *Auto-fill user data if logged in
     */
    private function autoFillUserData(array $incidentData): array
    {
        // Check if user is logged in
        if (auth()->check()) {
            $user = auth()->user();
            
            // Auto-fill name if not already set
            if (empty($incidentData['reported_by'])) {
                $incidentData['reported_by'] = $user->name;
                
                // Add flag to indicate this was auto-filled
                $incidentData['_auto_filled_name'] = true;
            }
            
            // Auto-fill contact info if not already set
            if (empty($incidentData['contact_info'])) {
                $incidentData['contact_info'] = $user->email;
                
                // Add flag to indicate this was auto-filled
                $incidentData['_auto_filled_contact'] = true;
            }
        }
        
        return $incidentData;
    }

    /**
     * Analyze image using Gemini Vision
     */
           public function analyzeImage(string $imagePath, string $description = '', array $context = [], array $incidentData = []): array
        {
            try {
                // Encode image to base64
                $imageData = base64_encode(file_get_contents($imagePath));
                $mimeType = mime_content_type($imagePath);
                $language = $context['language_detected'] ?? $incidentData['language'] ?? 'mixed';

                $prompt = $this->buildImageAnalysisPrompt($description);
                
                $response = Http::timeout(30)->post($this->baseUrl . "/models/gemini-1.5-flash-latest:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $imageData
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
                
                if ($response->successful()) {
                    $result = $response->json();
                    $analysis = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    $imageAnalysis = $this->parseImageAnalysis($analysis);
                    
                    // Check if we should complete the report after image upload
                    $conversationStage = $context['conversation_stage'] ?? 'collecting';
                    $language = $context['language_detected'] ?? 'mixed';
                    
                    // If we were waiting for photo response and got an image, complete the report
                    if ($conversationStage === 'awaiting_photo_response' || 
                        (empty($this->getMissingRequiredFields($incidentData)) && !empty($incidentData))) {
                        
                        return [
                            'description' => $analysis,
                            'extracted_at' => now(),
                            'confidence' => 0.8,
                            'language_support' => 'english',
                            // Add completion flags
                            'should_complete_report' => true,
                            'completion_message' => $this->getCompletionMessage($language, $incidentData),
                            'new_context' => array_merge($context, [
                                'conversation_stage' => 'saved',
                                'completed_at' => now()->toDateTimeString(),
                                'image_uploaded' => true
                            ])
                        ];
                    }
                    return $this->parseImageAnalysis($analysis);
                }
                
                return ['error' => 'Failed to analyze image'];
                
            } catch (\Exception $e) {
                Log::error('Image analysis error: ' . $e->getMessage());
                return ['error' => 'Image analysis failed'];
            }
        }

        public function processImageUpload(string $imagePath, string $description, array $context, array $incidentData): array
    {
        // Analyze the image first
        $imageAnalysis = $this->analyzeImage($imagePath, $description, $context, $incidentData);
        
        $language = $context['language_detected'] ?? 'mixed';
        $conversationStage = $context['conversation_stage'] ?? 'collecting';
        
        // Check if report should be completed
        $missingFields = $this->getMissingRequiredFields($incidentData);
        $shouldComplete = empty($missingFields) && (
            $conversationStage === 'awaiting_photo_response' || 
            isset($context['ready_to_save'])
        );
        
        if ($shouldComplete) {
            return [
                'reply' => $this->getCompletionMessage($language, $incidentData),
                'incident_data' => $incidentData,
                'context' => array_merge($context, [
                    'conversation_stage' => 'saved',
                    'language_detected' => $language,
                    'completed_at' => now()->toDateTimeString(),
                    'image_uploaded' => true
                ]),
                'is_complete' => true, 
                'missing_fields' => [],
                'language' => $language,
                'confidence' => 0.9,
                'image_analysis' => $imageAnalysis,
                'image_uploaded' => true
            ];
        }
        
        // If not ready to complete, continue collecting
        return [
            'reply' => "Image uploaded successfully. " . $this->getNextQuestionMessage($missingFields, $language, $incidentData),
            'incident_data' => $incidentData,
            'context' => $context,
            'is_complete' => false,
            'missing_fields' => $missingFields,
            'language' => $language,
            'confidence' => 0.8,
            'image_analysis' => $imageAnalysis,
            'image_uploaded' => true
        ];
    }

    /**
     * Detect language from message and conversation history
     */
       private function detectLanguage(string $message, array $conversationHistory): string
    {
        // Check recent messages for language patterns
        $recentMessages = array_slice($conversationHistory, -5);
        $allText = $message . ' ' . implode(' ', array_column($recentMessages, 'message'));
        
        // Enhanced Filipino/Tagalog detection
        $tagalogWords = [
            // Basic pronouns and particles
            'ako', 'ikaw', 'tayo', 'siya', 'kami', 'kayo', 'sila', 'namin', 'natin', 'ninyo', 'nila',
            // Particles (very common)
            'ang', 'ng', 'sa', 'na', 'ay', 'si', 'ni', 'kay', 'para', 'dahil', 'kasi', 'kaya',
            'mga', 'din', 'rin', 'naman', 'lang', 'lamang',
            // Polite markers (STRONG indicators)
            'po', 'opo', 'ho', 'oho',
            // Common verbs
            'nangyari', 'nahulog', 'nauntog', 'nasugatan', 'nasaktan', 'naaksidente', 'nasira',
            'nawala', 'nakita', 'narinig', 'naramdaman', 'naging', 'ginawa', 'sinabi',
            // Question words
            'ano', 'anong', 'sino', 'sinong', 'saan', 'saang', 'kailan', 'bakit', 'paano', 'papaano',
            // Common responses
            'hindi', 'oo', 'opo', 'wala', 'meron', 'mayroon', 'walang', 'may',
            // Courtesy words
            'kumusta', 'salamat', 'pasensya', 'patawad', 'sige', 'okay', 'ayos',
            // Incident-related
            'aksidente', 'sugat', 'sakit', 'dugo', 'masakit', 'nasira', 'delikado',
            // Location words
            'dito', 'doon', 'diyan', 'malapit', 'malayo', 'nasa', 'galing',
            // Time words
            'ngayon', 'kanina', 'mamaya', 'kahapon', 'bukas', 'noong',
            // Additional common words
            'talaga', 'sobra', 'grabe', 'yung', 'yun', 'nung', 'kasi', 'eh'
        ];
        
        $englishWords = [
            'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'what', 'who', 'where', 'when', 'why', 'how', 'is', 'are', 'was', 'were',
            'accident', 'incident', 'injury', 'sick', 'help', 'report', 'happened',
            'please', 'thank', 'you', 'sorry', 'excuse', 'me', 'yes', 'no',
            'this', 'that', 'these', 'those', 'can', 'will', 'would', 'could', 'should',
            'have', 'has', 'had', 'do', 'does', 'did', 'get', 'got', 'go', 'went'
        ];
        
        // IMPROVED: Common Taglish patterns (mixed usage indicators)
        $taglishPatterns = [
            // Common mixed constructions
            'yung', 'nung', 'dun', 'dito sa', 'nasa', 'sa mga',
            // English words commonly used in Filipino sentences
            'phone', 'computer', 'bag', 'room', 'building', 'office',
            'teacher', 'student', 'class', 'school', 'book',
            // Filipino particles with English words
            'po', 'naman', 'lang', 'din', 'rin'
        ];
        
        $tagalogCount = 0;
        $englishCount = 0;
        $taglishIndicators = 0;
        $words = str_word_count(strtolower($allText), 1);
        $totalWords = count($words);
        
        foreach ($words as $word) {
            if (in_array($word, $tagalogWords)) {
                $tagalogCount++;
            }
            if (in_array($word, $englishWords)) {
                $englishCount++;
            }
        }
        
        // Check for Taglish patterns in the full text
        foreach ($taglishPatterns as $pattern) {
            if (str_contains(strtolower($allText), $pattern)) {
                $taglishIndicators++;
            }
        }
        
        // IMPROVED: Check for specific mixed language patterns
        $mixedLanguagePatterns = [
            // Po/opo with English words
            '/\b(po|opo)\b.*\b(phone|computer|room|class|school|teacher|student)\b/i',
            '/\b(phone|computer|room|class|school|teacher|student)\b.*\b(po|opo)\b/i',
            
            // Filipino particles with English
            '/\b(yung|nung|sa)\s+(phone|computer|room|bag|book)/i',
            '/\b(may|meron|wala)\s+(phone|computer|money|bag)/i',
            
            // English verbs with Filipino particles
            '/\b(happened|occurred)\s+(po|naman|lang)/i',
            '/\b(thank you|thanks)\s+(po|ha|naman)/i',
            
            // Common Taglish constructions
            '/\b(hindi|wala)\s+(naman|lang)\s+\w+/i',
            '/\b(okay|ok)\s+(po|lang|naman)/i'
        ];
        
        $mixedPatternMatches = 0;
        foreach ($mixedLanguagePatterns as $pattern) {
            if (preg_match($pattern, $allText)) {
                $mixedPatternMatches++;
            }
        }
        
        // Strong Filipino indicators (almost always Filipino)
        if (preg_match('/\b(po|opo|ho|oho)\b/i', $allText)) {
            // If has "po/opo" but also significant English, it's mixed
            if ($englishCount >= 2 && ($englishCount >= $tagalogCount * 0.5)) {
                return 'mixed';
            }
            return 'tagalog';
        }
        
        // IMPROVED: Mixed language detection logic
        if ($mixedPatternMatches >= 1 || $taglishIndicators >= 2) {
            return 'mixed';
        }
        
        // If both languages present with reasonable counts
        if ($tagalogCount >= 2 && $englishCount >= 2) {
            return 'mixed';
        }
        
        // If one language is clearly dominant
        if ($tagalogCount >= 3 && $tagalogCount > $englishCount * 1.5) {
            return 'tagalog';
        }
        
        if ($englishCount >= 3 && $englishCount > $tagalogCount * 1.5) {
            return 'english';
        }
        
        // Edge case: short messages with mixed indicators
        if ($totalWords <= 5) {
            if ($tagalogCount >= 1 && $englishCount >= 1) {
                return 'mixed';
            }
        }
        
        // Default based on what was detected
        if ($tagalogCount > $englishCount) {
            return 'tagalog';
        } else if ($englishCount > $tagalogCount) {
            return 'english';
        }
        
        // Final fallback - prefer English over mixed for unclear cases
        return 'english';
    }

    /**
     * Build system prompt based on language
     */
    private function buildSystemPrompt(string $language, array $currentData): string
    {
        $userContext = "";
        if (auth()->check()) {
            $user = auth()->user();
            $userContext = "\n\nUSER CONTEXT: This user is logged in as '{$user->name}' with email '{$user->email}'. You do NOT need to ask for their name or contact information as this will be automatically filled.";
        }

        $basePrompt = "You are an AI assistant for a School Incident Reporting System. Your job is to help collect structured incident reports through natural conversation.

    REQUIRED FIELDS TO COLLECT:
    - incident_type: Type of incident 
    (1.Medical / Health Incidents
    2.Behavioral / Disciplinary Incidents
    3.Safety / Security Incidents
    4.Environmental / Facility-Related Incidents
    5.Natural Disasters & Emergency Events
    6.Technology / Cyber Incidents
    7.Transportation Incidents
    8.Administrative / Policy Violations
    9.Lost and Found)
    
    - esi_level: Emergency Severity (1=Emergency, 2=Urgent, 3=Non-Urgent)
    - location: Where the incident occurred (be specific: room number, area, etc.)";

            // Only mention name/contact if user is not logged in
            if (!auth()->check()) {
                $basePrompt .= "\n- reported_by: Name of the person reporting
    - contact_info: Phone number or email";
            }

            $basePrompt .= "\n- description: What happened (optional but helpful)
    - incident_datetime: When it happened (optional, defaults to now)

    CURRENT DATA COLLECTED: " . json_encode($currentData, JSON_PRETTY_PRINT) . "

    INSTRUCTIONS:
    1. Be conversational and empathetic
    2. Extract information naturally from user messages
    3. Ask for missing required fields one at a time
    4. Classify incident types and severity levels accurately
    5. Normalize location names (e.g., 'room 504' → 'Room 504')
    6. Always respond in the user's preferred language";

            if (auth()->check()) {
                $basePrompt .= "\n7. Do NOT ask for the user's name or contact information - they are already logged in";
            }

            $basePrompt .= $userContext;

            $languageInstructions = [
                'tagalog' => "\n\nLANGUAGE: Respond in Tagalog with formal/respectful tone. Use 'po' and 'opo'. Be warm but professional.",
                'english' => "\n\nLANGUAGE: Respond in English with professional but friendly tone.",
                'mixed' => "\n\nLANGUAGE: Respond in Taglish (mixed Tagalog-English) naturally. Match the user's code-switching style."
            ];

            return $basePrompt . ($languageInstructions[$language] ?? $languageInstructions['mixed']);
        }

    /**
     * Build conversation context for Gemini
     */
    private function buildConversationContext(array $history, array $context, array $incidentData): string
    {
        $contextString = "CONVERSATION HISTORY:\n";
        
        $recentHistory = array_slice($history, -10); // Last 10 messages
        
        foreach ($recentHistory as $entry) {
            $role = $entry['role'] === 'user' ? 'USER' : 'BOT';
            $contextString .= "$role: {$entry['message']}\n";
        }
        
        if (!empty($context)) {
            $contextString .= "\nCONTEXT: " . json_encode($context) . "\n";
        }
        
        if (!empty($incidentData)) {
            $contextString .= "\nCURRENT INCIDENT DATA: " . json_encode($incidentData) . "\n";
        }
        
        return $contextString;
    }

    /**
     * Build complete prompt for Gemini
     */
    private function buildPrompt(string $systemPrompt, string $context, string $userMessage): string
    {
        return $systemPrompt . "\n\n" . $context . "\n\nUSER MESSAGE: " . $userMessage . "\n\nPlease respond naturally and helpfully. Extract any incident information from the user's message and ask for missing required fields if needed.";
    }

    /**
     * Call Gemini API
     */
    private function callGeminiAPI(string $prompt): array
    {
        $response = Http::timeout(30)->post($this->baseUrl . "/models/{$this->model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 1024
            ]
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Gemini API call failed: ' . $response->body());
    }

    /**
     * Parse Gemini response
     */
    private function parseGeminiResponse(array $response, string $language, array $currentData): array
    {
        $content = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        return [
            'response_text' => $content,
            'intent' => $this->extractIntent($content),
            'confidence' => $response['candidates'][0]['finishReason'] === 'STOP' ? 0.9 : 0.7
        ];
    }

   /**
 * Enhanced incident data extraction with better Filipino support and bug fixes
 */
    private function extractIncidentData(string $message, array $parsedResponse, array $currentData, string $language): array
    {
        $updatedData = $currentData;
        
        // Extract incident type (only if not already set)
        if (empty($updatedData['incident_type'])) {
            $incidentType = $this->extractIncidentType($message, $language);
            if ($incidentType) {
                $updatedData['incident_type'] = $incidentType;
                $updatedData['esi_level'] = $this->calculateESILevel($incidentType, $message);
            }
        }
        
        // Extract location (only if not already set)
        if (empty($updatedData['location'])) {
            $location = $this->extractLocation($message, $language);
            if ($location) {
                $updatedData['location'] = $location;
            }
        }
        
        // Extract reporter name (only if not already set)
        if (empty($updatedData['reported_by'])) {
            $reporterName = $this->extractReporterName($message, $language);
            if ($reporterName) {
                $updatedData['reported_by'] = $reporterName;
            }
        }
        
        // Extract contact info (only if not already set)
        if (empty($updatedData['contact_info'])) {
            $contactInfo = $this->extractContactInfo($message);
            if ($contactInfo) {
                $updatedData['contact_info'] = $contactInfo;
            }
        }
        
        // Handle description more carefully
        if (empty($updatedData['description']) && strlen($message) > 20) {
            // Only set description if the message appears to be describing an incident
            // and not just providing name, contact info, or location
            if ($this->isIncidentDescription($message, $language)) {
                $updatedData['description'] = $message;
            }
        } elseif (!empty($updatedData['description']) && strlen($message) > 20) {
            // If description exists, only append if this is additional incident details
            if ($this->isAdditionalIncidentDetails($message, $language)) {
                $updatedData['description'] .= " | Additional details: " . $message;
            }
        }
        
        // Extract incident datetime if mentioned and not already set
        if (empty($updatedData['incident_datetime'])) {
            $datetime = $this->extractIncidentDateTime($message, $language);
            if ($datetime) {
                $updatedData['incident_datetime'] = $datetime;
            }
        }
        
        return $updatedData;
    }

    /**
     * Check if the message appears to be describing an incident
     */
    private function isIncidentDescription(string $message, string $language): bool
    {
        $message = strtolower($message);
        
        // Patterns that suggest incident description
        $incidentDescriptionPatterns = [
            // English patterns
            'happened', 'occurred', 'accident', 'incident', 'injured', 'hurt', 'fell', 'slipped',
            'there was', 'someone', 'student', 'teacher', 'during', 'while', 'when',
            // Filipino patterns
            'nangyari', 'naaksidente', 'nahulog', 'nasugatan', 'nasaktan', 'may', 'mayroong',
            'habang', 'noong', 'kanina', 'kahapon', 'ngayon', 'biglang'
        ];
        
        // Patterns that suggest NOT incident description (personal info)
        $nonIncidentPatterns = [
            // English patterns
            'my name is', 'i am', 'call me', 'contact', 'phone', 'number', 'email',
            'room', 'building', 'floor', 'located', 'address',
            // Filipino patterns
            'ako si', 'pangalan ko', 'tawag sakin', 'numero ko', 'contact ko',
            'nasa', 'sa room', 'sa building'
        ];
        
        // Check for non-incident patterns first
        foreach ($nonIncidentPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return false;
            }
        }
        
        // Check for incident description patterns
        foreach ($incidentDescriptionPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the message contains additional incident details
     */
    private function isAdditionalIncidentDetails(string $message, string $language): bool
    {
        $message = strtolower($message);
        
        $additionalDetailsPatterns = [
            // English patterns
            'also', 'additionally', 'furthermore', 'and then', 'after that', 'later',
            'more details', 'i forgot to mention', 'by the way',
            // Filipino patterns
            'pati na rin', 'tsaka', 'at saka', 'pagkatapos', 'nakalimutan ko',
            'dagdag pa', 'isa pa', 'ah oo nga pala'
        ];
        
        foreach ($additionalDetailsPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Extract incident date/time if mentioned in the message
     */
    private function extractIncidentDateTime(string $message, string $language): ?string
    {
        $message = strtolower($message);
        
        // Time patterns
        $timePatterns = [
            // English patterns
            '/(?:at|around|about)\s+(\d{1,2}:\d{2}|\d{1,2}\s?(?:am|pm))/i',
            '/(\d{1,2}:\d{2}|\d{1,2}\s?(?:am|pm))/i',
            // Filipino patterns  
            '/(?:mga|bandang|ala)\s+(\d{1,2}:\d{2}|\d{1,2})/i',
            '/alas\s+(\d{1,2})/i'
        ];
        
        // Date patterns
        $datePatterns = [
            // English patterns
            '/(?:yesterday|today|this morning|this afternoon|this evening)/i',
            '/(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i',
            // Filipino patterns
            '/(?:kahapon|ngayon|kanina|mamaya)/i',
            '/(?:umaga|tanghali|hapon|gabi)/i',
            '/(?:lunes|martes|miyerkules|huwebes|biyernes|sabado|linggo)/i'
        ];
        
        $extractedTime = '';
        $extractedDate = '';
        
        // Extract time
        foreach ($timePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $extractedTime = $matches[1];
                break;
            }
        }
        
        // Extract date
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                $extractedDate = $matches[0];
                break;
            }
        }
        
        if ($extractedDate || $extractedTime) {
            return trim($extractedDate . ' ' . $extractedTime);
        }
        
        return null;
    }
    
        /** GeminiService.php
     * Enhanced incident type extraction with comprehensive Filipino patterns
     */
    private function extractIncidentType(string $message, string $language): ?string
        {
            $message = strtolower($message);
            
            $typeDisplayNames = [
                    'medical_health' => 'Medical / Health',
                    'behavioral_disciplinary' => 'Behavioral / Disciplinary',
                    'safety_security' => 'Safety / Security',
                    'environmental_facility' => 'Environmental / Facility-Related Incident',
                    'natural_disasters_emergency' => 'Natural Disasters & Emergency Events',
                    'technology_cyber' => 'Technology / Cyber Incident',
                    'administrative_policy' => 'Administrative / Policy Violations',
                    'lost_found' => 'Lost and Found'
                ];

            $typePatterns = [
                // 1. MEDICAL / HEALTH INCIDENTS
                'medical_health' => [
                    // Critical medical conditions
                    'heart attack', 'cardiac arrest', 'pag-aatake sa puso', 'atake sa puso',
                    'stroke', 'seizure', 'epilepsy', 'convulsion', 'kumukumpulsyon', 'atake',
                    'anaphylaxis', 'severe allergic reaction', 'malubhang allergy', 'namamaga buong mukha',
                    'diabetic emergency', 'insulin shock', 'diabetic coma', 'diabetes emergency',
                    'asthma attack', 'severe asthma', 'hika', 'atake ng hika', 'walang inhaler',
                    'respiratory distress', 'can\'t breathe', 'hindi makahinga', 'walang hininga',
                    'unconscious', 'walang malay', 'hindi umiimik', 'nakahandusay', 'comatose',
                    'overdose', 'drug overdose', 'alcohol poisoning', 'na-overdose', 'nalason sa gamot',
                    'choking', 'naninikip', 'nasakal', 'something stuck in throat',
                    
                    // Injuries
                    'severe bleeding', 'dumudugo nang matindi', 'maraming dugo', 'hindi tumigil ang dugo',
                    'deep laceration', 'malalim na hiwa', 'malaking sugat', 'kailangan ng tahi',
                    'compound fracture', 'open fracture', 'bone sticking out', 'nakita ang buto',
                    'head trauma', 'traumatic brain injury', 'malubhang sugat sa ulo', 'basag ang bungo',
                    'spinal injury', 'neck injury', 'back injury', 'sugat sa gulugod', 'hindi makagalaw',
                    'amputation', 'severed finger', 'cut off', 'natanggal ang daliri', 'naputol',
                    'eye injury', 'eye trauma', 'something in eye', 'sugat sa mata', 'bulag',
                    'burns third degree', 'severe burns', 'chemical burns', 'malubhang paso', 'nasunog ng matindi',
                    'broken bone', 'fracture', 'bali', 'nabali ang buto', 'basag na buto', 'nabasag',
                    'dislocation', 'dislocated joint', 'naalis sa pwesto', 'lumabas ang buto',
                    'sprain', 'twisted ankle', 'pilay', 'napilay', 'napilayan', 'naligoy',
                    'moderate bleeding', 'bleeding but controlled', 'dumudugo pero kontrolado',
                    'cuts requiring stitches', 'sugat na kailangan ng tahi', 'malalim na gasgas',
                    'burn second degree', 'paso', 'nasunog', 'first degree burn', 'skin burned',
                    'dental injury', 'knocked out tooth', 'broken tooth', 'nabali ang ngipin', 'nalaglag ang ngipin',
                    'concussion', 'mild head injury', 'nauntog nang malakas', 'nahilo pagkatapos mauntog',
                    'bruise', 'pasa', 'namaga', 'nangitim', 'minor bruising', 'umitim',
                    'small cut', 'shallow cut', 'maliit na sugat', 'gasgas', 'nagasgas', 'scratch',
                    'minor burn', 'small burn', 'maliit na paso', 'skin redness from heat',
                    'splinter', 'thorn', 'tinik', 'nakatusok na kahoy', 'nakatusok sa balat',
                    'minor sprain', 'slight twist', 'konting pilay', 'medyo napilay',
                    'bump', 'minor bump', 'bukol', 'umbok', 'nauntog ng konti',
                    'nosebleed', 'bloody nose', 'balinguyngoy', 'dumudugo ang ilong',
                    
                    // General illness
                    'fever', 'high fever', 'lagnat', 'nilagnat', 'mataas na lagnat', 'init ng katawan',
                    'headache', 'severe headache', 'migraine', 'sakit ng ulo', 'masakit ang ulo',
                    'stomachache', 'stomach pain', 'sakit ng tiyan', 'masakit ang tiyan', 'abdominal pain',
                    'nausea', 'vomiting', 'nasusuka', 'suka', 'nahihilo at nasusuka',
                    'dizziness', 'dizzy', 'nahilo', 'hilo', 'parang lasing', 'parang umiikot',
                    'allergic reaction', 'allergy', 'allergic to food', 'allergy sa pagkain', 'pantal',
                    'food poisoning', 'nalason sa pagkain', 'masama ang kinain', 'spoiled food',
                    'dehydration', 'dehydrated', 'tuyo', 'walang tubig sa katawan', 'very thirsty',
                    'fainting', 'fainted', 'nahimatay', 'nawalan ng malay', 'suddenly collapsed',
                    
                    // Contagious diseases
                    'covid', 'coronavirus', 'flu', 'influenza', 'trangkaso', 'malakas na trangkaso',
                    'chickenpox', 'bulutong tubig', 'measles', 'tigdas', 'mumps', 'beke',
                    'conjunctivitis', 'pink eye', 'sore eyes', 'masakit na mata', 'namamagang mata',
                    'stomach virus', 'gastroenteritis', 'vomiting and diarrhea', 'suka at pagtatae',
                    'strep throat', 'masakit na lalamunan', 'throat infection', 'impeksyon sa lalamunan',
                    'skin infection', 'impetigo', 'ringworm', 'buni', 'impeksyon sa balat',
                    'lice', 'head lice', 'kuto', 'may kuto', 'lice outbreak',
                    
                    // Mental health
                    'mental health crisis', 'mental breakdown', 'psychological emergency',
                    'panic attack', 'panic attack', 'anxiety attack', 'anxiety crisis',
                    'depression episode', 'malungkot na episode', 'suicidal thoughts', 'gustong magpakamatay',
                    'self harm', 'sariling panakit', 'cutting', 'nag-cutting', 'self injury',
                    'eating disorder', 'problema sa pagkain', 'psychosis', 'mental episode',
                    
                    // Sports injuries
                    'sports injury', 'aksidente sa sports', 'gym accident', 'aksidente sa gym',
                    'playground accident', 'aksidente sa playground', 'equipment injury', 'nasugatan sa equipment',
                    'collision during sports', 'nabangga sa sports', 'overexertion', 'sobrang pagod sa sports',
                    'heat exhaustion from sports', 'nahilo sa init habang nagsports'
                ],
                
                // 2. BEHAVIORAL / DISCIPLINARY INCIDENTS
                'behavioral_disciplinary' => [
                    // Violence and fighting
                    'physical assault', 'physical violence', 'sinalakay', 'binugbog',
                    'beating', 'pagbubugbog', 'gang violence', 'gulo ng grupo',
                    'weapon involved', 'may ginamit na armas', 'knife fight', 'away may kutsilyo',
                    'serious injury from fight', 'nalubha sa away', 'hospitalized from fight',
                    'group assault', 'grupo na nananakit', 'jumped by group', 'ginulpi ng grupo',
                    'fight', 'away', 'physical fight', 'nag-away nang pisikal', 'fist fight', 'suntukan',
                    'hitting', 'nananakit', 'punching', 'nananaga', 'slapping', 'nananampal',
                    'pushing', 'nagtutulakan', 'shoving', 'nagtutulak', 'wrestling', 'nagbubuno',
                    'kicking', 'nanansipa', 'hair pulling', 'nangangabit ng buhok',
                    
                    // Bullying
                    'physical bullying', 'pisikal na pag-bully', 'binubully physically',
                    'pushing around', 'tinakot', 'sinadya saktan', 'intentionally hurt',
                    'threatening with violence', 'binanta na sasaktan', 'intimidation with force',
                    'verbal bullying', 'binubully sa salita', 'name calling', 'tinatawag ng pangit',
                    'insults', 'panlalait', 'mocking', 'ginagaya', 'teasing', 'inaasaran',
                    'threats', 'pagbabanta', 'intimidation', 'pinagtakot', 'harassment', 'ginugulo',
                    'discrimination', 'pagkakaiba-iba', 'racist comments', 'lahi discrimination',
                    'cyberbullying', 'cyber bully', 'online bullying', 'binubully online',
                    'social media harassment', 'ginugulo sa social media', 'online threats',
                    'inappropriate photos shared', 'nagtawag ng masama sa facebook',
                    'fake social media accounts', 'fake account ginawa', 'online impersonation',
                    
                    // Sexual misconduct
                    'sexual harassment', 'sexual assault', 'inappropriate touching', 'hinalay',
                    'unwanted advances', 'hindi gustong ginawa', 'sexual comments', 'bastos na salita',
                    'indecent exposure', 'nagpakita ng hindi dapat', 'voyeurism', 'nagtitingin nang masama',
                    
                    // Substance abuse
                    'drugs', 'droga', 'illegal drugs', 'marijuana', 'shabu', 'cocaine',
                    'drug possession', 'may dalang droga', 'drug use', 'gumagamit ng droga',
                    'drug dealing', 'nagtitinda ng droga', 'selling drugs', 'nagbebenta ng droga',
                    'alcohol', 'alak', 'beer', 'drunk', 'lasing', 'under influence',
                    'drinking on campus', 'uminom sa school', 'alcohol possession', 'may dalang alak',
                    'pills', 'tabletas', 'prescription abuse', 'ginagamit hindi tamang gamot',
                    
                    // Academic misconduct
                    'cheating', 'nandadaya', 'plagiarism', 'kinopya', 'exam fraud', 'nandaya sa exam',
                    'grade tampering', 'binago ang grade', 'academic dishonesty', 'hindi tapat sa academics',
                    'fake documents', 'pekeng dokumento', 'forged signature', 'pekeng pirma'
                ],
                
                // 3. SAFETY / SECURITY INCIDENTS
                'safety_security' => [
                    // Weapons
                    'weapon', 'armas', 'gun', 'baril', 'knife', 'kutsilyo', 'blade', 'talim',
                    'firearm', 'baril', 'pistol', 'revolver', 'rifle', 'shotgun',
                    'improvised weapon', 'ginawang armas', 'sharp object', 'matalas na bagay',
                    'brass knuckles', 'knuckles', 'balisong', 'butterfly knife',
                    'weapon threat', 'binanta gamit ang armas', 'showed weapon', 'pinakita ang armas',
                    
                    // Theft
                    'stole laptop', 'ninakaw ang laptop', 'computer stolen', 'ninakaw ang computer',
                    'stole money', 'ninakaw ang pera', 'large amount stolen', 'malaking pera ninakaw',
                    'multiple items stolen', 'maraming gamit ninakaw', 'locker ransacked', 'ginulo ang locker',
                    'organized theft', 'planado na pagnanakaw', 'theft ring', 'grupo ng magnanakaw',
                    'break in', 'pumasok nang walang paalam', 'forced entry', 'pinilit pasukin',
                    'armed robbery', 'holdap may armas', 'robbed with weapon', 'ninakawan gamit ang armas',
                    'stole phone', 'ninakaw ang cellphone', 'missing phone', 'nawala ang phone',
                    'stole wallet', 'ninakaw ang wallet', 'money missing', 'nawala ang pera',
                    'stole bag', 'ninakaw ang bag', 'missing backpack', 'nawala ang backpack',
                    'stole school supplies', 'ninakaw ang school supplies', 'missing notebook',
                    'pickpocketed', 'nambulsa', 'snatched', 'ninakaw habang naglalakad',
                    'lunch money stolen', 'ninakaw ang baon', 'allowance stolen', 'ninakaw ang allowance',
                    
                    // Visitor and intruder incidents
                    'unauthorized person', 'walang pahintulot na tao', 'intruder', 'nakapasok na hindi kilala',
                    'visitor incident', 'problema ng visitor', 'trespassing', 'nakapasok nang walang paalam',
                    'stranger on campus', 'hindi kilalang tao sa campus', 'security breach', 'nasira ang security',
                    
                    // Prohibited items
                    'contraband', 'bawal na gamit', 'inappropriate material', 'bawal na material',
                    'pornographic material', 'bastos na material', 'gambling', 'sugal',
                    'smoking', 'naninigarilyo', 'cigarettes', 'sigarilyo', 'vaping', 'e-cigarette',
                    'lighter', 'posporo', 'matches', 'flame source', 'may panggatong',
                    'explosive', 'paputok', 'fireworks', 'bomba', 'suspicious package'
                ],
                
                // 4. ENVIRONMENTAL / FACILITY-RELATED INCIDENTS
                'environmental_facility' => [
                    // Fire incidents
                    'fire', 'sunog', 'apoy', 'nasusunog', 'may nasusunog', 'usok', 'smoke',
                    'flames', 'ningas', 'electrical fire', 'sunog dahil sa kuryente',
                    'kitchen fire', 'sunog sa kusina', 'chemical fire', 'sunog ng kemikal',
                    'grass fire', 'sunog sa damuhan', 'building on fire', 'nasusunog na gusali',
                    'fire alarm activated', 'nag-alarm', 'fire drill', 'fire evacuation',
                    'smoke detected', 'may amoy sunog', 'burning smell', 'maamoy na sunog',
                    
                    // Chemical hazards
                    'chemical spill', 'nabuhusan ng kemikal', 'acid spill', 'nabuhusan ng acid',
                    'gas leak', 'chemical exposure', 'nahilanihan ng kemikal', 'nasamahan ng kemikal',
                    'toxic fumes', 'nakakalason na singaw', 'chemical burn', 'nasunog ng kemikal',
                    'laboratory accident', 'aksidente sa lab', 'experiment gone wrong', 'mali ang eksperimento',
                    'mercury spill', 'nabuhusan ng mercury', 'broken thermometer', 'nabasag na thermometer',
                    'cleaning chemical accident', 'aksidente sa cleaning supplies', 'bleach exposure',
                    
                    // Structural hazards
                    'ceiling collapse', 'bumagsak ang kisame', 'ceiling falling', 'nahuhulog na kisame',
                    'wall crack', 'bitak sa pader', 'structural damage', 'sira sa gusali',
                    'loose tiles', 'nakakalat na tiles', 'broken glass', 'basagang salamin',
                    'electrical hazard', 'nakakakuryente', 'exposed wires', 'nakalitaw na koryente',
                    'plumbing leak', 'water leak', 'tumutulong tubig', 'basag na tubo',
                    'gas leak', 'amoy gas', 'smell of gas', 'gas na nakakalat',
                    'falling objects', 'nahuhulog na bagay', 'unsafe structure', 'delikadong gusali',
                    'blocked emergency exit', 'naharang na exit', 'blocked fire exit',
                    
                    // Environmental hazards
                    'flooding', 'baha', 'tubig baha', 'flood in building', 'baha sa gusali',
                    'tree fall', 'bumagsak na puno', 'falling tree branches', 'nahuhulog na sanga',
                    'mudslide', 'landslide', 'gumuho ang lupa', 'dangerous ground condition',
                    'power line down', 'nahulog na poste ng kuryente', 'electrical wire down',
                    'animal intrusion', 'nakapaasok na hayop', 'dangerous animal', 'delikadong hayop',
                    'snake', 'ahas', 'nakita ang ahas', 'venomous snake', 'nakagat ng ahas',
                    'stray dogs', 'galang aso', 'aggressive dog', 'nangangagat na aso',
                    'insect swarm', 'libu-libong insekto', 'bee swarm', 'maraming bubuyog',
                    
                    // Equipment and facility failures
                    'equipment malfunction', 'sira ang equipment', 'machine breakdown', 'sira ang makina',
                    'elevator stuck', 'na-stuck sa elevator', 'elevator broken', 'sira ang elevator',
                    'escalator accident', 'aksidente sa escalator', 'escalator malfunction',
                    'laboratory equipment failure', 'sira ang lab equipment', 'microscope broken',
                    'projector not working', 'hindi gumagana ang projector', 'sound system failure',
                    'air conditioning failure', 'sira ang aircon', 'heating system failure',
                    
                    // Food safety
                    'food poisoning', 'nalason sa pagkain', 'spoiled food', 'masama na pagkain',
                    'contaminated food', 'nadumihan na pagkain', 'foodborne illness', 'sakit galing sa pagkain',
                    'allergic reaction to food', 'allergy sa pagkain', 'food contamination',
                    'expired food served', 'expired na pagkain', 'foreign object in food', 'may halong iba sa pagkain',
                    'cafeteria accident', 'aksidente sa cafeteria', 'kitchen fire', 'sunog sa kusina',
                    'food service problem', 'problema sa food service', 'unsanitary conditions', 'marumi na kondisyon',
                    'hot oil spill', 'nabuhusan ng mainit na mantika', 'kitchen equipment accident'
                ],
                
                // 5. NATURAL DISASTERS & EMERGENCY EVENTS
                'natural_disasters_emergency' => [
                    // Natural disasters
                    'earthquake', 'lindol', 'ground shaking', 'yanig ng lupa', 'seismic activity',
                    'aftershock', 'pagkakalindog', 'building swayed', 'umalog ang gusali',
                    'typhoon', 'bagyo', 'storm', 'unos', 'hurricane', 'strong winds', 'malakas na hangin',
                    'tornado', 'tornado', 'hail', 'yelo', 'lightning strike', 'kidlat na tumama',
                    'severe rain', 'malakas na ulan', 'flash flood', 'biglaang baha',
                    'severe weather', 'malakas na weather', 'storm damage', 'nasira ng bagyo',
                    
                    // Emergency events
                    'evacuation', 'pag-evacuate', 'emergency drill', 'emergency evacuation',
                    'bomb threat', 'bomb scare', 'banta ng bomba', 'suspicious package', 'lockdown',
                    'shelter in place', 'mag-shelter', 'emergency alert', 'emergency announcement'
                ],
                
                // 6. TECHNOLOGY / CYBER INCIDENTS
                'technology_cyber' => [
                    // Cyber security
                    'data breach', 'na-breach ang data', 'hacking', 'na-hack', 'cyber attack',
                    'unauthorized access', 'pumasok nang walang paalam sa system', 'password stolen',
                    'identity theft', 'ninakaw ang identity', 'phishing', 'scam email',
                    'malware', 'virus sa computer', 'ransomware', 'locked computer system',
                    
                    // System outages
                    'power outage', 'brownout', 'blackout', 'nawalan ng kuryente',
                    'internet down', 'walang internet', 'network failure', 'nawalan ng network',
                    'system crash', 'na-crash ang system', 'server down', 'walang access sa server',
                    'phone system down', 'hindi gumagana ang telephone', 'communication failure'
                ],
                
                // 7. ADMINISTRATIVE / POLICY VIOLATIONS
                'administrative_policy' => [
                    // Transportation issues
                    'transport delay', 'naantala ang transport', 'bus cancellation', 'na-cancel ang bus',
                    'no transportation', 'walang masasakyan', 'stranded students', 'naiwang estudyante',
                    'overcrowded bus', 'masikip na bus', 'unsafe transport', 'delikadong transport'
                ],
                
                // 8. LOST AND FOUND
                'lost_found' => [
                    'lost', 'nawala', 'missing', 'nawawala', 'can\'t find', 'hindi ko makita',
                    'misplaced', 'naligaw', 'left behind', 'naiwanan', 'forgot', 'nakalimutan',
                    'dropped', 'nahulog', 'fell out', 'napadpad', 'lost property', 'nawala property'
                ]
            ];
            
            // Priority order for detection (most critical first)
            $priorityOrder = [
                'medical_health',                    // Highest priority - health and safety
                'natural_disasters_emergency',       // Emergency situations
                'safety_security',                   // Security threats
                'environmental_facility',            // Facility hazards
                'behavioral_disciplinary',           // Behavioral issues
                'technology_cyber',                  // Tech incidents
                'administrative_policy',             // Policy issues
                'lost_found'                        // Lowest priority - lost items
            ];
            
            // Check for patterns with priority
            foreach ($priorityOrder as $type) {
                if (isset($typePatterns[$type])) {
                    foreach ($typePatterns[$type] as $pattern) {
                        if (str_contains($message, $pattern)) {
                            return $typeDisplayNames[$type];
                        }
                    }
                }
            }
            
            return null;
        }
    private function detectIncidentTypeWithContext(string $message, array $conversationHistory, string $language): ?string
        {
            // First try normal detection
            $incidentType = $this->extractIncidentType($message, $language);
            
            if ($incidentType) {
                return $incidentType;
            }
            
            // If no direct match, analyze conversation context
            $recentMessages = array_slice($conversationHistory, -3);
            $contextText = $message . ' ' . implode(' ', array_column($recentMessages, 'message'));
            
            // Try detection with broader context
            $contextIncidentType = $this->extractIncidentType($contextText, $language);
            
            if ($contextIncidentType) {
                return $contextIncidentType;
            }
            
            // Last resort: check for very general incident indicators
            return $this->detectGeneralIncidentType($message, $language);
        }
        
        private function detectGeneralIncidentType(string $message, string $language): ?string
            {
                $message = strtolower($message);
                
                // General health indicators
                $healthPatterns = ['hurt', 'pain', 'sick', 'hospital', 'doctor', 'nurse', 'medical',
                                'nasasaktan', 'masakit', 'ospital', 'doktor', 'nars', 'medikal'];
                
                foreach ($healthPatterns as $pattern) {
                    if (str_contains($message, $pattern)) {
                        return 'illness_general'; // Default to general illness
                    }
                }
                
                // General safety indicators
                $safetyPatterns = ['danger', 'unsafe', 'hazard', 'emergency', 'help',
                                'delikado', 'panganib', 'emergency', 'tulong'];
                
                foreach ($safetyPatterns as $pattern) {
                    if (str_contains($message, $pattern)) {
                        return 'environmental_hazard'; // Default to environmental hazard
                    }
                }
                
                // General behavioral indicators
                $behaviorPatterns = ['problem', 'trouble', 'issue', 'conflict', 'dispute',
                                    'problema', 'gulo', 'away', 'hindi pagkakaintindihan'];
                
                foreach ($behaviorPatterns as $pattern) {
                    if (str_contains($message, $pattern)) {
                        return 'violence_moderate'; // Default to moderate behavioral issue
                    }
                }
                
                return null;
            }


    
    /**
     * Enhanced location extraction with comprehensive Filipino patterns
     */
     private function extractLocation(string $message, string $language): ?string
        {
            $message = strtolower($message);
            
            // Define all school locations from the coordinate system with exact names
            $schoolLocations = [
                // Ground Floor - exact matches from coordinates
                'restroom 1 (ground floor)' => 'RESTROOM 1 (Ground Floor)',
                'restroom 1' => 'RESTROOM 1 (Ground Floor)',
                'foodcourt 5' => 'FOODCOURT 5',
                'foodcourt 6' => 'FOODCOURT 6',
                'foodcourt 7' => 'FOODCOURT 7',
                'stairs 2 (ground floor)' => 'STAIRS 2 (Ground Floor)',
                'stairs 2' => 'STAIRS 2 (Ground Floor)',
                'foodcourt 8' => 'FOODCOURT 8',
                'foodcourt 9' => 'FOODCOURT 9',
                'foodcourt 10' => 'FOODCOURT 10',
                'foodcourt 11' => 'FOODCOURT 11',
                'stairs 3 (ground floor)' => 'STAIRS 3 (Ground Floor)',
                'stairs 3' => 'STAIRS 3 (Ground Floor)',
                'restroom 2 (ground floor)' => 'RESTROOM 2 (Ground Floor)',
                'restroom 2' => 'RESTROOM 2 (Ground Floor)',
                'center for research and development' => 'CENTER FOR RESEARCH AND DEVELOPMENT',
                'office of the vice president' => 'OFFICE OF THE VICE PRESIDENT',
                'stairs 4 (ground floor)' => 'STAIRS 4 (Ground Floor)',
                'stairs 4' => 'STAIRS 4 (Ground Floor)',
                'electrical room' => 'ELECTRICAL ROOM',
                'social media department' => 'SOCIAL MEDIA DEPARTMENT',
                'tle 1' => 'TLE 1',
                'tle 2' => 'TLE 2',
                'guidance office' => 'GUIDANCE OFFICE',
                'clinic' => 'CLINIC',
                'restroom 3 (ground floor)' => 'RESTROOM 3 (Ground Floor)',
                'restroom 3' => 'RESTROOM 3 (Ground Floor)',
                'ssc & ssg office' => 'SSC & SSG OFFICE',
                'ssc and ssg office' => 'SSC & SSG OFFICE',
                'stairs 5 (ground floor)' => 'STAIRS 5 (Ground Floor)',
                'stairs 5' => 'STAIRS 5 (Ground Floor)',
                'gym' => 'GYM',
                'gymnasium' => 'GYM',
                'storage gf' => 'STORAGE GF',
                'restroom 4 (ground floor)' => 'RESTROOM 4 (Ground Floor)',
                'restroom 4' => 'RESTROOM 4 (Ground Floor)',
                'stairs 6 (ground floor)' => 'STAIRS 6 (Ground Floor)',
                'stairs 6' => 'STAIRS 6 (Ground Floor)',
                'principal office' => 'PRINCIPAL OFFICE',
                'ascendens asia' => 'ASCENDENS ASIA',
                'chapel' => 'CHAPEL',
                'prefect of discipline' => 'PREFECT OF DISCIPLINE',
                'safety and security' => 'SAFETY AND SECURITY',
                'foodcourt 1' => 'FOODCOURT 1',
                'foodcourt 2' => 'FOODCOURT 2',
                'foodcourt 3' => 'FOODCOURT 3',
                'foodcourt 4' => 'FOODCOURT 4',
                'stairs 1 (ground floor)' => 'STAIRS 1 (Ground Floor)',
                'stairs 1' => 'STAIRS 1 (Ground Floor)',
                
                // Second Floor - exact matches from coordinates
                'restroom 1 (second floor)' => 'RESTROOM 1 (SECOND FLOOR)',
                'room 211' => 'ROOM 211',
                'room 212' => 'ROOM 212',
                'room 213' => 'ROOM 213',
                'room 214' => 'ROOM 214',
                'stairs 2 (second floor)' => 'STAIRS 2 (SECOND FLOOR)',
                'room 215' => 'ROOM 215',
                'room 216' => 'ROOM 216',
                'room 217' => 'ROOM 217',
                'room 218' => 'ROOM 218',
                'room 219' => 'ROOM 219',
                'room 220' => 'ROOM 220',
                'stairs 3 (second floor)' => 'STAIRS 3 (SECOND FLOOR)',
                'speech lab' => 'SPEECH LAB',
                'room 221' => 'ROOM 221',
                'room 222' => 'ROOM 222',
                'room 223' => 'ROOM 223',
                'room 224' => 'ROOM 224',
                'room 225' => 'ROOM 225',
                'room 226' => 'ROOM 226',
                'stairs 4 (second floor)' => 'STAIRS 4 (SECOND FLOOR)',
                'room 227' => 'ROOM 227',
                'room 228' => 'ROOM 228',
                'room 229' => 'ROOM 229',
                'room 230' => 'ROOM 230',
                'room 231' => 'ROOM 231',
                'room 232' => 'ROOM 232',
                'computer lab' => 'COMPUTER LAB',
                'faculty room (second floor)' => 'FACULTY ROOM (SECOND FLOOR)',
                'stairs 5 (second floor)' => 'STAIRS 5 (SECOND FLOOR)',
                'stairs 6 (second floor)' => 'STAIRS 6 (SECOND FLOOR)',
                'strand head' => 'STRAND HEAD',
                'room 201' => 'ROOM 201',
                'gender and development' => 'GENDER AND DEVELOPMENT',
                'room 202' => 'ROOM 202',
                'room 203' => 'ROOM 203',
                'room 204' => 'ROOM 204',
                'room 205' => 'ROOM 205',
                'acer' => 'ACER',
                'room 206' => 'ROOM 206',
                'room 207' => 'ROOM 207',
                'room 208' => 'ROOM 208',
                'room 209' => 'ROOM 209',
                'room 210' => 'ROOM 210',
                'its' => 'ITS',
                'stairs 1 (second floor)' => 'STAIRS 1 (SECOND FLOOR)',
                
                // Third Floor - exact matches from coordinates
                'restroom 1 (third floor)' => 'RESTROOM 1 (THIRD FLOOR)',
                'room 311' => 'ROOM 311',
                'room 312' => 'ROOM 312',
                'room 313' => 'ROOM 313',
                'room 314' => 'ROOM 314',
                'stairs 2 (third floor)' => 'STAIRS 2 (THIRD FLOOR)',
                'room 315' => 'ROOM 315',
                'room 316' => 'ROOM 316',
                'room 317' => 'ROOM 317',
                'room 318' => 'ROOM 318',
                'room 319' => 'ROOM 319',
                'room 320' => 'ROOM 320',
                'stairs 3 (third floor)' => 'STAIRS 3 (THIRD FLOOR)',
                'restroom 2 (third floor)' => 'RESTROOM 2 (THIRD FLOOR)',
                'room 321' => 'ROOM 321',
                'room 322' => 'ROOM 322',
                'room 323' => 'ROOM 323',
                'room 324' => 'ROOM 324',
                'room 325' => 'ROOM 325',
                'room 326' => 'ROOM 326',
                'stairs 4 (third floor)' => 'STAIRS 4 (THIRD FLOOR)',
                'bsba department' => 'BSBA DEPARTMENT',
                'faculty room 1 (third floor)' => 'FACULTY ROOM 1 (THIRD FLOOR)',
                'room 327' => 'ROOM 327',
                'room 328' => 'ROOM 328',
                'room 329' => 'ROOM 329',
                'room 330' => 'ROOM 330',
                'room 331' => 'ROOM 331',
                'room 332' => 'ROOM 332',
                'faculty room 2 (third floor)' => 'FACULTY ROOM 2 (THIRD FLOOR)',
                'stairs 5 (third floor)' => 'STAIRS 5 (THIRD FLOOR)',
                'restroom 3 (third floor)' => 'RESTROOM 3 (THIRD FLOOR)',
                'library (third floor)' => 'LIBRARY (THIRD FLOOR)',
                'stairs 6 (third floor)' => 'STAIRS 6 (THIRD FLOOR)',
                'room 301' => 'ROOM 301',
                'room 302' => 'ROOM 302',
                'room 303' => 'ROOM 303',
                'room 304' => 'ROOM 304',
                'room 305' => 'ROOM 305',
                'physics lab' => 'PHYSICS LAB',
                'room 306' => 'ROOM 306',
                'room 307' => 'ROOM 307',
                'room 308' => 'ROOM 308',
                'room 309' => 'ROOM 309',
                'room 310' => 'ROOM 310',
                'stairs 1 (third floor)' => 'STAIRS 1 (THIRD FLOOR)',
                
                // Fourth Floor - exact matches from coordinates
                'restroom 1 (fourth floor)' => 'RESTROOM 1 (FOURTH FLOOR)',
                'room 411' => 'ROOM 411',
                'room 412' => 'ROOM 412',
                'room 413' => 'ROOM 413',
                'room 414' => 'ROOM 414',
                'stairs 2 (fourth floor)' => 'STAIRS 2 (FOURTH FLOOR)',
                'room 415' => 'ROOM 415',
                'room 416' => 'ROOM 416',
                'room 417' => 'ROOM 417',
                'room 418' => 'ROOM 418',
                'room 419' => 'ROOM 419',
                'room 420' => 'ROOM 420',
                'stairs 3 (fourth floor)' => 'STAIRS 3 (FOURTH FLOOR)',
                'room 421' => 'ROOM 421',
                'room 422' => 'ROOM 422',
                'room 423' => 'ROOM 423',
                'room 424' => 'ROOM 424',
                'room 425' => 'ROOM 425',
                'room 426' => 'ROOM 426',
                'room 427' => 'ROOM 427',
                'stairs 4 (fourth floor)' => 'STAIRS 4 (FOURTH FLOOR)',
                'hbm head (fourth floor)' => 'HBM HEAD (FOURTH FLOOR)',
                'room 428' => 'ROOM 428',
                'room 429' => 'ROOM 429',
                'room 430' => 'ROOM 430',
                'room 431' => 'ROOM 431',
                'room 432' => 'ROOM 432',
                'room 433' => 'ROOM 433',
                'room 434' => 'ROOM 434',
                'faculty room (fourth floor)' => 'FACULTY ROOM (FOURTH FLOOR)',
                'stairs 5 (fourth floor)' => 'STAIRS 5 (FOURTH FLOOR)',
                'restroom 3 (fourth floor)' => 'RESTROOM 3 (FOURTH FLOOR)',
                'library (fourth floor)' => 'LIBRARY (FOURTH FLOOR)',
                'stairs 6 (fourth floor)' => 'STAIRS 6 (FOURTH FLOOR)',
                'room 401' => 'ROOM 401',
                'room 402' => 'ROOM 402',
                'room 403' => 'ROOM 403',
                'room 404' => 'ROOM 404',
                'room 405' => 'ROOM 405',
                'chemistry lab' => 'CHEMISTRY LAB',
                'room 406' => 'ROOM 406',
                'room 407' => 'ROOM 407',
                'room 408' => 'ROOM 408',
                'room 409' => 'ROOM 409',
                'room 410' => 'ROOM 410',
                'stairs 1 (fourth floor)' => 'STAIRS 1 (FOURTH FLOOR)',
                
                // Fifth Floor - exact matches from coordinates
                'room 510' => 'ROOM 510',
                'room 511' => 'ROOM 511',
                'room 512' => 'ROOM 512',
                'room 513' => 'ROOM 513',
                'stairs 2 (fifth floor)' => 'STAIRS 2 (FIFTH FLOOR)',
                'room 514' => 'ROOM 514',
                'room 515' => 'ROOM 515',
                'room 516' => 'ROOM 516',
                'room 517' => 'ROOM 517',
                'room 518' => 'ROOM 518',
                'room 519' => 'ROOM 519',
                'stairs 3 (fifth floor)' => 'STAIRS 3 (FIFTH FLOOR)',
                'room 520' => 'ROOM 520',
                'room 521' => 'ROOM 521',
                'room 522' => 'ROOM 522',
                'room 523' => 'ROOM 523',
                'room 524' => 'ROOM 524',
                'room 525' => 'ROOM 525',
                'abm faculty' => 'ABM FACULTY',
                'stairs 4 (fifth floor)' => 'STAIRS 4 (FIFTH FLOOR)',
                'hbm head (fifth floor)' => 'HBM HEAD (FIFTH FLOOR)',
                'room 526' => 'ROOM 526',
                'room 527' => 'ROOM 527',
                'room 528' => 'ROOM 528',
                'room 529' => 'ROOM 529',
                'room 530' => 'ROOM 530',
                'room 531' => 'ROOM 531',
                'room 532' => 'ROOM 532',
                'publication' => 'PUBLICATION',
                'stairs 5 (fifth floor)' => 'STAIRS 5 (FIFTH FLOOR)',
                'stairs 6 (fifth floor)' => 'STAIRS 6 (FIFTH FLOOR)',
                'room 501' => 'ROOM 501',
                'room 502' => 'ROOM 502',
                'room 503' => 'ROOM 503',
                'room 504' => 'ROOM 504',
                'css department faculty room' => 'CSS DEPARTMENT FACULTY ROOM',
                'room 505' => 'ROOM 505',
                'room 506' => 'ROOM 506',
                'room 507' => 'ROOM 507',
                'room 508' => 'ROOM 508',
                'room 509' => 'ROOM 509',
                'lab 3 (fifth floor)' => 'LAB 3 (FIFTH FLOOR)',
                'lab 4 (fifth floor)' => 'LAB 4 (FIFTH FLOOR)',
                'lab 5 (fifth floor)' => 'LAB 5 (FIFTH FLOOR)',
                'css department' => 'CSS DEPARTMENT',
                'lab 6 (fifth floor)' => 'LAB 6 (FIFTH FLOOR)',
                'lab 7 (fifth floor)' => 'LAB 7 (FIFTH FLOOR)',
                'stairs 1 (fifth floor)' => 'STAIRS 1 (FIFTH FLOOR)',
                'lab 8 (fifth floor)' => 'LAB 8 (FIFTH FLOOR)',
                'lab 1 (fifth floor)' => 'LAB 1 (FIFTH FLOOR)',
                'lab 2 (fifth floor)' => 'LAB 2 (FIFTH FLOOR)',
                'case department fr' => 'CASE DEPARTMENT FR',
                'case department ho' => 'CASE DEPARTMENT HO',
            ];
            
            // Common aliases and variations for better matching
            $aliases = [
                // General terms
                'canteen' => 'foodcourt',
                'cafeteria' => 'foodcourt', 
                'kainan' => 'foodcourt',
                'kantina' => 'foodcourt',
                'banyo' => 'restroom',
                'bathroom' => 'restroom',
                'toilet' => 'restroom',
                'cr' => 'restroom',
                'comfort room' => 'restroom',
                'hagdan' => 'stairs',
                'staircase' => 'stairs',
                'stairway' => 'stairs',
                'aklatan' => 'library',
                'silid-aklatan' => 'library',
                'laboratoryo' => 'lab',
                'laboratory' => 'lab',
                'klinika' => 'clinic',
                'medical office' => 'clinic',
                'nurse station' => 'clinic',
                'first aid' => 'clinic',
                'silid-aralan' => 'room',
                'klase' => 'room',
                'silid ng klase' => 'room',
                'classroom' => 'room',
                'opisina' => 'office',
                'tanggapan' => 'office',
                'computer room' => 'computer lab',
                'comp lab' => 'computer lab',
                'science room' => 'lab',
                'paradahan' => 'parking',
                'parking area' => 'parking',
                'garage' => 'parking',
                'principal' => 'principal office',
                'guidance' => 'guidance office',
                'discipline' => 'prefect of discipline',
                'chapel area' => 'chapel',
                'church' => 'chapel',
                'kapilya' => 'chapel',
                'research center' => 'center for research and development',
                'research and development' => 'center for research and development',
                'vice president office' => 'office of the vice president',
                'vp office' => 'office of the vice president',
                'ssg office' => 'ssc & ssg office',
                'ssc office' => 'ssc & ssg office',
                'student government' => 'ssc & ssg office',
                'faculty' => 'faculty room',
                'teachers room' => 'faculty room',
                'chemistry laboratory' => 'chemistry lab',
                'chem lab' => 'chemistry lab',
                'physics laboratory' => 'physics lab',
                'phys lab' => 'physics lab',
                'publication office' => 'publication',
                'case dept' => 'case department',
                'css dept' => 'css department',
                'hbm head office' => 'hbm head',
                'bsba dept' => 'bsba department',
                
                // Floor references
                'ground floor' => 'ground',
                'first floor' => 'ground',
                'gf' => 'ground',
                '1st floor' => 'ground',
                '2nd floor' => 'second',
                'second floor' => 'second',
                '3rd floor' => 'third',
                'third floor' => 'third',
                '4th floor' => 'fourth',
                'fourth floor' => 'fourth',
                '5th floor' => 'fifth',
                'fifth floor' => 'fifth'
            ];
            
            // Filipino prepositions that might precede locations
            $prepositions = ['sa', 'nasa', 'sa loob ng', 'sa labas ng', 'malapit sa', 'tabi ng', 'doon sa',
                            'in', 'at', 'inside', 'outside', 'near', 'beside', 'in the', 'at the', 'to the', 'go to'];
            
            // Remove prepositions from the beginning of message for cleaner matching
            foreach ($prepositions as $prep) {
                if (str_starts_with($message, $prep . ' ')) {
                    $message = substr($message, strlen($prep) + 1);
                    break;
                }
            }
            
            // First, check for direct exact matches (prioritize longest matches first)
            $sortedLocations = $schoolLocations;
            uksort($sortedLocations, function($a, $b) {
                return strlen($b) - strlen($a); // Sort by length descending
            });
            
            foreach ($sortedLocations as $key => $location) {
                if (str_contains($message, $key)) {
                    return $location;
                }
            }
            
            // Check for room number patterns with floor context
            if (preg_match('/(?:room|silid|kwarto)?\s*#?\s*([2-5])(\d{2})/i', $message, $matches)) {
                $floor = $matches[1];
                $roomNum = $matches[1] . $matches[2];
                $roomKey = "room $roomNum";
                
                if (isset($schoolLocations[$roomKey])) {
                    return $schoolLocations[$roomKey];
                }
                return "ROOM $roomNum"; // Return formatted room number even if not in our list
            }
            
            // Check aliases and map them to actual locations
            foreach ($aliases as $alias => $mapped) {
                if (str_contains($message, $alias)) {
                    // Handle specific mappings
                    if ($mapped === 'foodcourt') {
                        // Extract number if present
                        if (preg_match('/(\d+)/', $message, $matches)) {
                            $num = $matches[1];
                            $foodcourtKey = "foodcourt $num";
                            if (isset($schoolLocations[$foodcourtKey])) {
                                return $schoolLocations[$foodcourtKey];
                            }
                        }
                        // Look for any foodcourt mention and return the first one found
                        foreach ($schoolLocations as $key => $location) {
                            if (str_contains($key, 'foodcourt')) {
                                return $location;
                            }
                        }
                    }
                    
                    if ($mapped === 'restroom') {
                        // Try to determine floor from context
                        $floorContext = null;
                        foreach (['ground', 'second', 'third', 'fourth', 'fifth'] as $floor) {
                            if (str_contains($message, $floor)) {
                                $floorContext = $floor;
                                break;
                            }
                        }
                        
                        // Extract number if present
                        if (preg_match('/(\d+)/', $message, $matches)) {
                            $num = $matches[1];
                            if ($floorContext) {
                                $restroomKey = "restroom $num ($floorContext floor)";
                                if (isset($schoolLocations[$restroomKey])) {
                                    return $schoolLocations[$restroomKey];
                                }
                            }
                            $restroomKey = "restroom $num";
                            if (isset($schoolLocations[$restroomKey])) {
                                return $schoolLocations[$restroomKey];
                            }
                        }
                        
                        // Return first restroom if no specific number
                        foreach ($schoolLocations as $key => $location) {
                            if (str_contains($key, 'restroom 1')) {
                                return $location;
                            }
                        }
                    }
                    
                    if ($mapped === 'stairs') {
                        // Extract number if present
                        if (preg_match('/(\d+)/', $message, $matches)) {
                            $num = $matches[1];
                            // Try to determine floor from context
                            foreach (['ground', 'second', 'third', 'fourth', 'fifth'] as $floor) {
                                if (str_contains($message, $floor)) {
                                    $stairsKey = "stairs $num ($floor floor)";
                                    if (isset($schoolLocations[$stairsKey])) {
                                        return $schoolLocations[$stairsKey];
                                    }
                                }
                            }
                            $stairsKey = "stairs $num";
                            if (isset($schoolLocations[$stairsKey])) {
                                return $schoolLocations[$stairsKey];
                            }
                        }
                    }
                    
                    if ($mapped === 'lab') {
                        // Extract number and floor if present
                        if (preg_match('/(\d+)/', $message, $matches)) {
                            $num = $matches[1];
                            foreach (['fifth', 'fourth', 'third'] as $floor) {
                                if (str_contains($message, $floor)) {
                                    $labKey = "lab $num ($floor floor)";
                                    if (isset($schoolLocations[$labKey])) {
                                        return $schoolLocations[$labKey];
                                    }
                                }
                            }
                        }
                        
                        // Check for specific lab types
                        if (str_contains($message, 'physics')) {
                            return 'PHYSICS LAB';
                        }
                        if (str_contains($message, 'chemistry') || str_contains($message, 'chem')) {
                            return 'CHEMISTRY LAB';
                        }
                        if (str_contains($message, 'computer') || str_contains($message, 'comp')) {
                            return 'COMPUTER LAB';
                        }
                        if (str_contains($message, 'speech')) {
                            return 'SPEECH LAB';
                        }
                    }
                    
                    // For other mapped terms, check if they exist in our locations
                    foreach ($schoolLocations as $key => $location) {
                        if (str_contains($key, $mapped)) {
                            return $location;
                        }
                    }
                }
            }
            
            // Check for simple room number without "room" prefix
            if (preg_match('/\b([2-5])(\d{2})\b/', $message, $matches)) {
                $roomNum = $matches[1] . $matches[2];
                $roomKey = "room $roomNum";
                if (isset($schoolLocations[$roomKey])) {
                    return $schoolLocations[$roomKey];
                }
                return "ROOM $roomNum";
            }
            
            // Check for floor-only references
            foreach (['ground floor', 'second floor', 'third floor', 'fourth floor', 'fifth floor', 
                    'ground', 'second', 'third', 'fourth', 'fifth',
                    '1st floor', '2nd floor', '3rd floor', '4th floor', '5th floor',
                    'gf', '2f', '3f', '4f', '5f'] as $floorRef) {
                if (str_contains($message, $floorRef)) {
                    $floorName = str_replace(['1st', '2nd', '3rd', '4th', '5th', 'gf', '2f', '3f', '4f', '5f'], 
                                        ['ground', 'second', 'third', 'fourth', 'fifth', 'ground', 'second', 'third', 'fourth', 'fifth'], 
                                        $floorRef);
                    $floorName = str_replace(' floor', '', $floorName);
                    return ucfirst($floorName) . ' Floor';
                }
            }
            
            return null;
        }
    /**
     * Enhanced reporter name extraction with Filipino patterns and standalone name detection
     */
    private function extractReporterName(string $message, string $language): ?string
    {
    $message = trim($message);
    
    // First, try pattern-based extraction (existing logic)
    $patterns = [
        // English patterns
        '/(?:my name is|i am|i\'m|call me|this is)\s+([a-zA-Z\s\.]+)(?:\s|$|\.)/i',
        '/name:\s*([a-zA-Z\s\.]+)(?:\s|$)/i',
        
        // Filipino patterns
        '/(?:ako si|ako ay|pangalan ko ay|pangalan ko|tawag sakin|kilala ako bilang)\s+([a-zA-Z\s\.]+)(?:\s|$|\.)/i',
        '/(?:si|ako)\s+([A-Z][a-zA-Z\s\.]{2,})(?:\s+ang|$|\.|,)/i',
        '/pangalan:\s*([a-zA-Z\s\.]+)(?:\s|$)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $message, $matches)) {
            $name = trim($matches[1]);
            $cleanedName = $this->cleanExtractedName($name);
            if ($cleanedName) {
                return $cleanedName;
            }
        }
    }
    
    // If no pattern matches, check if the entire message looks like a standalone name
    $standaloneNameCandidate = $this->extractStandaloneName($message);
    if ($standaloneNameCandidate) {
        return $standaloneNameCandidate;
    }
    
    return null;
    }

    /**
     * Improved standalone name extraction with more lenient validation
     */
    private function extractStandaloneName(string $message): ?string
    {
    $message = trim($message);
    
    // Skip if message is too long (likely not just a name)
    if (strlen($message) > 50) {
        return null;
    }
    
    // Skip if contains question words or other indicators it's not a name
    $nonNameIndicators = [
        // English
        'what', 'where', 'when', 'how', 'why', 'who', 'which', 'can', 'could', 'would', 'should',
        'the', 'incident', 'happened', 'accident', 'help', 'report', 'building', 'phone',
        'number', 'email', 'contact', 'address', 'time', 'date', 'yesterday', 'today',
        'room', 'floor', 'stairs', 'hallway', 'playground', 'clinic', 'office', 'library',
        'injured', 'hurt', 'sick', 'fell', 'slipped', 'cut', 'broken',
        // Filipino
        'ano', 'saan', 'kailan', 'paano', 'bakit', 'sino', 'alin', 'pwede', 'maaari', 'dapat',
        'ang', 'nangyari', 'aksidente', 'tulong', 'report', 'kwarto', 'gusali', 'numero',
        'telepono', 'email', 'kontak', 'lugar', 'oras', 'petsa', 'kahapon', 'ngayon',
        'sa', 'nasa', 'dito', 'doon', 'malapit', 'malayo', 'hagdan', 'pasilyo',
        'nasugatan', 'nasaktan', 'maysakit', 'nahulog', 'nadulas', 'nauntog'
    ];
    
    $lowerMessage = strtolower($message);
    foreach ($nonNameIndicators as $indicator) {
        if (str_contains($lowerMessage, $indicator)) {
            return null;
        }
    }
    
    // Check if it looks like a name pattern
    if ($this->looksLikeName($message)) {
        return $this->cleanExtractedName($message);
    }
    
    return null;
    }

    /**
     * Improved name validation with more lenient criteria for standalone names
     */
    private function looksLikeName(string $text): bool
    {
    $text = trim($text);
    
    // Must contain only letters, spaces, dots, hyphens, apostrophes, and common name characters
    if (!preg_match('/^[a-zA-Z\s\.\-\']+$/', $text)) {
        return false;
    }
    
    // Must be between 2 and 50 characters
    if (strlen($text) < 2 || strlen($text) > 50) {
        return false;
    }
    
    // Should have at least one letter
    if (!preg_match('/[a-zA-Z]/', $text)) {
        return false;
    }
    
    // Split into words
    $words = preg_split('/\s+/', $text);
    
    // Should have 1-5 words (reasonable for most names, including middle names)
    if (count($words) < 1 || count($words) > 5) {
        return false;
    }
    
    // Each word should be reasonable name length (1-20 characters to allow initials and longer names)
    foreach ($words as $word) {
        $word = trim($word, '.');
        if (strlen($word) < 1 || strlen($word) > 20) {
            return false;
        }
    }
    
    // More lenient check: if it has multiple words with proper capitalization pattern, likely a name
    if (count($words) >= 2) {
        $properlyCapitalized = 0;
        foreach ($words as $word) {
            if (preg_match('/^[A-Z][a-z]*$/', $word) || preg_match('/^[A-Z]\.?$/', $word)) {
                $properlyCapitalized++;
            }
        }
        
        // If at least half the words are properly capitalized, consider it a name
        if ($properlyCapitalized >= count($words) / 2) {
            return true;
        }
    }
    
    // Single word check: should start with capital letter and be reasonable length
    if (count($words) === 1) {
        $word = $words[0];
        if (preg_match('/^[A-Z][a-z]{1,}$/', $word) && strlen($word) >= 2) {
            return true;
        }
    }
    
    // Check against common name patterns
    if ($this->matchesCommonNamePatterns($words)) {
        return true;
    }
    
    return false;
    }

    /**
     * Check if words match common name patterns
     */
    private function matchesCommonNamePatterns(array $words): bool
    {
        // Common Filipino name prefixes/suffixes
        $filipinoNameParts = [
            'De', 'Del', 'De La', 'San', 'Santa', 'Ng', 'Tan', 'Lim', 'Go', 'Sy', 'Co',
            'Jr.', 'Sr.', 'III', 'IV', 'Jr', 'Sr'
        ];
        
        // Common Western name prefixes/suffixes
        $westernNameParts = [
            'Mc', 'Mac', 'O\'', 'Van', 'Von', 'De', 'Da', 'Di', 'Du',
            'Jr.', 'Sr.', 'III', 'IV', 'Jr', 'Sr'
        ];
        
        $allNameParts = array_merge($filipinoNameParts, $westernNameParts);
        
        foreach ($words as $word) {
            foreach ($allNameParts as $namePart) {
                if (stripos($word, $namePart) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Clean and validate extracted name
     */
    private function cleanExtractedName(string $name): ?string
    {
    $name = trim($name);
    
    // Remove common words that might be captured
    $excludeWords = [
        'po', 'ako', 'ang', 'na', 'ay', 'sa', 'ng', 'the', 'and', 'or', 
        'student', 'teacher', 'staff', 'faculty', 'is', 'am', 'my', 'name',
        'si', 'ni', 'kay', 'para'
    ];
    
    $nameParts = preg_split('/\s+/', $name);
    $cleanName = [];
    
    foreach ($nameParts as $part) {
        $part = trim($part, '.,');
        if (!in_array(strtolower($part), $excludeWords) && strlen($part) > 0) {
            // Capitalize first letter of each word, preserve original case for rest
            // This handles cases like "McArthur" or "O'Brien" better
            if (preg_match('/^[a-z]/', $part)) {
                $cleanName[] = ucfirst(strtolower($part));
            } else {
                $cleanName[] = $part; // Keep original capitalization if already capitalized
            }
        }
    }
    
    $result = implode(' ', $cleanName);
    
    // Final validation - more lenient
    if (strlen($result) >= 2 && 
        preg_match('/[a-zA-Z]/', $result) && 
        !preg_match('/\d/', $result)) { // No numbers allowed
        return $result;
    }
    
    return null;
    }

    /**
     * Enhanced context-aware name extraction
     * This method should be called when we specifically expect a name (e.g., after asking "What's your name?")
     */
    private function extractNameFromContext(string $message, array $context): ?string
    {
        // Check if the conversation context suggests we're expecting a name
        $expectingName = false;
        
        if (isset($context['last_question_field']) && $context['last_question_field'] === 'reported_by') {
            $expectingName = true;
        }
        
        // If we asked for a name in the previous interaction, be more lenient
        if ($expectingName) {
            // Try normal extraction first
            $extractedName = $this->extractReporterName($message, $context['language_detected'] ?? 'mixed');
            if ($extractedName) {
                return $extractedName;
            }
            
            // If normal extraction fails and we're expecting a name, try standalone extraction with lower threshold
            $standaloneCandidate = $this->extractStandaloneName($message);
            if ($standaloneCandidate) {
                return $standaloneCandidate;
            }
            
            // Last resort: if the message is short and looks somewhat like a name
            if (strlen(trim($message)) <= 30 && 
                preg_match('/^[a-zA-Z\s\.]+$/', trim($message)) && 
                preg_match('/[A-Z]/', $message)) {
                return $this->cleanExtractedName($message);
            }
        }
        
        return null;
    }

    

    /**
     * Enhanced contact information extraction
     */
    private function extractContactInfo(string $message): ?string
    {
        // Enhanced Philippine phone number patterns
        $phonePatterns = [
            '/(\+63|0063)\s*[29]\d{2}[-\s]?\d{3}[-\s]?\d{4}/', // +63 or 0063 format
            '/09\d{2}[-\s]?\d{3}[-\s]?\d{4}/', // 09 format
            '/\b9\d{2}[-\s]?\d{3}[-\s]?\d{4}\b/', // 9 format (without 0)
            '/\b0\d{10}\b/', // 11-digit starting with 0
            '/\b9\d{9}\b/', // 10-digit starting with 9
            // Landline patterns
            '/\(\d{2,4}\)\s?\d{3}[-\s]?\d{4}/', // Area code in parentheses
            '/\d{2,4}[-\s]?\d{3}[-\s]?\d{4}/' // Simple landline format
        ];
        
        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return $matches[0];
            }
        }
        
        // Email pattern
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $matches)) {
            return $matches[0];
        }
        
        return null;
    }

        /**
     * Enhanced ESI level calculation with proper categorization for school incidents
     */
    private function calculateESILevel(string $incidentType, string $description): int
        {
            $description = strtolower($description);
            
            // Level 1: EMERGENCY - Life-threatening, requires immediate response/resuscitation
            $emergencyKeywords = [
                // Critical medical conditions
                'heart attack', 'cardiac arrest', 'pag-aatake sa puso', 'atake sa puso',
                'stroke', 'seizure', 'convulsion', 'kumukumpulsyon', 'unconscious', 'walang malay',
                'anaphylaxis', 'severe allergic reaction', 'can\'t breathe', 'hindi makahinga',
                'overdose', 'choking', 'naninikip', 'nasakal', 'severe bleeding', 'dumudugo nang matindi',
                'compound fracture', 'bone sticking out', 'head trauma', 'spinal injury', 'amputation',
                'third degree burns', 'severe burns', 'malubhang paso',
                
                // Weapons and serious violence
                'weapon', 'armas', 'gun', 'baril', 'knife', 'kutsilyo', 'firearm', 'pistol',
                'physical assault', 'binugbog', 'gang violence', 'weapon involved',
                
                // Emergency events
                'fire', 'sunog', 'apoy', 'flames', 'bomb threat', 'bomb scare', 'evacuation',
                'earthquake', 'lindol', 'gas leak', 'chemical spill', 'explosion'
            ];
            
            // Check for emergency keywords in description
            foreach ($emergencyKeywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return 1;
                }
            }
            
            // Check incident type for emergencies
            if (in_array($incidentType, ['Natural Disasters & Emergency Events'])) {
                return 1;
            }
            
            // Level 2: URGENT - High risk, needs prompt attention
            $urgentKeywords = [
                // Serious but not immediately life-threatening medical
                'broken bone', 'fracture', 'bali', 'dislocation', 'concussion', 'moderate bleeding',
                'second degree burn', 'paso', 'deep laceration', 'cuts requiring stitches',
                'fever', 'high fever', 'lagnat', 'food poisoning', 'allergic reaction',
                'mental health crisis', 'panic attack', 'suicidal thoughts', 'self harm',
                
                // Serious behavioral issues
                'fight', 'away', 'physical fight', 'sexual harassment', 'sexual assault',
                'drugs', 'droga', 'alcohol', 'lasing', 'physical bullying',
                
                // Facility hazards
                'ceiling collapse', 'structural damage', 'electrical hazard', 'exposed wires',
                'chemical exposure', 'toxic fumes', 'flooding', 'baha', 'power line down',
                
                // Security issues
                'theft', 'ninakaw', 'break in', 'intruder', 'unauthorized person',
                
                // Cyber security
                'data breach', 'hacking', 'cyber attack', 'malware'
            ];
            
            // Check for urgent keywords in description
            foreach ($urgentKeywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return 2;
                }
            }
            
            // Check incident type for urgent situations
            if (in_array($incidentType, [
                'Medical / Health', 
                'Safety / Security', 
                'Environmental / Facility-Related Incident',
                'Technology / Cyber Incident'
            ])) {
                return 2;
            }
            
            // Level 3: NON-URGENT - Can be handled during regular operations
            // Check incident type for non-urgent situations
            if (in_array($incidentType, [
                'Behavioral / Disciplinary',
                'Administrative / Policy Violations',
                'Lost and Found'
            ])) {
                return 3;
            }
            
            // Additional non-urgent keywords check
            $nonUrgentKeywords = [
                'minor', 'small', 'maliit', 'lost', 'nawala', 'missing', 'nawawala',
                'verbal bullying', 'name calling', 'teasing', 'cheating', 'nandadaya',
                'transport delay', 'late', 'administrative', 'policy violation'
            ];
            
            foreach ($nonUrgentKeywords as $keyword) {
                if (str_contains($description, $keyword)) {
                    return 3;
                }
            }
            
            // Default to Level 2 (Urgent) for unknown situations to err on side of caution
            return 2;
        }

       


    /**
     * Get missing required fields
     */
     private function getMissingRequiredFields(array $incidentData): array
{
    $requiredFields = ['incident_type', 'location'];
    
    // Only require name and contact if user is not logged in AND not auto-filled
    if (!auth()->check()) {
        $requiredFields[] = 'reported_by';
        $requiredFields[] = 'contact_info';
    } else {
        // If user is logged in, check if the auto-filled flags are missing
        // This ensures auto-filled data is recognized as valid
        if (empty($incidentData['reported_by']) && !isset($incidentData['_auto_filled_name'])) {
            $requiredFields[] = 'reported_by';
        }
        if (empty($incidentData['contact_info']) && !isset($incidentData['_auto_filled_contact'])) {
            $requiredFields[] = 'contact_info';
        }
    }
    
    $missing = [];
    
    foreach ($requiredFields as $field) {
        if (empty($incidentData[$field])) {
            $missing[] = $field;
        }
    }
    
    return $missing;
}

    /**
     * Enhanced response generation with better Filipino support
     */
    private function generateResponse(array $parsedResponse, array $incidentData, array $missingFields, string $language, bool $isComplete): string
    {
        if ($isComplete) {
            return $this->getCompletionMessage($language, $incidentData);
        }
        
        return $this->getNextQuestionMessage($missingFields, $language, $incidentData);
    }

    /**
     * Enhanced completion message with better Filipino
     */
    private function getCompletionMessage(string $language, array $incidentData): string
    {
        $messages = [
            'english' => "Thank you! I have collected all the necessary information for your incident report. The report has been submitted successfully and will be reviewed by the appropriate authorities.",
            'tagalog' => "Salamat po! Nakompleto ko na po ang lahat ng kinakailangang impormasyon para sa inyong incident report. Naipadala na po ang report at susuriin ng mga kinauukulang opisyal.",
            'mixed' => "Salamat po! Nakompleto ko na ang lahat ng info para sa incident report ninyo. Na-submit na successfully ang report at ire-review ng mga authorities."
        ];
        
        return $messages[$language] ?? $messages['mixed'];
    }

    /**
     * Enhanced next question message with better Filipino
     */
    private function getNextQuestionMessage(array $missingFields, string $language, array $currentData): string
        {
            if (empty($missingFields)) {
                return $this->getCompletionMessage($language, $currentData);
            }
            
            $field = $missingFields[0];
            
            $reportedBy = isset($currentData['reported_by']) && !empty($currentData['reported_by']) 
                ? $currentData['reported_by'] 
                : (auth()->check() ? auth()->user()->name : 'there');
            
            $questions = [
                'english' => [
                'incident_type' => "Hello {$reportedBy}! I'm here to help you report this incident properly. Could you tell me what happened?
            For Example:
            🏥 Health & Safety: Someone got hurt, fell, had an accident, felt sick, or there's a medical emergency
            🔥 Environmental: Fire, flooding, structural damage, or hazardous conditions  
            👥 Behavioral: Bullying, fights, harassment, or disruptive behavior
            🚨 Security: Theft, missing items, unauthorized access, or prohibited items found
            ⚡ Operational: Power outages, system failures, transport issues
            Just describe what happened in your own words",

                'location' => "Thanks for that information. Where exactly did this happen?
            For Example:
            📍 Specific rooms like \"Room 304\" or \"Science Lab B\"
            🏢 General areas like \"Main hallway\", \"Cafeteria\", or \"Gymnasium\"  
            🌳 Outdoor spaces like \"Basketball court\", \"Parking area\", or \"Near the main gate\"
            📍 Floor details - \"2nd floor stairway\" or \"Ground floor restroom\"",

                    'reported_by' => "I need to know who I'm talking to for our records. What's your full name?
                    Don't worry, this is just so we can follow up with you if needed and keep everything official. Please give me your complete name",
                
                    'contact_info' => "Perfect, thanks {$reportedBy}! Now I need a way to reach you if there are any updates or follow-up questions about this incident.
            What's the best way to contact you?
            📱 Mobile number
            📧 Email address 
            ☎️ Emergency contact 
            Just share whichever of these you're most comfortable with!",
                ],
                'tagalog' => [
                    'incident_type' => "Nandito po ako para tulungan kayo sa pag-report ng insidenteng ito. Pwede po bang ikwento ninyo kung ano ang nangyari? Kailangan ko pong maintindihan ang sitwasyon para ma-ensure na makakakuha ito ng tamang attention.
            Halimabawa:
            🏥 Kalusugan & Kaligtasan: May nasaktan, nahulog, may naaksidente, masamang karamdamanan, o medical emergency
            🔥 Kapaligiran: Sunog, baha, sirang gusali, o mga delikadong kondisyon
            👥 Ugali/Behavior: Bullying, away, panggugulo, o disruptive behavior  
            🚨 Security: Magnakaw, nawawalang gamit, hindi awtorisadong pagpasok, o may nakitang bawal na gamit (eg. baril, kutsilyo etc.)
            ⚡ Operasyon: Brownout, sirang tubo o leak
            Ikwento lang po ninyo sa sarili ninyong paraan",
    
                    'location' => "Salamat po sa information na yun. Ngayon naman, saan po eksaktong nangyari ito? Mas specific mas mabuti, matutulongan ko kayong ma-route sa kinauukulan.
            Halimbawa:
            📍 Tukoy na silid tulad ng \"Room 304\" o \"Science Lab B\"
            🏢 General areas tulad ng \"main hallway\", \"cafeteria\", o \"gymnasyum\"
            🌳 Outdoor spaces tulad ng \"basketball court\", \"parking area\", o \"malapit sa main gate\"
            📍 Floor details  - \"2nd floor na hagdan\" o \"ground floor na restroom\"
             Saan po ito nangyari?",
                    
                    'reported_by' => "Ayos po, salamat. Kailangan ko pong malaman kung sino ako nakakausap para sa aming records. Ano po ang buong pangalan ninyo?
            Wag po kayong mag-worry - para lang po ito sa follow-up kung kailangan at para official lahat. Pakibigay po yung complete name ninyo.",
                    
                    'contact_info' => "Perfect po, salamat {$reportedBy}! Ngayon naman, kailangan ko po ng paraan para makontak kayo kung may mga updates o follow-up questions tungkol sa incident na ito.

            Ano po ang best way para ma-contact kayo?
            📱 Mobile number 
            📧 Email address
            ☎️ Emergency contact  
            Ibahagi lang po ninyo kung saan kayo mas comfortable!",
                ],
                
                 'mixed' => [
                    'incident_type' => "Hello po! I'm here para tulungan kayo sa incident report na ito. Pwede po bang i-share ninyo kung ano ang nangyari? I need to understand po yung situation para ma-ensure na makakakuha ito ng proper attention.
            For example:
            🏥 Health & Safety: May nasaktan, nahulog, accident, masamang karamdaman, or medical emergency
            🔥 Environmental: Fire, baha, structural damage, or hazardous conditions 
            👥 Behavioral: Bullying, away, harassment, or disruptive behavior
            🚨 Security: Theft, missing items, unauthorized access, or prohibited items found  
            ⚡ Operational: Power outages, system failures , water leakage
            Just describe po what happened sa sarili ninyong words",
                    
                    'location' => "Thanks po sa info na yun! Ngayon, saan po exactly nangyari yung incident na ito? The more specific po kayo, the better I can help route this sa right people.
            For example:
            📍 Specific rooms like \"Room 304\" or \"Science Lab B\"
            🏢 General areas like \"main hallway\", \"cafeteria\", or \"gymnasium\"
            🌳 Outdoor spaces like \"basketball court\", \"parking area\", or \"near the main gate\"
            📍 Floor details - \"2nd floor stairway\" or \"ground floor restroom\"
            Where did this happened?",
                    
                    'reported_by' => "Got it po, salamat! I need to know po kung sino ako nakakausap for our records. What's your full name po?
            Don't worry, this is just para ma-follow up namin kayo if needed at para official lahat. Please give me po yung complete name ninyo.",
                    
                    'contact_info' => "Perfect po, thanks {$reportedBy}! Now I need po ng way para ma-reach kayo if may updates or follow-up questions about this incident.

            What's the best way po to contact you?
            📱 Mobile number 
            📧 Email address po
            ☎️ Emergency contact
            Just share po whichever mas comfortable kayo!",
                ]
            ];

            // FIXED: Better fallback logic
            if (isset($questions[$language][$field])) {
                return $questions[$language][$field];
            }
            
            // If specific language not found, try English first, then mixed
            if (isset($questions['english'][$field])) {
                return $questions['english'][$field];
            }
            
            if (isset($questions['mixed'][$field])) {
                return $questions['mixed'][$field];
            }
            
            // Last resort
            return "I need more information to complete your report.";

                }
         private function ensureLanguageConsistency(array $context, string $detectedLanguage): string
        {
            // If we've already established a language, stick with it unless there's a strong indicator to switch
            if (isset($context['established_language'])) {
                $establishedLang = $context['established_language'];
                
                // Allow switching if detection is very confident
                // But generally maintain consistency
                if ($establishedLang === 'mixed') {
                    // Mixed can stay mixed or switch to pure language if very clear
                    if (in_array($detectedLanguage, ['english', 'tagalog'])) {
                        // Only switch if we have strong confidence
                        return $detectedLanguage;
                    }
                    return 'mixed';
                }
                
                // If established as pure language, allow mixing
                if (in_array($establishedLang, ['english', 'tagalog']) && $detectedLanguage === 'mixed') {
                    return 'mixed';
                }
                
                return $establishedLang;
            }
            
            return $detectedLanguage;
        }
            private function debugLanguageDetection(string $message, string $detectedLanguage): void
                {
                    Log::info('Language Detection Debug', [
                        'message' => $message,
                        'detected_language' => $detectedLanguage,
                        'message_length' => strlen($message)
                    ]);
                }


    /**
     * Enhanced intent extraction
     */

    private function extractIntent(string $content): string
    {
        $content = strtolower($content);
        
        // Gratitude patterns
        $gratitudePatterns = ['thank', 'thanks', 'salamat', 'maraming salamat', 'thank you'];
        foreach ($gratitudePatterns as $pattern) {
            if (str_contains($content, $pattern)) return 'gratitude';
        }
        
        // Help request patterns
        $helpPatterns = ['help', 'tulong', 'kailangan tulong', 'help me', 'tulungan', 'assist'];
        foreach ($helpPatterns as $pattern) {
            if (str_contains($content, $pattern)) return 'help_request';
        }
        
        // Clarification patterns
        $clarificationPatterns = ['what', 'ano', 'paano', 'how', 'where', 'saan', 'when', 'kailan'];
        foreach ($clarificationPatterns as $pattern) {
            if (str_contains($content, $pattern)) return 'clarification';
        }
        
        return 'report_incident';
    }

    /**
     * Build image analysis prompt
     */
    private function buildImageAnalysisPrompt(string $description): string
    {
        return "Analyze this image for a school incident report. Provide a clear, professional assessment in plain text format without any asterisks, bold formatting, or special characters.

    Include the following information:
    1. Type of incident observed (injury, damage, hazard, behavioral, safety concern, etc.)
    2. Severity assessment on a scale of 1-5 (1=Immediate, 2=Emergency, 3=Urgent, 4=Semi-Urgent 5=Non-Urgent)
    3. Location details visible in the image
    4. Any safety concerns or hazards present
    5. Visible injuries, damages, or evidence
    6. Environmental factors that may have contributed
    7. Immediate recommendations or concerns

    User provided description: {$description}

    Write your response in plain paragraph format using professional language suitable for official incident documentation. Do not use asterisks, bold text, bullet points, or any special formatting. Focus on factual observations and safety-related details.";
    }

    /**
     * Parse image analysis response - Updated for professional output
     */
    private function parseImageAnalysis(string $analysis): array
    {
        // Clean up any asterisks or formatting characters from Gemini's response
        $cleanAnalysis = $this->cleanImageAnalysisText($analysis);
        
        return [
            'description' => $cleanAnalysis,
            'extracted_at' => now(),
            'confidence' => 0.8,
            'language_support' => 'english'
        ];
    }

        private function cleanImageAnalysisText(string $text): string
    {
        // Remove asterisks used for bold formatting
        $text = str_replace(['**', '*'], '', $text);
        
        // Remove other markdown formatting
        $text = preg_replace('/#{1,6}\s+/', '', $text); // Remove headers
        $text = preg_replace('/\*\s+/', '', $text); // Remove bullet points
        $text = preg_replace('/-\s+/', '', $text); // Remove dash bullet points
        $text = preg_replace('/\d+\.\s+/', '', $text); // Remove numbered lists
        
        // Clean up extra whitespace
        $text = preg_replace('/\n\s*\n/', "\n\n", $text); // Multiple newlines to double
        $text = trim($text);
        
        return $text;
    }


    /**
     * Enhanced error message with better Filipino
     */
    private function getErrorMessage(string $language): string
    {
        $messages = [
            'english' => "I'm sorry, I'm having trouble processing your request right now. Please try again in a moment or contact technical support if the problem persists.",
            'tagalog' => "Pasensya na po, may problema ako sa pag-proseso ng inyong request ngayon. Subukan ulit po mamaya o makipag-ugnayan sa technical support kung patuloy ang problema.",
            'mixed' => "Sorry po, may technical issue ako ngayon sa pag-process ng request ninyo. Please try ulit in a moment po or contact support if tuloy-tuloy ang problema."
        ];
        
        return $messages[$language] ?? $messages['mixed'];
    }


        /**
    * Detect if user wants to cancel the report
    */
    private function detectCancellationIntent(string $message, string $language): bool
    {
        $message = strtolower(trim($message));
        
        $cancellationPatterns = [
            // English patterns
            'cancel', 'cancel report', 'stop', 'stop report', 'quit', 'exit', 
            'never mind', 'nevermind', 'forget it', 'abort', 'end report',
            'i don\'t want to', 'i changed my mind', 'not anymore',
            
            // Filipino patterns
            'cancel', 'icancel', 'i-cancel', 'tumigil', 'stop', 'wag na', 'ayaw na',
            'hindi na', 'wala na', 'nevermind na', 'forget it na', 'ayaw ko na',
            'nagbago isip ko', 'hindi na ako', 'wag na ituloy', 'stop na',
            
            // Mixed patterns
            'cancel na', 'stop na lang', 'nevermind na lang', 'ayaw na ako',
            'hindi na continue', 'wag na proceed'
        ];
        
        foreach ($cancellationPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

        /**
     * Generate cancellation confirmation prompt
     */
    private function getCancellationConfirmationMessage(string $language): string
    {
        $messages = [
            'english' => "Are you sure you want to cancel this incident report? All information collected so far will be lost.",
            'tagalog' => "Sigurado po ba kayong gusto ninyong i-cancel ang incident report na ito? Mawawala po lahat ng nakolektang impormasyon.",
            'mixed' => "Sure po ba kayong i-cancel yung incident report na ito? Mawawala lahat ng info na na-collect na namin."
        ];
        
        return $messages[$language] ?? $messages['mixed'];
    }

            /**
         * Check if user is confirming cancellation
         */
        private function isConfirmingCancellation(string $message, string $language): bool
    {
        $message = strtolower(trim($message));
        
        // Simple confirmation patterns
        $confirmationPatterns = ['yes', 'oo', 'opo', 'ok', 'okay', 'sige'];
        
        // Check exact matches first
        if (in_array($message, $confirmationPatterns)) {
            return true;
        }
        
        // Check if message starts with confirmation
        foreach ($confirmationPatterns as $pattern) {
            if (str_starts_with($message, $pattern . ' ') || str_starts_with($message, $pattern . ',')) {
                return true;
            }
        }
        
        return false;
    }

        /**
     * Generate cancellation confirmation message
     */
    private function getCancellationMessage(string $language): string
    {
        $messages = [
            'english' => "Your incident report has been cancelled. No information has been saved. If you need to report an incident later, just start a new conversation with me. Stay safe!",
            'tagalog' => "Na-cancel po ang inyong incident report. Walang nakatabi na impormasyon. Kung kailangan ninyo mag-report ng incident mamaya, magsimula lang po ng bagong conversation sa akin. Mag-ingat po!",
            'mixed' => "Na-cancel na po ang incident report ninyo. Walang na-save na information. If you need mag-report ng incident later, just start a new conversation lang po sa akin. Stay safe po!"
        ];
        
        return $messages[$language] ?? $messages['mixed'];
    }

            /**
         * Generate message when user decides to continue after cancellation request
         */
        private function getContinueReportMessage(string $language): string
        {
            $messages = [
                'english' => "Alright, let's continue with your incident report. What additional information can you provide?",
                'tagalog' => "Sige po, itutuloy natin ang inyong incident report. Anong karagdagang impormasyon po ang maibibigay ninyo?",
                'mixed' => "Okay po, let's continue sa incident report ninyo. Anong additional info po ang pwede ninyong ibigay?"
            ];
            
            return $messages[$language] ?? $messages['mixed'];
        }

        /**
         * Handle first image upload when starting a report
         */
           private function handleFirstImageUpload(string $language, array $incidentData, array $context = []): array
            {
                $incidentData = $this->autoFillUserData($incidentData);
                $messages = [
                    'english' => "I can see you've uploaded an image. Let me analyze this for your incident report. Can you tell me what happened in this image?",
                    'tagalog' => "Nakita ko po na nag-upload kayo ng larawan. I-analyze ko po ito para sa incident report ninyo. Ano po ang nangyari sa larawang ito?",
                    'mixed' => "I can see na nag-upload kayo ng image. Let me analyze po ito for your incident report. Ano po ang nangyari sa image na ito?"
                ];

                return [
                    'reply' => $messages[$language] ?? $messages['mixed'],
                    'incident_data' => $incidentData,
                    'context' => array_merge($context, [
                        'conversation_stage' => 'collecting',
                        'language_detected' => $language,
                        'first_image_processed' => true,
                        'awaiting_user_response' => true
                    ]),
                    'is_complete' => false,
                    'missing_fields' => $this->getMissingRequiredFields($incidentData),
                    'language' => $language,
                    'confidence' => 0.9
                ];
            }


        private function handleImageFollowupResponse(string $message, string $language, array $context, array $incidentData): array
    {
        $incidentData = $this->autoFillUserData($incidentData);
        $userResponse = strtolower(trim($message));
        
        // Check for positive responses (user is satisfied with analysis)
        $positiveResponses = ['yes', 'oo', 'ok', 'okay', 'tama', 'correct', 'wala na', 'complete', 'done', 'ayos na'];
        $negativeResponses = ['no', 'hindi', 'may kulang', 'incomplete', 'mali', 'wrong'];
        
        if (in_array($userResponse, $positiveResponses)) {
            // User confirmed - check if report is complete
            $missingFields = $this->getMissingRequiredFields($incidentData);
            
            if (empty($missingFields)) {
                // Report is complete
                $responses = [
                    'english' => "Perfect! Your incident report is now complete. Thank you for providing all the necessary information.",
                    'tagalog' => "Salamat po! Kumpleto na ang inyong incident report. Maraming salamat sa pagbibigay ng lahat ng kinakailangan.",
                    'mixed' => "Salamat po! Complete na po ang incident report ninyo. Thank you for providing all the information needed."
                ];
                
                return [
                    'reply' => $responses[$language] ?? $responses['mixed'],
                    'incident_data' => $incidentData,
                    'context' => array_merge($context, [
                        'conversation_stage' => 'completed',
                        'awaiting_user_response' => false
                    ]),
                    'is_complete' => true,
                    'missing_fields' => [],
                    'language' => $language,
                    'confidence' => 0.9
                ];
            } else {
                // Still need more info
                $updatedContext = array_merge($context, [
                    'awaiting_user_response' => false,
                    'conversation_stage' => 'collecting'
                ]);

                $reply = $this->getNextQuestionMessage($missingFields, $language, $incidentData);

                return [
                    'reply' => $reply,
                    'incident_data' => $incidentData,
                    'context' => $updatedContext,
                    'is_complete' => false,
                    'missing_fields' => $missingFields,
                    'language' => $language,
                    'confidence' => 0.8
                ];
            }
        } 
        else if (in_array($userResponse, $negativeResponses) || strlen($userResponse) > 10) {
            // User wants to add more or correct something
            $responses = [
                'english' => "I understand. Please tell me what additional information you'd like to provide or what needs to be corrected.",
                'tagalog' => "Naiintindihan ko po. Pakisabi po kung ano pang impormasyon ang gusto ninyong idagdag o itama.",
                'mixed' => "Gets ko po. Please sabihin ninyo kung ano pang information ang gusto ninyong i-add or i-correct."
            ];
            
            return [
                'reply' => $responses[$language] ?? $responses['mixed'],
                'incident_data' => $incidentData,
                'context' => array_merge($context, [
                    'conversation_stage' => 'collecting',
                    'awaiting_user_response' => false
                ]),
                'is_complete' => false,
                'missing_fields' => $this->getMissingRequiredFields($incidentData),
                'language' => $language,
                'confidence' => 0.8
            ];
        }
        
        // If response is unclear, ask for clarification
        $responses = [
            'english' => "I'm not sure I understood. Could you please answer 'yes' if the information is complete, or 'no' if you want to add more details?",
            'tagalog' => "Hindi ko po masyadong naintindihan. Pwede po ba ninyong sagutin ng 'oo' kung kumpleto na ang impormasyon, o 'hindi' kung may gusto pa kayong idagdag?",
            'mixed' => "Hindi ko po masyadong na-gets. Please po answer ninyo ng 'yes' or 'oo' kung complete na, or 'no'/'hindi' kung may gusto pa kayong i-add?"
        ];
        
        return [
            'reply' => $responses[$language] ?? $responses['mixed'],
            'incident_data' => $incidentData,
            'context' => $context, // Keep waiting for response
            'is_complete' => false,
            'missing_fields' => $this->getMissingRequiredFields($incidentData),
            'language' => $language,
            'confidence' => 0.6
        ];
    }

}