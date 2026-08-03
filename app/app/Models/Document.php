<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'name',
        'original_filename',
        'stored_path',
        'original_size_bytes',
        'stored_size_bytes',
        'page_count',
        'checksum_sha256',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'original_size_bytes' => 'integer',
            'stored_size_bytes' => 'integer',
            'page_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function humanSize(): Attribute
    {
        return Attribute::get(fn (): string => $this->stored_size_bytes >= 1048576
            ? round($this->stored_size_bytes / 1048576, 2).' MB'
            : round($this->stored_size_bytes / 1024, 1).' KB');
    }
}
