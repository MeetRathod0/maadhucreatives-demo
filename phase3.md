You are an expert Laravel 12 developer. This is Phase 3 of a multi-phase build.
Phase 1 (foundation) and Phase 2 (auth) are already complete.

In this phase you will build:
- Blog Form Requests (Store + Update)
- Blog Service (Admin)
- Blog Controller (Admin)
- All Admin Blog Views (index, create, edit, show)
- Updated Dashboard with real stats cards
- Storage symlink reminder

Generate every file completely — no truncation, no placeholders, no skipping.

---

## FILE 1 — app/Http/Requests/Admin/StoreBlogRequest.php

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:255', 'unique:blogs,slug', 'regex:/^[a-z0-9\-]+$/'],
            'description'      => ['required', 'string'],
            'image'            => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'           => ['required', 'in:0,1'],
            'metatags'         => ['nullable', 'array'],
            'metatags.*.key'   => ['required_with:metatags', 'string', 'max:100'],
            'metatags.*.value' => ['required_with:metatags', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Blog title is required.',
            'slug.required'        => 'Slug is required.',
            'slug.unique'          => 'This slug is already taken. Please choose another.',
            'slug.regex'           => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'description.required' => 'Blog description is required.',
            'image.required'       => 'Blog image is required.',
            'image.mimes'          => 'Image must be a JPG, JPEG, PNG, or WebP file.',
            'image.max'            => 'Image size must not exceed 2MB.',
            'status.required'      => 'Status is required.',
        ];
    }
}

---

## FILE 2 — app/Http/Requests/Admin/UpdateBlogRequest.php

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        $blogId = $this->route('blog')->id;

        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:255', 'regex:/^[a-z0-9\-]+$/', "unique:blogs,slug,{$blogId}"],
            'description'      => ['required', 'string'],
            'image'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'           => ['required', 'in:0,1'],
            'metatags'         => ['nullable', 'array'],
            'metatags.*.key'   => ['required_with:metatags', 'string', 'max:100'],
            'metatags.*.value' => ['required_with:metatags', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Blog title is required.',
            'slug.required'        => 'Slug is required.',
            'slug.unique'          => 'This slug is already taken. Please choose another.',
            'slug.regex'           => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'description.required' => 'Blog description is required.',
            'image.mimes'          => 'Image must be a JPG, JPEG, PNG, or WebP file.',
            'image.max'            => 'Image size must not exceed 2MB.',
            'status.required'      => 'Status is required.',
        ];
    }
}

---

## FILE 3 — app/Services/Admin/BlogService.php

<?php

namespace App\Services\Admin;

use App\Models\Blog;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BlogService
{
    public function __construct(
        private BlogRepositoryInterface $blogRepository
    ) {}

    public function getPaginatedBlogs(array $filters): LengthAwarePaginator
    {
        return $this->blogRepository->getAllPaginated($filters, 10);
    }

    public function createBlog(array $validated, int $authorId): Blog
    {
        $slug      = $this->generateUniqueSlug($validated['slug'] ?? $validated['title']);
        $imagePath = $this->handleImageUpload($validated['image'], $slug);

        $data = $this->prepareData($validated, $authorId, $slug, $imagePath);

        return $this->blogRepository->create($data);
    }

    public function updateBlog(Blog $blog, array $validated): Blog
    {
        $slug = $validated['slug'] ?? $blog->slug;

        if (isset($validated['image']) && $validated['image'] instanceof UploadedFile) {
            $this->deleteImage($blog->image);
            $imagePath = $this->handleImageUpload($validated['image'], $slug);
        } else {
            $imagePath = $blog->image;
        }

        $data = $this->prepareData($validated, $blog->author_id, $slug, $imagePath);

        return $this->blogRepository->update($blog, $data);
    }

    public function deleteBlog(Blog $blog): bool
    {
        return $this->blogRepository->softDelete($blog);
    }

    public function toggleStatus(Blog $blog): Blog
    {
        return $this->blogRepository->toggleStatus($blog);
    }

    public function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base  = Str::slug($title);
        $slug  = $base;
        $count = 1;

        while (!$this->blogRepository->isSlugUnique($slug, $excludeId)) {
            $slug = $base . '-' . $count++;
        }

        return $slug;
    }

    public function handleImageUpload(UploadedFile $file, string $slug): string
    {
        $manager  = new ImageManager(new Driver());
        $image    = $manager->read($file->getRealPath());
        $filename = $slug . '-' . time() . '.webp';
        $path     = storage_path('app/public/blogs/' . $filename);

        // Ensure directory exists
        if (!file_exists(storage_path('app/public/blogs'))) {
            mkdir(storage_path('app/public/blogs'), 0755, true);
        }

        $image->toWebp(85)->save($path);

        return 'blogs/' . $filename;
    }

    public function deleteImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    private function prepareData(array $validated, int $authorId, string $slug, string $imagePath): array
    {
        return [
            'author_id'   => $authorId,
            'title'       => $validated['title'],
            'slug'        => $slug,
            'description' => $validated['description'],
            'image'       => $imagePath,
            'status'      => $validated['status'],
            'metatags'    => $this->prepareMetatags($validated['metatags'] ?? []),
            'read_time'   => calculateReadTime($validated['description']),
        ];
    }

    private function prepareMetatags(?array $metatags): array
    {
        if (empty($metatags)) {
            return [];
        }

        return array_values(array_filter($metatags, function ($tag) {
            return !empty($tag['key']) && !empty($tag['value']);
        }));
    }
}

