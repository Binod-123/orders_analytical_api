<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SalesReportServiceProvider extends ServiceProvider {
  public function register() : void {
    $this->app->singleton('salesreport', function () {
      return new class {

        public function generate(string $type = 'full', array $filters = []) : array {
          try {
            // Apply filters only for the correct type
            $query = Order::query();
            if ($type === 'product') {
              $query = $this->applyProductFilters($query, $filters);
            } elseif ($type === 'user') {
              $query = $this->applyUserFilters($query, $filters);
            }

            return match ($type) {
              'user'    => $this->userSalesReport($query),
              'product' => $this->productSalesReport($query),
              default   => $this->fullSalesReport($query),
            };
          } catch (\Throwable $e) {
            Log::error('Sales report generation failed: ' . $e->getMessage());
            return [
              'status'  => 'error',
              'message' => 'Failed to generate sales report',
              'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ];
          }
        }

        /**
         * 🧾 Full summary (no date or brand filters)
         */
        private function fullSalesReport($query) : array {
          return [
            'status'  => 'success',
            'summary' => [
              'total_sales_count' => $this->getTotalSalesCount($query),
              'total_revenue'     => $this->getTotalRevenue($query),
              'top_products'      => $this->getTopProducts($query),
            ],
          ];
        }

        /**
         * 👤 User-wise report
         */
        private function userSalesReport($query) : array {
          $userSales = $query
            ->with('customer:id,name,email')
            ->get()
            ->groupBy('customer_id')
            ->map(fn($orders) => [
              'customer'     => $orders->first()->customer,
              'total_orders' => $orders->count(),
              'total_spent'  => $orders->sum('total_price'),
            ])
            ->values();

          return [
            'status'  => 'success',
            'summary' => $userSales,
          ];
        }

        /**
         * 📦 Product-wise report (brand/date filters apply here)
         */
        private function productSalesReport($query) : array {
          return [
            'status'  => 'success',
            'summary' => $this->getTopProducts($query),
          ];
        }

        /**
         * 🧰 Product filters (brand/date only)
         */
        private function applyProductFilters($query, array $filters) {
          if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('order_date', [
              $filters['start_date'], $filters['end_date']
            ]);
          }

          if (!empty($filters['brand'])) {
            $query->whereHas('product', function ($q) use ($filters) {
              $q->where('brand', 'LIKE', '%' . $filters['brand'] . '%');
            });
          }

          if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
          }

          return $query;
        }

        /**
         * 🧰 User filters (only by customer_id)
         */
        private function applyUserFilters($query, array $filters) {
          if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
          } elseif (!empty($filters['customer_name'])) {
            $query->whereHas('customer', fn($q) =>
              $q->where('name', 'like', "%{$filters['customer_name']}%")
            );
          }

          return $query;
        }

        private function getTotalSalesCount($query) : int {
          return $query->count();
        }

        private function getTotalRevenue($query) : float {
          return (float)$query->sum('total_price');
        }

        private function getTopProducts($query) {
          return $query
            ->with('product:id,brand,name,price')
            ->get()
            ->groupBy('product_id')
            ->map(function ($orders) {
              $product = $orders->first()->product;
              return [
                'product_id'    => $product->id,
                'brand'         => $product->brand,
                'name'          => $product->name,
                'price'         => $product->price,
                'total_sold'    => $orders->sum('quantity'),
                'total_revenue' => $orders->sum('total_price'),
              ];
            })
            ->sortByDesc('total_sold')
            ->take(10)
            ->values();
        }
      };
    });
  }

  public function boot() : void {
  }
}
