<?php

namespace App\Http\Livewire\Traits;

use App\Models\CategoriaMadera;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Services\InventarioService;

trait CatalogosTrait
{
    protected $empleadosCache;

    protected $maquinariasCache;

    protected $choferesCache;

    protected $clientesCache;

    protected $categoriasMaderaCache;

    protected $insumosCache;

    public function getEmpleadosProperty()
    {
        if (isset($this->empleadosCache)) {
            return $this->empleadosCache;
        }

        $this->empleadosCache = Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();

        return $this->empleadosCache;
    }

    public function getMaquinariasProperty()
    {
        if (isset($this->maquinariasCache)) {
            return $this->maquinariasCache;
        }

        $this->maquinariasCache = \App\Models\Maquinaria::with('tipoMaquinaria')
            ->orderBy('modelo')
            ->get();

        return $this->maquinariasCache;
    }

    public function getChoferesProperty()
    {
        if (isset($this->choferesCache)) {
            return $this->choferesCache;
        }

        $this->choferesCache = Chofer::where('estado', true)
            ->orderBy('apellido')
            ->get();

        return $this->choferesCache;
    }

    public function getClientesProperty()
    {
        if (isset($this->clientesCache)) {
            return $this->clientesCache;
        }

        $this->clientesCache = Cliente::orderBy('razon_social')->get();

        return $this->clientesCache;
    }

    public function getCategoriasMaderaProperty()
    {
        if (isset($this->categoriasMaderaCache)) {
            return $this->categoriasMaderaCache;
        }

        $this->categoriasMaderaCache = CategoriaMadera::orderBy('nombre')->get();

        return $this->categoriasMaderaCache;
    }

    public function getInsumosProperty()
    {
        if (isset($this->insumosCache)) {
            return $this->insumosCache;
        }

        $this->insumosCache = InventarioService::queryInsumosConStockYPrecio()
            ->with('unidadMedida')
            ->orderBy('nombre')
            ->get();

        return $this->insumosCache;
    }
}
