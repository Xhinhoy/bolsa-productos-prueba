<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="centered">
    <main class="card">
        <h1>Gestor de contratos</h1>

        @if ($errors->any())
            <p class="alert alert-error">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username">

            <label for="password">Contrasena</label>
            <input id="password" name="password" type="password"
                   required autocomplete="current-password">

            <label class="inline">
                <input type="checkbox" name="remember" value="1"> Recordarme
            </label>

            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
