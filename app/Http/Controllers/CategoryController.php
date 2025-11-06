<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categories = Category::when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:categories,name',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->has('status');
        Category::create($data);

        return redirect()->route('categories.index')->with('status', 'Category created');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->has('status');
        $category->update($data);

        return redirect()->route('categories.index')->with('status', 'Category updated');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('status', 'Category deleted');
    }
}
