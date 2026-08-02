@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <flux:icon.plus-circle class="size-6" />
            Nuevo Chofer
        </h1>
        <a href="{{ route('choferes.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
            <flux:icon.arrow-left class="size-4" />
            Volver
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="p-6">
            <form method="POST" action="{{ route('choferes.store') }}">
                @csrf

                <div class="mb-6">
                    <label for="id_cliente" class="block text-sm font-semibold text-slate-700 mb-1.5">Cliente <span class="text-red-500">*</span></label>
                    <select name="id_cliente" id="id_cliente" required
                        class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand @error('id_cliente') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror">
                        <option value="">Seleccione un cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente') == $cliente->id_cliente)>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cliente') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    <small class="text-slate-500 text-xs mt-1 block">El chofer estará asociado a este cliente</small>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="apellido" class="block text-sm font-semibold text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
                            class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('apellido') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror"
                            placeholder="Apellido del chofer">
                        @error('apellido') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                            class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('nombre') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror"
                            placeholder="Nombre del chofer">
                        @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="dni" class="block text-sm font-semibold text-slate-700 mb-1.5">DNI <span class="text-red-500">*</span></label>
                        <input type="text" name="dni" id="dni" value="{{ old('dni') }}" required
                            class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('dni') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror"
                            placeholder="Ej: 12345678">
                        @error('dni') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="telefono" class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('telefono') border-red-300 ring-1 ring-red-300 @enderror"
                            placeholder="Ej: +54 9 11 1234-5678">
                        @error('telefono') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="estado" class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                        <div class="flex items-center gap-3 mt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="estado" id="estado" value="activo" {{ old('estado', 'activo') == 'activo' ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-300 rounded-full peer peer-checked:bg-brand peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Activo</span>
                        </div>
                        <small class="text-slate-500 text-xs mt-1 block">Los choferes inactivos no aparecerán en los formularios de carga</small>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('choferes.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        <flux:icon.x-circle class="size-4" />
                        Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                        <flux:icon.check-circle class="size-4" />
                        Guardar Chofer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
