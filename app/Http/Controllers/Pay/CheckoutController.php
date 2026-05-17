<?php

namespace App\Http\Controllers\Pay;

use App\Http\Controllers\Controller;
use App\Services\Store\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutController extends Controller
{
    /**
     * Create Stripe checkout session from ACTIVE CART ORDER
     */
    public function __invoke(Request $request, CartService $cart)
    {
        $order = $cart->getActiveOrder();

        if (!$order || $order->items()->count() === 0) {
            return redirect()->back()->with('error', 'SELECTION EMPTY');
        }

        // Ensure fresh load
        $order->load('items');

        /**
         * Build Stripe line items from SNAPSHOT order items
         */
        $lineItems = $order->items->map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => strtoupper($item->name),
                        'description' => strtoupper($item->attr),
                        'images' => $item->image
                            ? [url(Storage::url($item->image))]
                            : [],
                    ],
                    'unit_amount' => (int) ($item->price * 100),
                ],
                'quantity' => $item->qty,
            ];
        })->values()->toArray();

        /**
         * Update total before checkout
         */
        $order->update([
            'total_amount' => $cart->total()
        ]);

        /**
         * =========================
         * AUTH USER FLOW (Cashier)
         * =========================
         */
        if (auth()->check()) {
            return $request->user()->checkout($lineItems, [
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('store.cart'),
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                ],
            ]);
        }

        /**
         * =========================
         * GUEST FLOW (Stripe SDK)
         * =========================
         */
        Stripe::setApiKey(config('cashier.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('store.cart'),
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        $order->update([
            'stripe_session_id' => $session->id
        ]);

        return redirect($session->url);
    }
}