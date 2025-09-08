<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
        .animate-slide-up { animation: slideUp 0.4s ease-out forwards; }
        .animate-pulse { animation: pulse 1.5s ease-in-out infinite; }

        .image-upload-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(30, 58, 138, 0.05), transparent);
            transition: left 0.5s;
        }

        .image-upload-card:hover::before {
            left: 100%;
        }

        .image-analysis::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #059669 0%, #10b981 100%);
        }

/* Fixed image preview styles */
/* Fixed image preview styles */
#image-preview {
    max-width: 240px;
    max-height: 240px;
    width: auto;
    height: auto;
    object-fit: cover;
    object-position: center;
}

/* Remove button redesign */
#remove-image {
    position: relative;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 2px solid #fca5a5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc2626;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
}

#remove-image:hover {
    background: linear-gradient(135deg, #fee2e2, #fca5a5);
    border-color: #dc2626;
    color: #ffffff;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
}

#remove-image:active {
    transform: scale(0.95);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #image-preview {
        max-width: 340px;
        max-height: 120px;
    }
    
    .image-upload-card {
        padding: 12px;
    }
    
    .image-upload-card .flex {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    #remove-image {
        width: 28px;
        height: 28px;
        font-size: 12px;
        align-self: flex-end;
        margin-top: -4px;
    }
    
    .image-preview-wrapper {
        align-self: center;
        order: -1;
    }
    
    .image-upload-card .flex-1 {
        min-width: 0;
        flex: 1;
        order: 0;
    }
    
    .image-upload-card .flex-1 .text-base {
        font-size: 14px;
        line-height: 1.4;
        word-break: break-word;
        overflow-wrap: break-word;
        margin-bottom: 6px;
    }
    
    .image-upload-card .flex-1 .text-xs {
        font-size: 11px;
        margin-bottom: 10px;
    }
    
    .image-upload-card input[type="text"] {
        font-size: 14px;
        padding: 8px 12px;
    }
}

/* Ensure container doesn't overflow */
.image-preview-wrapper {
    flex-shrink: 0;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}

/* Chat image message fixes */
.chat-image {
    max-width: 100%;
    max-height: 300px;
    width: auto;
    height: auto;
    object-fit: contain;
}

