<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Processed = 'processed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Processed => 'Procesado',
            self::Failed => 'Error',
        };
    }
}
