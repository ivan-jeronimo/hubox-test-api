<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\DocumentTypeResource; // Importamos el DocumentTypeResource

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the active document types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        Log::info('DocumentTypeController::index called');

        $documentTypes = DocumentType::where('is_active', true)->get();

        Log::debug('Fetched active document types', ['count' => $documentTypes->count()]);

        // Usamos DocumentTypeResource::collection para transformar una colección de modelos
        return $this->success(
            DocumentTypeResource::collection($documentTypes),
            'Tipos de documentos activos obtenidos exitosamente.'
        );
    }
}
