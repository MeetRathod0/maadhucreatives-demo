@extends('admin.layouts.app')

@section('title', 'Blog Preview — ' . $blog->title)
@section('page-title', 'Blog Preview')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.blogs.index') }}">Blogs</a>
    </li>
    <li class="breadcrumb-item active">Preview</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-md-10">

        <!-- Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Blogs
            </a>
            <div>
                <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-warning mr-2">
                    <i class="fas fa-edit mr-1"></i> Edit Blog
                </a>
                <a href="{{ route('blog.show', $blog->slug) }}"
                   target="_blank"
                   class="btn btn-info">
                    <i class="fas fa-external-link-alt mr-1"></i> View Public Page
                </a>
            </div>
        </div>

        <!-- Inactive Warning -->
        @if($blog->status === 0)
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Warning!</h5>
            This blog is <strong>inactive</strong>. The public link below will return 404 until activated.
        </div>
        @endif

        <!-- Blog Preview Card -->
        <div class="card">

            <!-- Featured Image -->
            <img src="{{ $blog->image_url }}"
                 alt="{{ $blog->title }}"
                 class="card-img-top"
                 style="max-height: 400px; object-fit: cover;">

            <div class="card-body">

                <!-- Status Badge -->
                <span class="badge badge-{{ $blog->status === 1 ? 'success' : 'danger' }} mb-2">
                    {{ $blog->status_label }}
                </span>

                <!-- Title -->
                <h2 class="card-title mb-3">{{ $blog->title }}</h2>

                <!-- Meta Bar -->
                <div class="d-flex flex-wrap gap-3 text-muted mb-3" style="gap: 1rem;">
                    <span>
                        <i class="fas fa-user mr-1"></i>
                        {{ $blog->author->name ?? 'Unknown' }}
                    </span>
                    <span>
                        <i class="fas fa-calendar mr-1"></i>
                        {{ $blog->published_date }}
                    </span>
                    <span>
                        <i class="far fa-clock mr-1"></i>
                        {{ $blog->read_time }} min read
                    </span>
                    <span>
                        <i class="far fa-eye mr-1"></i>
                        {{ number_format($blog->views) }} views
                    </span>
                </div>

                <hr>

                <!-- Blog Content -->
                <div class="blog-content" style="font-size: 1rem; line-height: 1.8;">
                    {!! $blog->description !!}
                </div>

                <!-- Meta Tags -->
                @if(!empty($blog->metatags))
                <hr>
                <h5><i class="fas fa-tags mr-2"></i>SEO Meta Tags</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blog->metatags as $meta)
                            <tr>
                                <td><code>{{ $meta['key'] }}</code></td>
                                <td>{{ $meta['value'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection
