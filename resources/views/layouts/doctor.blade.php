<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Doctor Dashboard — AI-based Working Length Tool</title>

  <link rel="icon" href="{{ asset('assets/image/logo.png') }}" />
  
  @vite(['resources/js/app.js'])
</head>

<body class="bg-light">

  <header class="app-header bg-white shadow-sm border-bottom fixed-top">
    
    <div class="header-left d-none d-lg-block"> <div class="user-name fw-semibold small" id="userNameHeader">
          {{ auth()->check() ? auth()->user()->name : '—' }}
        </div>
        <div class="user-id text-muted small" id="userIdHeader">
          Doctor
        </div>
    </div>

    <h1 class="app-title navbar-brand mx-auto">AI-based Working Length Tool</h1>

    <div class="header-right">
        <div class="d-none d-lg-flex align-items-center gap-2">
            @auth
              <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
              </form>
            @endauth
            @guest
              <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                <span class="me-1">🔑</span> Login
              </a>
            @endguest
        </div>
        
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-label="Toggle sidebar">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
  </header>

  <aside class="right-tabs d-none d-lg-block" role="navigation" aria-label="Sidebar">
    <nav class="tabs-nav">
      <a href="{{ url('/doctor') }}" class="tab-link {{ request()->is('doctor') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" width="30" height="30" aria-hidden="true">
          <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5z" fill="currentColor"/>
        </svg>
        <span>Home</span>
      </a>

      <a href="{{ url('/doctor/work') }}" class="tab-link {{ request()->is('doctor/work*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" width="30" height="30" aria-hidden="true">
          <path d="M4 8h16a1 1 0 0 1 1 1v8a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9a1 1 0 0 1 1-1zm4-3h8a1 1 0 0 1 1 1v2H7V6a1 1 0 0 1 1-1z" fill="currentColor"/>
        </svg>
        <span>Work</span>
      </a>

      <a href="{{ route('doctor.sections.index') }}" class="tab-link {{ request()->routeIs('doctor.sections.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" width="30" height="30" aria-hidden="true">
          <path d="M4 5h16v2H4V5zm0 6h16v2H4v-2zm0 6h10v2H4v-2z" fill="currentColor"/>
        </svg>
        <span>Sections</span>
      </a>
    </nav>
  </aside>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
      <nav class="tabs-nav d-flex flex-column">
        <a href="{{ url('/doctor') }}" class="tab-link {{ request()->is('doctor') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5z" fill="currentColor"/>
          </svg>
          <span>Home</span>
        </a>

        <a href="{{ url('/doctor/work') }}" class="tab-link {{ request()->is('doctor/work*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M4 8h16a1 1 0 0 1 1 1v8a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9a1 1 0 0 1 1-1zm4-3h8a1 1 0 0 1 1 1v2H7V6a1 1 0 0 1 1-1z" fill="currentColor"/>
          </svg>
          <span>Work</span>
        </a>

        <a href="{{ route('doctor.sections.index') }}" class="tab-link {{ request()->routeIs('doctor.sections.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M4 5h16v2H4V5zm0 6h16v2H4v-2zm0 6h10v2H4v-2z" fill="currentColor"/>
          </svg>
          <span>Sections</span>
        </a>

        <hr class="my-2">

        @auth
          <div class="px-3 py-2">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
              @csrf
              <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                Logout
              </button>
            </form>
          </div>
        @endauth

        @guest
          <div class="px-3 py-2">
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
              <span class="me-1">🔑</span> Login
            </a>
          </div>
        @endguest
      </nav>
    </div>
  </div>

  <main class="app-main container-fluid">
    @yield('content')
  </main>
  
  @stack('scripts')

</body>
</html>