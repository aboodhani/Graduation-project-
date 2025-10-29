@extends('layouts.app')
@vite(['resources/css/style.css'])

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* ===== Layout fixes ===== */
:root{ --header-h:64px; --rail-w:220px; }

/* kill stray horizontal scroll everywhere */
html, body { max-width:100%; overflow-x:hidden; }

/* main area respects fixed right rail on lg+ */
@media (min-width: 992px){
  .app-main{ padding-right: calc(var(--rail-w) + 16px); }
}

/* Centered content column */
@media (min-width: 992px){
  .col-lg-10.col-xl-9 { margin-left:auto; margin-right:auto; }
}

/* cards look tighter and consistent */
.card.border-0.shadow-sm{ border-radius: 14px; }
.divider{ border:0; border-top:1px solid #eef1f4; margin: 1rem 0; }

/* ===== Upload area ===== */
.border-dashed{ border-style: dashed !important; }
.upload-area{
  min-height: 280px;
  transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
  background: #fff;
}
.upload-area:hover,
.upload-area.dragover{
  border-color: rgba(13,110,253,.55);
  background-color: rgba(13,110,253,.03);
  box-shadow: 0 0 0 .25rem rgba(13,110,253,.12);
}
#uploadIcon svg{ display:block; margin-inline:auto; }

/* Preview image stays contained */
.preview-img{
  max-height: 260px; max-width: 100%;
  object-fit: cover; border-color:#e9ecef !important;
}

/* Upload status (your JS toggles classes) */
.upload-status{ display:none; border:0; }
.upload-status.show{ display:block; }
.upload-status.success{ background:#e8f5e9; color:#1b5e20; }
.upload-status.error{ background:#fdecea; color:#b00020; }
.upload-status.info{ background:#eef4ff; color:#084298; }

/* ===== Stepper badges (1 / 2 / 3) ===== */
.stepper{ display:flex; gap:12px; padding:0; margin:0 0 1rem 0; list-style:none; }
.stepper li{
  display:flex; align-items:center; gap:.5rem; color:#6c757d;
  font-weight:500;
}
.stepper li::before{
  content: attr(data-step);
  display:inline-grid; place-items:center;
  width:28px; height:28px; border-radius:999px;
  background:#f1f3f5; color:#6c757d; font-weight:700; font-size:.85rem;
}
.stepper li.active{ color:#212529; }
.stepper li.active::before{ background:#0d6efd; color:#fff; }
</style>

<main class="m-auto mt-2">
  <div class="container-xl py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10 col-xl-9">
        {{-- Optional server-side error --}}
        @if($errors->any())
          <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            {{ $errors->first() }}
          </div>
        @endif

        @php
          $assignmentId = $assignment->id ?? request()->query('assignment') ?? null;
        @endphp
 {{-- Tips card (optional) --}}
        <div class="card border-0 mb-3 shadow-sm">
          <div class="card-body p-4">
            <h2 class="h6 mb-3">Upload Tips</h2>
            <ul class="list-unstyled small text-secondary mb-0">
              <li class="mb-2">• Use a clear periapical radiograph.</li>
              <li class="mb-2">• Crop out unrelated borders if possible.</li>
              <li class="mb-2">• Supported formats: JPG, PNG, WEBP.</li>
              <li class="mb-2">• Max size: 10 MB.</li>
              <li>• Results appear instantly after analysis.</li>
            </ul>
          </div>
        </div>
        {{-- Upload & Analyze --}}
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4 p-lg-5">

            {{-- Progress / steps (visual only) --}}
            <ol class="stepper mb-4">
              <li class="active" data-step="1">Upload</li>
              <li data-step="2">Analyze</li>
              <li data-step="3">Result</li>
            </ol>

            {{-- Description --}}
            <section class="mb-3">
              <p class="mb-0 text-secondary">
                This online, AI-powered tool helps dental students get instant feedback on Working Length radiographs.
              </p>
            </section>

            {{-- Real form kept for graceful fallback --}}
            <form id="uploadForm"
                  action="{{ route('predict') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="needs-validation"
                  novalidate>
              @csrf
              @if($assignmentId)
                <input type="hidden" name="assignment_id" value="{{ $assignmentId }}">
              @endif

              {{-- Drag & drop area --}}
              <div id="uploadArea"
                   class="upload-area border border-2 border-dashed rounded-4 p-4 p-md-5 text-center"
                   role="button" tabindex="0"
                   aria-label="Upload radiograph by clicking or dragging a file here">
                <div id="uploadIcon" class="mb-3">
                  <svg width="48" height="48" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 16V4m0 0-4 4m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                          fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                  </svg>
                </div>
                <p class="upload-text fw-semibold mb-1">Drag & drop your image here, or click to browse</p>
                <p class="upload-formats text-muted small mb-0">
                  JPG · JPEG · PNG · WEBP — Max 10 MB
                </p>

                {{-- hidden input clicked by JS --}}
                <input id="fileInput"
                       class="visually-hidden"
                       type="file"
                       name="image"
                       accept="image/jpeg,image/jpg,image/png,image/webp"
                       @if(!$assignmentId) required @endif>

                {{-- Preview (hidden initially) --}}
                <div id="photoPreview" class="mt-4" style="display:none;">
                  <div class="row g-3 align-items-center justify-content-center">
                    <div class="col-12 col-md-auto">
                      <img id="previewImage"
                           src=""
                           alt="Selected radiograph preview"
                           class="preview-img rounded-3 border">
                    </div>
                    <div class="col-12 col-md">
                      <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                          <div class="fw-semibold" id="previewFilename">selected-image.jpg</div>
                          <div class="text-muted small">Looks good. Click <strong>Analyze</strong> to proceed.</div>
                        </div>
                        <button id="removePhoto" type="button" class="btn btn-outline-secondary">
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Status + helper --}}
              <div class="mt-3">
                <div id="uploadStatus" class="upload-status alert py-2 px-3 small mb-2"></div>
                <small id="submit-help" class="text-muted">Upload an image to enable analysis</small>
              </div>

              {{-- Actions --}}
              <div class="d-flex flex-wrap gap-2 mt-4">
                <button id="submitBtn" type="button" class="btn btn-primary btn-lg px-4">
                  <span class="btn-text">Analyze Radiograph</span>
                  <span class="btn-loader spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" style="display:none;"></span>
                </button>

                

                @if($assignmentId)
                  <span class="ms-auto small text-muted">
                    Assignment: <code>{{ $assignmentId }}</code>
                  </span>
                @endif
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ARIA live region for screen readers (your JS fills it) --}}
  <div id="aria-live-region" aria-live="polite" aria-atomic="true" class="visually-hidden"></div>

  {{-- Keep your existing app.js if needed --}}
  <script src="{{ asset('project1/static/js/app.js') }}"></script>
</main>
@endsection

<script>
/**
 * AI-Dental JU Application
 * Advanced JavaScript functionality for dental radiograph analysis
 * 
 * Features:
 * - File upload with drag & drop support
 * - Image validation and preview
 * - AI prediction integration
 * - Real-time date display
 * - Quick upload functionality
 * - Accessibility enhancements
 * - Error handling and user feedback
 * 
 * @author University of Jordan - Faculty of Dentistry
 * @version 2.0.0
 */

// ===== GLOBAL VARIABLES =====
let uploadedFile = null;
let isResultPage = false;
let uploadTimeout = null;

// Configuration constants
const CONFIG = {
    // File upload constraints
    MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB in bytes
    ALLOWED_TYPES: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],

    // API endpoints
    PREDICT_ENDPOINT: '/predict',

    // UI timing
    UPLOAD_DEBOUNCE_DELAY: 300,
    DATE_UPDATE_INTERVAL: 1000,

    // Animation durations
    FADE_DURATION: 600,
    SLIDE_DURATION: 800
};

// ===== APPLICATION INITIALIZATION =====
/**
 * Initialize the application when DOM is fully loaded
 * Detects current page and initializes appropriate functionality
 */
document.addEventListener('DOMContentLoaded', () => {
    try {
        // Detect current page type
        isResultPage = detectResultPage();

        // Initialize page-specific functionality
        if (isResultPage) {
            initializeResultPage();
        } else {
            initializeUploadPage();
        }

        // Apply global enhancements
        initializeGlobalFeatures();

        console.log(`AI-Dental JU initialized successfully (${isResultPage ? 'Result' : 'Upload'} page)`);
    } catch (error) {
        console.error('Application initialization failed:', error);
        showErrorMessage('Application failed to initialize. Please refresh the page.');
    }
});

// ===== PAGE DETECTION =====
/**
 * Detect if current page is the result page
 * @returns {boolean} True if on result page, false otherwise
 */
function detectResultPage() {
    return window.location.pathname.endsWith('/result') ||
        window.location.pathname.endsWith('/result/') ||
        document.getElementById('uploadedImage') !== null;
}

// ===== GLOBAL FEATURES INITIALIZATION =====
/**
 * Initialize features that are common to all pages
 */
function initializeGlobalFeatures() {
    // Add fade-in animation to main container
    const container = document.querySelector('.container');
    if (container) {
        container.classList.add('fade-in');
    }

    // Initialize keyboard navigation
    initializeKeyboardNavigation();

    // Initialize accessibility enhancements
    initializeAccessibilityFeatures();

    // Initialize error handling
    window.addEventListener('error', handleGlobalError);
    window.addEventListener('unhandledrejection', handleUnhandledRejection);
}

// ===== UPLOAD PAGE FUNCTIONALITY =====
/**
 * Ensure the upload area starts in the correct initial state
 */
function ensureInitialState() {
    const photoPreview = document.getElementById('photoPreview');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.querySelector('.upload-text');
    const uploadFormats = document.querySelector('.upload-formats');

    // Hide preview, show upload elements
    if (photoPreview) {
        photoPreview.style.display = 'none';
        console.log('Photo preview hidden');
    }
    if (uploadIcon) {
        uploadIcon.style.display = 'block';
        console.log('Upload icon shown');
    }
    if (uploadText) {
        uploadText.style.display = 'block';
        console.log('Upload text shown');
    }
    if (uploadFormats) {
        uploadFormats.style.display = 'block';
        console.log('Upload formats shown');
    }

    console.log('Initial state set correctly');
}

/**
 * Initialize upload page specific functionality
 * Sets up file upload, drag & drop, and form submission
 */
function initializeUploadPage() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const submitBtn = document.getElementById('submitBtn');

    // Validate required elements exist
    if (!uploadArea || !fileInput || !submitBtn) {
        console.error('Required upload elements not found');
        return;
    }

    // Ensure initial state is correct
    ensureInitialState();

    // Initialize upload area interactions
    initializeUploadArea(uploadArea, fileInput);

    // Initialize file input handling
    initializeFileInput(fileInput, submitBtn);

    // Initialize submit button
    initializeSubmitButton(submitBtn);

    console.log('Upload page initialized successfully');
}

