<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount('articles')->paginate(5)->withQueryString();

        return view('admin.categories', compact('categories'));
    }
}
