<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->after('id');
            $table->timestamp('stock_deducted_at')->nullable()->after('status');
            $table->timestamp('stock_restored_at')->nullable()->after('stock_deducted_at');
        });

        DB::table('orders')->select('id')->orderBy('id')->each(function ($order) {
            DB::table('orders')->where('id', $order->id)->update(['public_id' => (string) Str::uuid()]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('public_id')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['public_id', 'stock_deducted_at', 'stock_restored_at']));
    }
};
