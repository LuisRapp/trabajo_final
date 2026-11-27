<?php

namespace App\Enums;

enum ComportamientoMantenimiento: string
{
    case PREVENTIVO = 'preventivo';
    case CORRECTIVO = 'correctivo';
    case OTRO = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::PREVENTIVO => 'Preventivo',
            self::CORRECTIVO => 'Correctivo',
            self::OTRO => 'Otro',
        };
    }

    public function descripcion(): string
    {
        return match ($this) {
            self::PREVENTIVO => 'Genera órdenes automáticas al superar el umbral y usa el kit preventivo.',
            self::CORRECTIVO => 'Se crea a mano cuando algo se rompe: no usa kit ni genera órdenes automáticas.',
            self::OTRO => 'No tiene comportamiento automático.',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::PREVENTIVO => 'info',
            self::CORRECTIVO => 'warning',
            self::OTRO => 'neutral',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
