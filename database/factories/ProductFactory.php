<?php
namespace Database\Factories;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory {
    protected $model = Product::class;

    public function definition()
    {
        $brands = ['Apple', 'Samsung', 'Sony', 'Dell', 'Asus', 'Lenovo', 'HP', 'Xiaomi', 'Logitech', 'OnePlus', 'Infinix', 'Realme'];
        $items  = ['Phone', 'Laptop', 'Headphones', 'Tablet', 'Monitor', 'Keyboard', 'Mouse', 'Camera', 'Watch', 'Router'];
        $model  = strtoupper($this->faker->bothify('??-###'));
        $brand  = $this->faker->randomElement($brands);
        return [
            'brand' => $brand,
            'name'  => $brand . ' ' . $this->faker->randomElement($items) . ' ' . $model,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(10, 1000),
        ];
    }
}