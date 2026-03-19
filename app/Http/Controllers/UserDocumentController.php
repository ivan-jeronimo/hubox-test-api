<?php

namespace App\Http\Controllers;

use App\Models\IdentityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserDocumentController extends Controller
{
    /**
     * Store a newly created identity document in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::info('UserDocumentController::store called', ['user_id' => auth('api')->id(), 'data' => $request->all()]);

        $user = auth('api')->user();

        $request->validate([
            'document_type_id' => [
                'required',
                'exists:document_types,id',
                // Opcional: Asegurar que el usuario no suba el mismo tipo de documento dos veces si no se permite
                // Rule::unique('identity_documents')->where(function ($query) use ($user) {
                //     return $query->where('user_id', $user->id);
                // })
            ],
            'document_number' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
        ]);

        try {
            $file = $request->file('file');
            $path = 'documents/users/' . $user->id;
            $fileName = uniqid('doc_') . '.' . $file->getClientOriginalExtension();

            // Subir a S3
            $filePath = Storage::disk('s3')->putFileAs($path, $file, $fileName, 'public');

            if (!$filePath) {
                Log::error('Failed to upload file to S3', ['user_id' => $user->id, 'file_name' => $fileName]);
                return $this->failed('Error al subir el archivo a S3.', 500);
            }

            $identityDocument = IdentityDocument::create([
                'user_id' => $user->id,
                'document_type_id' => $request->document_type_id,
                'document_number' => $request->document_number,
                'file_path' => $filePath,
                'disk' => 's3',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'status' => 'pending', // Estado inicial
            ]);

            Log::info('Identity document uploaded and registered successfully', ['document_id' => $identityDocument->id, 'user_id' => $user->id]);

            return $this->success([], 'Documento subido exitosamente para revisión.');

        } catch (\Exception $e) {
            Log::error('Error uploading identity document: ' . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            return $this->error('Ocurrió un error inesperado al procesar el documento.', 500);
        }
    }
}
