@extends('layouts.app')
@vite(['resources/css/style.css'])

<style>
.cards {
    display: block;
}

html,
body,
.view-history,
.history-wrap {
    overflow-x: hidden;
    max-width: 100%;
}

@media (min-width: 992px) {

    .app-main,
    .view-history .history-wrap {
        padding-right: 220px;
    }
}

.card {
    border-radius: .75rem;
}

.btn-outline-secondary.rounded-pill {
    padding: .35rem .75rem;
}

/* Filter dialog */
dialog.filter-dialog {
    border: none;
    padding: 0;
    background: transparent;
}

dialog.filter-dialog[open] {
    display: block;
}

dialog.filter-dialog::backdrop {
    background: rgba(0, 0, 0, .25);
}

.filter-content {
    width: min(560px, 92vw);
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
    padding: 18px;
    margin: 8vh auto 0;
    transform: translateY(8px);
    animation: filterIn .18s ease-out forwards;
}

@keyframes filterIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chips {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .65rem;
    border-radius: 999px;
    border: 1px solid #dee2e6;
    background: #fff;
    color: #212529;
    cursor: pointer;
    user-select: none;
    transition: background .15s, border-color .15s, color .15s;
}

.chip:hover {
    background: #f8f9fa;
}

.chip.active {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    margin-top: 14px;
}

.btn.outline {
    border: 1px solid #dee2e6;
    background: #fff;
}
</style>

@section('content')
<section class="view view-history" data-view="history" aria-labelledby="assignmentsHeading">
    <section class="history-wrap mt-5">

        {{-- Header --}}
        <div
            class="ms-3 history-head d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div class="lead mt-4 mt-md-0">
                <h2 id="assignmentsHeading" class="m-0 fw-bold mt-3">Assignments</h2>
                <p class="mb-0 text-muted">
                    Your assignments list. Track deadlines, submission status, and grades.
                    Use filters to quickly find what you need.
                </p>
            </div>

            {{-- FIX: give the button the id your JS expects; remove Bootstrap modal attributes --}}
            <button type="button" id="filterBtn"
                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 rounded-pill"
                title="Filter" aria-haspopup="dialog" aria-controls="filterDialog" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <path d="M4 7h16M7 7v6m0 0h10m-6 0v4" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" />
                </svg>
                <span>Open filter</span>
            </button>
        </div>

        @if($filter)
        <div class="active-filter mt-2" role="status" aria-live="polite">
            Showing: <strong>{{ ucfirst($filter) }}</strong>
            <a class="btn tiny outline ms-2" href="{{ route('student.assignments') }}"
                aria-label="Clear filter">Clear</a>
        </div>
        @endif

        {{-- Cards grid --}}
        <div class="ms-3 row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 cards" id="cards">
            @forelse ($items as $a)
            @php
            $code = $a->status_code; // graded | submitted | overdue | pending
            $badgeClass = match ($code) {
            'graded' => 'bg-success',
            'submitted' => 'bg-warning text-dark',
            'overdue' => 'bg-danger',
            default => 'bg-info',
            };
            $desc = \Illuminate\Support\Str::limit($a->description ?? '', 140);
            $latest = $a->latest_submission ?? null;
            $grade = $latest?->grade;
            $gradeTxt = !is_null($grade) ? "Grade: " . $grade : null;
            $deadlineISO = $a->deadline_obj?->toIso8601String();
            $deadlineTxt = $a->deadline_obj?->format('Y-m-d H:i');
            @endphp

            <div class="col">
                <article class="card h-100" data-code="{{ $code }}">
                    <div class="position-relative d-flex align-items-center justify-content-center bg-light-subtle"
                        style="height:248px;">
                        <img src="{{ asset('images/assignments.png') }}" alt="tooth" class="img-fluid"
                            style="width: 248px;height: 248px;object-fit:contain;opacity:.9;">
                        <span class="badge {{ $badgeClass }} position-absolute" style="top:.5rem; left:.5rem;">
                            {{ strtoupper($code) }}
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title mb-2">{{ $a->title ?? ('Assignment #' . $a->id) }}</h4>

                        @if($deadlineTxt)
                        <p class="card-meta mb-1">
                            <span>Deadline: </span>
                            <time datetime="{{ $deadlineISO }}">{{ $deadlineTxt }}</time>
                        </p>
                        @endif

                        @if($desc)
                        <p class="card-text mb-2">{{ $desc }}</p>
                        @endif

                        @if($gradeTxt)
                        <p class="card-meta mt-1 mb-0">{{ $gradeTxt }}</p>
                        @endif

                        <div class="mt-auto pt-2">
                            @php
                            $isTextOnly = ($a->submission_type ?? 'both') === 'text';
                            @endphp

                            <a href="{{ $isTextOnly
            ? route('assignments.text.submit', $a->id)       
            : route('upload', ['assignment' => $a->id])      
          }}" class="btn btn-primary">
                                {{ in_array($code, ['submitted','graded']) ? 'View / Resubmit' : 'Submit' }}
                            </a>


                        </div>
                    </div>
                </article>
            </div>
            @empty
            <div class="col-12">
                <div class="empty-state">
                    <p>No assignments yet.</p>
                </div>
            </div>
            @endforelse
        </div>
    </section>

    {{-- Filter dialog (native <dialog>) --}}
    <dialog id="filterDialog" class="filter-dialog" aria-label="Filter by status">
        <div class="filter-content">
            <h3 class="mb-2">Filter by :</h3>

            <div class="chips" role="group" aria-label="Status" id="chipGroup">
                <button class="chip" data-filter="">All</button>
                <button class="chip" data-filter="pending">Pending</button>
                <button class="chip" data-filter="submitted">Submitted</button>
                <button class="chip" data-filter="graded">Graded</button>
                <button class="chip" data-filter="overdue">Overdue</button>
            </div>

            <div class="filter-actions">
                <button class="btn outline" id="clearFilter" type="button">Clear</button>
                <button class="btn btn-primary" id="closeFilter" type="button">Done</button>
            </div>
        </div>
    </dialog>
