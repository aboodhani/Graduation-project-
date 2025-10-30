@extends('layouts.doctor')
@vite (['resources/css/style.css', 'resources/css/sections.css'])

@section('content')
<main class="app-main">
    <section class="wrap">

        {{-- Header --}}
        <header class="page-head card">
            <div class="row align-items-center">
                <!-- Left side -->
                <div class="col-md-8 col-sm-12">
                    <h2 class="page-title mb-1">Section: {{ $section->title }}</h2>
                    <p class="muted mb-0">Created: {{ optional($section->created_at)->format('Y-m-d') }}</p>
                    @if(!empty($section->description))
                    <p class="page-sub mb-0">{{ $section->description }}</p>
                    @endif
                </div>

                <!-- Right side -->
                <div class="col-md-4 col-sm-12 text-md-end mt-3 mt-md-0">
                    <a class="btn outline back-btn" href="{{ route('doctor.sections.index') }}">← Back to sections</a>
                </div>
            </div>
        </header>





        {{-- Alerts --}}
        @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert danger">
            <strong>There were some issues:</strong>
            <ul class="alert-list">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ======= Two-column layout ======= --}}
        <div class="layout-grid">
            {{-- LEFT: Create Assignment --}}
            <div class="stack">
                <div class="card">
                    <div class="card__head">
                        <h3 class="card__title">Create Assignment</h3>
                        <p class="muted">Publish a new assignment for this section.</p>
                    </div>

{{-- Replace the form in your show.blade.php with this --}}
<form method="POST" action="{{ route('doctor.sections.assignments.store', $section->id) }}" class="form">
    @csrf

    {{-- Title --}}
    <div class="form-row">
        <label for="title" class="form-label">Title <span class="req">*</span></label>
        <input id="title" 
               name="title" 
               class="input @error('title') is-invalid @enderror" 
               required 
               value="{{ old('title') }}"
               placeholder="e.g., WL Evaluation #1" />
        @error('title') 
            <div class="error text-danger">{{ $message }}</div> 
        @enderror
    </div>

    {{-- Description --}}
    <div class="form-row">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" 
                  name="description" 
                  rows="5" 
                  class="textarea @error('description') is-invalid @enderror"
                  placeholder="Describe the task, rubric, and notes…">{{ old('description') }}</textarea>
        @error('description') 
            <div class="error text-danger">{{ $message }}</div> 
        @enderror
    </div>

    {{-- Deadline --}}
    <div class="form-row">
        <label for="deadline" class="form-label">Deadline</label>
        <input id="deadline" 
               name="deadline" 
               type="datetime-local" 
               class="input @error('deadline') is-invalid @enderror"
               value="{{ old('deadline') }}" />
        @error('deadline') 
            <div class="error text-danger">{{ $message }}</div> 
        @enderror
    </div>

    {{-- Submission Type --}}
    <div class="form-row">
        <label for="submission_type" class="form-label">Submission Type <span class="req">*</span></label>
        <select id="submission_type" 
                name="submission_type" 
                class="input @error('submission_type') is-invalid @enderror" 
                required>
            <option value="both" {{ old('submission_type', 'both') === 'both' ? 'selected' : '' }}>
                Text or Pictures
            </option>
            <option value="text" {{ old('submission_type') === 'text' ? 'selected' : '' }}>
                Text only
            </option>
            <option value="pdf" {{ old('submission_type') === 'pdf' ? 'selected' : '' }}>
                Pictures only
            </option>
        </select>
        @error('submission_type') 
            <div class="error text-danger">{{ $message }}</div> 
        @enderror
        <p class="muted" style="margin:.35rem 0 0;">Choose how students are allowed to submit.</p>
    </div>

    {{-- Create Placeholders --}}
    <div class="form-row form-row--inline">
        <label class="checkbox">
            <input type="checkbox" 
                   name="create_placeholders" 
                   value="1"
                   {{ old('create_placeholders', true) ? 'checked' : '' }}>
            <span>Create placeholder submissions for all students</span>
        </label>
    </div>

    {{-- Actions --}}
    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Create Assignment</button>
        <button class="btn btn-secondary" type="reset">Reset</button>
    </div>
