<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_tax_inclusive')->default(false)->after('quantity');
            $table->decimal('net_total', 14, 2)->default(0)->after('is_tax_inclusive');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('net_total');
        });

        $this->backfillHistoricalItems();
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_tax_inclusive', 'net_total', 'tax_amount']);
        });
    }

    /**
     * Historical order items were always stored as tax-exclusive unit prices.
     * Back-fill their net total and distribute the order VAT across the lines
     * so old orders keep a coherent per-item breakdown.
     */
    private function backfillHistoricalItems(): void
    {
        $items = DB::table('order_items')->select('id', 'order_id', 'price', 'quantity')->get();

        foreach ($items as $item) {
            DB::table('order_items')
                ->where('id', $item->id)
                ->update(['net_total' => round((float) $item->price * (int) $item->quantity, 2)]);
        }

        $orders = DB::table('orders')->select('id', 'vat')->get();

        foreach ($orders as $order) {
            $orderVat = round((float) $order->vat, 2);

            if ($orderVat <= 0) {
                continue;
            }

            $orderItems = DB::table('order_items')
                ->where('order_id', $order->id)
                ->orderBy('id')
                ->get(['id', 'net_total']);

            $netSum = (float) $orderItems->sum('net_total');

            if ($netSum <= 0) {
                continue;
            }

            $allocated = 0.0;
            $lastIndex = $orderItems->count() - 1;

            foreach ($orderItems as $index => $orderItem) {
                $tax = $index === $lastIndex
                    ? round($orderVat - $allocated, 2)
                    : round($orderVat * ((float) $orderItem->net_total / $netSum), 2);

                $allocated = round($allocated + $tax, 2);

                DB::table('order_items')
                    ->where('id', $orderItem->id)
                    ->update(['tax_amount' => max(0, $tax)]);
            }
        }
    }
};
