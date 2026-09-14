@extends('layouts.auth')
@section('title', 'Crear cuenta')
@section('content')
<h2>Tu próximo lavado, sin filas.</h2><p class="muted">Crea tu cuenta de estudiante para empezar a reservar.</p>
@include('partials.alerts')
<form method="POST" action="{{ route('register') }}" class="stack-form">@csrf
<label>Nombre completo<input name="name" value="{{ old('name') }}" maxlength="100" placeholder="Tu nombre y apellido" required autocomplete="name" autofocus></label>
<label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" maxlength="200" placeholder="tu.correo@ejemplo.com" required autocomplete="email"></label>
<label>Contraseña<input type="password" name="password" minlength="8" placeholder="Mínimo 8 caracteres" required autocomplete="new-password"></label>
<label>Confirmar contraseña<input type="password" name="password_confirmation" minlength="8" placeholder="Repite tu contraseña" required autocomplete="new-password"></label>
<button class="button full">Crear mi cuenta <x-icon name="arrow"/></button></form><p class="auth-switch">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
@endsection
