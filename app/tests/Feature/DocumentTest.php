<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function pdf(int $kb = 10): UploadedFile
    {
        return UploadedFile::fake()->create('contrato.pdf', $kb, 'application/pdf');
    }

    private function png(): UploadedFile
    {
        return UploadedFile::fake()->create('logo.png', 10, 'image/png');
    }

    private function fakeOk(): void
    {
        Http::fake(['*/watermark' => Http::response('%PDF-1.4 marcado', 200)]);
    }

    private function makeDocument(User $user, string $name = 'Contrato'): Document
    {
        Storage::disk('local')->put("documents/{$user->id}/{$name}.pdf", 'x');

        return $user->documents()->create([
            'name' => $name,
            'original_filename' => "{$name}.pdf",
            'stored_path' => "documents/{$user->id}/{$name}.pdf",
            'original_size_bytes' => 1024,
            'stored_size_bytes' => 2048,
        ]);
    }

    public function test_invitado_es_redirigido_de_toda_ruta_de_documentos(): void
    {
        $document = $this->makeDocument(User::factory()->create());

        $this->get('/documents')->assertRedirect('/login');
        $this->get('/documents/create')->assertRedirect('/login');
        $this->get("/documents/{$document->id}/download")->assertRedirect('/login');
        $this->delete("/documents/{$document->id}")->assertRedirect('/login');
    }

    public function test_un_usuario_no_accede_a_los_documentos_de_otro(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $document = $this->makeDocument($owner);

        $this->actingAs($intruder)->get("/documents/{$document->id}/download")->assertForbidden();
        $this->actingAs($intruder)->delete("/documents/{$document->id}")->assertForbidden();
    }

    public function test_el_listado_solo_muestra_los_documentos_propios(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->makeDocument($owner, 'Mio');
        $this->makeDocument($other, 'Ajeno');

        $this->actingAs($owner)->get('/documents')
            ->assertSee('Mio')
            ->assertDontSee('Ajeno');
    }

    public function test_registrar_crea_la_fila_y_el_archivo(): void
    {
        $this->fakeOk();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'name' => 'Contrato de arriendo',
            'pdf_file' => $this->pdf(),
            'watermark_image' => $this->png(),
        ])->assertRedirect('/documents')->assertSessionHas('success');

        $document = Document::sole();
        $this->assertSame($user->id, $document->user_id);
        $this->assertSame('contrato.pdf', $document->original_filename);
        Storage::disk('local')->assertExists($document->stored_path);
    }

    public function test_un_error_5xx_del_servicio_no_deja_fila_ni_archivo(): void
    {
        Http::fake(['*/watermark' => Http::response('boom', 500)]);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'name' => 'Contrato',
            'pdf_file' => $this->pdf(),
            'watermark_image' => $this->png(),
        ])->assertSessionHas('error');

        $this->assertSame(0, Document::count());
        $this->assertEmpty(Storage::disk('local')->allFiles('documents'));
    }

    public function test_si_el_servicio_no_responde_no_deja_fila_ni_archivo(): void
    {
        Http::fake(fn () => throw new ConnectionException('rechazado'));
        $user = User::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'name' => 'Contrato',
            'pdf_file' => $this->pdf(),
            'watermark_image' => $this->png(),
        ])->assertSessionHas('error');

        $this->assertSame(0, Document::count());
        $this->assertEmpty(Storage::disk('local')->allFiles('documents'));
    }

    public function test_la_validacion_rechaza_archivos_que_no_corresponden(): void
    {
        $this->fakeOk();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'name' => 'Contrato',
            'pdf_file' => UploadedFile::fake()->create('virus.pdf', 10, 'application/x-msdownload'),
            'watermark_image' => $this->png(),
        ])->assertSessionHasErrors('pdf_file');

        $this->actingAs($user)->post('/documents', [
            'name' => 'Contrato',
            'pdf_file' => $this->pdf(20000),
            'watermark_image' => $this->png(),
        ])->assertSessionHasErrors('pdf_file');
        $this->actingAs($user)->post('/documents', [
            'name' => 'Contrato',
            'pdf_file' => $this->pdf(),
            'watermark_image' => UploadedFile::fake()->create('animado.gif', 10, 'image/gif'),
        ])->assertSessionHasErrors('watermark_image');

        $this->assertSame(0, Document::count());
    }

    public function test_eliminar_borra_la_fila_y_el_archivo(): void
    {
        $user = User::factory()->create();
        $document = $this->makeDocument($user);

        $this->actingAs($user)->delete("/documents/{$document->id}")
            ->assertRedirect('/documents')->assertSessionHas('success');

        $this->assertSame(0, Document::count());
        Storage::disk('local')->assertMissing($document->stored_path);
    }

    public function test_el_listado_va_ordenado_por_fecha_desc_y_paginado(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $i) {
            $this->makeDocument($user, "Contrato {$i}")
                ->forceFill(['created_at' => now()->subDays(12 - $i)])->save();
        }

        $response = $this->actingAs($user)->get('/documents');
        $response->assertSee('Pagina 1 de 2');

        $names = $response->viewData('documents')->pluck('name')->all();
        $this->assertSame('Contrato 12', $names[0]);
        $this->assertCount(10, $names);
    }
}