@media (max-width: 768px) {
    .chat-image {
        max-height: 200px;
    }
}
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="{{ asset('logo/bcplogin.png') }}">
    <title>OSAS | AI Chatbot Reporter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-0 md:p-4 font-['Inter',sans-serif]">

    <div class="w-full bg-white shadow-2xl flex flex-col overflow-hidden max-w-full h-screen md:h-[85vh] md:max-w-4xl lg:max-w-6xl rounded-none md:rounded-3xl border-none md:border md:border-gray-300">
        <!-- Header -->
        <div class="bg-blue-900 px-4 md:px-6 py-5 md:py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-shield-alt text-white text-lg md:text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-white text-lg md:text-xl font-bold tracking-tight">OSAS AI Reporter</h1>
                        <p class="text-blue-100 text-sm md:text-base">Intelligent Security Assistant</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-blue-100 text-xs md:text-sm font-medium">Active</span>
                </div>
            </div>
        </div>

        <!-- Chatbox -->
        <div id="chatbox" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 md:space-y-5 bg-gradient-to-b from-gray-50 to-white">
            <!-- Chat messages load here -->
        </div>

        <!-- Input form -->
        <div class="bg-white border-t border-gray-200 p-4 md:p-5">
            <!-- Enhanced Image preview area with FIXED layout -->
            <div id="image-preview-container" class="hidden mb-4">
                <div class="image-upload-card bg-gradient-to-br from-white to-slate-50 border-2 border-slate-200 rounded-3xl p-4 md:p-5 transition-all duration-300 ease-in-out relative overflow-hidden hover:border-blue-900">
                    <div class="flex items-start gap-4 md:flex-row flex-col md:gap-4 gap-4">
                        <!-- Fixed image preview wrapper -->
                        <div class="image-preview-wrapper">
                            <img id="image-preview" class="rounded-2xl border-2 border-gray-200 transition-all duration-300 hover:border-blue-900 hover:scale-105" alt="Selected image" />
                        </div>
                        
                        <div class="flex-1 min-w-0 md:pl-2 pl-0">
                            <div class="text-base font-semibold text-slate-800 mb-1.5 leading-snug break-words" id="image-name"></div>
                            <div class="text-xs text-slate-600 mb-4 font-medium" id="image-size"></div>
                            
                            <input 
                                type="text" 
                                id="image-description" 
                                placeholder="Add a description for this image (optional)..." 
                                class="w-full text-sm border-2 border-slate-200 rounded-xl px-4 py-3 transition-all duration-300 bg-white text-gray-700 font-medium leading-relaxed placeholder-gray-400 focus:outline-none focus:border-blue-900 focus:shadow-lg focus:shadow-blue-900/10 focus:bg-white"
                                maxlength="500"
                            />
                            
                            <div id="upload-progress" class="hidden w-full h-1.5 bg-slate-100 rounded-full overflow-hidden mt-4 shadow-inner">
                                <div id="upload-progress-bar" class="h-full bg-gradient-to-r from-blue-900 to-blue-600 transition-all duration-300 rounded-full" style="width: 0%"></div>
                            </div>
                            
                            <div id="upload-status" class="hidden">
                                <div class="inline-flex items-center gap-2 bg-gradient-to-br from-emerald-100 to-green-50 text-emerald-700 px-4 py-2 rounded-full text-xs font-semibold mt-4 border border-emerald-200">
                                    <div class="w-4 h-4 bg-emerald-700 rounded-full flex items-center justify-center text-[10px] text-white">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <span>Image ready to send</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" id="remove-image" class="w-10 h-10 bg-gradient-to-br from-red-100 to-red-200 border-2 border-red-300 rounded-full flex items-center justify-center text-red-700 text-lg transition-all duration-300 ease-in-out cursor-pointer flex-shrink-0 hover:bg-gradient-to-br hover:from-red-200 hover:to-red-300 hover:border-red-700 hover:text-white hover:scale-110 hover:shadow-lg hover:shadow-red-500/40 md:self-start self-center">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Analysis display area -->
            <div id="image-analysis-container" class="hidden mb-4">
                <div class="image-analysis bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-green-200 rounded-3xl p-5 mb-5 relative overflow-hidden">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-700 to-green-600 rounded-full flex items-center justify-center text-white text-base">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="text-base font-bold text-emerald-800 tracking-tight">AI Image Analysis</div>
                    </div>
                    <div id="image-analysis-text" class="text-gray-700 text-sm leading-relaxed font-medium pl-1"></div>
                </div>
            </div>

            <form id="chat-form" class="flex items-end space-x-3 md:space-x-4">
                <div class="flex space-x-2 md:space-x-3">
                    <label for="image-upload" class="w-10 h-10 md:w-12 md:h-12 bg-gray-100 text-gray-500 rounded-2xl flex items-center justify-center cursor-pointer relative transition-all duration-300 hover:bg-gray-200 hover:text-gray-700 hover:scale-105">
                        <i class="fas fa-camera text-lg md:text-xl"></i>
                        <div id="image-upload-indicator" class="hidden absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full ring-2 ring-white"></div>
                    </label>
                    <input type="file" id="image-upload" accept="image/*" class="hidden" />
                </div>
                
                <div class="flex-1 relative">
                    <input
                        type="text"
                        id="user-input"
                        placeholder="Describe the incident or upload evidence..."
                        class="w-full rounded-2xl px-4 md:px-5 py-3 md:py-4 text-sm md:text-base text-gray-800 placeholder-gray-500 focus:outline-none border-2 border-gray-300 bg-white transition-all duration-300 focus:border-blue-900 focus:shadow-lg focus:shadow-blue-900/10"
                    >
                </div>
                
                <button
                    type="submit"
                    id="send-btn"
                    class="w-12 h-12 md:w-14 md:h-14 bg-blue-900 text-white rounded-2xl flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-900/40 transition-all duration-300 ease-in-out hover:shadow-xl hover:shadow-blue-900/50 hover:-translate-y-0.5"
                >
                    <i class="fas fa-paper-plane text-lg md:text-xl"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
const form = document.getElementById('chat-form');
const messageInput = document.getElementById('user-input');
const chatbox = document.getElementById('chatbox');
const imageUpload = document.getElementById('image-upload');
const sendBtn = document.getElementById('send-btn');
const imagePreviewContainer = document.getElementById('image-preview-container');
const imagePreview = document.getElementById('image-preview');
const imageName = document.getElementById('image-name');
const imageSize = document.getElementById('image-size');
const imageDescription = document.getElementById('image-description');
const removeImageBtn = document.getElementById('remove-image');
const uploadProgress = document.getElementById('upload-progress');
const uploadProgressBar = document.getElementById('upload-progress-bar');
const uploadStatus = document.getElementById('upload-status');
const imageUploadIndicator = document.getElementById('image-upload-indicator');
const imageAnalysisContainer = document.getElementById('image-analysis-container');
const imageAnalysisText = document.getElementById('image-analysis-text');

