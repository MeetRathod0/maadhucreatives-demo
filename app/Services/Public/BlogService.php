<?php

namespace App\Services\Public;

use App\Models\Blog;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogService
{
    public function __construct(
        private BlogRepositoryInterface $blogRepository
    ) {}

    public function getActivePaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->blogRepository->getActivePaginated($perPage);
    }

    public function getBlogBySlug(string $slug): Blog
    {
        return $this->blogRepository->findBySlug($slug);
    }

    public function incrementViews(Blog $blog): void
    {
        $this->blogRepository->incrementViews($blog);
    }

    public function getAdjacentBlogs(Blog $blog): array
    {
        return $this->blogRepository->getAdjacentBlogs($blog);
    }
}