---

## FILE 4 — app/Http/Controllers/Admin/BlogController.php

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Blog;
use App\Services\Admin\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        private BlogService $blogService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'date_from', 'date_to']);
        $blogs   = $this->blogService->getPaginatedBlogs($filters);

        return view('admin.blogs.index', compact('blogs', 'filters'));
    }

    public function create()
    {
        return view('admin.blogs.create');
    }

    public function store(StoreBlogRequest $request)
    {
        $this->blogService->createBlog(
            $request->validated(),
            auth('admin')->id()
        );

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog created successfully.');
    }

    public function show(Blog $blog)
    {
        return view('admin.blogs.show', compact('blog'));
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        $this->blogService->updateBlog($blog, $request->validated());

        return redirect()
            ->route('admin.blogs.index')
            ->with('success', 'Blog updated successfully.');
    }

    public function destroy(Blog $blog)
    {
        $this->blogService->deleteBlog($blog);

        return response()->json([
            'success' => true,
            'message' => 'Blog deleted successfully.',
        ]);
    }

    public function toggleStatus(Blog $blog)
    {
        $blog = $this->blogService->toggleStatus($blog);

        return response()->json([
            'success' => true,
            'status'  => $blog->status,
            'label'   => $blog->status_label,
        ]);
    }
}

---

## FILE 5 — app/Http/Controllers/Admin/DashboardController.php

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\BlogRepositoryInterface;

class DashboardController extends Controller
{
    public function __construct(
        private BlogRepositoryInterface $blogRepository
    ) {}

    public function index()
    {
        $stats = $this->blogRepository->getDashboardStats();

        return view('admin.dashboard.index', compact('stats'));
    }
}

---

## FILE 6 — resources/views/admin/dashboard/index.blade.php

Replace the Phase 2 placeholder completely. Full dashboard with stats:

@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
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
                    {{ $stats['latest_blog'] ? Str::limit($stats['latest_blog']->title, 20) : 'N/A' }}
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

---

## FILE 7 — resources/views/admin/blogs/index.blade.php

Full blog list page. Write completely:

@extends('admin.layouts.app')

@section('title', 'Blogs')
@section('page-title', 'Blogs')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active">Blogs</li>
@endsection

@section('content')

