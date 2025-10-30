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

    {{-- 1. Welcome Header --}}
    <div class="mb-4">
        <h1 class="display-5 fw-bold">
            Welcome back, {{ auth()->user()->name }}!
        </h1>
        <p class="fs-4 text-muted">
            Here's a summary of your workspace.
        </p>
    </div>

    {{-- 2. Stats Cards --}}
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-people-fill text-primary me-3" viewBox="0 0 16 16" aria-hidden="true">
                      <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                    </svg>
                    <div>
                        <div class="fs-2 fw-bold">{{ $studentCount }}</div>
                        <div class="text-muted">Total Students</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-collection-fill text-success me-3" viewBox="0 0 16 16" aria-hidden="true">
                      <path d="M0 13a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 16 13V6a1.5 1.5 0 0 0-1.5-1.5h-13A1.5 1.5 0 0 0 0 6zM2 3a.5.5 0 0 0 .5.5h11a.5.5 0 0 0 0-1h-11A.5.5 0 0 0 2 3m2-2a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7A.5.5 0 0 0 4 1"/>
                    </svg>
                    <div>
                        <div class="fs-2 fw-bold">{{ $sectionCount }}</div>
                        <div class="text-muted">Total Sections</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-check-all text-info me-3" viewBox="0 0 16 16" aria-hidden="true">
                      <path d="M8.97 4.97a.75.75 0 0 1 1.07 1.05l-3.5 3.5a.75.75 0 0 1-1.08.04L3.22 7.22a.75.75 0 0 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                      <path d="M1.5 12.5a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1zM1.5 8.5a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1z"/>
                    </svg>
                    <div>
                        <div class="fs-2 fw-bold">{{ $submissionCount }}</div>
                        <div class="text-muted">Total Submissions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Quick Links --}}
    <div class="row g-4 mt-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold">Manage Sections</h5>
                    <p class="text-muted">Create new sections, add students, and manage assignments.</p>
                    <a href="{{ route('doctor.sections.index') }}" class="btn primary btn-lg">
                        Go to Sections
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold">View Student Work</h5>
                    <p class="text-muted">Review, grade, and export all student submissions.</p>
                    <a href="{{ route('doctor.work') }}" class="btn primary btn-lg">
                        Go to Student Work
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Recent Activity --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Recent Submissions</h5>
        </div>
        
        @if ($recentSubmissions->isEmpty())
            <div class="card-body text-center p-5">
                <h4 class="text-muted">No submissions found yet.</h4>
                <p class="text-muted">When students submit their work, it will appear here.</p>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach ($recentSubmissions as $sub)
                    <div class="list-group-item p-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-semibold">
                                {{ $sub->student->name ?? 'Unknown Student' }}
                            </h6>
                            <small class="text-muted">{{ $sub->created_at->diffForHumans() }}</Gsmall>
                        </div>
                        <p class="mb-1">
                            Submitted to: 
                            <span class="text-primary">{{ $sub->assignment->title ?? 'N/A' }}</span>
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection