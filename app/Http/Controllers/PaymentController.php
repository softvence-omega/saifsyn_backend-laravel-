<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function index(Request $request)
{
    try {
        $perPage = $request->query('per_page', 10);
        $platform = $request->query('platform'); // optional: web or app

        $query = Payment::with(['user', 'plan'])->latest();

        if ($platform) {
            $query->where('platform', $platform);
        }

        $payments = $query->paginate($perPage);

        $formatted = $payments->getCollection()->map(function ($payment) {
            return [
                'id'             => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount'         => $payment->amount,
                'currency'       => $payment->currency,
                'status'         => $payment->status,
                'platform'       => $payment->platform,
                'created_at'     => $payment->created_at,
                'updated_at'     => $payment->updated_at,
                'user' => $payment->user ? [
                    'id' => $payment->user->id,
                    'name' => $payment->user->name,
                    'email' => $payment->user->email,
                    'subscription_plan_id' => $payment->user->subscription_plan_id
                ] : null,
                'plan' => $payment->plan ? [
                    'id' => $payment->plan->id,
                    'title' => $payment->plan->title,
                    'price' => $payment->plan->price
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'per_page'     => $payments->perPage(),
                'total'        => $payments->total(),
            ],
            'data' => $formatted,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch payments: ' . $e->getMessage(),
        ], 500);
    }
}






public function show(Request $request)
{
    try {
        $user = $request->user(); // লগইন করা ইউজার

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // ইউজারের সব payment history fetch
        $payments = Payment::with('plan')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        // latest payment
        $latestPayment = $payments->first();

        // formatted history
        $paymentHistory = $payments->map(function ($payment) {
            return [
                'id'             => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount'         => $payment->amount,
                'currency'       => $payment->currency,
                'status'         => $payment->status,
                'platform'       => $payment->platform,
                'created_at'     => $payment->created_at,
                'updated_at'     => $payment->updated_at,
                'plan' => $payment->plan ? [
                    'id' => $payment->plan->id,
                    'title' => $payment->plan->title,
                    'price' => $payment->plan->price
                ] : null,
            ];
        });

        // formatted latest
        $formattedLatest = $latestPayment ? [
            'id'             => $latestPayment->id,
            'transaction_id' => $latestPayment->transaction_id,
            'amount'         => $latestPayment->amount,
            'currency'       => $latestPayment->currency,
            'status'         => $latestPayment->status,
            'platform'       => $latestPayment->platform,
            'created_at'     => $latestPayment->created_at,
            'updated_at'     => $latestPayment->updated_at,
            'plan' => $latestPayment->plan ? [
                'id' => $latestPayment->plan->id,
                'title' => $latestPayment->plan->title,
                'price' => $latestPayment->plan->price
            ] : null,
        ] : null;

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'latest_payment' => $formattedLatest,
            'payment_history' => $paymentHistory,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch user info: ' . $e->getMessage(),
        ], 500);
    }
}



    /**
     * Process payment / subscription for web or app
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'plan_id'  => 'required|exists:subscription_plans,id',
            'platform' => 'required|in:web,app'
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $user = auth()->user();
        $platform = $request->platform;

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // Create a new payment record
            $payment = Payment::create([
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'transaction_id' => 'TEMP_' . uniqid(),
                'amount'         => $plan->price,
                'currency'       => 'usd',
                'status'         => 'unpaid',
                'platform'       => $platform,
            ]);

            if ($platform === 'web') {
                // Web: Checkout Session
                $session = $stripe->checkout->sessions->create([
                    'mode' => 'payment',
                    'line_items' => [[
                        'price_data' => [
                            'currency'    => 'usd',
                            'unit_amount' => (int) ($plan->price * 100),
                            'product_data' => [
                                'name' => $plan->title,
                                'description' => $plan->description,
                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'user_id'    => $user->id,
                    ],
                    'success_url' => url('/api/v1/payment/success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'  => url('/api/v1/payment/cancel'),
                ]);

                $payment->update(['transaction_id' => $session->id]);

                return response()->json([
                    'success'      => true,
                    'checkout_url' => $session->url,
                    'session_id'   => $session->id,
                    'amount'       => $plan->price,
                ]);

            } else {
                // App: PaymentIntent
                $paymentIntent = $stripe->paymentIntents->create([
                    'amount' => $plan->price * 100,
                    'currency' => 'usd',
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'user_id' => $user->id,
                    ],
                ]);

                $payment->update(['transaction_id' => $paymentIntent->id]);

                return response()->json([
                    'success' => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_id' => $payment->id,
                    'amount' => $plan->price,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Payment success (for web)
     */
    public function paymentSuccess(Request $request)
    {
        return response()->json([
            'success'    => true,
            'session_id' => $request->query('session_id'),
            'message'    => 'Payment completed. Subscription will be activated shortly after verification.',
        ]);
    }

    /**
     * Payment cancel
     */
    public function paymentCancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment cancelled. You can retry anytime.',
        ]);
    }

    /**
     * Stripe Webhook: Final verification
     */
    public function handleWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );

            if ($event->type !== 'checkout.session.completed' && $event->type !== 'payment_intent.succeeded') {
                return response('Event ignored', 200);
            }

            $session   = $event->data->object;
            $paymentId = $session->metadata->payment_id ?? null;

            if (!$paymentId) {
                return response('Missing payment ID', 400);
            }

            $payment = Payment::find($paymentId);

            if (!$payment || $payment->status === 'paid') {
                return response('Already processed', 200);
            }

            // Mark payment as paid
            $payment->update(['status' => 'paid']);

            // Update user's subscription_plan_id
            $payment->user->update([
                'subscription_plan_id' => $payment->plan_id,
            ]);

            return response('Subscription activated', 200);

        } catch (\Exception $e) {
            return response('Webhook error: ' . $e->getMessage(), 400);
        }
    }
}
