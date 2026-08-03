<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Contratos')</title>
<link rel="stylesheet" href="/css/app.css">
</head>
<body>
<header class="topbar">
<a href="{{ route('documents.index') }}" class="brand">Gestor de contratos</a>
<form method="POST" action="{{ route('logout') }}" class="topbar-user">
@csrf
<span>{{ auth()->user()->name }}</span>
<button type="submit" class="link">Salir</button>
</form>
</header>
<main class="wrap">
@if (session('success'))
<p class="alert alert-ok">{{ session('success') }}</p>
@endif
@if (session('error'))
<p class="alert alert-error">{{ session('error') }}</p>
@endif
@yield('content')
</main>
</body>
</html>
