<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DocumentTypeResource; // Importamos el DocumentTypeResource

class IdentityDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Obtener el primer archivo asociado a este IdentityDocument
        // Asumimos que solo hay un archivo por IdentityDocument debido a ->singleFile() en el modelo
        $media = $this->getFirstMedia('identity_documents');

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'documentTypeId' => $this->document_type_id,
            'documentType' => new DocumentTypeResource($this->whenLoaded('documentType')), // Cargar el recurso del tipo de documento
            'documentNumber' => $this->document_number,
            'status' => $this->status,
            'fileUrl' => $media ? $media->getUrl() : null, // Genera la URL del archivo en S3
            'fileName' => $media ? $media->file_name : null,
            'mimeType' => $media ? $media->mime_type : null,
            'size' => $media ? $media->size : null,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
