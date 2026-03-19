<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse; // Importamos el Trait
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse; // Usamos el Trait
}
