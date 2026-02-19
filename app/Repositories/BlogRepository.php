<?php

namespace App\Repositories;

use App\Models\Blog;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogRepository implements BlogRepositoryInterface
{
    public function getActivePaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Blog::with('author')
            ->active()
            ->latestFirst()
            ->paginate($perPage);
    }

    public function getAllPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Blog::with('author')
            ->search($filters['search'] ?? null)
            ->filterByStatus($filters['status'] ?? 'all')
            ->filterByDateRange($filters['date_from'] ?? null, $filters['date_to'] ?? null)
            ->latestFirst()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): Blog
    {
        return Blog::findOrFail($id);
    }

    public function findBySlug(string $slug): Blog
    {
        return Blog::where('slug', $slug)->active()->firstOrFail();
    }

    public function create(array $data): Blog
    {
        return Blog::create($data);
    }

    public function update(Blog $blog, array $data): Blog
    {
        $blog->update($data);
        return $blog->fresh();
    }

    public function softDelete(Blog $blog): bool
    {
        return (bool) $blog->delete();
    }

    public function incrementViews(Blog $blog): void
    {
        Blog::where('id', $blog->id)->increment('views');
    }

    public function toggleStatus(Blog $blog): Blog
    {
        $blog->update(['status' => $blog->status === 1 ? 0 : 1]);
        return $blog->fresh();
    }

    public function getAdjacentBlogs(Blog $blog): array
    {
        return [
            'prev' => Blog::active()
                          ->where('created_at', '<', $blog->created_at)
                          ->latestFirst()
                          ->first(),
            'next' => Blog::active()
                          ->where('created_at', '>', $blog->created_at)
                          ->oldest()
                          ->first(),
        ];
    }

    public function getDashboardStats(): array
    {
        return [
            'total_blogs'  => Blog::count(),
            'active_blogs' => Blog::active()->count(),
            'total_views'  => (int) Blog::sum('views'),
            'latest_blog'  => Blog::latestFirst()->first(),
        ];
    }

    public function isSlugUnique(string $slug, ?int $excludeId = null): bool
    {
        return Blog::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->doesntExist();
    }
}
