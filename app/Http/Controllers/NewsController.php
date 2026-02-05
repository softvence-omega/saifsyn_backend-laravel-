<?php
namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    // GET all news
    public function index()
    {
        return response()->json(News::latest()->get());
    }

    // STORE news
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|unique:news,title',
                'content' => 'required|string',
                'image' => 'nullable|string',
                'status' => 'boolean'
            ]);

            $news = News::create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'content' => $request->content,
                'image' => $request->image,
                'status' => $request->status ?? true,
                'published_at' => now()
            ]);

            return response()->json([
                'message' => 'News created successfully',
                'data' => $news
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // SHOW single news
    public function show($id)
    {
        return response()->json(News::findOrFail($id));
    }

    // UPDATE news
    public function update(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);

            $request->validate([
                'title' => 'sometimes|string|unique:news,title,' . $id,
                'content' => 'sometimes|string',
                'image' => 'nullable|string',
                'status' => 'boolean'
            ]);

            $news->update($request->all());

            return response()->json([
                'message' => 'News updated successfully',
                'data' => $news
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // SOFT DELETE news
    public function destroy($id)
    {
        try {
            $news = News::findOrFail($id);
            $news->delete(); // ✅ Soft delete

            return response()->json([
                'message' => 'News deleted (soft delete) successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete news',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
