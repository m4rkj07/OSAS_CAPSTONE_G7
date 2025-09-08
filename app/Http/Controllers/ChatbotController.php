<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\GeminiService;
use App\Models\Report;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatbotController extends Controller
{
    protected $geminiService;
    
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Reset chat session
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $sessionId = Session::getId();
            
            // Clear session data
            Session::forget('chat_context');
            Session::forget('incident_data');
            Session::forget('language_preference');
            Session::forget('conversation_history');
            
            // Delete existing chat session
            ChatSession::where('session_id', $sessionId)->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Chat session reset successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chat reset error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset chat session'
            ], 500);
        }
    }

        /**
     * Handle chatbot report message with optional image
     */
    public function report(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'nullable|string|max:2000',
                'image' => 'nullable|image|max:20480', // 20MB max
                'description' => 'nullable|string|max:500'
            ]);

            
            $hasImage = $request->hasFile('image');
            $isImageOnly = !$request->filled('message') && $hasImage;
            $message = trim($request->input('message', ''));
            $sessionId = Session::getId();

            // Validate that at least message or image is provided
            if (empty($message) && !$hasImage) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please provide a message or upload an image'
                ], 400);
            }

            // Get or create chat session
            $chatSession = $this->getOrCreateChatSession($sessionId);
            
            // Get current context and incident data
            $context = Session::get('chat_context', []);
            $incidentData = Session::get('incident_data', []);
            $conversationHistory = Session::get('conversation_history', []);

            $conversationStage = $context['conversation_stage'] ?? 'collecting';
            if ($conversationStage === 'saved') {
                return response()->json([
                    'reply' => $this->getPostSaveMessage($context['language_detected'] ?? 'mixed'),
                    'incident_data' => [],
                    'is_complete' => true,
                    'missing_fields' => [],
                    'confidence' => 1.0,
                    'language' => $context['language_detected'] ?? 'mixed',
                    'delay' => 1,
                    'session_reset' => true
                ]);
            }

            // Handle image uploads first (reorganized logic)
            $imageAnalysis = null;
            $imagePath = null;
            
            if ($hasImage) {
                // Process the image first
                $imagePath = $this->handleImageUpload($request->file('image'), $sessionId);
                
                $imageAnalysis = $this->geminiService->analyzeImage(
                    storage_path('app/public/' . $imagePath),
                    $request->input('description', ''),
                    $context,
                    $incidentData
                );

                // Add image context
                $context['image_uploaded'] = true;
                $context['has_image'] = true;
                
                if ($isImageOnly) {
                    $context['image_only'] = true;
                }

                // Update incident data with image info
                if (!isset($incidentData['images'])) {
                    $incidentData['images'] = [];
                }
                
                $incidentData['images'][] = [
                    'path' => $imagePath,
                    'description' => $request->input('description', ''),
                    'analysis' => $imageAnalysis,
                    'uploaded_at' => now()
                ];
                
                // Check if we should complete the report after image upload
                if (isset($imageAnalysis['should_complete_report']) && $imageAnalysis['should_complete_report']) {
                    // Update session data
                    Session::put('incident_data', $incidentData);
                    Session::put('chat_context', $imageAnalysis['new_context']);
                    
                    // Update chat session
                    $chatSession->update([
                        'status' => 'completed',
                        'incident_data' => $incidentData,
                        'context' => $imageAnalysis['new_context']
                    ]);
                    
                    // Save the report
                    $reportId = $this->saveIncidentReport($incidentData, $sessionId);

                    Session::forget('chat_context');
                    Session::forget('incident_data');
                    Session::forget('language_preference');
                    Session::forget('conversation_history');
                    return response()->json([
                        'reply' => $imageAnalysis['completion_message'],
                        'incident_data' => $incidentData,
                        'is_complete' => true,
                        'missing_fields' => [],
                        'confidence' => 0.9,
                        'language' => $imageAnalysis['new_context']['language_detected'] ?? 'mixed',
                        'delay' => 2,
                        'image_analysis' => $imageAnalysis,
                        'report_id' => $reportId,
                        'session_reset' => true
                    ]);
                }
                
                // If only image provided, create a better message for AI processing
                if (empty($message)) {
                    $message = $this->createImageMessage($imageAnalysis, $request->input('description', ''));
                }
            }
            
            // Add user message to history
            $conversationHistory[] = [
                'role' => 'user',
                'message' => $message,
                'has_image' => $hasImage,
                'image_path' => $imagePath,
                'timestamp' => now()
            ];

            // Pass additional context to prevent AI from using image message as reporter name
            $additionalContext = [];
            if ($hasImage && empty(trim($request->input('message', '')))) {
                $additionalContext['image_only_upload'] = true;
                $additionalContext['original_user_message'] = ''; // No text message provided
            }

            // Then process the message (which might be empty if image-only, but we've handled that above)
            $geminiResponse = $this->geminiService->processReportMessage(
                $message, 
                array_merge($context, $additionalContext), 
                $incidentData, 
                $conversationHistory
            );

            // [Rest of your existing code remains the same...]
            // Check if report was cancelled
            if (isset($geminiResponse['is_cancelled']) && $geminiResponse['is_cancelled']) {
                // Clear session data
                Session::forget('chat_context');
                Session::forget('incident_data');
                Session::forget('language_preference');
                Session::forget('conversation_history');
                
                // Mark chat session as cancelled - with null safety
                try {
                    if ($chatSession) {
                        $chatSession->update([
                            'status' => 'cancelled',
                            'last_message' => $message,
                            'updated_at' => now()
                        ]);
                    }
                } catch (\Exception $updateException) {
                    Log::warning('Failed to update chat session on cancellation', [
                        'error' => $updateException->getMessage(),
                        'session_id' => $sessionId
                    ]);
                    // Continue anyway - cancellation should still work
                }
                
                // Log the cancellation
                Log::info('Report cancelled by user', [
                    'session_id' => $sessionId,
                    'message' => $message
                ]);
                
                // Return cancellation response
                return response()->json([
                    'reply' => $geminiResponse['reply'],
                    'incident_data' => [],
                    'is_complete' => false,
                    'is_cancelled' => true,
                    'missing_fields' => [],
                    'confidence' => 1.0,
                    'language' => $geminiResponse['language'],
                    'delay' => 1
                ]);
            }

            // Update session data
            Session::put('chat_context', $geminiResponse['context']);
            Session::put('incident_data', $geminiResponse['incident_data']);
            Session::put('language_preference', $geminiResponse['language']);
            
            // Add bot response to history
            $conversationHistory[] = [
                'role' => 'bot',
                'message' => $geminiResponse['reply'],
                'timestamp' => now()
            ];
            Session::put('conversation_history', $conversationHistory);

            // Update chat session in database
            $chatSession->update([
                'last_message' => $message,
                'context' => $geminiResponse['context'],
                'incident_data' => $geminiResponse['incident_data'],
                'language' => $geminiResponse['language'],
                'updated_at' => now()
            ]);

            // Check if report is complete and save
            if ($geminiResponse['is_complete']) {
                $reportId = $this->saveIncidentReport($geminiResponse['incident_data'], $sessionId);
                $geminiResponse['report_id'] = $reportId;
                
                // Mark session as completed and link to report
                $chatSession->update([
                    'status' => 'completed',
                    'report_id' => $reportId
                ]);
                Session::forget('chat_context');
                Session::forget('incident_data');
                Session::forget('language_preference');
                Session::forget('conversation_history');
            }

            $responseData = [
                'reply' => $geminiResponse['reply'],
                'incident_data' => $geminiResponse['incident_data'],
                'is_complete' => $geminiResponse['is_complete'],
                'missing_fields' => $geminiResponse['missing_fields'] ?? [],
                'confidence' => $geminiResponse['confidence'] ?? 0.8,
                'language' => $geminiResponse['language'],
                'delay' => $this->calculateResponseDelay($geminiResponse['reply'])
            ];

            // Add image analysis to response if available
            if ($imageAnalysis) {
                $responseData['image_analysis'] = $imageAnalysis;
                $responseData['image_uploaded'] = true;
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage(), [
                'message' => $request->input('message'),
                'session' => Session::getId(),
                'has_image' => $request->hasFile('image'),
                'trace' => $e->getTraceAsString()
            ]);

            $errorResponse = $this->getErrorResponse($e);
            
            return response()->json([
                'reply' => $errorResponse,
                'incident_data' => Session::get('incident_data', []),
                'is_complete' => false,
                'delay' => 1
            ], 500);
        }
    }


        /**
     * Get message for attempts to interact after report is saved
     */
    private function getPostSaveMessage(string $language): string
    {
        $messages = [
            'english' => "Your incident report has already been submitted successfully. If you need to report a new incident, please refresh the page to start fresh.",
            'tagalog' => "Naipadala na po successfully ang inyong incident report. Kung kailangan ninyo mag-report ng bagong incident, please refresh po ang page para magsimula ng bago.",
            'mixed' => "Na-submit na po successfully ang incident report ninyo. If kailangan ninyo mag-report ng new incident, please refresh lang po ang page para start fresh."
        ];
        
        return $messages[$language] ?? $messages['mixed'];
    }

    /**
     * Handle image upload and return storage path
     */
    private function handleImageUpload($imageFile, string $sessionId): string
    {
        try {
            // Create a unique filename
            $originalName = $imageFile->getClientOriginalName();
            $extension = $imageFile->getClientOriginalExtension();
            $filename = time() . '_' . $sessionId . '_' . uniqid() . '.' . $extension;
            
            // Store in public/incident_images directory
            $path = $imageFile->storeAs('evidence_images', $filename, 'public');
            
            Log::info('Image uploaded successfully', [
                'original_name' => $originalName,
                'stored_path' => $path,
                'session_id' => $sessionId
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('Image upload error: ' . $e->getMessage());
            throw new \Exception('Failed to upload image: ' . $e->getMessage());
        }
    }

    /**
     * FIXED: Create descriptive message from image analysis that won't be confused as reporter name
     */
    private function createImageMessage(array $imageAnalysis, string $userDescription): string
    {
        $message = "Evidence image uploaded";
        
        if (!empty($userDescription)) {
            $message .= " - User description: " . $userDescription;
        }
        
        if (isset($imageAnalysis['description']) && !empty($imageAnalysis['description'])) {
            $message .= ". AI detected in image: " . substr($imageAnalysis['description'], 0, 200);
        } else {
            $message .= ". Please analyze this image for incident details.";
        }
        
        // Add clear context that this is system-generated, not user input
        $message .= " [System: Image-only submission - no text message provided by user]";
        
        return $message;
    }

    /**
     * Get chat history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $sessionId = Session::getId();
            $history = Session::get('conversation_history', []);
            
            return response()->json([
                'status' => 'success',
                'history' => $history,
                'session_id' => $sessionId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get history error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve chat history'
            ], 500);
        }
    }

    /**
     * Get current incident data
     */
    public function getIncidentData(Request $request): JsonResponse
    {
        try {
            $incidentData = Session::get('incident_data', []);
            $missingFields = $this->getMissingFields($incidentData);
            
            return response()->json([
                'status' => 'success',
                'incident_data' => $incidentData,
                'missing_fields' => $missingFields,
                'is_complete' => empty($missingFields)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get incident data error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve incident data'
            ], 500);
        }
    }

    /**
     * Legacy image upload endpoint (keep for backward compatibility)
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|image|max:20480', // 20MB max
                'description' => 'nullable|string|max:500'
            ]);

            $sessionId = Session::getId();
            $imagePath = $this->handleImageUpload($request->file('image'), $sessionId);
            
            // Process image with Gemini Vision if available
            $imageAnalysis = $this->geminiService->analyzeImage(
                storage_path('app/public/' . $imagePath),
                $request->input('description', ''),
                Session::get('chat_context', []),
                Session::get('incident_data', [])
            );

            // Update incident data with image info
            $incidentData = Session::get('incident_data', []);
            if (!isset($incidentData['images'])) {
                $incidentData['images'] = [];
            }
            
            $incidentData['images'][] = [
                'path' => $imagePath,
                'description' => $request->input('description', ''),
                'analysis' => $imageAnalysis,
                'uploaded_at' => now()
            ];
            Session::put('incident_data', $incidentData);

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
                'image_path' => $imagePath,
                'analysis' => $imageAnalysis
            ]);

        } catch (\Exception $e) {
            Log::error('Image upload error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get or create chat session
     */
    private function getOrCreateChatSession(string $sessionId): ChatSession
    {
        return ChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'status' => 'active',
                'context' => [],
                'incident_data' => [],
                'language' => 'mixed',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Save completed incident report with proper evidence_image handling
     */
    private function saveIncidentReport(array $incidentData, string $sessionId): int
    {
        try {

             if (auth()->check()) {
            $user = auth()->user();
            
            if (empty($incidentData['reported_by'])) {
                $incidentData['reported_by'] = $user->name;
            }
            
            if (empty($incidentData['contact_info'])) {
                $incidentData['contact_info'] = $user->email;
            }
        }

            // Generate a concise summary for the description field
            $shortDescription = $this->generateShortDescription($incidentData);
            
            // Get the primary evidence image path
            $evidenceImagePath = $this->getPrimaryEvidenceImage($incidentData['images'] ?? []);
            
            $report = Report::create([
                'description' => $shortDescription,
                'incident_type' => $incidentData['incident_type'] ?? 'unknown',
                'full_description' => $this->buildFullDescription($incidentData, $sessionId),
                'location' => $this->normalizeLocation($incidentData['location'] ?? ''),
                'reported_by' => $incidentData['reported_by'] ?? 'Anonymous',
                'contact_info' => $incidentData['contact_info'] ?? '',
                'esi_level' => $incidentData['esi_level'] ?? 1,
                'evidence_image' => $evidenceImagePath, // Store primary image path
                'user_id' => auth()->id() ?? null,
                'status' => 'pending', // Set default status
                'archived' => false,
                'created_at' => $incidentData['incident_datetime'] ?? now(),
            ]);

            Log::info('Incident report saved', [
                'report_id' => $report->id, 
                'session' => $sessionId,
                'evidence_image' => $evidenceImagePath,
                'total_images' => count($incidentData['images'] ?? [])
            ]);
            
            return $report->id;

        } catch (\Exception $e) {
            Log::error('Save report error: ' . $e->getMessage(), ['incident_data' => $incidentData]);
            throw $e;
        }
    }

    /**
     * Get the primary evidence image path for the reports table
     */
    private function getPrimaryEvidenceImage(array $images): ?string
    {
        if (empty($images)) {
            return null;
        }
        
        // Return the path of the first image
        $firstImage = $images[0];
        
        if (is_array($firstImage) && isset($firstImage['path'])) {
            return $firstImage['path'];
        }
        
        return is_string($firstImage) ? $firstImage : null;
    }

    /**
     * Generate a concise summary for the description field (max 255 characters typically)
     */
    private function generateShortDescription(array $incidentData): string
    {
        $parts = [];
        
        // Include incident type
        if (!empty($incidentData['incident_type'])) {
            $parts[] = ucwords(str_replace('_', ' ', $incidentData['incident_type']));
        }
        
        // Include location if available
        if (!empty($incidentData['location'])) {
            $parts[] = "at {$incidentData['location']}";
        }
        
        // Include key details from description if available
        if (!empty($incidentData['description'])) {
            // Extract key details from the main description
            $description = $incidentData['description'];
            
            // Clean up the description - remove extra details and system messages
            $cleanDescription = $this->extractKeyDetails($description);
            
            if ($cleanDescription && strlen($cleanDescription) > 10) {
                // Limit length for the short description
                $maxLength = 150 - strlen(implode(' ', $parts)) - 3; // Leave room for parts and " - "
                
                if ($maxLength > 20) {
                    $truncatedDescription = strlen($cleanDescription) > $maxLength 
                        ? substr($cleanDescription, 0, $maxLength) . '...'
                        : $cleanDescription;
                    
                    $parts[] = $truncatedDescription;
                }
            }
        }
        
        // If we have parts, join them
        if (!empty($parts)) {
            $shortDescription = implode(' - ', $parts);
            
            // Ensure it's not too long (database limit)
            if (strlen($shortDescription) > 255) {
                $shortDescription = substr($shortDescription, 0, 252) . '...';
            }
            
            return $shortDescription;
        }
        
        // Fallback if no meaningful data
        $reporter = $incidentData['reported_by'] ?? 'Anonymous';
        return "Incident reported by {$reporter} via AI Chatbot";
    }

    /**
     * Extract key details from the full description for the short summary
     */
    private function extractKeyDetails(string $description): string
    {
        // FIXED: Remove system messages and image-related messages
        $cleanDescription = preg_replace('/\|\s*Additional details:.*$/i', '', $description);
        $cleanDescription = preg_replace('/Evidence image uploaded.*?\[System:.*?\]/i', '', $cleanDescription);
        $cleanDescription = preg_replace('/AI detected in image:.*$/i', '', $cleanDescription);
        $cleanDescription = preg_replace('/\[System:.*?\]/i', '', $cleanDescription);
        $cleanDescription = trim($cleanDescription);
        
        // If it's still too long, try to get the first meaningful sentence
        if (strlen($cleanDescription) > 150) {
            // Split by sentences and take the first meaningful one
            $sentences = preg_split('/[.!?]+/', $cleanDescription);
            
            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                
                // Skip very short sentences, system messages, or generic image messages
                if (strlen($sentence) > 20 && 
                    !preg_match('/^(ako si|my name is|i am|ako ay)/i', $sentence) &&
                    !preg_match('/^(room|nasa|sa)/i', $sentence) &&
                    !preg_match('/^(evidence image|image uploaded|uploaded)/i', $sentence)) {
                    
                    return $sentence;
                }
            }
        }
        
        return $cleanDescription;
    }

    /**
     * Build comprehensive full description from collected data
     */
    private function buildFullDescription(array $incidentData, string $sessionId): string
    {
        $conversationHistory = Session::get('conversation_history', []);
        
        $description = "AI CHATBOT GENERATED REPORT\n\n";
        
        // Basic incident info
        if (!empty($incidentData['incident_type'])) {
            $description .= "Incident Type: " . ucwords(str_replace('_', ' ', $incidentData['incident_type'])) . "\n";
        }
        
        if (!empty($incidentData['esi_level'])) {
            $levelText = $this->getESILevelText($incidentData['esi_level']);
            $description .= "Severity Level: {$incidentData['esi_level']} - {$levelText}\n";
        }
        
        if (!empty($incidentData['location'])) {
            $description .= "Location: {$incidentData['location']}\n";
        }
        
        if (!empty($incidentData['incident_datetime'])) {
            $description .= "Date/Time: " . date('Y-m-d H:i:s', strtotime($incidentData['incident_datetime'])) . "\n";
        }
        
        $description .= "\n";
        
        // Reporter info
        if (!empty($incidentData['reported_by'])) {
            $description .= "Reported by: {$incidentData['reported_by']}\n";
        }
        
        if (!empty($incidentData['contact_info'])) {
            $description .= "Contact: {$incidentData['contact_info']}\n";
        }
        
        $description .= "\nINCIDENT DESCRIPTION\n";
        
        // Main description - filter out system messages
        if (!empty($incidentData['description'])) {
            $cleanDescription = preg_replace('/\[System:.*?\]/i', '', $incidentData['description']);
            $cleanDescription = trim($cleanDescription);
            if (!empty($cleanDescription)) {
                $description .= $cleanDescription . "\n\n";
            }
        }
        
        // Additional info
        if (!empty($incidentData['additional_info'])) {
            $description .= "Additional Information:\n" . $incidentData['additional_info'] . "\n\n";
        }

        // Image information
        if (!empty($incidentData['images'])) {
            $description .= "EVIDENCE IMAGES\n";
            foreach ($incidentData['images'] as $index => $image) {
                $imageNum = $index + 1;
                $description .= "Image {$imageNum}: {$image['path']}\n";
                
                if (!empty($image['description'])) {
                    $description .= "Description: {$image['description']}\n";
                }
                
                if (!empty($image['analysis']['description'])) {
                    $description .= "AI Analysis: {$image['analysis']['description']}\n";
                }
                
                $description .= "Uploaded: " . $image['uploaded_at'] . "\n\n";
            }
        }
        
        // Language and confidence info
        $language = $incidentData['language'] ?? 'mixed';
        $confidence = $incidentData['confidence'] ?? 0.8;
        
        $description .= "SYSTEM INFO\n";
        $description .= "Language Used: " . ucfirst($language) . "\n";
        $description .= "AI Confidence: " . round($confidence * 100, 1) . "%\n";
        $description .= "Session ID: {$sessionId}\n";
        $description .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        // Conversation summary (last few exchanges) - filter system messages
        if (!empty($conversationHistory)) {
            $description .= "CONVERSATION SUMMARY\n";
            $recentHistory = array_slice($conversationHistory, -6); // Last 6 messages
            
            foreach ($recentHistory as $entry) {
                // Skip system-generated messages about image uploads
                if (stripos($entry['message'], '[System:') !== false || 
                    stripos($entry['message'], 'Evidence image uploaded') !== false) {
                    continue;
                }
                
                $role = $entry['role'] === 'user' ? 'Reporter' : 'AI Assistant';
                $timestamp = date('H:i:s', strtotime($entry['timestamp']));
                $hasImage = isset($entry['has_image']) && $entry['has_image'] ? ' [IMAGE]' : '';
                $description .= "[{$timestamp}] {$role}: {$entry['message']}{$hasImage}\n";
            }
        }
        
        return $description;
    }

    /**
     * Process evidence images for storage (legacy method - kept for compatibility)
     */
    private function processEvidenceImages(array $images): ?string
    {
        return $this->getPrimaryEvidenceImage($images);
    }

    /**
     * Get ESI level text description
     */
    private function getESILevelText(int $level): string
    {
        $levels = [
            1 => 'Immediate',
            2 => 'Emergency', 
            3 => 'Urgent',
            4 => 'Semi-Urgent',
            5 => 'Non-Urgent'
        ];
        
        return $levels[$level] ?? 'Unknown';
    }

    /**
     * Normalize location for consistency
     */
    private function normalizeLocation(string $location): string
    {
        $location = trim(strtolower($location));
        
        // Common location mappings
        $locationMap = [
            'room' => 'Room',
            'classroom' => 'Classroom',
            'library' => 'Library',
            'playground' => 'Playground',
            'cafeteria' => 'Cafeteria',
            'gymnasium' => 'Gymnasium',
            'office' => 'Office',
            'hallway' => 'Hallway',
            'stairs' => 'Stairs',
            'restroom' => 'Restroom',
            'laboratory' => 'Laboratory',
            'auditorium' => 'Auditorium'
        ];

        foreach ($locationMap as $key => $value) {
            if (str_contains($location, $key)) {
                // Extract room number if present
                preg_match('/(\d+)/', $location, $matches);
                $roomNumber = $matches[0] ?? '';
                return $roomNumber ? "$value $roomNumber" : $value;
            }
        }

        return ucwords($location);
    }

    /**
     * Get missing required fields
     */
    private function getMissingFields(array $incidentData): array
    {
        $requiredFields = [
            'incident_type' => 'Type of incident',
            'location' => 'Location',
            'reported_by' => 'Reporter name',
            'contact_info' => 'Contact information'
        ];

        $missing = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($incidentData[$field])) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Calculate response delay for typing effect
     */
    private function calculateResponseDelay(string $message): int
{
    $baseDelay = 1;
    $lengthFactor = min(strlen($message) * 0.02, 2); // Max 2 seconds
    return max(1, (int)($baseDelay + $lengthFactor));
}

    /**
     * Get error response based on language preference
     */
    private function getErrorResponse(\Exception $e): string
    {
        $language = Session::get('language_preference', 'mixed');
        
        $responses = [
            'english' => "I'm sorry, I encountered an error. Please try again or contact support if the problem persists.",
            'tagalog' => "Pasensya na po, may problema ako ngayon. Subukan ulit o makipag-ugnayan sa support kung patuloy ang problema.",
            'mixed' => "Sorry po, may technical issue. Please try again or i-contact ang support kung tuloy-tuloy ang problema."
        ];

        return $responses[$language] ?? $responses['mixed'];
    }
}