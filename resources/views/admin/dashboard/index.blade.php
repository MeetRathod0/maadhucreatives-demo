@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Home</li>
@endsection

@section('content')

<div class="row">

    <!-- Total Blogs -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_blogs'] }}</h3>
                <p>Total Blogs</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <a href="{{ route('admin.blogs.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Blogs -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active_blogs'] }}</h3>
                <p>Active Blogs</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('admin.blogs.index', ['status' => '1']) }}" class="small-box-footer">
                View Active <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Views -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['total_views']) }}</h3>
                <p>Total Views</p>
            </div>
            <div class="icon">
                <i class="fas fa-eye"></i>
            </div>
            <a href="{{ route('admin.blogs.index') }}" class="small-box-footer">
                More Info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Latest Blog -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 style="font-size: 1.2rem;">
                    {{ $stats['latest_blog'] ? $stats['latest_blog']->title_short : 'N/A' }}
                </h3>
                <p>Latest Blog — {{ $stats['latest_blog'] ? $stats['latest_blog']->published_date : '—' }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            @if($stats['latest_blog'])
                <a href="{{ route('admin.blogs.edit', $stats['latest_blog']->id) }}" class="small-box-footer">
                    View Post <i class="fas fa-arrow-circle-right"></i>
                </a>
            @else
                <a href="{{ route('admin.blogs.create') }}" class="small-box-footer">
                    Create First Blog <i class="fas fa-arrow-circle-right"></i>
                </a>
            @endif
        </div>
    </div>

</div>

<!-- Welcome Card -->
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-body">
                <h5 class="mb-1">
                    <i class="fas fa-hand-wave mr-2"></i>
                    Welcome back, {{ auth('admin')->user()->name }}!
                </h5>
                <p class="text-muted mb-0">
                    Here is a quick overview of your blog panel.
                    You have <strong>{{ $stats['active_blogs'] }}</strong> active
                    {{ Str::plural('blog', $stats['active_blogs']) }} with a total of
                    <strong>{{ number_format($stats['total_views']) }}</strong> views.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
