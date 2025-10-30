@extends('layouts.doctor')

@section('content')
<div class="container-xl py-4">

    {{-- Session Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="display-5 fw-bold">
            Manage Sections
        </h1>
        <p class="fs-4 text-muted">
            Create, view, and manage all your student sections.
        </p>
    </div>

    {{-- Two-Column Layout --}}
    <div class="row g-4">

        {{-- Left Column: Create New Section Card --}}
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-square-dotted" viewBox="0 0 16 16" aria-hidden="true">
                          <path d="M2.5 0a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2a.5.5 0 0 1 .5-.5zM8 2.5a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2a.5.5 0 0 1 .5-.5zM13.5 2.5a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2a.5.5 0 0 1 .5-.5zM2.5 8a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2a.5.5 0 0 1 .5-.5zM13.5 8a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2a.5.5 0 0 1 .5-.5zM8 13.5a.5.5 0 0 1 .5.5v2h2a.5.5 0 0 1 0 1h-2v2a.5.5 0 0 1-1 0v-2h-2a.5.5 0 0 1 0-1h2v-2a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                        Create New Section
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('doctor.sections.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Section Name</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   placeholder="e.g., 'Group B - 2025'" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="course_name" class="form-label fw-semibold">Course Name (Optional)</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('course_name') is-invalid @enderror" 
                                   id="course_name" 
                                   name="course_name" 
                                   placeholder="e.g., 'Endodontics 301'">
                            @error('course_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid">
                            {{-- FIX: Added flexbox classes for alignment --}}
                            <button type="submit" class="btn primary btn-lg d-flex align-items-center justify-content-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2z"/>
                                </svg>
                                <span>Create Section</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Column: Your Sections List Card --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">All Your Sections</h5>
                </div>
                
                @if ($sections->isEmpty())
                    <div class="card-body text-center p-5">
                        <h4 class="text-muted">No sections found.</h4>
                        <p class="text-muted">Create your first section to get started.</p>
                    </div>
                @else
                    {{-- FIX: This list now includes Edit/Delete buttons --}}
                    <div class="list-group list-group-flush">
                        @foreach ($sections as $section)
                            <div class="list-group-item p-3">
                                <div classs="d-flex w-100 justify-content-between align-items-center">
                                    {{-- Section Info (left side) --}}
                                    <div>
                                        <a href="{{ route('doctor.sections.show', $section) }}" class="text-decoration-none">
                                            <h5 class="mb-1 fw-bold">{{ $section->name }}</h5>
                                        </a>
                                        <p class="mb-1 text-muted">
                                            {{ $section->course_name ?? 'No course name' }}
                                        </p>
                                        <small class="text-primary fw-medium">
                                            {{ $section->students_count }} {{ Str::plural('student', $section->students_count) }}
                                            &middot;
                                            {{ $section->assignments_count }} {{ Str::plural('assignment', $section->assignments_count) }}
                                        </small>
                                    </div>
                                    
                                    {{-- Action Buttons (right side) --}}
                                    <div class="d-flex gap-2 mt-2">
                                        <a href="{{ route('doctor.sections.show', $section) }}" 
                                           class="btn btn-sm btn-outline-secondary">
                                           View/Edit
                                        </a>
                                        {{-- This button now opens the modal --}}
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteSectionModal" 
                                                data-url="{{ route('doctor.sections.destroy', $section) }}">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Handles pagination --}}
                @if ($sections->hasPages())
                    <div class="card-footer bg-white">
                        {{ $sections->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div> {{-- End of .row --}}

</div> {{-- End of .container-xl --}}
@endsection

{{-- This part adds the custom "Delete" pop-up --}}
@push('scripts')
<div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSectionModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this section?
                <br>
                <strong class="text-danger">This action cannot be undone.</strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
                <form id="deleteModalForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Section</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteSectionModal');
        var deleteForm = document.getElementById('deleteModalForm');

        if(deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var deleteUrl = button.getAttribute('data-url');
                deleteForm.setAttribute('action', deleteUrl);
            });
        }
    });
</script>
@endpush