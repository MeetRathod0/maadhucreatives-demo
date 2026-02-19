<?php

namespace App\Repositories\Interfaces;

use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BlogRepositoryInterface
{
    public function getActivePaginated(int $perPage = 10): LengthAwarePaginator;
    public function getAllPaginated(array $filters, int $perPage = 10): LengthAwarePaginator;
    public function findById(int $id): Blog;
    public function findBySlug(string $slug): Blog;
    public function create(array $data): Blog;
    public function update(Blog $blog, array $data): Blog;
    public function softDelete(Blog $blog): bool;
    public function incrementViews(Blog $blog): void;
    public function toggleStatus(Blog $blog): Blog;
    public function getAdjacentBlogs(Blog $blog): array;
    public function getDashboardStats(): array;
    public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
}
