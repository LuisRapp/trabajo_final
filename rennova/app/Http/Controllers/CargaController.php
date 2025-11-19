<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\Lote;
use App\Models\CategoriaMadera;
use App\Models\ParteDiario;
use App\Models\Chofer;
use Illuminate\Http\Request;

class CargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cargas = Carga::with(['categoriaMadera', 'parteDiario', 'lote', 'chofer.cliente'])
            ->orderByDesc('id_carga')
            ->get();
        return view('cargas.index', compact('cargas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lotes = Lote::orderBy('id_lote')->get();
        $categorias = CategoriaMadera::orderBy('nombre')->get();
        $partes = ParteDiario::orderByDesc('id_parte_diario')->get();
        $choferes = Chofer::with('cliente')->orderBy('apellido')->orderBy('nombre')->get();
        return view('cargas.create', compact('lotes', 'categorias', 'partes', 'choferes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_lote' => 'required|exists:lotes,id_lote',
            'id_categoria_madera' => 'nullable|exists:categoria_maderas,id_categoria_madera',
            'id_chofer' => 'nullable|exists:choferes,id_chofer',
            'id_parte_diario' => 'nullable|exists:parte_diarios,id_parte_diario',
            'ticket' => 'nullable|string|max:20',
            'peso_bruto' => 'nullable|numeric|min:0',
            'tara' => 'nullable|numeric|min:0',
            'destino' => 'nullable|string|max:100',
            'fecha_carga' => 'required|date',
        ]);

        $data['peso_neto'] = ($data['peso_bruto'] ?? 0) - ($data['tara'] ?? 0);

        Carga::create($data);

        return redirect()->route('cargas.index')->with('status', 'Carga creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Carga $carga)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carga $carga)
    {
        $lotes = Lote::orderBy('id_lote')->get();
        $categorias = CategoriaMadera::orderBy('nombre')->get();
        $partes = ParteDiario::orderByDesc('id_parte_diario')->get();
        $choferes = Chofer::with('cliente')->orderBy('apellido')->orderBy('nombre')->get();
        return view('cargas.edit', compact('carga', 'lotes', 'categorias', 'partes', 'choferes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Carga $carga)
    {
        $data = $request->validate([
            'id_lote' => 'required|exists:lotes,id_lote',
            'id_categoria_madera' => 'nullable|exists:categoria_maderas,id_categoria_madera',
            'id_chofer' => 'nullable|exists:choferes,id_chofer',
            'id_parte_diario' => 'nullable|exists:parte_diarios,id_parte_diario',
            'ticket' => 'nullable|string|max:20',
            'peso_bruto' => 'nullable|numeric|min:0',
            'tara' => 'nullable|numeric|min:0',
            'destino' => 'nullable|string|max:100',
            'fecha_carga' => 'required|date',
        ]);

        $data['peso_neto'] = ($data['peso_bruto'] ?? 0) - ($data['tara'] ?? 0);

        $carga->update($data);

        return redirect()->route('cargas.index')->with('status', 'Carga actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carga $carga)
    {
        $carga->delete();
        return redirect()->route('cargas.index')->with('status', 'Carga eliminada correctamente.');
    }
}
