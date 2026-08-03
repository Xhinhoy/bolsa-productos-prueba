<?php

namespace App\Http\Controllers;

use App\Actions\StoreWatermarkedDocument;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Services\WatermarkException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): View
    {
        // Filtrado por relacion ademas de la policy: dos capas independientes.
        return view('documents.index', [
            'documents' => auth()->user()->documents()->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('documents.create');
    }

    public function store(StoreDocumentRequest $request, StoreWatermarkedDocument $action): RedirectResponse
    {
        try {
            $action->handle(
                $request->user(),
                $request->string('name')->toString(),
                $request->file('pdf_file'),
                $request->file('watermark_image'),
            );
        } catch (WatermarkException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('documents.index')
            ->with('success', 'Documento registrado correctamente.');
    }

    public function download(Document $document): StreamedResponse
    {
        Gate::authorize('download', $document);

        return Storage::disk('local')->download(
            $document->stored_path,
            $document->original_filename,
        );
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        // Archivo primero: si falla, la fila sobrevive y se puede reintentar.
        // Al reves quedaria un archivo huerfano invisible.
        Storage::disk('local')->delete($document->stored_path);
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Documento eliminado.');
    }
}