<!-- Header row: breadcrumb left, Add Blog button right -->
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Blog
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter Blogs</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.blogs.index') }}" method="GET">
            <div class="row">
                <!-- Search -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Search by Title</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Enter title..."
                               value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="1" {{ ($filters['status'] ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($filters['status'] ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Date From -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date"
                               name="date_from"
                               class="form-control"
                               value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                </div>

                <!-- Date To -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date"
                               name="date_to"
                               class="form-control"
                               value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Blog Table Card -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="10%">Published</th>
                    <th width="8%">Image</th>
                    <th width="22%">Title</th>
                    <th width="10%">Author</th>
                    <th width="8%">Read Time</th>
                    <th width="8%">Views</th>
                    <th width="10%">Status</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blogs as $blog)
                <tr id="blog-row-{{ $blog->id }}">
                    <td>{{ $blogs->firstItem() + $loop->index }}</td>
                    <td>
                        <small>{{ $blog->published_date }}</small>
                    </td>
                    <td>
                        @if($blog->image)
                            <img src="{{ Storage::url($blog->image) }}"
                                 alt="{{ $blog->title }}"
                                 class="img-thumbnail"
                                 style="width:50px; height:50px; object-fit:cover;">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ Str::limit($blog->title, 40) }}</strong>
                    </td>
                    <td>{{ $blog->author->name ?? '—' }}</td>
                    <td>
                        <i class="far fa-clock mr-1"></i>
                        {{ $blog->read_time }} min
                    </td>
                    <td>
                        <i class="far fa-eye mr-1"></i>
                        {{ number_format($blog->views) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $blog->status === 1 ? 'success' : 'danger' }} mr-1"
                              id="status-badge-{{ $blog->id }}">
                            {{ $blog->status_label }}
                        </span>
                        <button type="button"
                                class="btn btn-xs btn-{{ $blog->status === 1 ? 'warning' : 'success' }}"
                                id="toggle-btn-{{ $blog->id }}"
                                onclick="toggleStatus({{ $blog->id }})">
                            {{ $blog->status === 1 ? 'Deactivate' : 'Activate' }}
                        </button>
                    </td>
                    <td>
                        <!-- View -->
                        <a href="{{ route('admin.blogs.show', $blog->id) }}"
                           target="_blank"
                           class="btn btn-xs btn-info mb-1"
                           title="Preview">
                            <i class="fas fa-eye"></i>
                        </a>
                        <!-- Edit -->
                        <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                           class="btn btn-xs btn-warning mb-1"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <!-- Delete -->
                        <button type="button"
                                class="btn btn-xs btn-danger mb-1"
                                title="Delete"
                                onclick="deleteBlog({{ $blog->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No blogs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($blogs->hasPages())
    <div class="card-footer clearfix">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
// Delete Blog
function deleteBlog(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This blog will be soft deleted. This action cannot be undone easily.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/blogs/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`blog-row-${id}`).remove();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Something went wrong. Please try again.',
                    showConfirmButton: false,
                    timer: 3500,
                });
            });
        }
    });
}

// Toggle Status
function toggleStatus(id) {
    fetch(`/admin/blogs/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badge  = document.getElementById(`status-badge-${id}`);
            const btn    = document.getElementById(`toggle-btn-${id}`);

            if (data.status === 1) {
                badge.className = 'badge badge-success mr-1';
                badge.textContent = 'Active';
                btn.className = 'btn btn-xs btn-warning';
                btn.textContent = 'Deactivate';
            } else {
                badge.className = 'badge badge-danger mr-1';
                badge.textContent = 'Inactive';
                btn.className = 'btn btn-xs btn-success';
                btn.textContent = 'Activate';
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Blog ${data.label} successfully.`,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
            });
        }
    })
    .catch(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Toggle failed. Please try again.',
            showConfirmButton: false,
            timer: 3000,
        });
    });
}
</script>
@endpush

---

## FILE 8 — resources/views/admin/blogs/create.blade.php

Full create blog page. Write completely:

@extends('admin.layouts.app')

@section('title', 'Create Blog')
@section('page-title', 'Create Blog')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.blogs.index') }}">Blogs</a>
    </li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')

