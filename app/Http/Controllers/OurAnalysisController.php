<?php

namespace App\Http\Controllers;

use App\Models\OurAnalysis;
use Illuminate\Http\Request;
use App\Jobs\SendAnalysisNotification;

class OurAnalysisController extends Controller
{
    
// -----------------------------
// 1. List all analyses (paginated)
// -----------------------------
public function index()
{
    try {
        $perPage = 10; // Pagination limit
        $analyses = OurAnalysis::orderBy('created_at', 'desc')->paginate($perPage);

        // Only return actual analysis data, no image field
        $data = $analyses->getCollection()->transform(function ($analysis) {
            return $analysis;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'per_page' => $analyses->perPage(),
                'total' => $analyses->total(),
            ],
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

        // No image processing

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
                'symbol' => 'required|string',
                'name' => 'required|string',
                'status' => 'required|string',
                'debt_to_market_cap_ratio' => 'nullable|numeric',
                'securities_to_market_cap_ratio' => 'nullable|numeric',
                'compliant_revenue' => 'nullable|numeric',
                'non_compliant_revenue' => 'nullable|numeric',
                'questionable_revenue' => 'nullable|numeric',
                'recommendation' => 'nullable|string',
                'note' => 'nullable|string',
            ]);

            $analysis = OurAnalysis::create($data);

            // Dispatch FCM notification to all users
            if(!empty($data['note'])) {
                SendAnalysisNotification::dispatch($analysis);
            }

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
                'symbol' => 'sometimes|required|string',
                'name' => 'sometimes|required|string',
                'status' => 'sometimes|required|string',
                'debt_to_market_cap_ratio' => 'nullable|numeric',
                'securities_to_market_cap_ratio' => 'nullable|numeric',
                'compliant_revenue' => 'nullable|numeric',
                'non_compliant_revenue' => 'nullable|numeric',
                'questionable_revenue' => 'nullable|numeric',
                'recommendation' => 'nullable|string',
                'note' => 'nullable|string',
            ]);

            $analysis->update($data);

            // Dispatch notification if note updated
            if(isset($data['note']) && $data['note']) {
                SendAnalysisNotification::dispatch($analysis);
            }

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
                'success' => true,
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