/**
 * Initialize upload area with click and drag & drop functionality
 * @param {HTMLElement} uploadArea - The upload area element
 * @param {HTMLInputElement} fileInput - The file input element
 */
function initializeUploadArea(uploadArea, fileInput) {
    // Click to upload
    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    // Keyboard accessibility
    uploadArea.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
        }
    });

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', (event) => handleDrop(event, fileInput));

    // Prevent default drag behaviors on document
    document.addEventListener('dragover', (e) => e.preventDefault());
    document.addEventListener('drop', (e) => e.preventDefault());
}

/**
 * Initialize file input change handling
 * @param {HTMLInputElement} fileInput - The file input element
 * @param {HTMLButtonElement} submitBtn - The submit button element
 */
function initializeFileInput(fileInput, submitBtn) {
    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            handleFileSelection(file, submitBtn);
        }
    });
}

/**
 * Initialize submit button functionality
 * @param {HTMLButtonElement} submitBtn - The submit button element
 */
function initializeSubmitButton(submitBtn) {
    submitBtn.addEventListener('click', handleSubmit);

    // Add loading state management
    submitBtn.addEventListener('mouseenter', () => {
        if (!submitBtn.disabled) {
            submitBtn.style.transform = 'translateY(-2px)';
        }
    });

    submitBtn.addEventListener('mouseleave', () => {
        if (!submitBtn.disabled) {
            submitBtn.style.transform = '';
        }
    });
}

