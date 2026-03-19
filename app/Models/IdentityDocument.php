<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IdentityDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_type_id',
        'document_number',
        'file_path',
        'disk',
        'mime_type',
        'size',
        'status',
    ];

    /**
     * Get the full URL of the document using the appropriate disk (e.g., S3).
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
