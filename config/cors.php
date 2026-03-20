<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines which domains are allowed to access your
    | application's resources from a different domain. You may adjust
    | these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*'],

    'allowed_methods' => ['*'],

    // Modificado: Ahora permite explícitamente el origen del frontend de DigitalOcean y localhost para desarrollo
    'allowed_origins' => [
        'https://hubox-frontend-3j8p5.ondigitalocean.app',
        'http://localhost:5173', // Añadido para desarrollo local
        // Puedes añadir otros orígenes aquí si es necesario
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
