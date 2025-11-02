<?php
namespace Database\Factories;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory {
  protected $model = Order::class;

  public function definition() {
    $product  = Product::inRandomOrder()->first();
    $quantity = $this->faker->numberBetween(1, 10);

    return [
      'customer_id' => Customer::inRandomOrder()->first()->id,
      'product_id'  => $product->id,
      'quantity'    => $quantity,
      'total_price' => $product->price * $quantity,
      'order_date'  => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
    ];
  }
}