</form>

            {{-- RIGHT: Students then Assignments --}}
            <aside class="right-col stack">
                {{-- Students --}}
                <div class="card">
                    <div class="card__head">
                        <h4 class="card__title">Students <span class="badge neutral">{{ $students->count() }}</span>
                        </h4>
                    </div>
                    <ul class="people-list">
                        @forelse($students as $st)
                        <li class="people-list__item">
                            <div class="avatar">{{ strtoupper(mb_substr($st->name, 0, 1)) }}</div>
                            <div class="people-list__text">
                                <div class="people-list__name">{{ $st->name }}</div>
                                <div class="people-list__muted">{{ $st->student_number ?? ('ID:' . $st->id) }}</div>
                            </div>
                        </li>
                        @empty
                        <li class="people-list__empty">No students yet.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Assignments (under Students) --}}
                <div class="card">
                    <div class="card__head">
                        <h3 class="card__title">Assignments</h3>
                        <p class="muted">All assignments for this section.</p>
                    </div>

                    @foreach($assignments as $ass)
                    <div class="list-item">
                        <div class="list-item__main">
                            <strong class="list-item__title">{{ $ass->title }}</strong>
                            @if($ass->deadline)
                            <span class="badge text-dark">
                                Deadline: {{ \Carbon\Carbon::parse($ass->deadline)->format('Y-m-d H:i') }}
                            </span>
                            @endif
                        </div>

                        <div class="list-item__meta">
                            <span class="meta-label">Submissions</span>
                            <span class="meta-value">{{ $ass->submissions_count ?? $ass->submissions()->count() }}</span>
                        </div>

                        <div class="list-item__actions" style="display:flex; gap:.5rem;">
                            {{-- Edit button --}}
                            <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editAssignmentModal-{{ $ass->id }}">
                                Edit
                            </button>

                            {{-- Delete --}}
                            <button type="button" class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteAssignmentModal-{{ $ass->id }}">
                                Delete
                            </button>
                        </div>
                    </div>

                    {{-- Delete Modal --}}
                    <div class="modal fade" id="deleteAssignmentModal-{{ $ass->id }}" tabindex="-1"
                        aria-labelledby="deleteAssignmentLabel-{{ $ass->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="deleteAssignmentLabel-{{ $ass->id }}">
                                        Delete Assignment
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <p>Are you sure you want to delete <strong>{{ $ass->title }}</strong>?</p>
                                    <p class="text-muted mb-0">
                                        This action cannot be undone and will remove all related submissions.
                                    </p>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                                    <form action="{{ route('doctor.sections.assignments.destroy', [$section->id, $ass->id]) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- EDIT MODAL (Bootstrap) --}}
                    <div class="modal fade" id="editAssignmentModal-{{ $ass->id }}" tabindex="-1"
                        aria-labelledby="editAssignmentLabel-{{ $ass->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content mt-5">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="editAssignmentLabel-{{ $ass->id }}">Edit Assignment</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form method="POST"
                                    action="{{ route('doctor.sections.assignments.update', [$section->id, $ass->id]) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="modal-body">
                                        {{-- Title --}}
                                        <div class="mb-3">
                                            <label class="form-label" for="title-{{ $ass->id }}">Title <span class="text-danger">*</span></label>
                                            <input id="title-{{ $ass->id }}"
                                                name="title"
                                                class="form-control"
                                                value="{{ old('title', $ass->title) }}"
                                                required>
                                        </div>

                                        {{-- Description --}}
                                        <div class="mb-3">
                                            <label class="form-label" for="description-{{ $ass->id }}">Description</label>
                                            <textarea id="description-{{ $ass->id }}"
                                                name="description"
                                                rows="5"
                                                class="form-control"
                                                placeholder="Describe the task, rubric, and notes…">{{ old('description', $ass->description) }}</textarea>
                                        </div>

                                        {{-- Deadline --}}
                                        @php
                                        $deadlineVal = optional($ass->deadline)->format('Y-m-d\TH:i');
                                        @endphp
                                        <div class="mb-3">
                                            <label class="form-label" for="deadline-{{ $ass->id }}">Deadline</label>
                                            <input id="deadline-{{ $ass->id }}"
                                                type="datetime-local"
                                                name="deadline"
                                                class="form-control"
                                                value="{{ old('deadline', $deadlineVal) }}">
                                            <div class="form-text">Leave empty to remove deadline.</div>
                                        </div>

                                        {{-- Submission type --}}
                                        <div class="mb-2">
                                            <label class="form-label" for="submission_type-{{ $ass->id }}">Submission Type <span class="text-danger">*</span></label>
                                            <select id="submission_type-{{ $ass->id }}" name="submission_type" class="form-select" required>
                                                <option value="both" {{ old('submission_type', $ass->submission_type) === 'both' ? 'selected' : '' }}>Text or PDF</option>
                                                <option value="text" {{ old('submission_type', $ass->submission_type) === 'text' ? 'selected' : '' }}>Text only</option>
                                                <option value="pdf" {{ old('submission_type', $ass->submission_type) === 'pdf'  ? 'selected' : '' }}>Pictures only</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </aside>
        </div>

    </section>
</main>
@endsection

