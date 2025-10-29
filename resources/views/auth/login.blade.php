@vite (['resources/css/login.css'])
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>AI-based Working Length Tool — Login</title>
</head>

<body>
    <!-- Header -->
    <header class="topbar">
        <div class="topbar-inner">
            <img src="{{ asset('/images/logo.jpg') }}" alt="The University of Jordan" class="uj-logo" />
            <h1 class="site-title">AI-based Working Length Tool</h1>
            <div class="spacer"></div>
        </div>
    </header>

    <!-- Banner -->
    <section class="hero" style="background-image: url('{{ asset('images/hero.png') }}');">
        <div class="card">
            <h2 class="card-title">
                Welcome to AI-based Working Length Tool
                <br /><span class="muted">please log in to proceed</span>
            </h2>

            {{-- Session status --}}
            @if (session('status'))
            <div class="session-status">{{ session('status') }}</div>
            @endif

            {{-- Validation errors (general) --}}
            @if ($errors->any())
            <div class="validation-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="row">
                    <div class="col-12">
                        <label class="field w-100">
                            <span class="label">username</span>
                            <input id="email" name="email" type="text" value="{{ old('email') }}" required autofocus />
                            @error('email') <div class="field-error">{{ $message }}</div> @enderror
                        </label>
                    </div>

                    <div class="col-12">
                        <label class="field w-100">
                            <span class="label">password</span>
                            <input id="password" name="password" type="password" placeholder="••••••••" required />
                            @error('password') <div class="field-error">{{ $message }}</div> @enderror
                        </label>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="forgot-wrap">
                                <a href="https://adresetpw.ju.edu.jo/" class="forgot">forget the password?</a>
                            </div>

                            <label class="remember mb-0">
                                <input type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }} />
                                <span>Remember me</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="actions">
                            <button type="submit" class="btn primary small">signin</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

</body>

</html>