<?php

namespace App\Events;

use App\Models\Carga;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CargaRegistrada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $carga;
    public $maquinariaId;
    public $toneladas;

    /**
     * Create a new event instance.
     */
    public function __construct(Carga $carga, $maquinariaId, $toneladas)
    {
        $this->carga = $carga;
        $this->maquinariaId = $maquinariaId;
        $this->toneladas = $toneladas;
    }
}
