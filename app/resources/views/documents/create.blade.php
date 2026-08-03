@extends('layouts.app')

@section('title', 'Registrar contrato')

@section('content')
    <h1>Registrar contrato</h1>

    @if ($errors->any())
        <ul class="alert alert-error">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('documents.store') }}"
          enctype="multipart/form-data" class="card" id="doc-form">
        @csrf

        <label for="name">Nombre del contrato</label>
        <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name') }}">

        <label for="pdf_file">Contrato en PDF <span class="hint">(maximo 10 MB)</span></label>
        <input id="pdf_file" name="pdf_file" type="file" accept="application/pdf,.pdf" required>

        <label for="watermark_image">Imagen de marca de agua
            <span class="hint">(PNG o JPG, maximo 2 MB)</span></label>
        <input id="watermark_image" name="watermark_image" type="file"
               accept="image/png,image/jpeg,.png,.jpg,.jpeg" required>

        <p class="alert alert-error" id="js-errors" hidden></p>

        <button type="submit">Procesar y guardar</button>
    </form>

    <p><a href="{{ route('documents.index') }}">Volver al listado</a></p>

    <script>
        document.getElementById('doc-form').addEventListener('submit', function (event) {
            const errors = [];
            const pdf = document.getElementById('pdf_file').files[0];
            const image = document.getElementById('watermark_image').files[0];

            if (pdf && pdf.type !== 'application/pdf') errors.push('El contrato debe ser un PDF.');
            if (pdf && pdf.size > 10 * 1024 * 1024) errors.push('El PDF supera los 10 MB.');
            if (image && !['image/png', 'image/jpeg'].includes(image.type)) errors.push('La marca debe ser PNG o JPG.');
            if (image && image.size > 2 * 1024 * 1024) errors.push('La imagen supera los 2 MB.');

            const box = document.getElementById('js-errors');

            if (errors.length) {
                event.preventDefault();
                box.textContent = errors.join(' ');
                box.hidden = false;
                return;
            }

            // setTimeout: deshabilitar el boton dentro del propio evento submit
            // cancela el envio en algunos navegadores.
            const button = this.querySelector('button[type=submit]');
            setTimeout(() => {
                button.disabled = true;
                button.textContent = 'Procesando...';
            }, 0);
        });
    </script>
@endsection
