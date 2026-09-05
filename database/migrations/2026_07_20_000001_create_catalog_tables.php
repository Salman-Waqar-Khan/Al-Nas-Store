<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->timestamps(); });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name'); $table->string('slug')->unique(); $table->string('size')->nullable(); $table->string('notes')->nullable();
            $table->text('description')->nullable(); $table->unsignedInteger('price'); $table->unsignedInteger('old_price')->nullable();
            $table->unsignedInteger('stock')->default(0); $table->string('image', 1000)->nullable(); $table->boolean('featured')->default(false); $table->boolean('active')->default(true); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); Schema::dropIfExists('categories'); }
};
