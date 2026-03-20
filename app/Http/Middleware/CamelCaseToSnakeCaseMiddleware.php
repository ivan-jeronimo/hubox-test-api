<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CamelCaseToSnakeCaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si la petición es JSON, procesamos el cuerpo JSON
        if ($request->isJson()) {
            $input = $request->json()->all();
            $transformedInput = $this->transformKeysToSnakeCase($input);
            $request->json()->replace($transformedInput);
        }
        // Si no es JSON (ej. form-data), procesamos todos los inputs del request
        else {
            $input = $request->all();
            $transformedInput = $this->transformKeysToSnakeCase($input);
            $request->replace($transformedInput); // Usamos replace para actualizar todos los inputs
        }

        return $next($request);
    }

    /**
     * Recursively transform array keys from camelCase to snake_case.
     *
     * @param array $array
     * @return array
     */
    protected function transformKeysToSnakeCase(array $array): array
    {
        $transformedArray = [];
        foreach ($array as $key => $value) {
            $snakeCaseKey = Str::snake($key);
            if (is_array($value)) {
                $transformedArray[$snakeCaseKey] = $this->transformKeysToSnakeCase($value);
            } else {
                $transformedArray[$snakeCaseKey] = $value;
            }
        }
        return $transformedArray;
    }
}
