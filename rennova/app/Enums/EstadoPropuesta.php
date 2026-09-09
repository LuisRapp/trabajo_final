<?php

namespace App\Enums;

enum EstadoPropuesta: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case APPLIED = 'applied';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::CONFIRMED => 'Confirmada',
            self::APPLIED => 'Aplicada',
            self::CLOSED => 'Cerrada',
        };
    }

    /**
     * Label for presentation, with a safe fallback for unknown or null values.
     */
    public static function etiqueta(?string $valor): string
    {
        $estado = self::tryFrom((string) $valor);

        return $estado ? $estado->label() : (string) $valor;
    }
}
