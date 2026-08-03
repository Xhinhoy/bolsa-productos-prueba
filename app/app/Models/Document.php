<?php

namespace App\Models;

use App\Enums\DocumentStatus;
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function humanSize(): string
    {
        return $this->stored_size_bytes >= 1048576
            ? round($this->stored_size_bytes / 1048576, 2).' MB'
            : round($this->stored_size_bytes / 1024, 1).' KB';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'original_size_bytes' => 'integer',
            'stored_size_bytes' => 'integer',
            'page_count' => 'integer',
        ];
    }
}
