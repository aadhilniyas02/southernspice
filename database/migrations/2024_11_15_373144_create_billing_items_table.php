<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('billing_systems')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('order_quantity'); // Quantity of this product in the bill
            $table->decimal('unit_price', 10, 2); // Price per unit of the product
            $table->decimal('total_price', 10, 2); // Total price (order_quantity * unit_price)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_items');
    }
};
