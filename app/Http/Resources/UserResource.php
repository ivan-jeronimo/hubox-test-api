<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            // 'middleName' => $this->middle_name, // Eliminado
            'paternalSurname' => $this->paternal_surname,
            'maternalSurname' => $this->maternal_surname,
            'fullName' => $this->full_name, // Usamos el accesor del modelo
            'email' => $this->email,
            'phone' => $this->phone,
            'curp' => $this->curp,
            'dateOfBirth' => $this->date_of_birth,
            'address' => $this->address,
            'photoPath' => $this->photo_path,
            'emailVerifiedAt' => $this->email_verified_at,
            'phoneVerifiedAt' => $this->phone_verified_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'isAdmin' => (bool) $this->is_admin, // Añadido para el contexto de administración
            // 'documents' => IdentityDocumentResource::collection($this->whenLoaded('identityDocuments')), // Si cargas los documentos de identidad
        ];
    }
}
