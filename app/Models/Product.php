<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model {
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'brand',
    'name',
    'price',
    'stock',
  ];

  /**
   * Relationship: A product can have many orders.
   */
  public function orders() : HasMany {
    return $this->hasMany(Order::class, 'product_id', 'id');
  }

  /**
   * Accessor: Calculate total revenue generated from this product.
   */
  public function getTotalRevenueAttribute() {
    return $this->orders()->sum('total_price');
  }

  /**
   * Accessor: Calculate total quantity sold.
   */
  public function getTotalQuantitySoldAttribute() {
    return $this->orders()->sum('quantity');
  }

  /**
   * Scope: Filter low-stock products.
   */
  public function scopeLowStock($query, $threshold = 10) {
    return $query->where('stock', '<', $threshold);
  }

  /**
   * Scope: Filter by brand name.
   */
  public function scopeBrand($query, $brand) {
    return $query->where('brand', $brand);
  }
}
