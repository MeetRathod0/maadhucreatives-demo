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
            'metatags.*.key'   => ['nullable', 'string', 'max:100'],
            'metatags.*.value' => ['nullable', 'string', 'max:255'],
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
