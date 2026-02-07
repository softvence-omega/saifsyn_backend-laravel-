<?php

namespace App\Http\Controllers;

use App\Models\OurAnalysis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendAnalysisNotification;

class OurAnalysisController extends Controller
{
    // -----------------------------
    // 1. List all analyses (paginated)
    // -----------------------------
    public function index()
    {
        try {
            $analyses = OurAnalysis::orderBy('created_at', 'desc')->paginate(10);

            // Add full image URL
            $analyses->getCollection()->transform(function ($analysis) {
                $analysis->image = $analysis->image ? asset('storage/' . $analysis->image) : null;
                return $analysis;
            });

            return response()->json([
                'success' => true,
                'data' => $analyses
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch analyses', $e);
        }
    }

    // -----------------------------
    // 2. Show single analysis
    // -----------------------------
    public function show($id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);

            // Full image URL
            $analysis->image = $analysis->image ? asset('storage/' . $analysis->image) : null;

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Analysis not found', $e, 404);
        }
    }

    // -----------------------------
    // 3. Create new analysis
    // -----------------------------
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|unique:our_analyses,title',
                'content' => 'required|string',
                'image' => 'nullable|image|max:2048',
                'status' => 'nullable',
                'published_at' => 'nullable|date',
            ]);

            // Convert status to boolean if provided
            $data['status'] = isset($data['status']) ? filter_var($data['status'], FILTER_VALIDATE_BOOLEAN) : true;

            // Slug generation
            $data['slug'] = Str::slug($data['title']);

            // Image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('analyses', 'public');
            }

            // Create analysis
            $analysis = OurAnalysis::create($data);

            // Dispatch FCM notification via Job
            SendAnalysisNotification::dispatch($analysis);

            // Full image URL
            $analysis->image = $analysis->image ? asset('storage/' . $analysis->image) : null;

            return response()->json([
                'success' => true,
                'message' => 'Analysis created successfully',
                'data' => $analysis
            ], 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create analysis', $e);
        }
    }

    // -----------------------------
    // 4. Update existing analysis
    // -----------------------------
    public function update(Request $request, $id)
    {
        try {
            $analysis = OurAnalysis::findOrFail($id);

            $data = $request->validate([
                'title' => 'sometimes|required|string|unique:our_analyses,title,' . $id,
                'content' => 'sometimes|required|string',
                'image' => 'nullable|image|max:2048',
                'status' => 'sometimes',
                'published_at' => 'nullable|date',
            ]);

            // Convert status to boolean if provided
            if (isset($data['status'])) {
                $data['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
            }

            // Image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('analyses', 'public');
            }

            // Slug update if title changes
            if (isset($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $analysis->update($data);

            // Full image URL
            $analysis->image = $analysis->image ? asset('storage/' . $analysis->image) : null;

            return response()->json([
                'success' => true,
                'message' => 'Analysis updated successfully',
                'data' => $analysis
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update analysis', $e);
        }
    }

    // -----------------------------
    // 5. Soft delete analysis
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
            return $this->errorResponse('Failed to delete analysis', $e);
        }
    }

    // -----------------------------
    // 6. Standardized error response
    // -----------------------------
    private function errorResponse($message, \Exception $e, $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $e->getMessage()
        ], $status);
    }
}
