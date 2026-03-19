<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Update the authenticated user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        Log::info('UserController::updateProfile called', ['user_id' => auth('api')->id(), 'data' => $request->all()]);

        $user = auth('api')->user();

        // Define los campos que son nullable y que deben ser null si se envían como cadena vacía o se omiten
        // Las claves aquí ya serán snake_case debido al middleware
        $fieldsToProcessAsNullable = [
            'paternal_surname',
            'maternal_surname',
            'phone',
            'curp',
            'date_of_birth',
            'address',
            // 'photo_path' si se actualizara aquí
        ];

        $request->validate([
            'first_name' => 'nullable|string|max:255', // first_name es requerido en el registro, pero opcional en la actualización
            'paternal_surname' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'curp' => [
                'nullable',
                'string',
                'max:18',
                Rule::unique('users', 'curp')->ignore($user->id),
            ],
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);

        $dataToUpdate = [];

        // Procesar first_name: si está presente, se actualiza; si se envía vacío, se hace null.
        // Si se omite, no se toca (comportamiento por defecto de fill() si no está en $dataToUpdate)
        if ($request->has('first_name')) {
            $dataToUpdate['first_name'] = $request->input('first_name') === '' ? null : $request->input('first_name');
        }

        // Procesar los demás campos nullable: si están presentes (vacíos o con valor) o se omiten
        foreach ($fieldsToProcessAsNullable as $field) {
            if ($request->has($field)) {
                // Si el campo está presente en la petición, lo actualizamos.
                // Si el valor es una cadena vacía, lo convertimos a null.
                $dataToUpdate[$field] = $request->input($field) === '' ? null : $request->input($field);
            } else {
                // Si el campo NO está presente en la petición (se omitió), lo establecemos a null.
                $dataToUpdate[$field] = null;
            }
        }

        $user->fill($dataToUpdate);
        $user->save();

        Log::info('User profile updated successfully', ['user_id' => $user->id]);

        return $this->success([], 'Perfil de usuario actualizado exitosamente.');
    }
}
