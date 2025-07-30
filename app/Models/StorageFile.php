<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate

use Illuminate\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n\nclass StorageFile extends Model\n{\n    use HasFactory;\n}\nuse Illuminate\Support\Facades\Storage;\nuse Illuminate\Database\Eloquent\SoftDeletes;\n\nclass StorageFile extends Model\n{\n    use HasFactory, SoftDeletes;\n\n    protected $fillable = [\n        'filename',\n        'original_name',\n        'mime_type',\n        'size',\n        'user_id',\n    ];\n\n    public function user()\n    {\n        return $this->belongsTo(User::class);\n    }\n\n    public function getFileUrlAttribute()\n    {\n        return Storage::url($this->filename);\n    }
