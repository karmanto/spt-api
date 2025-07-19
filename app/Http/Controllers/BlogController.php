<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search');

        $query = Blog::query();

        if ($search) {
            $searchTerm = strtolower($search);
            $query->where(function($q) use ($searchTerm) {
                $q->where(DB::raw('LOWER(title_id)'), 'like', "%{$searchTerm}%")
                ->orWhere(DB::raw('LOWER(title_en)'), 'like', "%{$searchTerm}%")
                ->orWhere(DB::raw('LOWER(title_ru)'), 'like', "%{$searchTerm}%");
            });
        }

        $blogs = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $blogs->items(),
            'pagination' => [
                'current_page' => $blogs->currentPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total(),
                'last_page' => $blogs->lastPage(),
            ]
        ]);
    }

    public function show(Blog $blog)
    {
        return $blog;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_id' => 'required',
            'title_en' => 'required',
            'title_ru' => 'required',
            'content_id' => 'required',
            'content_en' => 'required',
            'content_ru' => 'required',
            'posting_date' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/blogs');
            $validated['image'] = str_replace('public/', '', $path);
        }

        return Blog::create($validated);
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title_id' => 'sometimes|required',
            'title_en' => 'sometimes|required',
            'title_ru' => 'sometimes|required',
            'content_id' => 'sometimes|required',
            'content_en' => 'sometimes|required',
            'content_ru' => 'sometimes|required',
            'posting_date' => 'sometimes|required|date',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            Storage::delete('public/' . $blog->image);
            
            $path = $request->file('image')->store('public/blogs');
            $validated['image'] = str_replace('public/', '', $path);
        }

        $blog->update($validated);
        return $blog;
    }

    public function destroy(Blog $blog)
    {
        Storage::delete('public/' . $blog->image);
        
        $blog->delete();
        return response()->noContent();
    }
}