<form action="{{ route('admin.blogs.store') }}"
      method="POST"
      enctype="multipart/form-data"
      id="blog-form">
    @csrf

    <div class="row">

        <!-- Left Column: Main Content -->
        <div class="col-md-8">

            <!-- Title -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-heading mr-2"></i>Blog Content</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               id="title"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="Enter blog title"
                               value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug <span class="text-danger">*</span></label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               placeholder="auto-generated-from-title"
                               value="{{ old('slug') }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Only lowercase letters, numbers, and hyphens.</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea name="description"
                                  id="blog-description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="10"
                                  placeholder="Write your blog content here...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Meta Tags -->
            <div class="card card-outline card-info">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-tags mr-2"></i>SEO Meta Tags</h3>
                    <button type="button" class="btn btn-sm btn-info" id="add-metatag">
                        <i class="fas fa-plus mr-1"></i> Add Meta Tag
                    </button>
                </div>
                <div class="card-body">
                    <div id="metatags-container">
                        @if(old('metatags'))
                            @foreach(old('metatags') as $index => $meta)
                            <div class="row metatag-row mb-2">
                                <div class="col-md-5">
                                    <input type="text"
                                           name="metatags[{{ $index }}][key]"
                                           class="form-control"
                                           placeholder="e.g. og:title"
                                           value="{{ $meta['key'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="text"
                                           name="metatags[{{ $index }}][value]"
                                           class="form-control"
                                           placeholder="e.g. My Blog Title"
                                           value="{{ $meta['value'] ?? '' }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-metatag">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted" id="no-metatags-msg">
                                No meta tags added yet. Click "Add Meta Tag" to begin.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Settings -->
        <div class="col-md-4">

            <!-- Publish Settings -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Publish Settings</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="status">Status <span class="text-danger">*</span></label>
                        <select name="status"
                                id="status"
                                class="form-control @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-save mr-2"></i> Publish Blog
                    </button>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary btn-block mt-2">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-image mr-2"></i>Featured Image</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="image">Upload Image <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file"
                                   name="image"
                                   id="image"
                                   class="custom-file-input @error('image') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.webp">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">
                            JPG, JPEG, PNG, WebP — Max 2MB. Will be converted to WebP.
                        </small>
                    </div>

                    <!-- Image Preview -->
                    <div id="image-preview" class="mt-2" style="display:none;">
                        <img id="preview-img"
                             src=""
                             alt="Preview"
                             class="img-fluid rounded"
                             style="max-height: 200px; width:100%; object-fit:cover;">
                    </div>
                </div>
            </div>

        </div>
    </div>

</form>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/tinymce/skins/ui/oxide/skin.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
<script>
// TinyMCE Init
tinymce.init({
    selector: '#blog-description',
    height: 450,
    menubar: true,
    plugins: 'lists link image table code wordcount',
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; font-size: 14px; }',
    setup: function(editor) {
        editor.on('change', function() {
            editor.save(); // sync to textarea
        });
    }
});

// Slug Auto-generation from Title
document.getElementById('title').addEventListener('blur', function () {
    const slugField = document.getElementById('slug');
    if (slugField.value === '') {
        slugField.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }
});

// Image Preview
document.getElementById('image').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        document.querySelector('.custom-file-label').textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Meta Tags — Add Row
let metaIndex = {{ old('metatags') ? count(old('metatags')) : 0 }};

document.getElementById('add-metatag').addEventListener('click', function () {
    const noMsg = document.getElementById('no-metatags-msg');
    if (noMsg) noMsg.remove();

    const container = document.getElementById('metatags-container');
    const row = document.createElement('div');
    row.className = 'row metatag-row mb-2';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="metatags[${metaIndex}][key]"
                   class="form-control" placeholder="e.g. og:title">
        </div>
        <div class="col-md-6">
            <input type="text" name="metatags[${metaIndex}][value]"
                   class="form-control" placeholder="e.g. My Blog Title">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-metatag">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    metaIndex++;
});

// Meta Tags — Remove Row
document.getElementById('metatags-container').addEventListener('click', function (e) {
    if (e.target.closest('.remove-metatag')) {
        e.target.closest('.metatag-row').remove();
    }
});
</script>
@endpush

---

## FILE 9 — resources/views/admin/blogs/edit.blade.php

Full edit blog page. Same structure as create but pre-filled. Write completely:

@extends('admin.layouts.app')