</section>
@endsection

@push('scripts')
<script>
// Elements
const filterBtn = document.getElementById('filterBtn');
const filterDialog = document.getElementById('filterDialog');
const chipGroup = document.getElementById('chipGroup');
const closeFilter = document.getElementById('closeFilter');
const clearFilter = document.getElementById('clearFilter');
const cardsWrap = document.getElementById('cards');
const chips = chipGroup ? Array.from(chipGroup.querySelectorAll('.chip')) : [];

const supportsDialog = typeof window.HTMLDialogElement === 'function';

function openFilter() {
    if (!filterDialog) return;
    const current = new URL(window.location.href).searchParams.get('filter') || '';
    activateChip(current);
    if (supportsDialog && typeof filterDialog.showModal === 'function') filterDialog.showModal();
    else filterDialog.setAttribute('open', '');
    filterBtn?.setAttribute('aria-expanded', 'true');
}

function closeFilterDialog() {
    if (!filterDialog) return;
    if (supportsDialog && typeof filterDialog.close === 'function') filterDialog.close();
    else filterDialog.removeAttribute('open');
    filterBtn?.setAttribute('aria-expanded', 'false');
}

function activateChip(val) {
    chips.forEach(c => {
        const active = (c.dataset.filter === (val ?? ''));
        c.classList.toggle('active', active);
        c.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
}

function applyFilter(val) {
    const url = new URL(window.location.href);
    if (val) url.searchParams.set('filter', val);
    else url.searchParams.delete('filter');
    window.history.replaceState({}, '', url.toString());
    filterCards(val);
}

function filterCards(val) {
    if (!cardsWrap) return;
    const showAll = !val;
    cardsWrap.querySelectorAll('.card').forEach(card => {
        const code = card.getAttribute('data-code');
        const col = card.closest('.col');
        const show = showAll || val === code;
        if (col) col.style.display = show ? '' : 'none';
        card.classList.toggle('hidden', !show);
    });
}

// Events
filterBtn?.addEventListener('click', openFilter);

closeFilter?.addEventListener('click', () => {
    const active = chips.find(c => c.classList.contains('active'));
    const val = active ? active.dataset.filter : '';
    applyFilter(val);
    closeFilterDialog();
});

clearFilter?.addEventListener('click', () => {
    activateChip('');
    applyFilter('');
});

chipGroup?.addEventListener('click', (e) => {
    const btn = e.target.closest('.chip');
    if (!btn) return;
    activateChip(btn.dataset.filter);
});

// ESC / backdrop
if (supportsDialog) {
    filterDialog?.addEventListener('cancel', (e) => {
        e.preventDefault();
        closeFilterDialog();
    });
} else {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && filterDialog?.hasAttribute('open')) closeFilterDialog();
    });
}
filterDialog?.addEventListener('mousedown', (e) => {
    if (e.target === filterDialog) closeFilterDialog();
});

// Init from URL on load
(function init() {
    const val = new URL(window.location.href).searchParams.get('filter') || '';
    activateChip(val);
    filterCards(val);
})();
</script>
@endpush