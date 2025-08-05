<?php

namespace App\Http\Controllers;

use App\Models\StorageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function index(Request $request)
    {
        $query = StorageFile::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $type = $request->type;
            $query->where('mime_type', 'like', "%{$type}%");
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'asc');

        $allowedSorts = ['filename', 'size', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'desc' ? 'desc' : 'asc');
        }

        $limit = $request->get('limit', 15);
        $limit = min($limit, 100);

        $files = $query->paginate($limit);

        $files->getCollection()->transform(function ($file) {
            $file->file_url = Storage::url($file->filename);

            return $file;
        });

        return response()->json([
            'message' => 'Files retrieved successfully',
            'data' => $files,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,txt,csv,json,xml',
                'max:20480', // 20MB in kilobytes
            ],
            'folder' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $userId = Auth::id();

        $path = $file->store('public/user_'.$userId.'/'.($request->folder ?? ''), 'local');
        $filename = str_replace('public/', '', $path);

        $storageFile = StorageFile::create([
            'filename' => $filename,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'user_id' => $userId,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => $storageFile,
        ], 201);
    }

    public function show($filename)
    {
        $file = StorageFile::where('filename', $filename)->firstOrFail();

        if ($file->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to access this file');
        }

        return Storage::disk('local')->download('public/'.$file->filename, $file->original_name);
    }

    public function destroy($filename)
    {
        $file = StorageFile::where('filename', $filename)->firstOrFail();

        if ($file->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to delete this file');
        }

        $file->delete(); // Soft delete

        return response()->json(['message' => 'File deleted successfully'], 200);
    }
}