@section('title', 'Edit Blog')
@section('page-title', 'Edit Blog')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.blogs.index') }}">Blogs</a>
    </li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<form action="{{ route('admin.blogs.update', $blog->id) }}"
      method="POST"
      enctype="multipart/form-data"
      id="blog-form">
    @csrf
    @method('PUT')

    <div class="row">

        <!-- Left Column -->
        <div class="col-md-8">

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-heading mr-2"></i>Blog Content</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text"
                               name="title"
                               id="title"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="Enter blog title"
                               value="{{ old('title', $blog->title) }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="slug">Slug <span class="text-danger">*</span></label>
                        <input type="text"
                               name="slug"
                               id="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $blog->slug) }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Only lowercase letters, numbers, and hyphens.</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea name="description"
                                  id="blog-description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="10">{{ old('description', $blog->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Meta Tags -->
            <div class="card card-outline card-info">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-tags mr-2"></i>SEO Meta Tags</h3>
                    <button type="button" class="btn btn-sm btn-info" id="add-metatag">
                        <i class="fas fa-plus mr-1"></i> Add Meta Tag
                    </button>
                </div>
                <div class="card-body">
                    <div id="metatags-container">
                        @php
                            $metatags = old('metatags', $blog->metatags ?? []);
                        @endphp

                        @if(count($metatags) > 0)
                            @foreach($metatags as $index => $meta)
                            <div class="row metatag-row mb-2">
                                <div class="col-md-5">
                                    <input type="text"
                                           name="metatags[{{ $index }}][key]"
                                           class="form-control"
                                           placeholder="e.g. og:title"
                                           value="{{ $meta['key'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="text"
                                           name="metatags[{{ $index }}][value]"
                                           class="form-control"
                                           placeholder="e.g. My Blog Title"
                                           value="{{ $meta['value'] ?? '' }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-metatag">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted" id="no-metatags-msg">
                                No meta tags yet. Click "Add Meta Tag" to begin.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-md-4">

            <!-- Publish Settings -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Publish Settings</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label for="status">Status <span class="text-danger">*</span></label>
                        <select name="status"
                                id="status"
                                class="form-control @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', $blog->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $blog->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <small class="text-muted">
                            <i class="fas fa-calendar mr-1"></i>
                            Created: {{ $blog->published_date }}
                        </small><br>
                        <small class="text-muted">
                            <i class="fas fa-eye mr-1"></i>
                            Views: {{ number_format($blog->views) }}
                        </small><br>
                        <small class="text-muted">
                            <i class="far fa-clock mr-1"></i>
                            Read time: {{ $blog->read_time }} min
                        </small>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-save mr-2"></i> Update Blog
                    </button>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary btn-block mt-2">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-image mr-2"></i>Featured Image</h3>
                </div>
                <div class="card-body">

                    <!-- Current Image -->
                    @if($blog->image)
                    <div class="mb-3">
                        <label class="d-block text-muted"><small>Current Image:</small></label>
                        <img src="{{ Storage::url($blog->image) }}"
                             alt="{{ $blog->title }}"
                             class="img-fluid rounded"
                             style="max-height:180px; width:100%; object-fit:cover;">
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="image">
                            Replace Image
                            <small class="text-muted">(optional)</small>
                        </label>
                        <div class="custom-file">
                            <input type="file"
                                   name="image"
                                   id="image"
                                   class="custom-file-input @error('image') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.webp">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        @error('image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-1">
                            JPG, JPEG, PNG, WebP — Max 2MB. Will be converted to WebP.
                        </small>
                    </div>

                    <!-- New Image Preview -->
                    <div id="image-preview" class="mt-2" style="display:none;">
                        <label class="d-block text-muted"><small>New Image Preview:</small></label>
                        <img id="preview-img"
                             src=""
                             alt="Preview"
                             class="img-fluid rounded"
                             style="max-height: 180px; width:100%; object-fit:cover;">
                    </div>

                </div>
            </div>

        </div>
    </div>

</form>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/tinymce/skins/ui/oxide/skin.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
<script>
tinymce.init({
    selector: '#blog-description',
    height: 450,
    menubar: true,
    plugins: 'lists link image table code wordcount',
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; font-size: 14px; }',
    setup: function(editor) {
        editor.on('change', function() {
            editor.save();
        });
    }
});

