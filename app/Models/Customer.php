<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Customer extends Model {
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'name',
    'email',
  ];

  /**
   * Define relationship: A customer can have many orders.
   */
  public function orders() : HasMany {
    return $this->hasMany(Order::class, 'customer_id', 'id');
  }

  /**
   * Optional: Accessor to get total spent by this customer.
   */
  public function getTotalSpentAttribute() {
    return $this->orders()->sum('total_price');
  }

  /**
   * Optional: Accessor to get last order date.
   */
  public function getLastOrderDateAttribute() {
    return $this->orders()->max('order_date');
  }
}
