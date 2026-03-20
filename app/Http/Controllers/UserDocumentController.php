<?php

namespace App\Http\Controllers;

use App\Models\IdentityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Http\Resources\IdentityDocumentResource; // Importamos el IdentityDocumentResource

class UserDocumentController extends Controller
{
    /**
     * Display a listing of the user's identity documents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        Log::info('UserDocumentController::index called', ['user_id' => auth('api')->id()]);

        $user = auth('api')->user();

        // Cargar los documentos de identidad del usuario, incluyendo la relación documentType
        $identityDocuments = $user->identityDocuments()->with('documentType')->get();

        Log::debug('Fetched user identity documents', ['user_id' => $user->id, 'count' => $identityDocuments->count()]);

        // Usar IdentityDocumentResource::collection para transformar la colección
        return $this->success(
            IdentityDocumentResource::collection($identityDocuments),
            'Documentos de identidad del usuario obtenidos exitosamente.'
        );
    }

    /**
     * Store a newly created or updated identity document in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::info('UserDocumentController::store called', ['user_id' => auth('api')->id(), 'data' => $request->all()]);

        $user = auth('api')->user();

        // Las reglas de validación SIEMPRE deben usar snake_case,
        // ya que el middleware CamelCaseToSnakeCaseMiddleware transforma el input antes de la validación.
        $request->validate([
            'document_type_id' => [
                'required',
                'exists:document_types,id',
            ],
            'document_number' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360', // Max 15MB (15 * 1024)
        ]);

        try {
            // Obtener el archivo subido
            $uploadedFile = $request->file('file');

            // Usar el método upsertForUser para crear o actualizar el documento
            $identityDocument = IdentityDocument::upsertForUser(
                $user->id,
                $request->document_type_id,
                $request->document_number,
                [$uploadedFile] // El método espera un array de archivos
            );

            Log::info('Identity document upserted and registered successfully via API', ['document_id' => $identityDocument->id, 'user_id' => $user->id]);

            return $this->success([], 'Documento subido exitosamente para revisión.');

        } catch (\Exception $e) {
            Log::error('Error upserting identity document via API: ' . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            return $this->error('Ocurrió un error inesperado al procesar el documento.', 500);
        }
    }
}
