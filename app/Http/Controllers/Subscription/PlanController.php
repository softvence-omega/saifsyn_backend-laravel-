<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    // -----------------------------
    // 1. Show all plans
    // GET /plans
    // -----------------------------
    public function index()
    {
        try {
            $plans = SubscriptionPlan::all();
            return response()->json($plans);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }





    // -----------------------------
// 4. Show a single subscription plan
// GET /plans/{id}
// -----------------------------
public function show($id)
{
    try {
        $plan = SubscriptionPlan::findOrFail($id);

        return response()->json([
            'message' => 'Plan fetched successfully',
            'plan' => $plan
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Plan not found',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to fetch plan',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // -----------------------------
    // 2. Create a new subscription plan
    // POST /plans
    // -----------------------------
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|unique:subscription_plans,title',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'features' => 'required|array',
                'duration_type' => 'required|string',
                'duration_value' => 'required|integer',
                'is_popular' => 'boolean',
                'status' => 'boolean',
            ]);

            $plan = SubscriptionPlan::create($request->all());

            return response()->json([
                'message' => 'Plan created successfully',
                'plan' => $plan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // -----------------------------
    // 3. Update an existing subscription plan
    // POST /plans/{id}
    // -----------------------------
    public function update(Request $request, $id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);

            $request->validate([
                'title' => 'sometimes|required|string|unique:subscription_plans,title,' . $id,
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric',
                'features' => 'sometimes|required|array',
                'duration_type' => 'sometimes|required|string',
                'duration_value' => 'sometimes|required|integer',
                'is_popular' => 'sometimes|boolean',
                'status' => 'sometimes|boolean',
            ]);

            $plan->update($request->all());

            return response()->json([
                'message' => 'Plan updated successfully',
                'plan' => $plan
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Plan not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
