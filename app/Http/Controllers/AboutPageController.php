<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AboutPage;
use Illuminate\Support\Facades\Storage;
use Exception;

class AboutPageController extends Controller
{
    // Show About Page
    public function show()
    {
        try {
            $about = AboutPage::first();

            // Add full video URL if video exists
            if ($about && $about->video) {
                $about->video = asset('storage/' . $about->video);
            }

            return response()->json([
                'success' => true,
                'data' => $about
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch about page',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Create or Update About Page (Single Row)
    public function store(Request $request)
    {
        try {
            // ✅ Validation
            $validated = $request->validate([
                'description' => 'nullable|string',
                'our_mission' => 'nullable|array',
                'our_vision' => 'nullable|array',

                // Video validation
                'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:51200' // 50MB
            ]);

            // Find existing row
            $about = AboutPage::first();

            // ✅ Handle Video Upload
            if ($request->hasFile('video')) {

                // Delete old video if exists
                if ($about && $about->video && Storage::disk('public')->exists($about->video)) {
                    Storage::disk('public')->delete($about->video);
                }

                // Store new video
                $videoPath = $request->file('video')->store('about/videos', 'public');
                $validated['video'] = $videoPath;
            }

            // ✅ Create OR Update Single Row
            $about = AboutPage::updateOrCreate(
                ['id' => $about->id ?? 1],
                $validated
            );

            // Add full video URL for API response
            if ($about->video) {
                $about->video = asset('storage/' . $about->video);
            }

            return response()->json([
                'success' => true,
                'message' => 'About page saved successfully',
                'data' => $about
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save about page',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete About Page
    public function destroy()
    {
        try {
            $about = AboutPage::first();

            if (!$about) {
                return response()->json([
                    'success' => false,
                    'message' => 'No about page found'
                ], 404);
            }

            // Delete video file
            if ($about->video && Storage::disk('public')->exists($about->video)) {
                Storage::disk('public')->delete($about->video);
            }

            // Soft delete record
            $about->delete();

            return response()->json([
                'success' => true,
                'message' => 'About page deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete about page',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
