@extends('emails.layout')

@section('title', 'Documento Rechazado')

@section('content')
    <h2 style="margin-top: 0; color: #1e293b;">¡Hola, {{ $userName ?? 'usuario' }}!</h2>
    <p>Queremos informarte que hemos revisado tu documento <strong>{{ $documentName }}</strong> y, lamentablemente, <strong>no pudo ser aprobado</strong> en esta ocasión.</p>

    <p>Por favor, ingresa a la aplicación para revisar los detalles y vuelve a subir el documento asegurándote de que cumpla con los requisitos necesarios.</p>

    <p style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
        <a href="{{ env('APP_FRONTEND_URL', config('app.url')) }}" style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Ir a la aplicación</a>
    </p>

    <p>Si necesitas asistencia, nuestro equipo de soporte está listo para ayudarte.</p>
@endsection
