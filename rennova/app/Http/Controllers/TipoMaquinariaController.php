<?php

namespace App\Http\Controllers;

use App\Models\TipoMaquinaria;
use Illuminate\Http\Request;

class TipoMaquinariaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tipos-maquinaria.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoMaquinaria $tipoMaquinaria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoMaquinaria $tipoMaquinaria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoMaquinaria $tipoMaquinaria)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoMaquinaria $tipoMaquinaria)
    {
        //
    }
}
