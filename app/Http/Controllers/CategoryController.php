<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource with query parameters.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Query builder dengan eager loading
        $query = Category::with('user');

        // Include relasi produk jika diminta
        if ($request->has('with_products') && $request->with_products === 'true') {
            $query->with(['products']);
        }

        // Pencarian berdasarkan nama kategori
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'asc');
        
        // Validasi kolom yang bisa di-sort
        $allowedSorts = ['name', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $limit = $request->get('limit', 15);
        $limit = min($limit, 100); // Maksimal 100 item per halaman

        // Filter berdasarkan tanggal
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $categories = $query->paginate($limit);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        // Catat aktivitas create
        AuditTrailService::logCreate($category, 'Category', $request);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::with('user')->find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $oldValues = $category->toArray();

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->id(),
        ]);

        // Catat aktivitas update
        AuditTrailService::logUpdate($category, 'Category', $oldValues, $category->toArray(), $request);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $oldValues = $category->toArray();

        $category->delete();

        // Catat aktivitas delete
        AuditTrailService::logDelete($category, 'Category', $oldValues, request());

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