// ===== FILE HANDLING FUNCTIONS =====
/**
 * Handle drag over event
 * @param {DragEvent} event - The drag event
 */
function handleDragOver(event) {
    event.preventDefault();
    event.currentTarget.classList.add('dragover');
}

/**
 * Handle drag leave event
 * @param {DragEvent} event - The drag event
 */
function handleDragLeave(event) {
    event.currentTarget.classList.remove('dragover');
}

/**
 * Handle file drop event
 * @param {DragEvent} event - The drop event
 * @param {HTMLInputElement} fileInput - The file input element
 */
function handleDrop(event, fileInput) {
    event.preventDefault();
    event.currentTarget.classList.remove('dragover');

    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];

        // Update file input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Handle file selection
        const submitBtn = document.getElementById('submitBtn');
        handleFileSelection(file, submitBtn);
    }
}

/**
 * Handle file selection with validation
 * @param {File} file - The selected file
 * @param {HTMLButtonElement} submitBtn - The submit button element
 */
function handleFileSelection(file, submitBtn) {
    console.log('File selected:', file.name, file.type, file.size);

    // Clear any existing timeout
    if (uploadTimeout) {
        clearTimeout(uploadTimeout);
    }

    // Debounce file processing
    uploadTimeout = setTimeout(() => {
        if (validateFile(file)) {
            uploadedFile = file;
            console.log('File validated, showing preview...');
            showPhotoPreview(file);
            enableSubmitButton(submitBtn);
            showUploadStatus('File selected successfully', 'success');
        } else {
            console.log('File validation failed');
            resetUploadArea();
            disableSubmitButton(submitBtn);
        }
    }, CONFIG.UPLOAD_DEBOUNCE_DELAY);
}