{{-- Page CSS (scoped) --}}
<style>
    :root {
        --rail-w: 220px;
        --wrap-max: 1120px;
        --header-h: 64px;
    }

    html,
    body {
        max-width: 100%;
        overflow-x: hidden;
    }

    /* Keep clear of fixed right rail on desktop (if you have one) */
    @media (min-width: 992px) {
        .app-main .wrap {
            padding-right: calc(var(--rail-w) + 16px);
        }
    }

    /* Page container */
    .wrap {
        max-width: var(--wrap-max);
        margin-inline: auto;
        padding: 1.25rem 1rem 2rem;
    }

    /* Header */
    .page-head {
        background: #fff;
        border: 1px solid #e7e7e9;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        margin-bottom: 1.25rem;
    }

    .page-title {
        font-weight: 700;
        margin: 0;
    }

    .page-sub {
        margin-top: .25rem;
        color: #555;
    }

    .back-btn {
        font-size: .95rem;
        padding: .45rem .9rem;
        border-radius: 8px;
        white-space: nowrap;
    }



    /* ===== Two-column grid ===== */
    .layout-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 420px;
        /* Left wide, right narrow */
        gap: 1.25rem;
        align-items: start;
    }

    /* Right column stacked (Students then Assignments) */
    .stack>*+* {
        margin-top: 1rem;
    }

    .right-col {
        position: sticky;
        top: calc(var(--header-h) + 12px);
    }

    @media (max-width: 1024px) {
        .layout-grid {
            grid-template-columns: 1fr;
        }

        .right-col {
            position: static;
            top: auto;
        }
    }

    /* Cards */
    .card {
        background: #fff;
        border: 1px solid #e7e7e9;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
    }

    .card__head {
        margin-bottom: .75rem;
    }

    .card__title {
        margin: 0;
    }

    /* Forms */
    .form {
        display: grid;
        gap: .85rem;
    }

    .form-row {
        display: grid;
        gap: .35rem;
    }

    .form-label {
        font-weight: 600;
    }

    .input,
    .textarea {
        width: 100%;
        border: 1px solid #d7d7db;
        border-radius: 10px;
        padding: .65rem .75rem;
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
    }

    .input:focus,
    .textarea:focus {
        outline: none;
        border-color: #7a8cff;
        box-shadow: 0 0 0 3px rgba(122, 140, 255, .15);
    }

    .checkbox {
        display: flex;
        gap: .5rem;
        align-items: center;
    }

    .req {
        color: #d33;
        margin-left: .25rem;
        font-weight: 700;
    }

    .form-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .error {
        color: #b42318;
        font-size: .9rem;
    }

    /* Lists */
    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .75rem;
        border: 1px dashed #e5e5ea;
        border-radius: 10px;
        margin-bottom: .6rem;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .list-item__main {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
        min-width: 0;
    }

    .list-item__title {
        font-weight: 600;
        max-width: 42ch;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .list-item__meta {
        display: flex;
        gap: .5rem;
        align-items: center;
        white-space: nowrap;
    }

    .meta-label {
        color: #666;
        font-size: .9rem;
    }

    .meta-value {
        font-weight: 700;
    }

    /* People */
    .people-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: .5rem;
    }

    .people-list__item {
        display: flex;
        gap: .6rem;
        align-items: center;
        padding: .5rem;
        border-radius: 8px;
    }

    .people-list__item:hover {
        background: #fafafa;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: #eef1ff;
        color: #4450ff;
        font-weight: 700;
    }

    .people-list__name {
        font-weight: 600;
    }

    .people-list__muted {
        color: #6a6a6a;
        font-size: .9rem;
    }

    .people-list__empty {
        color: #6a6a6a;
        padding: .5rem;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: .2rem .5rem;
        border-radius: 999px;
        background: #f2f4ff;
        color: #3f51ff;
        font-weight: 600;
        font-size: .85rem;
    }

    .badge.neutral {
        background: #f5f5f7;
        color: #444;
    }

    /* Alerts */
    .alert {
        border-radius: 10px;
        padding: .75rem .9rem;
        margin: .75rem 0;
    }

    .alert.success {
        background: #eefaf3;
        border: 1px solid #c8efd9;
        color: #156a36;
    }

    .alert.danger {
        background: #fff3f3;
        border: 1px solid #ffd6d6;
        color: #a12020;
    }

    .alert-list {
        margin: .4rem 0 0 1.1rem;
    }

    /* Empty state */
    .empty {
        display: flex;
        gap: 1rem;
        align-items: center;
        padding: .9rem;
        border: 1px dashed #ddd;
        border-radius: 10px;
    }

    .empty__img {
        width: 48px;
        height: 48px;
        opacity: .7;
    }

    .empty__text h4 {
        margin: .2rem 0;
    }

    .muted {
        color: #6a6a6a;
    }

    .modal {
        z-index: 999;
    }

    .modal-backdrop {
        display: none !important;
    }
</style>
@push('scripts')
<script>
    document.addEventListener('show.bs.modal', function() {
        // Close ALL native <dialog> elements if any are open
        document.querySelectorAll('dialog[open]').forEach(d => {
            try {
                d.close();
            } catch (_) {}
        });
    });
</script>
@endpush