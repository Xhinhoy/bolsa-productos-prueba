<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * mimes valida la extension; mimetypes inspecciona el contenido real via
&
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
            'watermark_image' => ['required', 'file', 'mimes:png,jpg,jpeg', 'mimetypes:image/png,image/jpeg', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre del contrato',
            'pdf_file' => 'archivo PDF',
            'watermark_image' => 'imagen de marca de agua',
        ];
    }
}
