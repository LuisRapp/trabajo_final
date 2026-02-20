<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $guard_name = 'web';

    protected $table = 'usuarios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nombre',
        'apellido',
        'name',
        'email',
        'password',
        'telefono',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'password' => 'hashed',
    ];

    public function getNameAttribute(): string
    {
        $fullName = trim(($this->nombre ?? '') . ' ' . ($this->apellido ?? ''));

        return $fullName !== '' ? $fullName : (string) ($this->email ?? '');
    }

    public function setNameAttribute(?string $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $parts = preg_split('/\s+/', $value) ?: [];
        $this->attributes['nombre'] = $parts[0] ?? $value;
        $this->attributes['apellido'] = trim(implode(' ', array_slice($parts, 1)));
    }
}

