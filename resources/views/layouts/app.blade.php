<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Students Work — AI-based Working Length Tool</title>

  <link rel="icon" href="{{ asset('assets/image/logo.png') }}" />
  @vite(['resources/js/app.js'])

</head>

<body class="bg-light">

  <!-- Header -->
<header class="app-header bg-white shadow-sm border-bottom fixed-top">

    <div class="header-left d-none d-lg-block"> <div class="user-name fw-semibold small" id="userNameHeader">
          {{ auth()->check() ? auth()->user()->name : '—' }}
        </div>
        <div class="user-id text-muted small" id="userIdHeader">
          {{ auth()->check() ? (auth()->user()->student_number ?? '—') : '—' }}
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
      <a href="{{ url('/') }}" class="tab-link {{ request()->is('/') ? 'active' : '' }}" data-tab="home">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5z" fill="currentColor" />
        </svg>
        <span>Home</span>
      </a>

      <a href="{{ url('/assignments') }}" class="tab-link {{ request()->is('assignments*') ? 'active' : '' }}"
        data-tab="assignments">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <path d="M12 3v12m0 0 4-4m-4 4-4-4m-3 7h14a2 2 0 0 0 2-2V7" fill="none" stroke="currentColor"
            stroke-width="1.6" />
        </svg>
        <span>Assignments</span>
      </a>

      <a href="{{ url('/history') }}" class="tab-link {{ request()->is('history*') ? 'active' : '' }}"
        data-tab="history">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <path d="M12 8v5l3 3M4 13a8 8 0 1 0 2.3-5.7L4 9V5" fill="none" stroke="currentColor" stroke-width="1.6" />
        </svg>
        <span>History</span>
      </a>
      
      @auth
        <form method="POST" action="{{ route('logout') }}" class="m-0 d-lg-block d-none">
          @csrf
          <button type="submit" class="btn btn-outline-secondary btn-sm d-lg-none d-block">Logout</button>
        </form>
      @endauth
      @guest
        <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-lg-block d-none">
          <span class="me-1">🔑</span> Login
        </a>
      @endguest
      </div>


    </nav>
  </aside>

  <!-- Offcanvas Sidebar (Mobile/Tablet) -->
  <!-- Offcanvas Sidebar (Mobile/Tablet) -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0">
      <nav class="tabs-nav d-flex flex-column">
        <!-- Links -->
        <a href="{{ url('/') }}" class="tab-link {{ request()->is('/') ? 'active' : '' }}" >
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5z" fill="currentColor" />
          </svg>
          <span>Home</span>
        </a>

        <a href="{{ url('/assignments') }}" class="tab-link {{ request()->is('assignments*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M12 3v12m0 0 4-4m-4 4-4-4m-3 7h14a2 2 0 0 0 2-2V7" fill="none" stroke="currentColor"
              stroke-width="1.6" />
          </svg>
          <span>Assignments</span>
        </a>

        <a href="{{ url('/history') }}" class="tab-link {{ request()->is('history*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <path d="M12 8v5l3 3M4 13a8 8 0 1 0 2.3-5.7L4 9V5" fill="none" stroke="currentColor" stroke-width="1.6" />
          </svg>
          <span>History</span>
        </a>

        <!-- Divider -->
        <hr class="my-2">

        <!-- Language -->
       

        <!-- Auth buttons (mobile/tablet) -->
        @auth
          <div class="px-3 py-2">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
              @csrf
              <button type="submit" class="btn btn-outline-secondary btn-sm w-100" >
                Logout
              </button>
            </form>
          </div>
        @endauth

        @guest
          <div class="px-3 py-2">
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100" >
              <span class="me-1">🔑</span> Login
            </a>
          </div>
        @endguest
      </nav>
    </div>
  </div>


  <!-- Main -->
  <main class="app-main container-fluid">
    @yield('content')
  </main>
@stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
</body>

</html>