/**
 * Validate uploaded file
 * @param {File} file - The file to validate
 * @returns {boolean} True if file is valid, false otherwise
 */
function validateFile(file) {
    // Check file type
    if (!CONFIG.ALLOWED_TYPES.includes(file.type.toLowerCase())) {
        showUploadStatus(
            `Invalid file type. Please select: ${CONFIG.ALLOWED_TYPES.join(', ')}`,
            'error'
        );
        return false;
    }

    // Check file size
    if (file.size > CONFIG.MAX_FILE_SIZE) {
        const maxSizeMB = CONFIG.MAX_FILE_SIZE / (1024 * 1024);
        showUploadStatus(
            `File too large. Maximum size: ${maxSizeMB}MB`,
            'error'
        );
        return false;
    }

    return true;
}

/**
 * Show photo preview in the upload area
 * @param {File} file - The selected file
 */
function showPhotoPreview(file) {
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');
    const previewFilename = document.getElementById('previewFilename');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.querySelector('.upload-text');
    const uploadFormats = document.querySelector('.upload-formats');

    console.log('showPhotoPreview called with elements:', {
        photoPreview,
        previewImage,
        previewFilename
    });

    if (photoPreview && previewImage && previewFilename) {
        // Create preview URL
        const previewURL = URL.createObjectURL(file);
        console.log('Created preview URL:', previewURL);

        // Set image source and filename
        previewImage.src = previewURL;
        previewFilename.textContent = file.name;

        // Debug: Check if image source was set
        console.log('Image source set to:', previewImage.src);
        console.log('Image element:', previewImage);
        console.log('Image dimensions:', previewImage.width, 'x', previewImage.height);

        // Add error handling for image load
        previewImage.onload = () => {
            console.log('Image loaded successfully');
            console.log('Image natural dimensions:', previewImage.naturalWidth, 'x', previewImage.naturalHeight);
            console.log('Image display dimensions:', previewImage.width, 'x', previewImage.height);
        };

        previewImage.onerror = (error) => {
            console.error('Failed to load image:', error);
            console.error('Image source was:', previewImage.src);
        };

        // Show preview, hide upload elements
        photoPreview.style.display = 'block';
        console.log('Photo preview displayed');

        if (uploadIcon) {
            uploadIcon.style.display = 'none';
            console.log('Upload icon hidden');
        }
        if (uploadText) {
            uploadText.style.display = 'none';
            console.log('Upload text hidden');
        }
        if (uploadFormats) {
            uploadFormats.style.display = 'none';
            console.log('Upload formats hidden');
        }

        // Add success styling to upload area
        const uploadArea = document.getElementById('uploadArea');
        if (uploadArea) {
            uploadArea.style.borderColor = 'var(--color-success)';
            uploadArea.style.backgroundColor = '#ecfdf5';
        }

        // Initialize remove photo button
        initializeRemovePhotoButton();

        // Double-check the state after a short delay
        setTimeout(() => {
            console.log('Final state check:');
            console.log('Photo preview display:', photoPreview.style.display);
            console.log('Upload icon display:', uploadIcon ? uploadIcon.style.display : 'N/A');
            console.log('Upload text display:', uploadText ? uploadText.style.display : 'N/A');
            console.log('Upload formats display:', uploadFormats ? uploadFormats.style.display : 'N/A');
        }, 100);

        console.log('Photo preview shown successfully');
    } else {
        console.error('Some preview elements not found:', {
            photoPreview,
            previewImage,
            previewFilename
        });
    }
}

