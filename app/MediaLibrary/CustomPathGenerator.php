<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use App\Models\IdentityDocument; // Importamos el modelo IdentityDocument
use App\Models\User; // Importamos el modelo User

class CustomPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root of the media disk.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root of the media disk.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root of the media disk.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /*
     * Get the base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        // La ruta deseada es: users/{user_id}/identity_documents/{document_type_code}/{media_id}
        $model = $media->model;

        if ($model instanceof IdentityDocument) {
            $userId = $model->user_id;
            $documentTypeCode = $model->documentType->code ?? 'unknown'; // Usamos el código del tipo de documento
            $mediaId = $media->id; // El ID de la media se genera después de guardar, así que lo usaremos aquí

            return "users/{$userId}/identity_documents/{$documentTypeCode}/{$mediaId}";
        }

        // Fallback para otros modelos si se usa este generador para más cosas
        return 'other_media/'.$media->id;
    }
}
