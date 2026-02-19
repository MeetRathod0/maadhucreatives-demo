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
        // Ensure directory exists
        if (!file_exists(storage_path('app/public/blogs'))) {
            mkdir(storage_path('app/public/blogs'), 0755, true);
        }

        $hasGd = extension_loaded('gd');
        $hasImagick = extension_loaded('imagick');

        if ($hasGd || $hasImagick) {
            try {
                // Determine available driver
                $driver = $hasGd ? new Driver() : new \Intervention\Image\Drivers\Imagick\Driver();
                $manager = new ImageManager($driver);
                
                $image    = $manager->read($file->getRealPath());
                $filename = $slug . '-' . time() . '.webp';
                $path     = storage_path('app/public/blogs/' . $filename);

                $image->toWebp(85)->save($path);
                return 'blogs/' . $filename;
            } catch (\Exception $e) {
                // Log error if needed and fallback
            }
        }

        // Fallback: Store original image if no driver available or if processing fails
        $extension = $file->getClientOriginalExtension();
        $filename  = $slug . '-' . time() . '.' . $extension;
        $file->move(storage_path('app/public/blogs'), $filename);

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
