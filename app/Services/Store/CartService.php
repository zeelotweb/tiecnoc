<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\ProductVariant;

class CartService
{
    /*
    |--------------------------------------------------------------------------
    | GET OR CREATE ACTIVE CART
    |--------------------------------------------------------------------------
    */
    protected function getOrCreateCart(): Order
    {
        $order = Order::currentCart();

        if ($order) return $order;

        return Order::create([
            'user_id' => auth()->id(),
            'order_number' => 'TNC-' . strtoupper(bin2hex(random_bytes(4))),
            'status' => 'pending',
            'total_amount' => 0,
            'metadata' => ['session_id' => session()->getId()],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADD ITEM TO CART
    |--------------------------------------------------------------------------
    */
    public function add($variantId, $qty = 1): Order
    {
        $variant = ProductVariant::with([
            'color.product', // ✅ correct chain
        ])->findOrFail($variantId);

        $color = $variant->color;
        $product = $color->product;

        $order = $this->getOrCreateCart();

        $item = $order->items()
            ->where('product_variant_id', $variantId)
            ->first();

        // ✅ consistent image source
        $image = $color->front_image_path ?? null;

        if ($item) {
            $item->increment('qty', $qty);
        } else {
            $order->items()->create([
                'product_variant_id' => $variant->id,
                'name' => $product->name,
                'sku' => $variant->sku ?? 'N/A',
                'image' => $image,
                'attr' => $color->color_name . ' / ' . $variant->size,
                'qty' => $qty,
                'price' => ($variant->price && $variant->price > 0)
                    ? $variant->price
                    : $product->base_price,
            ]);
        }

        $this->recalculate($order);

        return $order;
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVE ORDER
    |--------------------------------------------------------------------------
    */
    public function getActiveOrder(): ?Order
    {
        return Order::currentCart();
    }

    /*
    |--------------------------------------------------------------------------
    | CART ITEMS (EAGER LOADED - CRITICAL)
    |--------------------------------------------------------------------------
    */
    public function getItems()
    {
        return $this->getActiveOrder()?->items()
            ->with([
                'variant.color.product', // ✅ prevents N+1
            ])
            ->get() ?? collect();
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */
    public function total(): float
    {
        return (float) ($this->getActiveOrder()?->total_amount ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | ITEM COUNT
    |--------------------------------------------------------------------------
    */
    public function count(): int
    {
        $order = $this->getActiveOrder();

        return $order
            ? (int) $order->items()->sum('qty')
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAR CART
    |--------------------------------------------------------------------------
    */
    public function clear(): void
    {
        $order = $this->getActiveOrder();

        if ($order) {
            $order->items()->delete();
            $order->delete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RECALCULATE TOTALS
    |--------------------------------------------------------------------------
    */
    protected function recalculate(Order $order): void
    {
        $total = $order->items()
            ->selectRaw('SUM(price * qty) as total')
            ->value('total') ?? 0;

        $order->update([
            'total_amount' => $total
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MERGE GUEST CART INTO USER CART
    |--------------------------------------------------------------------------
    */
    public function mergeGuestCart(): void
    {
        if (!auth()->check()) return;

        $guestCart = Order::where('status', 'pending')
            ->whereNull('user_id')
            ->where('metadata->session_id', session()->getId())
            ->first();

        if (!$guestCart) return;

        $userCart = $this->getOrCreateCart();

        foreach ($guestCart->items as $item) {

            $existing = $userCart->items()
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($existing) {
                $existing->increment('qty', $item->qty);
            } else {
                $userCart->items()->create($item->only([
                    'product_variant_id',
                    'name',
                    'sku',
                    'image',
                    'attr',
                    'qty',
                    'price',
                ]));
            }
        }

        $guestCart->delete();

        $this->recalculate($userCart);
    }
}