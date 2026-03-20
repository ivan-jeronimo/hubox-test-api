@extends('emails.layout')

@section('title', 'Documento Aprobado')

@section('content')
    <h2 style="margin-top: 0; color: #1e293b;">¡Felicidades, {{ $userName ?? 'usuario' }}!</h2>
    <p>Queremos informarte que tu documento <strong>{{ $documentName }}</strong> ha sido revisado y <strong>aprobado</strong> exitosamente.</p>

    <p>¡Gracias por confiar en nosotros y seguir completando tu perfil!</p>

    <p>Si tienes alguna duda, no dudes en contactarnos.</p>
@endsection
