<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name', 'Blog') }}</title>
    
    @yield('meta')

    <!-- Load Local Bootstrap 5 -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap5/css/bootstrap.min.css') }}">
    
    {{-- For better typography optionally add a web-safe font stack --}}
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        body {
            font-family: Georgia, 'Times New Roman', Times, serif;
            background-color: #f8f9fa;
            color: #212529;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }

        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand { 
            font-weight: 700; 
            letter-spacing: 0.5px; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .nav-link {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-weight: 500;
        }

        .footer { 
            background: #1a1a2e; 
            color: #adb5bd; 
            padding: 40px 0; 
            margin-top: 60px;
            border-top: 1px solid #0f3460;
        }

        .blog-content { 
            font-size: 1.05rem; 
            line-height: 1.85; 
        }
        .blog-content p { margin-bottom: 1.2rem; }
        .blog-content h2, .blog-content h3 { margin-top: 2rem; margin-bottom: 1rem; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .blog-content img { max-width: 100%; border-radius: 8px; }

        @stack('styles')
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Maadhu Creatives') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('blog.*') ? 'active fw-bold' : '' }}" href="{{ route('blog.index') }}">Blog</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p class="mb-2">&copy; {{ date('Y') }} {{ config('app.name', 'Maadhu Creatives') }}. All rights reserved.</p>
            <small class="text-muted">A premium blog experience.</small>
        </div>
    </footer>

    <!-- Load Local Bootstrap 5 Bundle -->
    <script src="{{ asset('assets/vendor/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
