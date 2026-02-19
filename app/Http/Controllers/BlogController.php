<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return "Public Blog Index (Coming Soon)";
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        return "Public Blog Show: " . $slug . " (Coming Soon)";
    }
}