/**
 * Initialize remove photo button functionality
 */
function initializeRemovePhotoButton() {
    const removePhotoBtn = document.getElementById('removePhoto');
    if (removePhotoBtn) {
        // Remove any existing event listeners to prevent duplicates
        removePhotoBtn.replaceWith(removePhotoBtn.cloneNode(true));

        // Get the new button reference
        const newRemovePhotoBtn = document.getElementById('removePhoto');
        if (newRemovePhotoBtn) {
            newRemovePhotoBtn.addEventListener('click', handleRemovePhoto);
        }
    }
}

/**
 * Handle removing the selected photo
 */
function handleRemovePhoto() {
    const photoPreview = document.getElementById('photoPreview');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.querySelector('.upload-text');
    const uploadFormats = document.querySelector('.upload-formats');
    const fileInput = document.getElementById('fileInput');
    const submitBtn = document.getElementById('submitBtn');

    // Hide preview, show upload elements
    if (photoPreview) photoPreview.style.display = 'none';
    if (uploadIcon) uploadIcon.style.display = 'block';
    if (uploadText) uploadText.style.display = 'block';
    if (uploadFormats) uploadFormats.style.display = 'block';

    // Reset upload area styling
    const uploadArea = document.getElementById('uploadArea');
    if (uploadArea) {
        uploadArea.style.borderColor = '';
        uploadArea.style.backgroundColor = '';
    }

    // Clear file input and uploaded file
    if (fileInput) fileInput.value = '';
    uploadedFile = null;

    // Disable submit button
    if (submitBtn) {
        disableSubmitButton(submitBtn);
    }

    // Show status message
    showUploadStatus('Photo removed. Please select a new image.', 'info');
}

/**
 * Reset upload area to default state
 */
function resetUploadArea() {
    const photoPreview = document.getElementById('photoPreview');
    const uploadIcon = document.getElementById('uploadIcon');
    const uploadText = document.querySelector('.upload-text');
    const uploadFormats = document.querySelector('.upload-formats');
    const uploadArea = document.getElementById('uploadArea');

    // Hide preview, show upload elements
    if (photoPreview) photoPreview.style.display = 'none';
    if (uploadIcon) uploadIcon.style.display = 'block';
    if (uploadText) uploadText.style.display = 'block';
    if (uploadFormats) uploadFormats.style.display = 'block';

    // Reset upload area styling
    if (uploadArea) {
        uploadArea.style.borderColor = '';
        uploadArea.style.backgroundColor = '';
    }
}

/**
 * Enable submit button
 * @param {HTMLButtonElement} submitBtn - The submit button element
 */
function enableSubmitButton(submitBtn) {
    submitBtn.disabled = false;
    const helpText = document.getElementById('submit-help');
    if (helpText) {
        helpText.textContent = 'Click to analyze your radiograph';
    }
}

/**
 * Disable submit button
 * @param {HTMLButtonElement} submitBtn - The submit button element
 */
function disableSubmitButton(submitBtn) {
    submitBtn.disabled = true;
    const helpText = document.getElementById('submit-help');
    if (helpText) {
        helpText.textContent = 'Upload an image to enable analysis';
    }
}

/**
 * Show upload status message
 * @param {string} message - The status message
 * @param {string} type - The message type ('success' or 'error')
 */
function showUploadStatus(message, type) {
    const statusElement = document.getElementById('uploadStatus');
    if (statusElement) {
        statusElement.textContent = message;
        statusElement.className = `upload-status ${type} show`;

        // Auto-hide after 5 seconds
        setTimeout(() => {
            statusElement.classList.remove('show');
        }, 5000);
    }
}

