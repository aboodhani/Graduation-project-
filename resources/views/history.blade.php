@extends('layouts.app')
@vite(['resources/css/style.css'])
<style>
  /* Small polish that complements Bootstrap */
  .icon-btn svg {
    display: block;
  }

  .icon-btn {
    gap: .4rem;
  }

  .object-fit-cover {
    object-fit: cover;
  }

  /* Reduce card title weight a bit for dense grids */
  .card .card-title {
    font-weight: 600;
  }

  /* Avoid layout shift when badges overlap image corners */
  .card .badge {
    border-radius: .5rem;
  }

  /* keep the right sidebar from overlapping the last column */
  @media (min-width: 992px) {
    .app-main {
      padding-right: 220px;
    }

    /* sidebar width + a bit */
  }

  .object-fit-cover {
    object-fit: cover;
  }

  /* subtle polish */
  .card {
    border-radius: .75rem;
  }

  .card .badge {
    border-radius: .5rem;
  }

  /* clean clamping (Bootstrap doesn't ship line-clamp utilities) */
  .line-clamp-1,
  .line-clamp-2,
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .line-clamp-1 {
    -webkit-line-clamp: 1;
  }

  .line-clamp-2 {
    -webkit-line-clamp: 2;
  }

  .line-clamp-3 {
    -webkit-line-clamp: 3;
  }

  /* optional: make all cards the same minimum height if your texts vary a lot */
  #cards>[data-code] .card {
    min-height: 360px;
  }

  /* tweak if needed */
