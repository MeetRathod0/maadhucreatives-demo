@extends('public.layouts.app')

@section('title', $blog->title)

@section('meta')
    @if($blog->metatags)
        @foreach($blog->metatags as $tag)
            @if(!empty($tag['key']) && !empty($tag['value']))
                <meta name="{{ $tag['key'] }}" content="{{ $tag['value'] }}">
            @endif
        @endforeach
    @endif
    <meta property="og:title" content="{{ $blog->title }}">
    <meta property="og:description" content="{{ $blog->excerpt }}">
    <meta property="og:image" content="{{ $blog->image_url }}">
    <meta property="og:type" content="article">
@endsection

@push('styles')
<style>
    .hero-wrap {
        position: relative;
        height: 350px;
        width: 700px;
        overflow: hidden;
        border-radius: 8px;
        border: 1px solid #eee;
    }
    .hero-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .blog-container {
        max-width: 820px;
        margin: 40px auto 60px;
        background: white;
        padding: 50px 60px;
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.1);
        position: relative;
        z-index: 10;
    }
    @media (max-width: 768px) {
        .blog-container {
            margin-top: -40px;
            padding: 30px 20px;
            border-radius: 0;
        }
        .hero-wrap {
            height: 350px;
        }
    }
    .blog-title {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-weight: 800;
        font-size: 3rem;
        line-height: 1.2;
        letter-spacing: -1.5px;
        color: #1a1a2e;
        margin-bottom: 25px;
    }
    @media (max-width: 768px) {
        .blog-title {
            font-size: 2.2rem;
        }
    }
    .meta-bar {
        font-size: 0.95rem;
        color: #6c757d;
        margin-bottom: 40px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }
    .meta-bar i {
        color: #0d6efd;
        margin-right: 5px;
    }
    .nav-post {
        flex: 1;
        padding: 25px;
        border: 1px solid #eee;
        border-radius: 12px;
        text-decoration: none;
        color: #212529;
        transition: all 0.3s ease;
        background: white;
        display: flex;
        flex-direction: column;
    }
    .nav-post:hover {
        background: #f8f9fa;
        border-color: #0d6efd;
        color: #0d6efd;
        text-decoration: none;
        transform: translateY(-3px);
    }
    .nav-post small {
        color: #6c757d;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .nav-post strong {
        font-size: 1rem;
        line-height: 1.4;
    }
    .nav-post i {
        margin-top: 10px;
        font-size: 0.9rem;
    }
    .blog-content blockquote {
        border-left: 5px solid #0d6efd;
        padding: 20px 30px;
        background: #f0f7ff;
        color: #444;
        font-style: italic;
        margin: 40px 0;
        border-radius: 0 10px 10px 0;
    }
</style>
@endpush

@section('content')
<article>
    <!-- Main Content Area -->
    <div class="container pb-5">
        <div class="blog-container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4 d-none d-md-block">
                <ol class="breadcrumb" style="background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none">Blog</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($blog->title, 30) }}</li>
                </ol>
            </nav>

            <h1 class="blog-title">{{ $blog->title }}</h1>
            
            <div class="meta-bar">
                <span><i class="far fa-user"></i> By <strong>{{ $blog->author->name }}</strong></span>
                <span class="opacity-25">•</span>
                <span><i class="far fa-calendar-alt"></i> {{ $blog->published_date }}</span>
                <span class="opacity-25">•</span>
                <span><i class="far fa-clock"></i> {{ $blog->read_time }} min read</span>
                <span class="opacity-25">•</span>
                <span><i class="far fa-eye"></i> {{ number_format($blog->views) }} views</span>
            </div>

            <div class="blog-content">
                <!-- Featured Image (Moved inside card as requested) -->
                <div class="hero-wrap mb-4">
                    <img src="{{ $blog->image_url }}" class="hero-image" alt="{{ $blog->title }}">
                </div>

                {!! $blog->description !!}
            </div>

            <hr class="my-5 opacity-10">

            <!-- Next / Prev Post Links -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch gap-3 mb-5">
                @if($adjacent['prev'])
                <a href="{{ route('blog.show', $adjacent['prev']->slug) }}" class="nav-post">
                    <small>← Previous Story</small>
                    <strong>{{ Str::limit($adjacent['prev']->title, 45) }}</strong>
                </a>
                @else
                <div class="flex-1 d-none d-md-block"></div>
                @endif

                @if($adjacent['next'])
                <a href="{{ route('blog.show', $adjacent['next']->slug) }}" class="nav-post text-md-end">
                    <small>Next Story →</small>
                    <strong>{{ Str::limit($adjacent['next']->title, 45) }}</strong>
                </a>
                @else
                <div class="flex-1 d-none d-md-block"></div>
                @endif
            </div>

            <!-- Back to Blog -->
            <div class="text-center">
                <a href="{{ route('blog.index') }}" class="btn btn-dark px-4 py-2 rounded-pill font-weight-bold">
                    <i class="fas fa-arrow-left mr-2"></i> Back to All Stories
                </a>
            </div>
        </div>
    </div>
</article>
@endsection
