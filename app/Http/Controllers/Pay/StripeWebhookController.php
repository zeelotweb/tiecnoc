<?php

namespace App\Http\Controllers\Pay;

use App\Http\Controllers\Controller;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends CashierController
{
    /**
     * Handle successful Stripe checkout session
     */
    public function handleCheckoutSessionCompleted($payload)
    {
        $session = $payload['data']['object'];

        $orderId = $session['metadata']['order_id'] ?? null;

        if (!$orderId) {
            Log::error("Stripe Webhook Missing order_id: {$session['id']}");
            return $this->successMethod();
        }

        $order = Order::with('items')->find($orderId);

        /**
         * Safety: already processed OR missing order
         */
        if (!$order || $order->status === 'paid') {
            return $this->successMethod();
        }

        /**
         * Mark order as paid (idempotent)
         */
        $order->update([
            'status' => 'paid',
            'stripe_session_id' => $session['id'],
        ]);

        /**
         * Reduce stock safely
         */
        foreach ($order->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

            if ($variant) {
                $variant->decrement('stock_quantity', $item->qty);
            }
        }

        Log::info("ORDER PAID: {$order->order_number} | Session: {$session['id']}");

        return $this->successMethod();
    }
}