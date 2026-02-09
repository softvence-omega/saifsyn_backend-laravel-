<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    //show-all
    
    public function index(Request $request)
{
    try {
        $showAll = $request->query('all', false);

        $query = $showAll 
            ? News::orderBy('published_at','desc') 
            : News::where('status','published')->orderBy('published_at','desc');

        $news = $query->get();

        if ($news->isEmpty()) {
            return response()->json([
                'message' => 'No news found'
            ], 404); // optional 404 status
        }

        // Full image path
        $news->transform(function($item){
            if($item->image) {
                $item->image = asset('storage/'.$item->image);
            }
            return $item;
        });

        return response()->json($news);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch news', 'message'=>$e->getMessage()], 500);
    }
}


    /**
     * Store manually (admin)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'status' => 'required|in:draft,published',
                'published_at' => 'nullable|date',
            ]);

            if($request->hasFile('image')){
                $file = $request->file('image');
                $filename = Str::slug($data['title']).'_'.time().'.'.$file->getClientOriginalExtension();
                $data['image'] = $file->storeAs('news',$filename,'public');
            }

            $news = News::create($data);
            if($news->image) $news->image = asset('storage/'.$news->image);

            return response()->json($news, 201);
        } catch (\Exception $e) {
            return response()->json(['error'=>'Failed to save news', 'message'=>$e->getMessage()], 500);
        }
    }

    /**
     * Update news (admin)
     */
    public function update(Request $request, News $news)
    {
        try {
            $data = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'status' => 'in:draft,published',
                'published_at' => 'nullable|date',
            ]);

            if($request->hasFile('image')){
                // Delete old image
                if($news->image && Storage::disk('public')->exists($news->image)){
                    Storage::disk('public')->delete($news->image);
                }
                $file = $request->file('image');
                $filename = Str::slug($data['title'] ?? $news->title).'_'.time().'.'.$file->getClientOriginalExtension();
                $data['image'] = $file->storeAs('news',$filename,'public');
            }

            $news->update($data);
            if($news->image) $news->image = asset('storage/'.$news->image);

            return response()->json($news);
        } catch (\Exception $e) {
            return response()->json(['error'=>'Failed to update news','message'=>$e->getMessage()],500);
        }
    }

    /**
     * Soft delete news
     */
    public function destroy(News $news)
    {
        try {
            if($news->image && Storage::disk('public')->exists($news->image)){
                Storage::disk('public')->delete($news->image);
            }
            $news->delete();
            return response()->json(['message'=>'News deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error'=>'Failed to delete news','message'=>$e->getMessage()],500);
        }
    }
}
