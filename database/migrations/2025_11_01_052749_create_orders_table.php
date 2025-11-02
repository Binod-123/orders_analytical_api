<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('customer_id')->constrained()->onDelete('cascade');
      $table->foreignId('product_id')->constrained()->onDelete('cascade');
      $table->integer('quantity');
      $table->decimal('total_price', 10, 2);
      $table->date('order_date');
      $table->timestamps();
    });
  }
  /**
   * Reverse the migrations.
   */
  public function down() : void {
    Schema::dropIfExists('orders');
  }
};
