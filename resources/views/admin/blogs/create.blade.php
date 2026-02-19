@extends('admin.layouts.app')

@section('page-title', 'Create New Blog')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blogs</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.blogs.store') }}" method="POST" id="blog-form" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Left Column: Primary Content -->
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Blog Content</h3>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="form-group">
                            <label for="title" class="font-weight-bold">Blog Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   placeholder="Enter a catchy title..." 
                                   value="{{ old('title') }}" 
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
                                       value="{{ old('slug') }}" 
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
                                       {{ old('status', '1') == '1' ? 'checked' : '' }}>
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
                                      rows="15">{{ old('description') }}</textarea>
                            <small class="text-muted"><i class="fas fa-lightbulb text-warning mr-1"></i> Use headings (H2, H3), lists, and images to structure your story.</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Featured Image -->
                        <div class="form-group mt-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-image mr-1"></i>
                                Featured Image <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file"
                                           name="image"
                                           id="image"
                                           class="custom-file-input @error('image') is-invalid @enderror"
                                           accept=".jpg,.jpeg,.png,.webp"
                                           required>
                                    <label class="custom-file-label" for="image">Choose image file</label>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle mr-1"></i> JPG, JPEG, PNG, WebP — Max 2MB.
                            </small>
                            @error('image')
                                <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                            @enderror

                            {{-- Preview box --}}
                            <div id="image-preview" class="mt-3" style="display:none; max-width: 320px;">
                                <label class="text-muted small">Image Preview:</label>
                                <img id="preview-img"
                                     src=""
                                     alt="Preview"
                                     style="width:100%; height:180px; object-fit:cover; border-radius:10px; border: 2px solid #007bff;">
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
                            @if(old('metatags'))
                                @foreach(old('metatags') as $index => $tag)
                                    <div class="meta-tag-row mb-3 bg-light p-2 rounded">
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
                            @else
                                <div class="meta-tag-row mb-3 bg-light p-2 rounded border border-info">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold mb-1">Tag Name</label>
                                        <input type="text" name="metatags[0][key]" class="form-control form-control-sm" placeholder="e.g. description" value="description">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold mb-1">Content</label>
                                        <div class="input-group">
                                            <input type="text" name="metatags[0][value]" class="form-control form-control-sm" placeholder="Meta Content">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-xs btn-danger" onclick="removeMetaTag(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <p class="text-muted small mt-2 mb-0">
                            Professional tip: Add <code>description</code> and <code>keywords</code>.
                        </p>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="fas fa-history mr-1"></i> Public status can be toggled later from the list page.
                        </p>
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
    // TinyMCE Initialization
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

    // Trigger Save on Form Submit
    document.getElementById('blog-form').addEventListener('submit', function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
    });

    // Auto-generate Slug
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

    // Image Preview
    document.getElementById('image').addEventListener('change', function() {
        const preview = document.getElementById('image-preview');
        const img = document.getElementById('preview-img');
        const file = this.files[0];
        
        const fileName = file ? file.name : "Choose image file";
        this.nextElementSibling.innerText = fileName;

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

    // Meta Tags Management
    let metaTagCount = {{ old('metatags') ? count(old('metatags')) : 1 }};
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
