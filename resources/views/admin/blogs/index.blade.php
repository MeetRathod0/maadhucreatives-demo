@extends('admin.layouts.app')

@section('page-title', 'Manage Blogs')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Blogs</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Filter Card -->
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Blogs</h3>
            <div class="card-tools">
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i> Create New Blog
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.blogs.index') }}" method="GET" id="filter-form">
                <div class="row">
                    <!-- Search -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Search Title</label>
                            <input type="text" name="search" class="form-control" placeholder="Search title..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>
                    <!-- Status -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" {{ (isset($filters['status']) && $filters['status'] == '1') ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ (isset($filters['status']) && $filters['status'] == '0') ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <!-- Date From -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                    </div>
                    <!-- Date To -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                    </div>
                    <!-- Buttons -->
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Blog Table Card -->
    <div class="card card-outline card-info">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Published</th>
                            <th width="8%">Image</th>
                            <th width="25%">Title</th>
                            <th width="10%">Author</th>
                            <th width="8%">Read Time</th>
                            <th width="8%">Views</th>
                            <th width="12%">Status</th>
                            <th width="14%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blogs as $blog)
                        <tr id="blog-row-{{ $blog->id }}">
                            <td>{{ $blogs->firstItem() + $loop->index }}</td>
                            <td>
                                <small class="text-muted">{{ $blog->published_date }}</small>
                            </td>
                            <td>
                                <img src="{{ $blog->image_url }}"
                                     alt="{{ $blog->title }}"
                                     class="img-thumbnail"
                                     style="width:55px; height:55px; object-fit:cover; border-radius:8px;">
                            </td>
                            <td>
                                <a href="{{ route('admin.blogs.show', $blog->id) }}" 
                                   target="_blank" 
                                   class="text-dark font-weight-bold"
                                   title="Preview in new tab"
                                   data-toggle="tooltip">
                                    {{ Str::limit($blog->title, 50) }}
                                </a>
                            </td>
                            <td>{{ $blog->author->name ?? '—' }}</td>
                            <td>
                                <i class="far fa-clock mr-1 text-muted"></i>
                                {{ $blog->read_time }} min
                            </td>
                            <td>
                                <i class="far fa-eye mr-1 text-muted"></i>
                                {{ number_format($blog->views) }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="toggle-{{ $blog->id }}"
                                               {{ $blog->status === 1 ? 'checked' : '' }}
                                               onchange="toggleStatus({{ $blog->id }})">
                                        <label class="custom-control-label" for="toggle-{{ $blog->id }}"></label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <!-- View -->
                                    <a href="{{ route('admin.blogs.show', $blog->id) }}"
                                       class="btn btn-info"
                                       title="Preview"
                                       data-toggle="tooltip">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <!-- Edit -->
                                    <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                                       class="btn btn-warning"
                                       title="Edit"
                                       data-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- Delete -->
                                    <button type="button"
                                            class="btn btn-danger"
                                            title="Delete"
                                            data-toggle="tooltip"
                                            onclick="deleteBlog({{ $blog->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No blogs found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($blogs->hasPages())
        <div class="card-footer clearfix bg-white">
            <div class="float-right">
                {{ $blogs->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

// Toggle Status Switch
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
            const checkbox = document.getElementById(`toggle-${id}`);
            checkbox.checked = data.status === 1;

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Blog status updated`,
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
            timerProgressBar: true,
        });
    });
}

// Delete Blog
function deleteBlog(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will move the blog to trash. You can restore it later if needed.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
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
                    $(`#blog-row-${id}`).fadeOut(400, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });

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
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        }
    });
}
</script>
@endpush
