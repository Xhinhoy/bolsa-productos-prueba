<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Inicio</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <p>Sesion iniciada como {{ auth()->user()->name }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Salir</button>
    </form>
</body>
</html>
