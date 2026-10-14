<?php

namespace App\Enums;

enum AlertSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Danger = 'danger';

    /** Bootstrap colour used in the admin panel. */
    public function color(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'warning',
            self::Danger => 'danger',
        };
    }
}
