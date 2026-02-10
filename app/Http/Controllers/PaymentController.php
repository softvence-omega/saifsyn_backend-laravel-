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

        // Query only with user and plan
        $query = \App\Models\Payment::with(['user', 'plan'])->latest();

        $payments = $query->paginate($perPage);

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payments found.',
                'data' => []
            ]);
        }

        $formatted = $payments->getCollection()->map(function ($payment) {
            return [
                'id'             => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'amount'         => $payment->amount,
                'currency'       => $payment->currency,
                'status'         => $payment->status,
                'created_at'     => $payment->created_at,
                'updated_at'     => $payment->updated_at,
                'user' => $payment->user ? [
                    'id' => $payment->user->id,
                    'name' => $payment->user->first_name . ' ' . $payment->user->last_name,
                    'email' => $payment->user->email,
                    'subscription_plan_id' => $payment->user->subscription_plan_id
                ] : 'No user found',
                'plan' => $payment->plan ? [
                    'id' => $payment->plan->id,
                    'title' => $payment->plan->title,
                    'price' => $payment->plan->price
                ] : 'No plan found',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'current_page'   => $payments->currentPage(),
                'per_page'       => $payments->perPage(),
                'total'          => $payments->total(),
                'first_page_url' => $payments->url(1),
                'last_page_url'  => $payments->url($payments->lastPage()),
                'next_page_url'  => $payments->nextPageUrl(),
                'prev_page_url'  => $payments->previousPageUrl(),
                'from'           => $payments->firstItem(),
                'to'             => $payments->lastItem(),
                'data'           => $formatted,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch payments: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
}





    /**
 * Process payment / subscription
 */
    public function processPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $user = auth()->user();

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // Create a new payment record
            $payment = Payment::create([
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'transaction_id' => 'TEMP_' . uniqid(),
                'amount'         => $plan->price,
                'currency'       => 'usd',
                'status'         => 'unpaid',
            ]);

            // Create Stripe checkout session
            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency'    => 'usd',
                        'unit_amount' => (int) ($plan->price * 100), // cents
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

            // Update payment with Stripe session ID
            $payment->update(['transaction_id' => $session->id]);

            return response()->json([
                'success'      => true,
                'checkout_url' => $session->url,
                'session_id'   => $session->id,
                'amount'       => $plan->price,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe error: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Payment success
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

            if ($event->type !== 'checkout.session.completed') {
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
