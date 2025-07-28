<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
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
        $query = StorageFile::with('user');

        // Pencarian berdasarkan nama file atau original name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tipe file
        if ($request->has('type')) {
            $query->where('file_type', 'like', "%{$request->type}%");
        }

        // Filter berdasarkan user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'asc');
        
        // Validasi kolom yang bisa di-sort
        $allowedSorts = ['file_name', 'file_type', 'file_size', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $limit = $request->get('limit', 15);
        $limit = min($limit, 100); // Maksimal 100 item per halaman

        $files = $query->paginate($limit);

        // Transform URLs untuk file URLs
        $files->getCollection()->transform(function($file) {
            $file->file_url = Storage::url($file->file_path);
            return $file;
        });

        return response()->json([
            'message' => 'Files retrieved successfully',
            'data' => $files
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image',
        ]);

        $path = $request->file('file')->store('/', 'public');

        return response()->json([
            'path' => $path,
            'url' => Storage::url($path),
        ], 201);
    }

    public function show($filename)
    {
        return Storage::disk('public')->download($filename);
    }

    public function destroy($filename)
    {
        Storage::disk('public')->delete($filename);

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
