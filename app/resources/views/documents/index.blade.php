@extends('layouts.app')
@section('title', 'Mis contratos')
@section('content')
<div class="page-head">
<h1>Mis contratos</h1>
<a class="btn" href="{{ route('documents.create') }}">Registrar contrato</a>
</div>
@if ($documents->isEmpty())
<p class="empty">Aun no has registrado contratos. Comienza subiendo el primero.</p>
@else
<table>
<thead>
<tr>
<th>Nombre</th>
<th>Nombre del archivo</th>
<th>Tamano</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
@foreach ($documents as $document)
<tr>
<td>{{ $document->name }}</td>
<td>{{ $document->original_filename }}</td>
<td>{{ $document->human_size }}</td>
<td><span class="badge">{{ $document->status->label() }}</span></td>
<td class="actions">
<a href="{{ route('documents.download', $document) }}">Descargar</a>
<form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Eliminar este contrato? La accion no se puede deshacer.')">
@csrf
@method('DELETE')
<button type="submit" class="link danger">Eliminar</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
@if ($documents->hasPages())
<nav class="pager">
@if ($documents->onFirstPage())
<span class="off">Anterior</span>
@else
<a href="{{ $documents->previousPageUrl() }}">Anterior</a>
@endif
<span>Pagina {{ $documents->currentPage() }} de {{ $documents->lastPage() }}</span>
@if ($documents->hasMorePages())
<a href="{{ $documents->nextPageUrl() }}">Siguiente</a>
@else
<span class="off">Siguiente</span>
@endif
</nav>
@endif
@endif
@endsection
