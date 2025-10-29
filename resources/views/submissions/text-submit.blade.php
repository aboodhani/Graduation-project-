@extends('layouts.app')
@vite(['resources/css/style.css'])

{{-- Page-scoped styles --}}
@section('head')
<style>
  /* Answer textarea */
  .form-control#content {
    min-height: 220px;
    background: #f8f9fb;
    border-color: #e8e9ee;
  }

  /* Prevent horizontal scroll anywhere */
  :root { --rail-w: 220px; }

  html, body { max-width: 100%; overflow-x: hidden; }

  @media (min-width: 992px){
    main.sections-main { padding-right: calc(var(--rail-w) + 16px); }

    .score-range {
      width: 100%;
      touch-action: pan-y;
    }
  }

  /* Range slider */
  .score-range {
    height: 2rem;
    --track: #e8ebf2;
    --fill: #0d6efd;
  }
  .score-range::-webkit-slider-runnable-track {
    height: .5rem; background: var(--track); border-radius: 999px;
  }
  .score-range::-moz-range-track {
    height: .5rem; background: var(--track); border-radius: 999px;
  }
  .score-range::-webkit-slider-thumb {
    -webkit-appearance: none; appearance: none;
    width: 18px; height: 18px; background: #0d6efd;
    border: 2px solid #fff; border-radius: 50%;
    margin-top: -7px; box-shadow: 0 2px 6px rgba(13,110,253,.35);
  }
  .score-range::-moz-range-thumb {
    width: 18px; height: 18px; background: #0d6efd;
    border: 2px solid #fff; border-radius: 50%;
    box-shadow: 0 2px 6px rgba(13,110,253,.35);
  }

  /* Bubble above thumb */
  .score-field { padding-top: 14px; position: relative; max-width: 320px; }
  .score-bubble {
    position: absolute; top: -2px; left: 0; transform: translateX(-50%);
    padding: 2px 8px; background: #0d6efd; color: #fff; border-radius: 999px;
    font-weight: 700; font-size: .85rem; white-space: nowrap;
    box-shadow: 0 6px 18px rgba(13,110,253,.25);
    pointer-events: none; transition: left .08s ease-out;
  }

  /* Compact number box */
  #scoreNumber {
    width: 84px;
    padding-right: .5rem;
    padding-left: .5rem;
  }

  @media (max-width: 991.98px) {
    .score-field { max-width: 100%; }
  }
</style>
@endsection

@section('content')
<div class="container mt-5">
  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-1">{{ $assignment->title ?? ('Assignment #'.$assignment->id) }}</h3>
      @if(!empty($assignment->deadline))
        <p class="text-muted mb-0">Deadline:
          {{ \Carbon\Carbon::parse($assignment->deadline)->format('Y-m-d H:i') }}
        </p>
      @endif
      <span class="badge bg-secondary mt-2">Text Only</span>
    </div>
    <a href="{{ route('student.assignments') }}" class="btn btn-outline-secondary">← Back</a>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @error('content')
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror
  @error('score')
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror

  {{-- Form --}}
  <form method="POST" action="{{ route('assignments.text.store', $assignment->id) }}">
    @csrf

    {{-- Answer --}}
    <div class="mb-3">
      <label class="form-label fw-semibold">Your Answer</label>
      <textarea
        name="content"
        id="content"
        rows="10"
        class="form-control"
        placeholder="Write your answer..."
        style="width: 81% !important"
      >{{ old('content', $submission->content ?? '') }}</textarea>
    </div>

    {{-- Score (range 10–30) with bubble + number input --}}
    <div class="mb-4">
      <div class="d-flex justify-content-start align-items-center mb-1">
        <label for="scoreRange" class="form-label fw-semibold mb-0">Score</label>
        <small class="text-muted ms-5">Drag or type a number (10–30)</small>
      </div>

      <div class="row g-3 align-items-center">
        <div class="col-12 col-lg-auto">
          <div class="score-field">
            <span id="scoreBubble" class="score-bubble">
              {{ old('score', $submission->score ?? 15) }}
            </span>

            <input
              type="range"
              class="form-range score-range"
              id="scoreRange"
              name="score"
              min="10"
              max="30"
              step="1"
              value="{{ old('score', $submission->score ?? 15) }}"
              list="scoreTicks"
              required
            >

            <div class="d-flex justify-content-between mt-1 small text-muted">
              <span>10</span><span>20</span><span>30</span>
            </div>

            <datalist id="scoreTicks">
              <option value="10"></option>
              <option value="15"></option>
              <option value="20"></option>
              <option value="25"></option>
              <option value="30"></option>
            </datalist>
          </div>
        </div>

        <div class="col-auto">
          <div class="input-group w-auto">
            <span class="input-group-text">#</span>
            <input
              type="number"
              id="scoreNumber"
              class="form-control"
              inputmode="numeric"
              min="10"
              max="30"
              step="1"
              placeholder="10–30"
              value="{{ old('score', $submission->score ?? 15) }}"
              aria-label="Score (10 to 30)"
            >
          </div>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Submit</button>
      <button class="btn btn-outline-secondary" type="button" id="saveDraftBtn">Save draft (local)</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  // Local draft for content
  (function(){
    const key = 'textSubmitDraft-{{ $assignment->id }}';
    const ta  = document.getElementById('content');
    const btn = document.getElementById('saveDraftBtn');
    if (ta && localStorage.getItem(key) && !ta.value) ta.value = localStorage.getItem(key);
    btn?.addEventListener('click', () => {
      localStorage.setItem(key, ta.value || '');
      btn.textContent = 'Draft saved ✓';
      setTimeout(() => btn.textContent = 'Save draft (local)', 1200);
    });
  })();

  // Sync range, number, and bubble
  (function(){
    const range  = document.getElementById('scoreRange');
    const number = document.getElementById('scoreNumber');
    const bubble = document.getElementById('scoreBubble');

    function clamp(v, min, max){
      v = parseInt(v,10); if (isNaN(v)) return min;
      return Math.min(max, Math.max(min, v));
    }

    function setBubble(){
      const min = parseInt(range.min,10) || 1;
      const max = parseInt(range.max,10) || 30;
      const val = parseInt(range.value,10);
      bubble.textContent = val;
      const percent = (val - min) / (max - min);
      const trackW  = range.clientWidth;
      bubble.style.left = `${percent * trackW}px`;
    }

    function fromRange(){
      number.value = range.value;
      setBubble();
    }

    function fromNumber(){
      const v = clamp(number.value, parseInt(range.min,10), parseInt(range.max,10));
      number.value = v;
      range.value  = v;
      setBubble();
    }

    range?.addEventListener('input', fromRange);
    number?.addEventListener('input', fromNumber);

    fromRange();
    window.addEventListener('resize', setBubble);
  })();
</script>
@endpush
