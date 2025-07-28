<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function index()
    {
        return Storage::disk('public')->allFiles();
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