// ===== FORM SUBMISSION =====
/**
 * Handle form submission and AI prediction
 */
async function handleSubmit() {
    if (!uploadedFile) {
        showErrorMessage('Please select an image file first.');
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');

    try {
        setLoadingState(submitBtn, btnText, btnLoader, true);

        const formData = new FormData();
        formData.append('image', uploadedFile, uploadedFile.name);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(CONFIG.PREDICT_ENDPOINT, {
            method: 'POST',
            headers: csrfToken ? {'X-CSRF-TOKEN': csrfToken} : {},
            body: formData,
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (!response.ok || !data.ok) {
            throw new Error(data.error || 'Prediction failed');
        }

        // === SUCCESS: redirect to /result ===
        // Optionally, store data in localStorage if needed
        localStorage.setItem('aiResult', JSON.stringify(data));
        window.location.href = '/result';

    } catch (error) {
        console.error('Submission error:', error);
        showErrorMessage(error.message || 'Analysis failed. Please try again.');
        setLoadingState(submitBtn, btnText, btnLoader, false);
    }
}


/**
 * Set loading state for submit button
 * @param {HTMLButtonElement} submitBtn - The submit button
 * @param {HTMLElement} btnText - The button text element
 * @param {HTMLElement} btnLoader - The button loader element
 * @param {boolean} loading - Whether to show loading state
 */
function setLoadingState(submitBtn, btnText, btnLoader, loading) {
    if (loading) {
        btnText.textContent = 'Analyzing...';
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    } else {
        btnText.textContent = 'Analyze Radiograph';
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
    }
}

/**
 * Store AI results and navigate to result page
 * @param {Object} data - The AI prediction data
 */
async function storeResultsAndNavigate(data) {
    try {
        // Store AI results
        const results = {
            code: data.code,
            label: data.label,
            feedback: data.feedback,
            timestamp: new Date().toISOString()
        };

        localStorage.setItem('aiResult', JSON.stringify(results));

        // Store image preview
        const imageData = await fileToBase64(uploadedFile);
        localStorage.setItem('uploadedImageData', imageData);
        localStorage.setItem('uploadedFileName', uploadedFile.name);

        // Navigate to result page
        window.location.href = '/result';

    } catch (error) {
        console.error('Error storing results:', error);
        throw new Error('Failed to process results');
    }
}

/**
 * Convert file to base64 string
 * @param {File} file - The file to convert
 * @returns {Promise<string>} Base64 string representation
 */
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

// ===== RESULT PAGE FUNCTIONALITY =====
/**
 * Initialize result page specific functionality
 */
function initializeResultPage() {
    // Initialize real-time date display
    initializeDateDisplay();

    // Load and display uploaded image
    loadUploadedImage();

    // Load and display AI results
    loadAIResults();

    // Initialize action buttons
    initializeActionButtons();

    console.log('Result page initialized successfully');
}

/**
 * Initialize real-time date display
 */
function initializeDateDisplay() {
    updateRealTimeDate();
    setInterval(updateRealTimeDate, CONFIG.DATE_UPDATE_INTERVAL);
}

/**
 * Update real-time date display
 */
function updateRealTimeDate() {
    const dateElement = document.getElementById('realTimeDate');
    if (dateElement) {
        const now = new Date();
        const formattedDate = now.toLocaleDateString('en-US', {
            month: 'numeric',
            day: 'numeric',
            year: 'numeric'
        });
        const formattedTime = now.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: false
        });

        dateElement.textContent = `${formattedDate} ${formattedTime}`;
        dateElement.setAttribute('datetime', now.toISOString());
    }
}

/**
 * Load and display uploaded image
 */
function loadUploadedImage() {
    const imageData = localStorage.getItem('uploadedImageData');
    const imageElement = document.getElementById('uploadedImage');

    if (imageData && imageElement) {
        imageElement.src = imageData;
        imageElement.onload = () => {
            imageElement.parentElement.classList.add('slide-up');
        };
        imageElement.onerror = () => {
            console.error('Failed to load uploaded image');
            showErrorMessage('Failed to load uploaded image');
        };
    }
}

/**
 * Load and display AI analysis results
 */
function loadAIResults() {
    const resultsData = localStorage.getItem('aiResult');

    if (resultsData) {
        try {
            const results = JSON.parse(resultsData);
            displayResults(results);
        } catch (error) {
            console.error('Error parsing AI results:', error);
            showErrorMessage('Failed to load analysis results');
        }
    } else {
        // Show placeholder if no results
        displayPlaceholderResults();
    }
}

/**
 * Display AI analysis results
 * @param {Object} results - The AI results object
 */
function displayResults(results) {
    // Update result label
    const resultLabel = document.getElementById('resultLabel');
    if (resultLabel) {
        resultLabel.textContent = results.label || 'Unknown';
        resultLabel.className = `result-value ${getResultClass(results.code)}`;
    }

    // Update overlay title with the result
    const overlayTitle = document.getElementById('overlayTitle');
    if (overlayTitle) {
        overlayTitle.textContent = results.label || 'Working Length Radiograph';
    }

    // Update feedback text
    const feedbackText = document.getElementById('feedbackText');
    if (feedbackText) {
        feedbackText.textContent = results.feedback || 'No feedback available';
    }

    // Add slide-up animation to info section
    setTimeout(() => {
        const infoSection = document.querySelector('.info-section');
        if (infoSection) {
            infoSection.classList.add('slide-up');
        }
    }, 300);
}

/**
 * Display placeholder results when no data is available
 */
function displayPlaceholderResults() {
    const resultLabel = document.getElementById('resultLabel');
    const feedbackText = document.getElementById('feedbackText');

    if (resultLabel) resultLabel.textContent = 'No analysis available';
    if (feedbackText) feedbackText.textContent = 'Please upload and analyze a radiograph first.';
}

/**
 * Get CSS class for result type
 * @param {string} code - The result code
 * @returns {string} CSS class name
 */
function getResultClass(code) {
    const classMap = {
        'optimal': 'success',
        'under_extended': 'warning',
        'over_extended': 'error'
    };
    return classMap[code] || 'info';
}



/**
 * Initialize action buttons (back and survey)
 */
function initializeActionButtons() {
    // Back button
    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
        backBtn.addEventListener('click', handleBackNavigation);
    }

    // Survey button
    const surveyBtn = document.getElementById('surveyBtn');
    if (surveyBtn) {
        surveyBtn.addEventListener('click', handleSurveyOpen);
    }
}