let selectedImage = null;
const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB

let incidentData = {};
let conversationContext = {};
let conversationHistory = [];

window.addEventListener('load', function () {
    // Reset chat session on load
    fetch('/chatbot/reset', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

    // Show random greeting
    const greetings = [
        "👋 Kumusta! Ako ang inyong OSAS AI Reporting Assistant. Ano ang nais ninyong i-report ngayon?",
        "👋 Hi! I'm your OSAS AI Reporting Assistant. How can I help?",
        "👋 Magandang araw! Ako ang OSAS AI Reporting Assistant. Sabihin lamang ang nais i-report.",
        "👋 Hello! I'm your OSAS AI Reporting Assistant. Anong insidente ang gusto ninyong i-report?",
        "👋 Kamusta kayo? Ako ang OSAS AI Reporting Assistant. Nandito ako para tumulong sa inyong report.",
        "👋 Hi there! I'm the OSAS AI Reporting Assistant. You can describe an incident or upload an image as evidence.",
        "👋 Hello! I'm the OSAS AI Reporting Assistant. I can help you report incidents with text descriptions and photo evidence.",
    ];
    const greeting = greetings[Math.floor(Math.random() * greetings.length)];
    typeBotMessage(greeting);
});

// Image upload handling
imageUpload.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validate file
    if (!validateImageFile(file)) {
        return;
    }

    selectedImage = file;
    displayImagePreview(file);
});

function validateImageFile(file) {
    // Check file size
    if (file.size > MAX_FILE_SIZE) {
        showError('File size exceeds 20MB limit. Please choose a smaller image.');
        imageUpload.value = '';
        return false;
    }

    // Check file type
    if (!file.type.startsWith('image/')) {
        showError('Please select a valid image file.');
        imageUpload.value = '';
        return false;
    }

    return true;
}

function displayImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        imagePreview.src = e.target.result;
        imageName.textContent = file.name;
        imageSize.textContent = formatFileSize(file.size);
        imagePreviewContainer.classList.remove('hidden');
        uploadStatus.classList.remove('hidden');
        imageUploadIndicator.classList.remove('hidden');
        
        // Update placeholder text
        messageInput.placeholder = "Add a message or send image alone...";
    };
    reader.readAsDataURL(file);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

removeImageBtn.addEventListener('click', function() {
    clearImageSelection();
});

function clearImageSelection() {
    selectedImage = null;
    imageUpload.value = '';
    imageDescription.value = '';
    imagePreviewContainer.classList.add('hidden');
    uploadProgress.classList.add('hidden');
    uploadStatus.classList.add('hidden');
    imageUploadIndicator.classList.add('hidden');
    imageAnalysisContainer.classList.add('hidden');
    messageInput.placeholder = "Describe the incident or upload an image...";
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const message = messageInput.value.trim();
  const imageDesc = imageDescription.value.trim();
  
  // Validate that at least message or image is provided
  if (!message && !selectedImage) {
    showError('Please provide a message or upload an image.');
    return;
  }

  // Disable form during submission
  setFormDisabled(true);

  // Display user message if present
  if (message) {
    appendMessage('user', message);
  }
  
  // Display image if present
  if (selectedImage) {
    appendImageMessage('user', selectedImage, imageDesc);
  }

  // Clear inputs
  messageInput.value = '';
  chatbox.scrollTop = chatbox.scrollHeight;

  showTypingBubble();
  
  try {
    await sendMessage(message, selectedImage, imageDesc);
  } catch (error) {
    console.error('Send message error:', error);
    showError('Failed to send message. Please try again.');
  } finally {
    setFormDisabled(false);
    clearImageSelection();
  }
});

function setFormDisabled(disabled) {
  messageInput.disabled = disabled;
  sendBtn.disabled = disabled;
  imageUpload.disabled = disabled;
  imageDescription.disabled = disabled;
}

function showTypingBubble() {
  document.getElementById('typing-bubble')?.remove();

  const typingBubble = document.createElement('div');
  typingBubble.id = 'typing-bubble';
  typingBubble.className = 'flex items-center space-x-3 md:space-x-4 animate-slide-up';
  typingBubble.innerHTML = `
    <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-900 shadow-lg shadow-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
      <i class="fas fa-shield-alt text-white text-sm md:text-base"></i>
    </div>
    <div class="bg-white px-4 md:px-5 py-3 md:py-4 rounded-3xl rounded-bl-lg shadow-lg shadow-black/8">
      <div class="flex space-x-1">
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
      </div>
    </div>
  `;
  chatbox.appendChild(typingBubble);
  chatbox.scrollTop = chatbox.scrollHeight;
}

