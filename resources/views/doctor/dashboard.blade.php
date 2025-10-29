@extends('layouts.doctor')
@vite(['resources/css/style.css'])
<style>
    @media (max-width: 1024px) {
        .right-tabs{
                width: 147px !important;
        }
    }
</style>
@section('content')
  <!-- Hero banner -->
  <section class="view view-home" data-view="home" aria-labelledby="homeHeading">
    <div class="hero position-sticky top-header z-0">
      <img src="{{ asset('images/hero.png') }}" alt="dental banner" class="hero-img" />
      <div class="hero-text">
        <h2 id="homeHeading" class="m-0">
          Welcome back <span id="welcomeName">{{ auth()->check() ? auth()->user()->name : '—' }}</span> !
        </h2>
      </div>
    </div>
  </section>

  <!-- Sections -->
  <section class="mt-5 container p-0">
    <h3 class="h5 fw-semibold mb-3">Your sections :</h3>

    @if(isset($sections) && $sections->isNotEmpty())
      <div class="row g-3">
        @foreach($sections as $section)
          <div class="col-12 col-md-6 ">
            <a href="{{ route('doctor.sections.show', $section) }}" class="text-decoration-none text-body">
              <div class="card w-75 border-0 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div>
                    <h5 class="card-title fw-bold mb-1 text-truncate">
                      {{ $section->title ?? $section->name ?? 'Section #'.$section->id }}
                    </h5>

                    @if(!empty($section->description))
                      <p class="card-text text-secondary mb-0">
                        {{ \Illuminate\Support\Str::limit($section->description, 120) }}
                      </p>
                    @endif
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">{{ optional($section->created_at)->format('Y-m-d') }}</small>
                    @if(isset($section->users_count))
                      <span class="badge text-bg-light border">{{ $section->users_count }} students</span>
                    @endif
                  </div>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>

      @isset($sections)
        <div class="mt-3">
          {{ method_exists($sections, 'links') ? $sections->links() : '' }}
        </div>
      @endisset
    @else
      <div class="alert alert-light border d-flex align-items-center" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M11 7h2v6h-2V7zm0 8h2v2h-2v-2z"/><path d="M12 2 1 21h22L12 2z"/>
        </svg>
        <div>You haven't created any sections yet.</div>
      </div>
    @endif
  </section>
@endsection