/**
 * Handle back navigation
 */
function handleBackNavigation() {
    // Clear stored data
    localStorage.removeItem('aiResult');
    localStorage.removeItem('uploadedImageData');
    localStorage.removeItem('uploadedFileName');

    // Navigate back to upload page
    window.location.href = '/';
}

/**
 * Handle survey opening
 */
function handleSurveyOpen() {
    const surveyUrl = 'http://127.0.0.1:5000/api/predict'; // Replace with actual survey URL
    window.open(surveyUrl, '_blank', 'noopener,noreferrer');
}



// ===== ACCESSIBILITY FEATURES =====
/**
 * Initialize accessibility enhancements
 */
function initializeAccessibilityFeatures() {
    // Add ARIA live regions for dynamic content
    addAriaLiveRegions();

    // Enhance focus management
    enhanceFocusManagement();

    // Add skip links if needed
    addSkipLinks();
}

/**
 * Add ARIA live regions for screen readers
 */
function addAriaLiveRegions() {
    if (!document.getElementById('aria-live-region')) {
        const liveRegion = document.createElement('div');
        liveRegion.id = 'aria-live-region';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.style.position = 'absolute';
        liveRegion.style.left = '-10000px';
        liveRegion.style.width = '1px';
        liveRegion.style.height = '1px';
        liveRegion.style.overflow = 'hidden';
        document.body.appendChild(liveRegion);
    }
}

/**
 * Enhance focus management for better keyboard navigation
 */
function enhanceFocusManagement() {
    // Trap focus in modals if any
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
            handleTabNavigation(event);
        }
    });
}

/**
 * Handle tab navigation
 * @param {KeyboardEvent} event - The keyboard event
 */
function handleTabNavigation(event) {
    const focusableElements = document.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
    }
}

/**
 * Add skip links for keyboard navigation
 */

// ===== KEYBOARD NAVIGATION =====
/**
 * Initialize keyboard navigation enhancements
 */
