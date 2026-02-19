<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        private BlogService $blogService
    ) {}

    public function index()
    {
        $blogs = $this->blogService->getActivePaginated(9);
        return view('public.blog.index', compact('blogs'));
    }

    public function show(string $slug)
    {
        $blog = $this->blogService->getBlogBySlug($slug);
        
        $this->blogService->incrementViews($blog);
        
        $adjacent = $this->blogService->getAdjacentBlogs($blog);

        return view('public.blog.show', compact('blog', 'adjacent'));
    }
}
