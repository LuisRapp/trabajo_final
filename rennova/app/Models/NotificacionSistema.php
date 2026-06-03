<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Usuario;

class NotificacionSistema extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notificaciones_sistema';

    protected $fillable = [
        'user_id',
        'mantenimiento_id',
        'tipo',
        'titulo',
        'mensaje',
        'fecha_limite',
        'leida',
        'accionada',
        'leida_at',
        'accionada_at',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'accionada' => 'boolean',
        'fecha_limite' => 'date',
        'leida_at' => 'datetime',
        'accionada_at' => 'datetime',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id', 'id_mantenimiento');
    }

    // Scopes
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopePendientes($query)
    {
        return $query->where('accionada', false);
    }

    public function scopeVigentes($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_limite')
              ->orWhere('fecha_limite', '>=', now()->toDateString());
        });
    }

    public function scopeVencidas($query)
    {
        return $query->whereNotNull('fecha_limite')
                    ->where('fecha_limite', '<', now()->toDateString())
                    ->where('accionada', false);
    }

    // Métodos helper
    public function marcarComoLeida(): void
    {
        if (!$this->leida) {
            $this->update([
                'leida' => true,
                'leida_at' => now(),
            ]);
        }
    }

    public function marcarComoAccionada(): void
    {
        if (!$this->accionada) {
            $this->update([
                'accionada' => true,
                'accionada_at' => now(),
                'leida' => true,
                'leida_at' => $this->leida_at ?? now(),
            ]);
        }
    }

    public function estaVencida(): bool
    {
        if (!$this->fecha_limite || $this->accionada) {
            return false;
        }
        
        return $this->fecha_limite->isPast();
    }

    public function diasRestantes(): ?int
    {
        if (!$this->fecha_limite || $this->accionada) {
            return null;
        }

        return now()->diffInDays($this->fecha_limite, false);
    }
}
