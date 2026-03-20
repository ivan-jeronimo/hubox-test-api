<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\DB; // Necesario para transacciones

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
             ->useDisk('s3'); // Eliminado ->singleFile() para permitir múltiples archivos
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

    /**
     * Creates or updates an IdentityDocument for a user, handling INE document replacement.
     *
     * @param int $userId
     * @param int $documentTypeId
     * @param string|null $documentNumber
     * @param array $uploadedFiles Array of uploaded files (e.g., from SpatieMediaLibraryFileUpload)
     * @return IdentityDocument
     * @throws \Exception
     */
    public static function upsertForUser(int $userId, int $documentTypeId, ?string $documentNumber, array $uploadedFiles): IdentityDocument
    {
        return DB::transaction(function () use ($userId, $documentTypeId, $documentNumber, $uploadedFiles) {
            $ineFrontTypeId = DocumentType::where('code', 'INE_FRONT')->value('id');
            $ineBackTypeId = DocumentType::where('code', 'INE_BACK')->value('id');

            $isIneDocument = in_array($documentTypeId, [$ineFrontTypeId, $ineBackTypeId]);

            $identityDocument = null;

            if ($isIneDocument) {
                // Check if an existing INE document of the same type exists for the user
                $identityDocument = static::where('user_id', $userId)
                                          ->where('document_type_id', $documentTypeId)
                                          ->first();
            }

            if ($identityDocument) {
                // Update existing document
                // First, clear existing media
                $identityDocument->clearMediaCollection('identity_documents');

                $identityDocument->update([
                    'document_number' => $documentNumber,
                    'status' => 'pending', // Reset status on update
                    'approved_at' => null,
                ]);
            } else {
                // Create new document
                $identityDocument = static::create([
                    'user_id' => $userId,
                    'document_type_id' => $documentTypeId,
                    'document_number' => $documentNumber,
                    'status' => 'pending',
                    'approved_at' => null,
                ]);
            }

            // Attach new media files
            foreach ($uploadedFiles as $file) {
                $identityDocument->addMedia($file)->toMediaCollection('identity_documents');
            }

            return $identityDocument;
        });
    }
}