// Image Preview
document.getElementById('image').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        document.querySelector('.custom-file-label').textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Meta Tags
let metaIndex = {{ count(old('metatags', $blog->metatags ?? [])) }};

document.getElementById('add-metatag').addEventListener('click', function () {
    const noMsg = document.getElementById('no-metatags-msg');
    if (noMsg) noMsg.remove();

    const container = document.getElementById('metatags-container');
    const row = document.createElement('div');
    row.className = 'row metatag-row mb-2';
    row.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="metatags[${metaIndex}][key]"
                   class="form-control" placeholder="e.g. og:title">
        </div>
        <div class="col-md-6">
            <input type="text" name="metatags[${metaIndex}][value]"
                   class="form-control" placeholder="e.g. My Blog Title">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-metatag">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    metaIndex++;
});

document.getElementById('metatags-container').addEventListener('click', function (e) {
    if (e.target.closest('.remove-metatag')) {
        e.target.closest('.metatag-row').remove();
    }
});
</script>
@endpush

---

## FILE 10 — resources/views/admin/blogs/show.blade.php

Admin preview page (opens in new tab). Write completely:

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

        <!-- Blog Preview Card -->
        <div class="card">

            <!-- Featured Image -->
            @if($blog->image)
            <img src="{{ Storage::url($blog->image) }}"
                 alt="{{ $blog->title }}"
                 class="card-img-top"
                 style="max-height: 400px; object-fit: cover;">
            @endif

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

---

## PHASE 3 COMPLETION CHECKLIST

After generating all files verify:
1. StoreBlogRequest — image is required, slug is unique:blogs,slug
2. UpdateBlogRequest — image is nullable, slug unique excludes current blog id
3. BlogService — handleImageUpload creates blogs directory if not exists
4. BlogService — deleteImage checks Storage::disk('public')->exists() before deleting
5. BlogService — prepareMetatags filters out empty key/value pairs
6. BlogService — prepareData calls calculateReadTime() helper from Phase 1
7. BlogController — all 7 methods present and thin (no business logic)
8. BlogController — destroy() and toggleStatus() return JSON responses
9. DashboardController — injects BlogRepositoryInterface (not BlogService)
10. dashboard/index.blade.php — uses $stats array with all 4 keys
11. blogs/index.blade.php — delete uses fetch() DELETE + removes <tr> from DOM
12. blogs/index.blade.php — toggle uses fetch() POST + updates badge and button in DOM
13. blogs/index.blade.php — pagination uses bootstrap-4 style
14. blogs/create.blade.php — TinyMCE loaded from local /assets/vendor/tinymce/tinymce.min.js
15. blogs/create.blade.php — slug auto-generates on title blur only if slug field is empty
16. blogs/create.blade.php — image preview uses FileReader API
17. blogs/edit.blade.php — all fields pre-filled using old() with $blog fallback
18. blogs/edit.blade.php — existing metatags rendered as pre-filled rows
19. blogs/show.blade.php — has link to public blog page (blog.show route)
20. blogs/show.blade.php — description rendered with {!! !!} (safe for TinyMCE HTML)

## PHASE 3 SETUP COMMANDS

Run after generating all files:
php artisan storage:link

Place TinyMCE locally before testing create/edit:
public/assets/vendor/tinymce/
  - tinymce.min.js
  - (full tinymce package with plugins and skins folders)

Download TinyMCE Community from: https://www.tiny.cloud/get-tiny/self-hosted/

Then test:
- /admin/blogs → list loads, empty state shows correctly
- /admin/blogs/create → form loads with TinyMCE editor
- Create a blog → image uploads, converts to webp, redirects with success toast
- /admin/blogs → blog appears in list with thumbnail, read time, status badge
- Toggle status → badge updates without page reload
- Edit blog → all fields pre-filled including metatags
- Update blog with new image → old image deleted, new webp stored
- Delete blog → SweetAlert2 confirm → row removed → success toast
- /admin/dashboard → all 4 stats cards show real data
- Admin show page → opens in new tab, shows full blog content

Generate every file completely. Do not skip any file.