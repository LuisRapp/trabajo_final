<?php

namespace App\Models;

use App\Enums\ComportamientoMantenimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TipoMantenimiento extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'tipo_mantenimientos';

    protected $primaryKey = 'id_tipo_mantenimiento';

    protected function casts(): array
    {
        return [
            'comportamiento' => ComportamientoMantenimiento::class,
        ];
    }

    protected $fillable = ['nombre', 'comportamiento'];

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_tipo_mantenimiento');
    }
}
