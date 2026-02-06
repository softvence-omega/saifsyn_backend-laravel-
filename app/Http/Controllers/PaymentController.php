<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Auth;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    /**
     * List all payments (admin)
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $payments = Payment::with(['user', 'plan'])->latest()->paginate($perPage);

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payments found.',
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Process payment for a subscription plan
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $user = Auth::user();

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // 1️⃣ Create a local payment record (unpaid by default)
            $payment = Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_method' => 'stripe',
                'transaction_id' => 'TEMP_' . uniqid(),
                'amount' => $plan->price, // store dollars
                'currency' => 'usd',
                'status' => 'unpaid',
            ]);

            // 2️⃣ Create Stripe PaymentIntent (amount in cents)
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => round($plan->price * 100), // convert dollars to cents
                'currency' => 'usd',
                'metadata' => [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            // 3️⃣ Update transaction_id with actual Stripe PaymentIntent ID
            $payment->update(['transaction_id' => $paymentIntent->id]);

            // 4️⃣ Return full Stripe object with amount in dollars
            $paymentIntentArray = $paymentIntent->toArray();
            $paymentIntentArray['amount'] = number_format($plan->price, 2, '.', '');

            return response()->json([
                'success' => true,
                'message' => 'Stripe payment initiated.',
                'payment_intent' => $paymentIntentArray,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stripe webhook handler
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );

            // ✅ Only mark paid when payment actually succeeded
            if ($event->type === 'payment_intent.succeeded') {
                $intent = $event->data->object;
                $paymentId = $intent->metadata->payment_id ?? null;

                if ($paymentId) {
                    $payment = Payment::find($paymentId);
                    if ($payment && $payment->status !== 'paid') {
                        $payment->update(['status' => 'paid']);
                        $payment->user->update(['subscription_plan_id' => $payment->plan_id]);
                    }
                }
            }

            return response('Webhook handled', 200);

        } catch (\Exception $e) {
            return response('Webhook error: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Payment success (redirect after payment)
     */
    public function paymentSuccess(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully.',
            'session_id' => $request->query('session_id'),
        ]);
    }

    /**
     * Payment canceled (redirect after cancel)
     */
    public function paymentCancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment was cancelled.',
        ]);
    }
}
