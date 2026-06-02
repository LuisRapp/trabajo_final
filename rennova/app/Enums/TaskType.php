<?php

namespace App\Enums;

enum TaskType: string
{
    case TALA_RASA = 'tala_rasa';
    case RALEO = 'raleo';
    case PODA = 'poda';
    case LIMPIEZA = 'limpieza';

    public function label(): string
    {
        return match ($this) {
            self::TALA_RASA => 'Tala Rasa',
            self::RALEO => 'Raleo',
            self::PODA => 'Poda',
            self::LIMPIEZA => 'Limpieza',
        };
    }
}
