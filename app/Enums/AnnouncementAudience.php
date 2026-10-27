<?php

namespace App\Enums;

enum AnnouncementAudience: string
{
    case Teachers = 'teachers';
    case Students = 'students';
    case Parents = 'parents';
    case Both = 'both';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Teachers => 'Teachers',
            self::Students => 'Students',
            self::Parents => 'Parents',
            self::Both => 'Students and parents',
            self::Custom => 'Custom Selection',
        };
    }
}
