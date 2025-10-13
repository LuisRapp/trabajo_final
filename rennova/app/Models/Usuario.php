<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
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
    ];
}
{
    //
}
