@extends('public.layouts.app')

@section('title', 'Our Blog')

@push('styles')
<style>
    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 80px 0;
        margin-bottom: 50px;
        text-align: center;
        border-bottom: 5px solid #0d6efd;
    }
    .page-header h1 {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-weight: 800;
        letter-spacing: -1px;
    }
    .blog-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        background: white;
    }
    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
    }
    .blog-card-img-wrap {
        height: 220px;
        overflow: hidden;
    }
    .blog-card img {
        height: 100%;
        object-fit: cover;
        width: 100%;
        transition: transform 0.5s ease;
    }
    .blog-card:hover img {
        transform: scale(1.05);
    }
    .blog-card-body {
        padding: 25px;
        display: flex;
        flex-direction: column;
    }
    .blog-title {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 12px;
        color: #1a1a2e;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .blog-meta {
        font-size: 0.85rem;
        color: #6c757d;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        margin-bottom: 15px;
    }
    .blog-meta i {
        margin-right: 4px;
        color: #0d6efd;
    }
    .blog-excerpt {
        color: #555;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .btn-read-more {
        border-radius: 30px;
        padding-left: 20px;
        padding-right: 20px;
        font-weight: 600;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<header class="page-header">
    <div class="container">
        <h1 class="display-4 mb-3">Insights & Stories</h1>
        <p class="lead opacity-75">Explore the latest trends, guides, and thoughts from our expert team.</p>
    </div>
</header>

<div class="container mb-5">
    @if($blogs->isEmpty())
        <div class="text-center py-5">
            <div class="mb-4">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#dee2e6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    <path d="M12 6v6"></path>
                    <path d="M12 18h.01"></path>
                </svg>
            </div>
            <h3 class="text-muted fw-bold">No blog posts found.</h3>
            <p class="text-secondary">We're currently crafting some great content for you. Check back very soon!</p>
            <a href="{{ url('/') }}" class="btn btn-primary mt-3 px-4 rounded-pill">Back to Home</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($blogs as $blog)
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="blog-card">
                        <div class="blog-card-img-wrap">
                            <a href="{{ route('blog.show', $blog->slug) }}">
                                <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}">
                            </a>
                        </div>
                        <div class="blog-card-body">
                            <div class="blog-meta">
                                <span><i class="far fa-user"></i> {{ $blog->author->name }}</span>
                                <span class="mx-2 text-muted opacity-50">•</span>
                                <span><i class="far fa-calendar-alt"></i> {{ $blog->published_date }}</span>
                            </div>
                            <h5 class="blog-title">
                                <a href="{{ route('blog.show', $blog->slug) }}" class="text-decoration-none text-dark">
                                    {{ $blog->title }}
                                </a>
                            </h5>
                            <p class="blog-excerpt">
                                {{ $blog->excerpt }}
                            </p>
                            <div class="mt-auto">
                                <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-outline-primary btn-read-more">
                                    Read Full Story
                                </a>
                                <span class="float-end text-muted small mt-1">
                                    <i class="far fa-clock"></i> {{ $blog->read_time }}m
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5 pt-4">
            {{ $blogs->links() }}
        </div>
    @endif
</div>
@endsection
