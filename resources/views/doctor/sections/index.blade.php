@extends('layouts.doctor')
@vite(['resources/css/style.css'])

{{-- Page-specific polish --}}
<style>
  :root { --rail-w: 220px; }

  /* No horizontal scroll anywhere */
  html, body { max-width: 100%; overflow-x: hidden; }

  /* Reserve space for fixed right rail on desktop */
  @media (min-width: 992px){
    main.sections-main { padding-right: calc(var(--rail-w) + 16px); }
  }

  /* Centered content column with nice readable width */
  .sections-wrap { max-width: 1140px; margin: 0 auto; }

  /* Grid baseline height so rows feel balanced */
  .grid-min-h { min-height: 320px; }

  /* Cards */
  .card-tile { transition: transform .12s ease, box-shadow .12s ease; border-radius: 14px !important; }
  .card-tile:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.06); }

  .card-body { padding: 1rem 1.125rem; }
  @media (min-width: 576px){ .card-body { padding: 1.125rem 1.25rem; } }

  .avatar-36 {
    inline-size: 36px; block-size: 36px;
    display: inline-grid; place-items: center;
    font-weight: 700;
  }

  /* Clamp description to 2 lines to keep card heights tidy */
  .desc-clamp {
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .empty-state .empty-illustration { width: 64px; opacity: .85; }

  /* Mobile breathing room */
  @media (max-width: 575.98px){
    .container-fluid { padding-left: 1rem; padding-right: 1rem; }
  }
</style>

@section('content')
<main class="sections-main mt-5">
  <section class="container-fluid py-4">
    <div class="sections-wrap">
      {{-- Page Header --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
          <h2 class="h4 fw-semibold mb-1">Sections</h2>
          <p class="text-muted mb-0">Create a new section and manage existing ones.</p>
        </div>

        <div class="ms-auto">
          <button class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#createSectionModal">
            <span class="me-1">+</span> New Section
          </button>
        </div>
      </div>

      {{-- Alerts --}}
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger" role="alert">
          <strong class="d-block mb-1">There were some issues:</strong>
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Grid --}}
      <h5 class="fw-semibold mb-3">Your Sections</h5>

      <div id="sectionsGrid" class="row g-3 g-md-4 grid-min-h">
        @forelse($sections as $s)
          @php
            // إن وفّرت withCount('assignments') في الكنترولر، هذا السطر يشتغل فوراً
            $assignmentsCount = $s->assignments_count ?? null;
            if (is_null($assignmentsCount)) {
              // fallback خفيف لو مش موفرة withCount
              $assignmentsCount = $s->assignments()->count();
            }
            $hasAssignments = $assignmentsCount > 0;
          @endphp

          <div class="col-12 col-sm-6 col-lg-4">
            <div class="card card-tile h-100 border-0 shadow-sm position-relative">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                  <a href="{{ route('doctor.sections.show', $s) }}" class="text-decoration-none text-body flex-grow-1 me-2">
                    <div class="d-flex align-items-center gap-2">
                      <div class="avatar-36 rounded-3 d-inline-grid place-items-center text-primary fw-bold bg-light">
                        {{ strtoupper(mb_substr($s->title, 0, 1)) }}
                      </div>
                      <h6 class="fw-bold mb-0 text-truncate" title="{{ $s->title }}">{{ $s->title }}</h6>
                    </div>
                  </a>

                  {{-- Actions dropdown --}}
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">⋮</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="{{ route('doctor.sections.show', $s) }}">Open</a></li>
                      <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editSectionModal-{{ $s->id }}">Edit</button>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteSectionModal-{{ $s->id }}">Delete</button>
                      </li>
                    </ul>
                  </div>
                </div>

                @if(!empty($s->description))
                  <p class="text-muted small mb-3 desc-clamp">{{ \Illuminate\Support\Str::limit($s->description, 180) }}</p>
                @else
                  <p class="text-muted small mb-3">No description provided.</p>
                @endif

                <div class="small text-muted d-flex align-items-center gap-2">
                  <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 2h12a2 2 0 0 1 2 2v16l-8-4-8 4V4a2 2 0 0 1 2-2z" fill="currentColor" opacity=".15"/>
                    <path d="M6 2h12a2 2 0 0 1 2 2v16l-8-4-8 4V4a2 2 0 0 1 2-2z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                  </svg>
                  <span>Created: {{ optional($s->created_at)->format('Y-m-d') }}</span>
                  <span class="ms-2">•</span>
                  <span title="Assignments count">{{ $assignmentsCount }} task{{ $assignmentsCount == 1 ? '' : 's' }}</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Edit Modal --}}
          <div class="modal fade" id="editSectionModal-{{ $s->id }}" tabindex="-1" aria-labelledby="editSectionLabel-{{ $s->id }}" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content border-0 shadow-sm">
                <form method="POST" action="{{ route('doctor.sections.update', $s) }}">
                  @csrf
                  @method('PATCH')
                  <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="editSectionLabel-{{ $s->id }}">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Title <span class="text-danger">*</span></label>
                      <input type="text" name="title" class="form-control" value="{{ $s->title }}" required>
                    </div>
                    <div class="mb-0">
                      <label class="form-label">Description</label>
                      <textarea name="description" class="form-control" rows="3">{{ $s->description }}</textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          {{-- Delete Modal (مع Force delete) --}}
          <div class="modal fade" id="deleteSectionModal-{{ $s->id }}" tabindex="-1" aria-labelledby="deleteSectionLabel-{{ $s->id }}" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
              <div class="modal-content border-0 shadow-sm">
                <form method="POST" action="{{ route('doctor.sections.destroy', $s) }}">
                  @csrf
                  @method('DELETE')
                  <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-danger" id="deleteSectionLabel-{{ $s->id }}">Delete Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p class="mb-2">  (<strong>{{ $s->title }}</strong>)؟</p>

                    @if($hasAssignments)
                      <div class="alert alert-warning small">
                        <em>Force delete</em>.
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="force-{{ $s->id }}" name="force">
                        <label class="form-check-label" for="force-{{ $s->id }}">
                          Force delete (this will delete all assignments and their submissions under this section)
                        </label>
                      </div>
                    @else
                      <div class="alert alert-info small mb-0">
                        no assignments found in this section. Deleting it will not affect any tasks.
                      </div>
                    @endif
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

        @empty
          <div class="col-12">
            <div class="empty-state text-center py-5">
              <img src="{{ asset('project1/static/images/empty-state.svg') }}" alt="No sections" class="mb-3 empty-illustration">
              <p class="text-muted mb-3">No sections yet.</p>
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSectionModal">
                Create your first section
              </button>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  {{-- Create Modal --}}
  <div class="modal fade" id="createSectionModal" tabindex="-1" aria-labelledby="createSectionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content border-0 shadow-sm">
        <form id="createSectionForm" method="POST" action="{{ route('doctor.sections.store') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title fw-semibold" id="createSectionLabel">Create Section</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div id="formErrors" class="d-none" role="alert"></div>

            <div class="mb-3">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="secTitle" class="form-control" placeholder="e.g., Endodontics Sec 1" required>
            </div>

            <div class="mb-0">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Optional brief about this section"></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="createSectionBtn">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

