<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatermarkClient
{
    private const UNAVAILABLE = 'El servicio de procesamiento no esta disponible, intente nuevamente.';

    /**
     * @return string Bytes del PDF ya marcado.
     *
     * @throws WatermarkException
     */
    public function apply(UploadedFile $pdf, UploadedFile $image): string
    {
        $start = microtime(true);

        try {
            $response = Http::connectTimeout(5)
                ->timeout(config('services.watermark.timeout'))
                // Reintentar solo por conexion: repetir un 422 triplica la espera
                // de un fallo garantizado.
                ->retry(2, 250, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->attach('pdf_file', $pdf->get(), $pdf->getClientOriginalName())
                ->attach('watermark_image', $image->get(), $image->getClientOriginalName())
                ->post(config('services.watermark.url').'/watermark');
        } catch (ConnectionException $e) {
            $this->log('unreachable', $pdf, $start, null, $e->getMessage());
            throw new WatermarkException(self::UNAVAILABLE);
        }

        $this->log(
            $response->successful() ? 'ok' : 'rejected',
            $pdf,
            $start,
            $response->status(),
        );

        if ($response->successful()) {
            return $response->body();
        }

        // 4xx: el mensaje habla del archivo del usuario, se propaga.
        // 5xx: fallo nuestro, mensaje generico.
        throw new WatermarkException($response->clientError()
            ? ($response->json('message') ?? 'El archivo no pudo procesarse.')
            : self::UNAVAILABLE);
    }

    private function log(
        string $outcome,
        UploadedFile $pdf,
        float $start,
        ?int $status = null,
        ?string $error = null,
    ): void {
        Log::info('watermark.call', array_filter([
            'outcome' => $outcome,
            'user_id' => auth()->id(),
            'filename' => $pdf->getClientOriginalName(),
            'size_bytes' => $pdf->getSize(),
            'http_status' => $status,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'error' => $error,
        ], fn ($v) => $v !== null));
    }
}
