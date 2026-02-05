<?php

namespace App\Http\Controllers;

use App\Models\OurAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OurAnalysisController extends Controller
{
    // -----------------------------
    // List all analyses
    // -----------------------------
    public function index()
    {
        try {
            $analyses = OurAnalysis::all();
            return response()->json($analyses);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch analyses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // Show single analysis
    // -----------------------------
    public function show($id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);
            return response()->json($analysis);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Analysis not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // -----------------------------
    // Create new analysis
    // -----------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|unique:our_analyses,title',
                'content' => 'required|string',
                'image' => 'nullable|image|max:2048',
                'status' => 'boolean',
                'published_at' => 'nullable|date',
            ]);

            $data = $request->all();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('analyses', 'public');
                $data['image'] = $path;
            }

            $data['slug'] = Str::slug($data['title']);

            $analysis = OurAnalysis::create($data);

            $this->sendFCMNotification($analysis);

            return response()->json([
                'message' => 'Analysis created successfully',
                'analysis' => $analysis
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // Update existing analysis
    // -----------------------------
    public function update(Request $request, $id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);

            $request->validate([
                'title' => 'sometimes|required|string|unique:our_analyses,title,' . $id,
                'content' => 'sometimes|required|string',
                'image' => 'nullable|image|max:2048',
                'status' => 'sometimes|boolean',
                'published_at' => 'nullable|date',
            ]);

            $data = $request->all();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('analyses', 'public');
                $data['image'] = $path;
            }

            if (isset($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $analysis->update($data);

            return response()->json([
                'message' => 'Analysis updated successfully',
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // Soft delete analysis
    // -----------------------------
    public function destroy($id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);
            $analysis->delete();

            return response()->json([
                'message' => 'Analysis deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // Send FCM notification
    // -----------------------------
    protected function sendFCMNotification($analysis)
    {
        $tokens = \App\Models\User::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
        if (empty($tokens)) return;

        $notification = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $analysis->title,
                'body' => \Str::limit($analysis->content, 100),
                'click_action' => url('/analysis/' . $analysis->slug),
            ],
            'data' => [
                'analysis_id' => $analysis->id,
                'slug' => $analysis->slug,
            ]
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.key'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $notification);
    }
}