async function sendMessage(message, imageFile, imageDesc) {
  try {
    const formData = new FormData();
    if (message) formData.append('message', message);
    if (imageFile) {
      formData.append('image', imageFile);
      if (imageDesc) formData.append('description', imageDesc);
    }

    // Show upload progress for images
    if (imageFile) {
      uploadProgress.classList.remove('hidden');
      uploadStatus.classList.add('hidden');
      simulateUploadProgress();
    }

    const response = await fetch('/api/chatbot/report', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: formData,
    });

    if (!response.ok) {
      const errText = await response.text();
      throw new Error(`HTTP ${response.status}: ${errText}`);
    }

    const data = await response.json();
    console.log('[✅ NLP Response]', data);

     if (data.session_reset) {
      // Clear any local state variables
      incidentData = {};
      conversationContext = {};
      conversationHistory = [];
      console.log('Session state reset');
    }

    // Show image analysis if available
    if (data.image_analysis && data.image_analysis.description) {
      showImageAnalysis(data.image_analysis.description);
    }

    setTimeout(() => {
      document.getElementById('typing-bubble')?.remove();
      addBotMessage(data.reply || '✔️ Message processed.');
      
      // Show completion message if report is complete
      if (data.is_complete && data.report_id) {
        setTimeout(() => {
          addBotMessage(`🎉 Great! Your incident report has been successfully submitted with ID: ${data.report_id}. OSAS security officer will review it shortly.`);
        }, 1500);
      }
      
    }, (data.delay || 1) * 1000);

  } catch (error) {
    console.error('🛑 Chatbot fetch error:', error);
    document.getElementById('typing-bubble')?.remove();
    addBotMessage('⚠️ Something went wrong. Please try again.');
    throw error;
  }
}

async function simulateUploadProgress() {
  for (let i = 0; i <= 100; i += 5) {
    uploadProgressBar.style.width = i + '%';
    await new Promise(resolve => setTimeout(resolve, 30));
  }
}

function showImageAnalysis(analysis) {
  imageAnalysisText.textContent = analysis;
  imageAnalysisContainer.classList.remove('hidden');
  setTimeout(() => {
    chatbox.scrollTop = chatbox.scrollHeight;
  }, 100);
}

function appendMessage(sender, text, isHtml = false) {
  const now = new Date();
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const dateKey = now.toDateString();

  addDateHeader(now, dateKey);

  const bubble = document.createElement('div');
  bubble.className = sender === 'user' ? 'flex justify-end' : 'flex items-start space-x-3 md:space-x-4';
  
  if (sender === 'user') {
    bubble.innerHTML = `
      <div class="max-w-[85%] md:max-w-[75%] animate-fade-in">
        <div class="bg-gradient-to-br from-gray-800 to-gray-600 text-white px-4 md:px-5 py-3 md:py-4 rounded-3xl rounded-br-lg text-sm md:text-base font-medium shadow-lg shadow-gray-800/30">
          ${isHtml ? text : escapeHtml(text)}
        </div>
        <div class="text-xs text-gray-500 text-right mt-2 px-2">${time}</div>
      </div>
    `;
  } else {
    bubble.innerHTML = `
      <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-900 shadow-lg shadow-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
        <i class="fas fa-shield-alt text-white text-sm md:text-base"></i>
      </div>
      <div class="max-w-[85%] md:max-w-[75%] animate-fade-in">
        <div class="bg-gray-50 border border-gray-200 font-['Roboto',sans-serif] tracking-wide leading-relaxed px-4 md:px-5 py-3 md:py-4 rounded-3xl rounded-bl-lg text-sm md:text-base">
          ${isHtml ? text : escapeHtml(text)}
        </div>
        <div class="text-xs text-gray-500 mt-2 px-2">${time}</div>
      </div>
    `;
  }
  
  chatbox.appendChild(bubble);
  chatbox.scrollTop = chatbox.scrollHeight;
}

function appendImageMessage(sender, imageFile, description = '') {
  const now = new Date();
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  
  const reader = new FileReader();
  reader.onload = function(e) {
    const bubble = document.createElement('div');
    bubble.className = 'flex justify-end animate-fade-in';
    
    bubble.innerHTML = `
      <div class="max-w-[85%] md:max-w-[75%]">
        <div class="bg-gradient-to-br from-gray-800 to-gray-600 p-3 rounded-3xl rounded-br-lg">
          <img src="${e.target.result}" alt="Uploaded evidence" class="chat-image rounded-2xl border-2 border-gray-200" />
          ${description ? `<p class="text-white text-sm mt-3 px-1">${escapeHtml(description)}</p>` : ''}
        </div>
        <div class="text-xs text-gray-500 text-right mt-2 px-2">${time}</div>
      </div>
    `;
    
    chatbox.appendChild(bubble);
    chatbox.scrollTop = chatbox.scrollHeight;
  };
  reader.readAsDataURL(imageFile);
}

