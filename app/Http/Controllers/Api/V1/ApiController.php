<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;


class ApiController extends Controller {
    // -------------------- PRODUCT SUMMARY --------------------
    public function all_Products(Request $request)
    {
        try {
            $validated = $request->validate([
                'brand'     => 'nullable|string',
                'min_price' => 'nullable|numeric',
                'max_price' => 'nullable|numeric',
                'min_stock' => 'nullable|integer',
                'max_stock' => 'nullable|integer',
            ]);

            $query = Product::query();

            if (!empty($validated['brand'])) {
                $query->where('brand', 'LIKE', '%' . $validated['brand'] . '%');
            }

            if (!empty($validated['min_price'])) {
                $query->where('price', '>=', $validated['min_price']);
            }

            if (!empty($validated['max_price'])) {
                $query->where('price', '<=', $validated['max_price']);
            }

            if (!empty($validated['min_stock'])) {
                $query->where('stock', '>=', $validated['min_stock']);
            }

            if (!empty($validated['max_stock'])) {
                $query->where('stock', '<=', $validated['max_stock']);
            }

            $totalProducts = $query->count();

            $distinctBrands = empty($validated['brand'])
                ? Product::select('brand')->distinct()->count('brand')
                : null;

            $productsByBrand = Product::select('brand', DB::raw('COUNT(*) as count'))
                ->when($validated['brand'] ?? NULL, fn($q, $brand) => $q->where('brand', 'LIKE', "%$brand%"))
                ->groupBy('brand')
                ->get();

            $lowStockProducts = Product::where('stock', '<', 10)
                ->when($validated['brand'] ?? NULL, fn($q, $brand) => $q->where('brand', 'LIKE', "%$brand%"))
                ->select('id', 'name', 'brand', 'stock')
                ->get();

            $responseData = [
                'filters_used'       => $validated,
                'total_products'     => $totalProducts,
                'products_by_brand'  => $productsByBrand,
                'low_stock_products' => $lowStockProducts,
            ];

            if ($distinctBrands !== null) {
                $responseData['distinct_brands'] = $distinctBrands;
            }

            return response()->json([
                'status'  => TRUE,
                'message' => 'Products summary fetched',
                'data'    => $responseData,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Product summary error: ' . $e->getMessage());

            return response()->json([
                'status'  => FALSE,
                'message' => 'Failed to fetch product summary',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }


    // -------------------- SEARCH ORDERS --------------------
    public function searchOrders(Request $request)
    {
        try {
            $validated = $request->validate([
                'search' => 'required|string|max:255',
            ]);

            $search = $validated['search'];

            $orders = Order::with(['product', 'customer'])
                ->where(function ($q) use ($search) {

                    $q->where('id', $search)
                        ->orWhere('customer_id', $search)
                        ->orWhere('product_id', $search);

                    $q->orWhereHas('product', function ($p) use ($search) {
                        $p->where('id', $search)
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    });

                    $q->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('id', $search)
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('order_date')
                ->get();

            return response()->json([
                'status'       => 'success',
                'filters_used' => ['search' => $search],
                'count'        => $orders->count(),
                'data'         => $orders
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Order search error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch orders',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }



    // -------------------- SALES SUMMARY (via SERVICE PROVIDER) --------------------
    public function salesSummary(Request $request)
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'brand', 'product_id', 'customer_id']);
            $type    = $request->query('type', 'full');
            $report  = app('salesreport')->generate($type, $filters);

            if (isset($report['status']) && $report['status'] === 'error') {
                return response()->json($report, 500);
            }

            return response()->json($report, 200);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in salesSummary: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Database error while generating report.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);

        } catch (\InvalidArgumentException $e) {
            Log::warning('Invalid argument in salesSummary: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid input or filter.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 400);

        } catch (\Throwable $e) {
            Log::error('Unexpected error in salesSummary: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Unexpected error occurred while generating report.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    // -------------------- RECENT ORDERS --------------------
    public function recentOrders()
    {
        try {
            $orders = Order::with(['product', 'customer'])
                ->orderBy('order_date', 'desc')
                ->take(10)
                ->get();
            if ($orders->isEmpty()) {
                return response()->json([
                    'status'        => 'success',
                    'message'       => 'No recent orders found',
                    'recent_orders' => []
                ], 200);
            }
            return response()->json([
                'status'        => 'success',
                'recent_orders' => $orders
            ]);
        } catch (\Throwable $e) {
            Log::error('Recent orders error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch recent orders',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }
}