<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewsNotification;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|unique:news,title',
                'content' => 'required|string',
                'image' => 'nullable|image|max:2048',
                'status' => 'boolean',
                'published_at' => 'nullable|date',
            ]);

            $data = $request->all();

            // Image Upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('news', 'public');
            }

            // Slug generate
            $data['slug'] = Str::slug($data['title']);

            // Create news
            $news = News::create($data);

            // Dispatch FCM notification via queue
            SendNewsNotification::dispatch($news);

            return response()->json([
                'message' => 'News created successfully',
                'news' => $news
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Soft delete
    public function destroy($id)
    {
        try {
            $news = News::findOrFail($id);
            $news->delete();

            return response()->json([
                'message' => 'News deleted successfully (soft delete)',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // List all news
    public function index()
    {
        try {
            $news = News::all();
            return response()->json($news);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch news',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show single news
    public function show($id)
    {
        try {
            $news = News::findOrFail($id);
            return response()->json($news);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'News not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
