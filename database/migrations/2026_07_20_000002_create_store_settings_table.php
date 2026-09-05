<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('fresh_label')->default('This week');
            $table->string('fresh_title')->default('Fresh arrivals');
            $table->string('fresh_image', 1000)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('store_settings'); }
};