function addDateHeader(now, dateKey) {
  const lastHeader = [...chatbox.querySelectorAll('.date-header')].pop();
  if (!lastHeader || lastHeader.dataset.date !== dateKey) {
    const header = document.createElement('div');
    header.className = 'date-header text-center py-3';
    header.dataset.date = dateKey;
    header.innerHTML = `<span class="bg-gray-200 text-gray-600 px-4 py-2 rounded-full text-xs font-medium">${getDateLabel(now)}</span>`;
    chatbox.appendChild(header);
  }
}

function addBotMessage(message) {
  typeBotMessage(message);
}

function typeBotMessage(fullMessage) {
  const now = new Date();
  const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const dateKey = now.toDateString();

  addDateHeader(now, dateKey);

  const bubble = document.createElement('div');
  bubble.className = 'flex items-start space-x-3 md:space-x-4 animate-slide-up';

  const avatar = document.createElement('div');
  avatar.className = 'w-8 h-8 md:w-10 md:h-10 bg-blue-900 shadow-lg shadow-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0';
  avatar.innerHTML = '<i class="fas fa-shield-alt text-white text-sm md:text-base"></i>';

  const messageContainer = document.createElement('div');
  messageContainer.className = 'max-w-[85%] md:max-w-[75%]';

  const messageDiv = document.createElement('div');
  messageDiv.className = 'bg-gray-50 border border-gray-200 font-["Roboto",sans-serif] tracking-wide leading-relaxed px-4 md:px-5 py-3 md:py-4 rounded-3xl rounded-bl-lg text-sm md:text-base';

  const typingSpan = document.createElement('span');
  const timeLabel = document.createElement('div');
  timeLabel.className = 'text-xs text-gray-500 mt-2 px-2';
  timeLabel.textContent = time;

  messageDiv.appendChild(typingSpan);
  messageContainer.appendChild(messageDiv);
  messageContainer.appendChild(timeLabel);
  bubble.appendChild(avatar);
  bubble.appendChild(messageContainer);
  chatbox.appendChild(bubble);
  chatbox.scrollTop = chatbox.scrollHeight;

  // Typing animation
  let i = 0;
  const typingSpeed = 10;
  const typingInterval = setInterval(() => {
    if (i < fullMessage.length) {
      typingSpan.innerHTML += escapeHtml(fullMessage[i]);
      chatbox.scrollTop = chatbox.scrollHeight;
      i++;
    } else {
      clearInterval(typingInterval);
    }
  }, typingSpeed);
}

function showError(message) {
  // Create a temporary error message
  const errorDiv = document.createElement('div');
  errorDiv.className = 'fixed top-4 right-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-lg z-50 animate-fade-in';
  errorDiv.innerHTML = `
    <div class="flex items-center space-x-2">
      <i class="fas fa-exclamation-triangle text-red-600"></i>
      <span>${escapeHtml(message)}</span>
      <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-red-700 hover:text-red-900">×</button>
    </div>
  `;
  document.body.appendChild(errorDiv);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (errorDiv.parentNode) {
      errorDiv.remove();
    }
  }, 5000);
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.innerText = text;
  return div.innerHTML;
}

function getDateLabel(date) {
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  if (date.toDateString() === today.toDateString()) return "Today";
  if (date.toDateString() === yesterday.toDateString()) return "Yesterday";
  return date.toLocaleDateString();
}

// Add drag and drop functionality
chatbox.addEventListener('dragover', (e) => {
  e.preventDefault();
  chatbox.classList.add('border-emerald-300', 'bg-emerald-50');
});

chatbox.addEventListener('dragleave', (e) => {
  e.preventDefault();
  chatbox.classList.remove('border-emerald-300', 'bg-emerald-50');
});

chatbox.addEventListener('drop', (e) => {
  e.preventDefault();
  chatbox.classList.remove('border-emerald-300', 'bg-emerald-50');
  
  const files = e.dataTransfer.files;
  if (files.length > 0 && files[0].type.startsWith('image/')) {
    if (validateImageFile(files[0])) {
      selectedImage = files[0];
      displayImagePreview(files[0]);
    }
  }
});
</script>

</body>
</html>