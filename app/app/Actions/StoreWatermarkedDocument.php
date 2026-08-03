<?php

namespace App\Actions;

use App\Models\Document;
use App\Models\User;
use App\Services\WatermarkClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class StoreWatermarkedDocument
{
    public function __construct(private readonly WatermarkClient $watermark)
    {
    }

    /**
     * @throws \App\Services\WatermarkException
     */
    public function handle(User $user, string $name, UploadedFile $pdf, UploadedFile $image): Document
    {
        // Fuera de toda transaccion: mantenerla abierta durante una llamada de
        // hasta 60s bloquearia filas en Postgres ese minuto entero.
        $bytes = $this->watermark->apply($pdf, $image);

        // Nombre generado, nunca el del usuario: path traversal, colisiones,
        // unicode y limites de longitud del filesystem.
        $path = "documents/{$user->id}/".Str::ulid().'.pdf';
        Storage::disk('local')->put($path, $bytes);

        try {
            return $user->documents()->create([
                'name' => $name,
                'original_filename' => $pdf->getClientOriginalName(),
                'stored_path' => $path,
                'original_size_bytes' => $pdf->getSize(),
                'stored_size_bytes' => strlen($bytes),
                'checksum_sha256' => hash('sha256', $bytes),
            ]);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);

            throw $e;
        }
    }
}
