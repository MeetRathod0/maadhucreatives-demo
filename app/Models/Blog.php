<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Blog extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'description',
        'image',
        'read_time',
        'views',
        'status',
        'metatags',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metatags' => 'array',
            'status' => 'integer',
            'views' => 'integer',
            'read_time' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the author that owns the blog.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /**
     * Scope a query to only include active blogs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include inactive blogs.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to search blogs by title.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn($q) => $q->where('title', 'LIKE', "%{$term}%"));
    }

    /**
     * Scope a query to filter blogs by status.
     */
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            $status !== null && $status !== 'all',
            fn($q) => $q->where('status', $status)
        );
    }

    /**
     * Scope a query to filter blogs by date range.
     */
    public function scopeFilterByDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    /**
     * Scope a query to order blogs by latest first.
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::url($this->image)
            : asset('assets/images/placeholder.webp');
    }

    /**
     * Get truncated description.
     */
    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->description ?? ''), 150);
    }

    /**
     * Get formatted published date.
     */
    public function getPublishedDateAttribute(): string
    {
        return $this->created_at?->format('M d, Y') ?? '';
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 1 ? 'Active' : 'Inactive';
    }

    /**
     * Get shortened title for UI.
     */
    public function getTitleShortAttribute(): string
    {
        return Str::limit($this->title, 25);
    }
}
