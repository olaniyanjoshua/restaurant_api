<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const TAX_RATE = 0.08;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'fulfillment' => ['required', Rule::in(['Pickup', 'Delivery'])],
            'address' => ['required_if:fulfillment,Delivery', 'nullable', 'string', 'max:500'],
            'preferred_time' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $menuItems = MenuItem::whereIn(
            'id',
            collect($validated['items'])->pluck('menu_item_id')
        )->get()->keyBy('id');

        $order = DB::transaction(function () use ($validated, $menuItems) {
            $subtotal = 0;
            $lineItems = [];

            foreach ($validated['items'] as $line) {
                $menuItem = $menuItems->get($line['menu_item_id']);
                $lineTotal = $menuItem->price * $line['quantity'];
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'price' => $menuItem->price,
                    'quantity' => $line['quantity'],
                ];
            }

            $tax = round($subtotal * self::TAX_RATE, 2);
            $total = round($subtotal + $tax, 2);

            $order = Order::create([
                'order_number' => 'GH-'.strtoupper(Str::random(6)),
                'customer_name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'fulfillment' => $validated['fulfillment'],
                'address' => $validated['address'] ?? null,
                'preferred_time' => $validated['preferred_time'] ?? null,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
            ]);

            $order->items()->createMany($lineItems);

            return $order;
        });

        $order->load('items');

        return response()->json($this->formatOrder($order), 201);
    }

    public function show(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        return $this->formatOrder($order);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'orderNumber' => $order->order_number,
            'status' => $order->status,
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->email,
                'phone' => $order->phone,
                'fulfillment' => $order->fulfillment,
                'address' => $order->address,
                'time' => $order->preferred_time,
                'payment' => $order->payment_method,
                'notes' => $order->notes,
            ],
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->menu_item_id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
            ]),
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'total' => (float) $order->total,
            'placedAt' => $order->created_at->toIso8601String(),
        ];
    }
}
