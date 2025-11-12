<?php

namespace App\Http\Controllers;

use App\Models\KitPreventivo;
use Illuminate\Http\Request;

class KitInsumoController extends Controller
{
    public function index($kitId)
    {
        $kit = KitPreventivo::with('tipoMaquinaria')->findOrFail($kitId);
        return view('kits.insumos', compact('kit'));
    }
}
