@extends('layouts.app')
@vite(['resources/css/style.css','resources/css/model.css'])

@section('content')
<main class="result-content" role="main">
    <div class="container">
       

        @php
            // لو جت الصفحة بدون بيانات (رفرش مثلاً)، أعتمد على السيشن كـ fallback
            $aiResult      = $aiResult ?? session('aiResult', []);
            $uploadedImage = $uploadedImage ?? (session('uploadedImage') ? asset('storage/'.session('uploadedImage')) : null);

            // استخراج القيم بأمان
            $rawCode  = data_get($aiResult, 'code', null);
            $code     = is_numeric($rawCode) ? (int)$rawCode : -1;  // 0/1/2 وإلا -1
            $label    = data_get($aiResult, 'label', 'Unknown');
            $feedback = data_get($aiResult, 'feedback', 'No feedback available');

            // ماب للكود → كلاس CSS
            $codeClass = match ($code) {
                0       => 'success',   // optimal
                1       => 'warning',   // under_extended (افتراضي)
                2       => 'error',     // over_extended (افتراضي)
                default => 'info',      // غير معروف
            };
        @endphp

        <div class="result-layout">
            <section class="image-section">
                @if($uploadedImage)
                    <img src="{{ $uploadedImage }}" alt="Uploaded radiograph" class="uploaded-image">
                @endif
            </section>

            <section class="info-section">
                @if(empty($aiResult))
                    <div class="alert alert-warning" role="alert" style="margin-bottom:1rem;">
                        No analysis found. Please upload and analyze a radiograph first.
                    </div>
                    <div class="button-group">
                        <a href="{{ route('upload') }}" class="action-btn back-btn">Back to Upload</a>
                    </div>
                @else
                    <div class="result-header">
                        <div class="result-status">
                            <span class="result-label">Result:</span>
                            <span id="resultLabel" class="result-value {{ $codeClass }}">{{ $label }}</span>
                        </div>
                        <div class="timestamp-container">
                            <span class="timestamp-label">Analyzed:</span>
                            <time class="timestamp">{{ now()->format('m/d/Y H:i') }}</time>
                        </div>
                    </div>

                    <div class="feedback-section">
                        <h3 class="feedback-title">Feedback</h3>
                        <p id="feedbackText" class="feedback-text">{{ $feedback }}</p>
                    </div>

                    {{-- أسطورة الألوان (اختياري) --}}
                    <div class="legend" aria-hidden="true" style="margin-top:1rem; font-size:.9rem; opacity:.9;">
                        <span class="legend-item"><span class="dot success"></span> Optimal</span>
                        <span class="legend-item"><span class="dot warning"></span> Under-extended</span>
                        <span class="legend-item"><span class="dot error"></span> Over-extended</span>
                    </div>

                    <div class="button-group" style="margin-top:1rem;">
                        <a href="{{ route('upload') }}" class="action-btn back-btn">Back to Home</a>
                    </div>
                @endif
            </section>
        </div>
    </div>
</main>
@endsection