</style>
@section('content')
  <main class="container py-3">

    <section class="view view-history" data-view="history" aria-labelledby="historyHeading">
      <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
        <div class="lead-wrap">
          <h2 id="historyHeading" class="visually-hidden">History</h2>
          <p class="text-secondary mb-0">
            View all your uploaded cases, along with the evaluation results and feedback. Track your progress over time.
          </p>
        </div>

        <button class="btn btn-outline-secondary icon-btn d-inline-flex align-items-center gap-2" id="filterBtn"
          data-bs-toggle="modal" data-bs-target="#filterModal" aria-haspopup="dialog" aria-controls="filterModal"
          aria-expanded="false" title="Filter">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M4 7h16M7 7v6m0 0h10m-6 0v4" fill="none" stroke="currentColor" stroke-width="1.8"
              stroke-linecap="round" />
          </svg>
          <span>Filter</span>
        </button>
      </div>

      {{-- Active filter pill (optional) --}}
      @if($filter)
        <div class="alert alert-light border d-inline-flex align-items-center gap-2 py-2 px-3 mb-3" role="status"
          aria-live="polite">
          <span>Showing:</span>
          <span class="badge text-bg-primary text-uppercase">{{ $filter }}</span>
          <a class="btn btn-sm btn-outline-secondary ms-2" href="{{ route('student.history') }}"
            aria-label="Clear filter">Clear</a>
        </div>
      @endif

      {{-- Cards grid --}}
      {{-- Cards grid --}}
      {{-- Cards grid (fully responsive, zero custom CSS required) --}}
      <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4" id="cards">
        @forelse ($subs as $sub)
          @php
            // 1) Take label straight from DB
            $label = $sub->label ?? 'unknown';

            // 2) Make a SHORT "code" ONLY for client-side filtering
            $code = match ($label) {
              'over_extended' => 'over',
              'under_extended' => 'under',
              'optimal' => 'optimal',
              default => 'unknown'
            };

            // 3) Color by the ACTUAL label
            $badgeClass = match ($label) {
              'optimal' => 'text-bg-success',
              'under_extended' => 'text-bg-warning',
              'over_extended' => 'text-bg-danger',
              default => 'text-bg-secondary'
            };

            // 4) Prefer your accessor (nice!)
            $imgUrl = $sub->image_url
              ?? asset('project1/static/images/tooth.png');
          @endphp

          <div class="col" data-code="{{ $code }}">
            <article class="card h-100 shadow-sm">
              <div class="position-relative">
                <div class="ratio ratio-16x9">
                  <img src="{{ $imgUrl }}" alt="Case image" class="w-100 h-100" style="object-fit:cover" loading="lazy">
                </div>
                <span class="badge {{ $badgeClass }} position-absolute top-0 start-0 m-2 px-2 py-1">
                  {{ strtoupper(str_replace('_', ' ', $label)) }}
                </span>

              </div>

              <div class="card-body">
                <h4 class="h6 card-title mb-1">
                  {{ $sub->assignment->title ?? 'Assignment #' . $sub->assignment_id }}
                </h4>
                <p class="card-subtitle small text-secondary mb-2">
                  <time datetime="{{ $sub->created_at->toIso8601String() }}">
                    {{ $sub->created_at->format('Y-m-d H:i') }}
                  </time>
                </p>
                <p class="card-text mb-0">
                  {{ $sub->feedback ?: 'No feedback available.' }}
                </p>
              </div>
            </article>
          </div>
        @empty
          <div class="col">
            <div class="text-center py-5 border rounded-3 bg-light">
              <p class="mb-3">No submissions yet.</p>
              <a href="{{ route('upload') }}" class="btn btn-primary">Upload your first case</a>
            </div>
          </div>
        @endforelse
      </div>



      {{-- Pagination --}}
      @if ($subs->hasPages())
        <nav class="mt-4">
          {{ $subs->links() }}
        </nav>
      @endif
    </section>
  </main>

  {{-- Filter Modal (Bootstrap) --}}
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title fs-5" id="filterModalLabel">Filter by</h3>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeFilter"></button>
        </div>

        <div class="modal-body">
          {{-- Single-select chips using btn-check (radios) --}}
          <div class="d-flex flex-wrap gap-2" role="group" aria-label="Result type">
            @php
              $current = request('filter');
            @endphp

            <input type="radio" class="btn-check" name="filterChip" id="filter-all" value="" {{ $current ? '' : 'checked' }}>
            <label class="btn btn-outline-secondary rounded-pill" for="filter-all">All</label>

            <input type="radio" class="btn-check" name="filterChip" id="filter-over" value="over" {{ $current === 'over' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary rounded-pill" for="filter-over">over extended</label>

            <input type="radio" class="btn-check" name="filterChip" id="filter-under" value="under" {{ $current === 'under' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary rounded-pill" for="filter-under">under extended</label>

            <input type="radio" class="btn-check" name="filterChip" id="filter-optimal" value="optimal" {{ $current === 'optimal' ? 'checked' : '' }}>
            <label class="btn btn-outline-secondary rounded-pill" for="filter-optimal">optimal</label>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button class="btn btn-outline-secondary" id="clearFilter" type="button">Clear filter</button>
          <button class="btn btn-primary" data-bs-dismiss="modal" type="button">Done</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    // ====== Elements ======
    const cardsWrap = document.getElementById('cards');
    const filterBtn = document.getElementById('filterBtn');
    const clearFilter = document.getElementById('clearFilter');

    // Bootstrap will handle open/close; we just manage aria-expanded on open/close
    const filterModalEl = document.getElementById('filterModal');
    if (filterModalEl) {
      filterModalEl.addEventListener('show.bs.modal', () => filterBtn?.setAttribute('aria-expanded', 'true'));
      filterModalEl.addEventListener('hidden.bs.modal', () => filterBtn?.setAttribute('aria-expanded', 'false'));
    }

    // ====== Filtering ======
    function filterCards(val) {
      if (!cardsWrap) return;
      const cols = cardsWrap.querySelectorAll('[data-code]');
      cols.forEach(col => {
        const code = col.getAttribute('data-code');
        col.style.display = (!val || val === code) ? '' : 'none';
      });
    }

    // Apply current from URL on load
    (function initFromURL() {
      const url = new URL(window.location.href);
      const val = url.searchParams.get('filter');
      filterCards(val);
      // sync radios
      const input = document.querySelector(`input[name="filterChip"][value="${val ?? ''}"]`);
      if (input) input.checked = true;
    })();

    // Change handler for radios
    document.querySelectorAll('input[name="filterChip"]').forEach(input => {
      input.addEventListener('change', () => {
        const val = input.value || null;
        filterCards(val);
        const url = new URL(window.location.href);
        if (val) url.searchParams.set('filter', val); else url.searchParams.delete('filter');
        window.history.replaceState({}, '', url.toString());
      });
    });

    // Clear filter button
    if (clearFilter) {
      clearFilter.addEventListener('click', () => {
        const all = document.getElementById('filter-all');
        if (all) all.checked = true;
        filterCards(null);
        const url = new URL(window.location.href);
        url.searchParams.delete('filter');
        window.history.replaceState({}, '', url.toString());
      });
    }
  </script>
@endpush