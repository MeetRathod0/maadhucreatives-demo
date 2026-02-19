<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\BlogRepositoryInterface;

class DashboardController extends Controller
{
    public function __construct(
        private BlogRepositoryInterface $blogRepository
    ) {}

    public function index()
    {
        $stats = $this->blogRepository->getDashboardStats();

        return view('admin.dashboard.index', compact('stats'));
    }
}
