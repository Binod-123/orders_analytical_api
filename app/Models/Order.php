<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model {
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'customer_id',
    'product_id',
    'quantity',
    'total_price',
    'order_date',
  ];

  /**
   * Relationship: Each order belongs to a customer.
   */
  public function customer() : BelongsTo {
    return $this->belongsTo(Customer::class, 'customer_id', 'id');
  }

  /**
   * Relationship: Each order belongs to a product.
   */
  public function product() : BelongsTo {
    return $this->belongsTo(Product::class, 'product_id', 'id');
  }

  /**
   * Accessor: Compute unit price.
   */
  public function getUnitPriceAttribute() : float {
    return $this->quantity > 0 ? $this->total_price / $this->quantity : 0;
  }

  /**
   * Scope: Filter orders between two dates.
   */
  public function scopeBetweenDates($query, $start, $end) {
    return $query->whereBetween('order_date', [$start, $end]);
  }

  /**
   * Scope: Filter orders by product.
   */
  public function scopeForProduct($query, $productId) {
    return $query->where('product_id', $productId);
  }

  /**
   * Scope: Filter orders by customer.
   */
  public function scopeForCustomer($query, $customerId) {
    return $query->where('customer_id', $customerId);
  }
}
