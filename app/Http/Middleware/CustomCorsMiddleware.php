<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomCorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        $allowedOrigins = config('cors.allowed_origins');

        // Determinar si el origen de la petición está permitido
        $isOriginAllowed = false;
        if (in_array('*', $allowedOrigins)) {
            $isOriginAllowed = true;
        } elseif ($origin && in_array($origin, $allowedOrigins)) {
            $isOriginAllowed = true;
        }

        // Si la petición es OPTIONS (preflight), la manejamos directamente
        if ($request->isMethod('OPTIONS')) {
            if ($isOriginAllowed) {
                // Aplicar encabezados CORS para la respuesta OPTIONS
                $response = new Response();
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Methods', implode(', ', config('cors.allowed_methods')));
                $response->headers->set('Access-Control-Allow-Headers', implode(', ', config('cors.allowed_headers')));
                $response->headers->set('Access-Control-Allow-Credentials', config('cors.supports_credentials') ? 'true' : 'false');
                $response->headers->set('Access-Control-Max-Age', config('cors.max_age'));
                return $response->setStatusCode(200); // Responder 200 OK para preflight
            } else {
                // Si el origen no está permitido, responder con 403 Forbidden para OPTIONS
                return response('Forbidden', 403)->header('Access-Control-Allow-Origin', $origin);
            }
        }

        // Para peticiones que no son OPTIONS, procesamos la petición y luego añadimos los encabezados
        $response = $next($request);

        if ($isOriginAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', config('cors.allowed_methods')));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', config('cors.allowed_headers')));
            $response->headers->set('Access-Control-Allow-Credentials', config('cors.supports_credentials') ? 'true' : 'false');
            $response->headers->set('Access-Control-Max-Age', config('cors.max_age'));
        }

        return $response;
    }
}