function initializeKeyboardNavigation() {
    document.addEventListener('keydown', handleKeyboardShortcuts);
}

/**
 * Handle keyboard shortcuts
 * @param {KeyboardEvent} event - The keyboard event
 */
function handleKeyboardShortcuts(event) {
    // Escape key handling
    if (event.key === 'Escape') {
        handleEscapeKey();
    }

    // Enter key handling for custom elements
    if (event.key === 'Enter') {
        handleEnterKey(event);
    }
}

/**
 * Handle escape key press
 */
function handleEscapeKey() {
    // Close any open modals or return to upload page from result page
    if (isResultPage) {
        const backBtn = document.getElementById('backBtn');
        if (backBtn) {
            backBtn.click();
        }
    }
}

/**
 * Handle enter key press
 * @param {KeyboardEvent} event - The keyboard event
 */
function handleEnterKey(event) {
    const target = event.target;

    // Handle enter on upload area
    if (target.id === 'uploadArea') {
        event.preventDefault();
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.click();
        }
    }
}

// ===== ERROR HANDLING =====
/**
 * Show error message to user
 * @param {string} message - The error message
 */
function showErrorMessage(message) {
    // Update ARIA live region
    const liveRegion = document.getElementById('aria-live-region');
    if (liveRegion) {
        liveRegion.textContent = `Error: ${message}`;
    }

    // Show visual error message
    const errorContainer = getOrCreateErrorContainer();
    errorContainer.textContent = message;
    errorContainer.className = 'error-message show';

    // Auto-hide after 8 seconds
    setTimeout(() => {
        errorContainer.classList.remove('show');
    }, 8000);

    console.error('User error:', message);
}


/**
 * Handle global JavaScript errors
 * @param {ErrorEvent} event - The error event
 */
function handleGlobalError(event) {
    console.error('Global error:', event.error);
    showErrorMessage('An unexpected error occurred. Please refresh the page.');
}

/**
 * Handle unhandled promise rejections
 * @param {PromiseRejectionEvent} event - The rejection event
 */
function handleUnhandledRejection(event) {
    console.error('Unhandled promise rejection:', event.reason);
    showErrorMessage('An unexpected error occurred. Please try again.');
}

// ===== UTILITY FUNCTIONS =====
/**
 * Debounce function to limit function calls
 * @param {Function} func - The function to debounce
 * @param {number} wait - The wait time in milliseconds
 * @returns {Function} The debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format file size for display
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Check if device is mobile
 * @returns {boolean} True if mobile device
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

// ===== PERFORMANCE MONITORING =====
/**
 * Log performance metrics
 */
function logPerformanceMetrics() {
    if ('performance' in window) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                console.log('Page load performance:', {
                    domContentLoaded: perfData.domContentLoadedEventEnd - perfData
                        .domContentLoadedEventStart,
                    loadComplete: perfData.loadEventEnd - perfData.loadEventStart,
                    totalTime: perfData.loadEventEnd - perfData.fetchStart
                });
            }, 0);
        });
    }
}

// Initialize performance monitoring
logPerformanceMetrics();

// ===== EXPORT FOR TESTING =====
// Export functions for unit testing if in test environment
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        validateFile,
        formatFileSize,
        debounce,
        isMobileDevice
    };
}

async function handleFileUpload(file) {
    const formData = new FormData();
    formData.append('image', file);

    try {
        const res = await fetch('http://127.0.0.1:5000/api/predict', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        console.log(data);
    } catch (err) {
        console.error('Upload failed', err);
    }
}
async function handleFileUpload(file) {
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const res = await fetch('http://127.0.0.1:5000/api/predict', {
            method: 'POST',
            body: formData
        });

        if (!res.ok) {
            const text = await res.text();
            console.error('Server returned error:', text);
            alert('Prediction failed. Check console.');
            return;
        }

        const data = await res.json();
        console.log('Prediction result:', data);
        alert(`Prediction: ${data.label || 'N/A'}`);
    } catch (err) {
        console.error('Upload failed', err);
        alert('Could not reach server. Make sure Flask is running on HTTP port 5000.');
    }
}

// ===== ATTACH FILE INPUT =====
const fileInput = document.getElementById('fileInput');
if (fileInput) {
    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        handleFileUpload(file);
    });
}
</script>