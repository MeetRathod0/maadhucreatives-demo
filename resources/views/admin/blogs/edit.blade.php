@extends('admin.layouts.app')

@section('page-title', 'Edit Blog')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blogs</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" id="blog-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Left Column: Primary Content -->
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Content</h3>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="form-group">
                            <label for="title" class="font-weight-bold">Blog Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="Enter title..." 
                                   value="{{ old('title', $blog->title) }}" 
                                   required>
                            <small class="text-muted"><i class="fas fa-lightbulb text-warning mr-1"></i> Use a clear and descriptive title for better impact.</small>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="form-group">
                            <label for="slug" class="font-weight-bold">Slug / URL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ url('/blog') }}/</span>
                                </div>
                                <input type="text" 
                                       name="slug" 
                                       id="slug" 
                                       class="form-control @error('slug') is-invalid @enderror" 
                                       placeholder="blog-post-url" 
                                       value="{{ old('slug', $blog->slug) }}" 
                                       required>
                            </div>
                            <small class="text-muted">Lowercase, numbers, and hyphens only.</small>
                            @error('slug')
                                <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                            @enderror
                        </div>

                        <!-- Status Toggle (Relocated after Slug) -->
                        <div class="form-group">
                            <label class="font-weight-bold d-block">Status</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       name="status" 
                                       class="custom-control-input" 
                                       id="status" 
                                       value="1" 
                                       {{ old('status', $blog->status) == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="status">
                                    <span class="text-muted small">Enable to make this post public</span>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="blog-description" class="font-weight-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" 
                                      id="blog-description" 
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="15">{{ old('description', $blog->description) }}</textarea>
                            <small class="text-muted"><i class="fas fa-lightbulb text-warning mr-1"></i> Use headings (H2, H3), lists, and images to structure your story.</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Featured Image -->
                        <div class="form-group mt-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-image mr-1"></i>
                                Featured Image (Keep empty to keep current)
                            </label>

                            @if($blog->image)
                            <div class="mb-3" style="max-width: 320px;">
                                <img src="{{ $blog->image_url }}"
                                     alt="Current"
                                     style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px; border: 2px solid #dee2e6;">
                                <small class="text-muted d-block mt-1">Current image used on public site.</small>
                            </div>
                            @endif

                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file"
                                           name="image"
                                           id="image"
                                           class="custom-file-input @error('image') is-invalid @enderror"
                                           accept=".jpg,.jpeg,.png,.webp">
                                    <label class="custom-file-label" for="image">Choose new image</label>
                                </div>
                            </div>
                            @error('image')
                                <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                            @enderror

                            {{-- New Preview box --}}
                            <div id="image-preview" class="mt-3" style="display:none; max-width: 320px;">
                                <label class="text-muted small">New Image Preview:</label>
                                <img id="preview-img"
                                     src=""
                                     alt="Preview"
                                     style="width:100%; height:180px; object-fit:cover; border-radius:10px; border: 2px solid #28a745;">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Save Button -->
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.blogs.index') }}" class="btn btn-default mr-3 px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-save mr-2"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar Settings -->
            <div class="col-md-6">
                <!-- SEO Meta Tags Card -->
                <div class="card card-outline card-info shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO Settings</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-xs btn-info" onclick="addMetaTag()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div id="meta-tags-container">
                            @php
                                $metatags = old('metatags', $blog->metatags ?: [['key' => 'description', 'value' => '']]);
                            @endphp
                            @foreach($metatags as $index => $tag)
                                <div class="meta-tag-row mb-3 bg-light p-2 rounded {{ $index === 0 ? 'border border-info' : '' }}">
                                    <div class="form-group mb-2">
                                        <input type="text" name="metatags[{{ $index }}][key]" class="form-control form-control-sm" placeholder="Meta Name" value="{{ $tag['key'] ?? '' }}">
                                    </div>
                                    <div class="input-group">
                                        <input type="text" name="metatags[{{ $index }}][value]" class="form-control form-control-sm" placeholder="Meta Content" value="{{ $tag['value'] ?? '' }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-xs btn-danger" onclick="removeMetaTag(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-body">
                        <div class="small text-muted mb-2">
                            <i class="fas fa-calendar mr-1"></i> Created: {{ $blog->created_at->format('M d, Y') }}
                        </div>
                        <div class="small text-muted mb-0">
                            <i class="fas fa-eye mr-1"></i> Total Views: {{ number_format($blog->views) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- TinyMCE (Community Edition via cdnjs) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.7.0/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    $(function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#blog-description',
                height: 350,
                menubar: 'edit view insert format table tools help',
                promotion: false,
                branding: false,
                plugins: 'lists link image table code wordcount',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | table | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 15px; line-height: 1.7; }',
                setup: function(editor) {
                    editor.on('change', function() { editor.save(); });
                }
            });
        }
    });

    document.getElementById('blog-form').addEventListener('submit', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
    });

    // Auto-slug
    document.getElementById('title').addEventListener('keyup', function() {
        if (!document.getElementById('slug').dataset.manual) {
            let slug = this.value.toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            document.getElementById('slug').value = slug;
        }
    });

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manual = true;
    });

    // Preview
    document.getElementById('image').addEventListener('change', function() {
        const preview = document.getElementById('image-preview');
        const img = document.getElementById('preview-img');
        const file = this.files[0];
        
        this.nextElementSibling.innerText = file ? file.name : "Choose new image";

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Meta Tags
    let metaTagCount = {{ count($metatags) }};
    function addMetaTag() {
        const container = document.getElementById('meta-tags-container');
        const row = document.createElement('div');
        row.className = 'meta-tag-row mb-3 bg-light p-2 rounded';
        row.innerHTML = `
            <div class="form-group mb-2">
                <input type="text" name="metatags[${metaTagCount}][key]" class="form-control form-control-sm" placeholder="Meta Name">
            </div>
            <div class="input-group">
                <input type="text" name="metatags[${metaTagCount}][value]" class="form-control form-control-sm" placeholder="Meta Content">
                <div class="input-group-append">
                    <button type="button" class="btn btn-xs btn-danger" onclick="removeMetaTag(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(row);
        metaTagCount++;
    }

    function removeMetaTag(btn) {
        btn.closest('.meta-tag-row').remove();
    }
</script>
@endpush
