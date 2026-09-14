<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#a42643"><title>@yield('title', 'Inicio') · Quick Wash</title><link rel="icon" href="{{ asset('images/univalle.png') }}"><link rel="stylesheet" href="{{ asset('css/app.css') }}"><script src="{{ asset('js/app.js') }}" defer></script></head>
<body>
<a class="skip-link" href="#main">Ir al contenido</a>
<aside class="sidebar">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><x-icon /></span><span>quick<span class="brand-light">wash</span><small>LAVANDERÍA UNIVERSITARIA</small></span></a>
    <div class="nav-label">MI ESPACIO</div>
    <nav aria-label="Navegación principal">
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'selected' : '' }}" href="{{ route('dashboard') }}"><x-icon name="home"/> Inicio</a>
        @unless(auth()->user()->isStaff())<a class="nav-item {{ request()->routeIs('reservations.create') ? 'selected' : '' }}" href="{{ route('reservations.create') }}"><x-icon name="washer"/> Reservar máquina</a>@endunless
        <a class="nav-item {{ request()->routeIs('reservations.index') ? 'selected' : '' }}" href="{{ route('reservations.index') }}"><x-icon name="calendar"/> {{ auth()->user()->isStaff() ? 'Todas las reservas' : 'Mis reservas' }}</a>
        @if(auth()->user()->isStaff())<a class="nav-item {{ request()->routeIs('machines.*') ? 'selected' : '' }}" href="{{ route('machines.index') }}"><x-icon/> Gestionar máquinas</a>@endif
    </nav>
    <div class="sidebar-bottom"><div class="campus-card"><img src="{{ asset('images/univalle.png') }}" alt="Logo de la Universidad del Valle Bolivia"><div>Universidad del Valle<small>Comunidad Univalle</small></div></div><div class="sidebar-caption">Tu tiempo vale. Nosotros lo cuidamos.</div></div>
</aside>
<div class="app-shell">
    <header class="topbar"><div class="breadcrumb">Mi espacio <span>/</span> <strong>@yield('title', 'Inicio')</strong></div><div class="profile"><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div>{{ auth()->user()->name }}<small>{{ auth()->user()->isStaff() ? 'Personal de lavandería' : 'Estudiante' }}</small></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-button" aria-label="Cerrar sesión" title="Cerrar sesión"><x-icon name="logout"/></button></form></div></header>
    <main id="main" class="main">
        @include('partials.alerts')
        @yield('content')
        <footer class="page-footer"><span>© {{ date('Y') }} Quick Wash · Universidad del Valle</span><span>Hecho para facilitar tu día.</span></footer>
    </main>
</div>
<dialog id="confirm-dialog"><form method="dialog"><span class="dialog-icon"><x-icon name="info"/></span><h2>Confirma la acción</h2><p id="confirm-text"></p><div class="form-actions"><button class="button secondary" value="cancel">Volver</button><button class="button" value="confirm">Sí, continuar</button></div></form></dialog>
</body></html>
