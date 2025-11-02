<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
class DataSeeder extends Seeder {
  /**
   * Run the database seeds.
   */
  public function run() : void {
    Product::factory(50)->create();
    Customer::factory(100)->create();

    // Ensure products & customers exist before creating orders
    if (Product::count() && Customer::count()) {
      Order::factory(500)->create();
    }
  }
}
