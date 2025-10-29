@extends('layouts.doctor')
@vite(['resources/css/style.css'])

<style>
@media (min-width: 992px) {

    .app-main,
    .wrap,
    .container,
    .table-responsive,
    .filters-toolbar {
        padding-right: calc(var(--right-tabs-w) + 20px) !important;
    }
}

.sticky-thead th {
    position: sticky;
    top: var(--header-h);
    z-index: 5;
    background: var(--bs-table-bg, #fff);
    box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .06);
}

.students-table tbody tr {
    height: 76px;
}

html,
body {
    overflow-x: hidden;
    max-width: 100%;
}

.table-responsive {
    overflow-y: hidden;
}

.badge {
    font-size: .8rem;
}
</style>

@section('content')
<main class="app-main">
    <section class="wrap">
        <header class="page-head mb-2">
            <h2 class="page-title text-center">Students Work</h2>
        </header>

        <div class="ms-3">

            {{-- Toolbar --}}
            @php
            $active = collect(request()->except(['page','ai_page','text_page']))
            ->filter(fn($v)=>$v!==null && $v!=='');
            $aiCount = $aiSubs->total(); // إجمالي نتائج تبويب AI
            $textCount = $textSubs->total(); // إجمالي نتائج تبويب Text
            @endphp

            <div class="filters-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
                {{-- Filters --}}
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#filtersModal">
                    Filters
                    @if($active->count())
                    <span class="badge bg-primary ms-1">{{ $active->count() }}</span>
                    @endif
                </button>

                <a href="{{ route('doctor.work') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @php
                $exportParams = array_merge(request()->query(), ['scope' => request('tab', $tab ?? 'ai')]);
                @endphp
                <a href="{{ route('doctor.work.export', $exportParams) }}" class="btn btn-outline-secondary btn-sm">
                    Export CSV ({{ strtoupper(request('tab', $tab ?? 'AI')) }})
                </a>
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    Export CSV
                </button>
                {{-- Active filter tags --}}
                @if($active->count())
                <div class="d-flex flex-wrap gap-2 ms-2">
                    @foreach($active as $k => $v)
                    <span class="badge text-bg-light border">{{ $k }}: {{ $v }}</span>
                    @endforeach
                </div>
                @endif

                {{-- Spacer pushes export to right --}}
                <div class="flex-grow-1"></div>
                <div class="btn-group">
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('doctor.work.export', array_merge(request()->query(), ['scope'=>'ai'])) }}">
                                AI only
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('doctor.work.export', array_merge(request()->query(), ['scope'=>'text'])) }}">
                                Text only
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('doctor.work.export', array_merge(request()->query(), ['scope'=>'all'])) }}">
                                Both
                            </a>
                        </li>
                    </ul>
                </div>
            </div>


            {{-- Filters Modal --}}
            <div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <form id="submissionsFilter" method="GET" action="{{ route('doctor.work') }}">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filtersModalLabel">Filter Submissions</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                {{-- حافظ على التبويب الحالي --}}
                                <input type="hidden" name="tab" value="{{ request('tab', $tab ?? 'ai') }}">

                                <div class="row g-3">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">Student</label>
                                        <input type="text" name="student" value="{{ request('student') }}"
                                            class="form-control" placeholder="Name…">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">Sdt-No.</label>
                                        <input type="text" name="student_no" value="{{ request('student_no') }}"
                                            class="form-control" placeholder="e.g. S2025…">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">Section</label>
                                        <input type="text" name="section" value="{{ request('section') }}"
                                            class="form-control" placeholder="sec1…">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">Assignment</label>
                                        <input type="text" name="assignment" value="{{ request('assignment') }}"
                                            class="form-control" placeholder="Title…">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">Result</label>
                                        @php $r = request('result'); @endphp
                                        <select name="result" class="form-select">
                                            <option value="">Any</option>
                                            <option value="0" {{ $r==='0' ? 'selected' : '' }}>Optimal</option>
                                            <option value="1" {{ $r==='1' ? 'selected' : '' }}>Under</option>
                                            <option value="2" {{ $r==='2' ? 'selected' : '' }}>Over</option>
                                            <option value="na" {{ $r==='na' ? 'selected' : '' }}>N/A</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">From</label>
                                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label mb-1">To</label>
                                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <a href="{{ route('doctor.work') }}" class="btn btn-outline-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Bootstrap Tabs --}}
            @php $activeTab = request('tab', $tab ?? 'ai'); @endphp
            <ul class="nav nav-tabs mb-3" id="subsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab==='ai' ? 'active' : '' }}" id="ai-tab" data-bs-toggle="tab"
                        data-bs-target="#ai-pane" type="button" role="tab" aria-controls="ai-pane"
                        aria-selected="{{ $activeTab==='ai' ? 'true' : 'false' }}">
                        AI Submit <span class="badge bg-secondary ms-1">{{ $aiCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab==='text' ? 'active' : '' }}" id="text-tab"
                        data-bs-toggle="tab" data-bs-target="#text-pane" type="button" role="tab"
                        aria-controls="text-pane" aria-selected="{{ $activeTab==='text' ? 'true' : 'false' }}">
                        Text Submit <span class="badge bg-secondary ms-1">{{ $textCount }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="subsTabsContent">

                {{-- AI SUBMIT --}}
                <div class="tab-pane fade {{ $activeTab==='ai' ? 'show active' : '' }}" id="ai-pane" role="tabpanel"
                    aria-labelledby="ai-tab" tabindex="0">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 students-table">
                                <thead class="table-light sticky-thead">
                                    <tr>
                                        <th>Student</th>
                                        <th>Sdt-No.</th>
                                        <th>Section</th>
                                        <th>Assignment</th>
                                        <th>Result</th>
                                        <th>Submitted at</th>
                                        <th>Photo / File</th>
                                        <th class="w-50">Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($aiSubs as $s)
                                    @php
                                    $status = match ($s->code) {
                                    0 => ['text' => ($s->label ?? 'optimal'), 'bg' => 'success'],
                                    1 => ['text' => ($s->label ?? 'under'), 'bg' => 'warning'],
                                    2 => ['text' => ($s->label ?? 'over'), 'bg' => 'danger'],
                                    default => ['text' => ($s->label ?? 'N/A'), 'bg' => 'secondary'],
                                    };
                                    $thumbUrl = $s->file_path ? asset('storage/'.$s->file_path)
                                    : ($s->image_path ? asset('storage/'.$s->image_path) : null);
                                    @endphp
                                    <tr>
                                        <td>{{ $s->student->name ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $s->student->student_number ?? '—' }}</td>
                                        <td>{{ $s->section?->title ?? $s->assignment?->section?->title ?? '—' }}</td>
                                        <td>{{ $s->assignment?->title ?? '—' }}</td>
                                        <td><span
                                                class="badge bg-{{ $status['bg'] }}">{{ strtoupper($status['text']) }}</span>
                                        </td>
                                        <td class="text-nowrap">{{ optional($s->submitted_at)->format('Y-m-d H:i') }}
                                        </td>
                                        <td>
                                            @if($thumbUrl)
                                            <a href="{{ $thumbUrl }}" target="_blank" rel="noopener"
                                                class="d-inline-block">
                                                <img src="{{ $thumbUrl }}" alt="attachment" class="img-fluid rounded"
                                                    style="width:80px; height:60px; object-fit:cover;">
                                            </a>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 320px;">
                                                {{ \Illuminate\Support\Str::limit($s->feedback, 300) }}
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No AI submissions yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2 d-flex justify-content-center">
                            {{ $aiSubs->appends(array_merge(request()->query(), ['tab'=>'ai']))->links() }}
                        </div>
                    </div>
                </div>

                {{-- TEXT SUBMIT --}}
                <div class="tab-pane fade {{ $activeTab==='text' ? 'show active' : '' }}" id="text-pane" role="tabpanel"
                    aria-labelledby="text-tab" tabindex="0">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 students-table">
                                <thead class="table-light sticky-thead">
                                    <tr>
                                        <th>Student</th>
                                        <th>Sdt-No.</th>
                                        <th>Section</th>
                                        <th>Assignment</th>
                                        <th>Submitted at</th>
                                        <th>Length</th>
                                        <th class="w-50">Text</th>
                                        <th>Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($textSubs as $s)
                                    <tr>
                                        <td>{{ $s->student->name ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $s->student->student_number ?? '—' }}</td>
                                        <td>{{ $s->section?->title ?? $s->assignment?->section?->title ?? '—' }}</td>
                                        <td>{{ $s->assignment?->title ?? '—' }}</td>
                                        <td class="text-nowrap">{{ optional($s->submitted_at)->format('Y-m-d H:i') }}
                                        </td>

                                        {{-- FIX: use $s->score (not $sub) --}}
                                        <td>{{ $s->score ?? '—' }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 420px;">
                                                {{ \Illuminate\Support\Str::limit($s->content, 300) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 260px;">
                                                {{ \Illuminate\Support\Str::limit($s->feedback, 220) }}
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No text submissions yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>

                        <div class="mt-2 d-flex justify-content-center">
                            {{ $textSubs->appends(array_merge(request()->query(), ['tab'=>'text']))->links() }}
                        </div>
                    </div>
                </div>

            </div> {{-- /.tab-content --}}
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
// حفظ آخر تبويب
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.getElementById('subsTabs');
    if (!tabs) return;
    const stored = localStorage.getItem('studentsWorkTabId');
    if (stored) {
        const triggerEl = document.querySelector(`button[data-bs-target="${stored}"]`);
        if (triggerEl) new bootstrap.Tab(triggerEl).show();
    }
    tabs.addEventListener('shown.bs.tab', function(e) {
        const target = e.target.getAttribute('data-bs-target');
        localStorage.setItem('studentsWorkTabId', target);
        // حدّث باراميتر tab في العنوان (اختياري)
        const tab = target === '#text-pane' ? 'text' : 'ai';
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        history.replaceState({}, '', url.toString());
    });
});
</script>
@endpush