@extends('emails.layout')

@section('title', 'Código de Verificación')

@section('content')
    <h2 style="margin-top: 0; color: #1e293b;">¡Hola!</h2>
    <p>Gracias por iniciar tu registro con nosotros. Para continuar y verificar que este correo te pertenece, por favor ingresa el siguiente código de seguridad en la aplicación:</p>

    <div class="otp-box">
        {{ $otpCode }}
    </div>

    <p><em>Este código es válido por los próximos 15 minutos.</em></p>
    <p>Si no has solicitado este código o no estás intentando registrarte, puedes ignorar y eliminar este correo de forma segura.</p>
@endsection