@push('scripts')
<script>
/* Focus title on modal open */
document.addEventListener('shown.bs.modal', (e) => {
  if (e.target.id === 'createSectionModal') {
    e.target.querySelector('#secTitle')?.focus();
  }
});

/* AJAX Create -> prepend new card to grid */
(() => {
  const form   = document.getElementById('createSectionForm');
  const btn    = document.getElementById('createSectionBtn');
  const grid   = document.getElementById('sectionsGrid');
  const errBox = document.getElementById('formErrors');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating…';
    errBox.className = 'd-none';
    errBox.innerHTML = '';

    try {
      const fd  = new FormData(form);
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.content || '')
        },
        body: fd
      });

      const data = await res.json();
      if (!res.ok) throw data;

      bootstrap.Modal.getInstance(document.getElementById('createSectionModal'))?.hide();
      form.reset();

      const d = data.section || {};
      const id = d.id;
      const title = (d.title || '').trim() || 'Untitled';
      const desc = (d.description || '').trim();
      const created = (d.created_at || '').slice(0, 10);
      const initial = title.slice(0,1).toUpperCase();

      const col = document.createElement('div');
      col.className = 'col-12 col-sm-6 col-lg-4';
      col.innerHTML = `
        <a href="{{ url('/doctor/sections') }}/${id}" class="text-decoration-none text-body">
          <div class="card card-tile h-100 border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="avatar-36 rounded-3 d-inline-grid place-items-center text-primary fw-bold bg-light">${initial}</div>
                <h6 class="fw-bold mb-0 text-truncate" title="${title}">${title}</h6>
              </div>
              ${desc ? `<p class="text-muted small mb-3 desc-clamp">${desc}</p>` : `<p class="text-muted small mb-3">No description provided.</p>`}
              <div class="small text-muted d-flex align-items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h12a2 2 0 0 1 2 2v16l-8-4-8 4V4a2 2 0 0 1 2-2z" fill="currentColor" opacity=".15"/><path d="M6 2h12a2 2 0 0 1 2 2v16l-8-4-8 4V4a2 2 0 0 1 2-2z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                <span>Created: ${created || ''}</span>
              </div>
            </div>
          </div>
        </a>
      `;
      grid.prepend(col);
    } catch (err) {
      errBox.className = 'alert alert-danger';
      if (err?.errors) {
        errBox.innerHTML = '<ul class="mb-0 ps-3">' + Object.values(err.errors).flat().map(e => `<li>${e}</li>`).join('') + '</ul>';
      } else {
        errBox.textContent = 'Error creating section.';
      }
    } finally {
      btn.disabled = false;
      btn.textContent = 'Create';
    }
  });
})();
</script>
@endpush
@endsection
