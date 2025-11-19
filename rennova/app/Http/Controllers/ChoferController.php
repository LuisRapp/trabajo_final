<?php

namespace App\Http\Controllers;

use App\Models\Chofer;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ChoferController extends Controller
{
    public function index()
    {
        $choferes = Chofer::with('cliente')->orderByDesc('id_chofer')->get();
        return view('choferes.index', compact('choferes'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('razon_social')->get();
        return view('choferes.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'dni' => 'required|string|max:20|unique:choferes,dni',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:150',
            'estado' => 'boolean',
        ]);
        $data['estado'] = $request->has('estado');
        Chofer::create($data);
        return redirect()->route('choferes.index')->with('status', 'Chofer creado correctamente.');
    }

    public function edit(Chofer $chofer)
    {
        $clientes = Cliente::orderBy('razon_social')->get();
        return view('choferes.edit', compact('chofer', 'clientes'));
    }

    public function update(Request $request, Chofer $chofer)
    {
        $data = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'dni' => 'required|string|max:20|unique:choferes,dni,' . $chofer->id_chofer . ',id_chofer',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:150',
            'estado' => 'boolean',
        ]);
        $data['estado'] = $request->has('estado');
        $chofer->update($data);
        return redirect()->route('choferes.index')->with('status', 'Chofer actualizado correctamente.');
    }

    public function destroy(Chofer $chofer)
    {
        $chofer->delete();
        return redirect()->route('choferes.index')->with('status', 'Chofer eliminado correctamente.');
    }
}
