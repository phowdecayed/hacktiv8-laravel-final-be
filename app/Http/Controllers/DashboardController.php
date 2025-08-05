<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalTransactions = Transaction::count();
        $totalSales = Transaction::sum('total_amount');

        $recentTransactions = Transaction::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $topSellingProducts = Product::select('products.name', DB::raw('SUM(transaction_items.quantity) as total_quantity_sold'))
            ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity_sold')
            ->limit(5)
            ->get();

        $lowStockItems = Product::where('stock', '<', 10) // Assuming 'low stock' is defined as less than 10
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        $recentUserRegistrations = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        return response()->json([
            'message' => 'Dashboard statistics retrieved successfully',
            'data' => [
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_transactions' => $totalTransactions,
                'total_sales' => $totalSales,
                'recent_transactions' => $recentTransactions,
                'top_selling_products' => $topSellingProducts,
                'low_stock_items' => $lowStockItems,
                'recent_user_registrations' => $recentUserRegistrations,
            ],
        ], 200);
    }

    public function getSalesOverview(Request $request)
    {
        $totalSales = Transaction::sum('total_amount');

        $driver = DB::connection()->getDriverName();
        $monthExpression = "strftime('%Y-%m', created_at)"; // Fallback for unknown drivers

        if ($driver === 'mysql') {
            $monthExpression = "DATE_FORMAT(created_at, '%Y-%m')";
        } elseif ($driver === 'pgsql') {
            $monthExpression = "to_char(created_at, 'YYYY-MM')";
        }

        $salesByMonth = Transaction::select(
            DB::raw("$monthExpression as month"),
            DB::raw('SUM(total_amount) as total_sales')
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $salesByCategory = TransactionItem::select(
            'categories.name as category_name',
            DB::raw('SUM(transaction_items.total) as total_sales')
        )
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->orderByDesc('total_sales')
            ->get();

        return response()->json([
            'message' => 'Sales overview retrieved successfully',
            'data' => [
                'total_sales' => $totalSales,
                'sales_by_month' => $salesByMonth,
                'sales_by_category' => $salesByCategory,
            ],
        ], 200);
    }
}
