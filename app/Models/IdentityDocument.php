<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IdentityDocument extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'document_type_id',
        'document_number',
        'status',
        'approved_at', // Añadido para registrar la fecha de aprobación
    ];

    protected $casts = [
        'approved_at' => 'datetime', // Castear a tipo datetime
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Define media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('identity_documents')
             ->singleFile(); // Opcional: si solo se permite un archivo por documento de identidad
    }

    /**
     * Define media conversions (optional, for image manipulation).
     */
    public function registerMediaConversions(Media $media = null): void
    {
        // $this->addMediaConversion('thumb')
        //     ->width(100)
        //     ->height(100);
    }
}
