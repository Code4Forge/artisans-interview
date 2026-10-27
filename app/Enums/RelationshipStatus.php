<?php

namespace App\Enums;

enum RelationshipStatus: string
{
    case Mother = 'Mother';
    case Father = 'Father';
    case Guardian = 'Guardian';
    case Other = 'Other';

    public function label(): string
    {
        return match ($this) {
            self::Mother => 'Mother',
            self::Father => 'Father',
            self::Guardian => 'Guardian',
            self::Other => 'Other',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
