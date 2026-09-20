@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.plus class="size-6" />
            Nuevo Chofer
        </h1>
        <a href="{{ route('choferes.index') }}"
            class="btn-secondary">
            <flux:icon.arrow-left class="size-4" />
            Volver
        </a>
    </div>

    <x-ui.card>
        <div class="p-6">
            <form method="POST" action="{{ route('choferes.store') }}">
                @csrf

                <div class="mb-6">
                    <label for="id_cliente" class="block text-sm font-semibold text-tinta mb-1.5">Cliente <span class="text-tierra">*</span></label>
                    <select name="id_cliente" id="id_cliente" required
                        class="form-input @error('id_cliente') border-tierra bg-tierra-suave @enderror">
                        <option value="">Seleccione un cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente') == $cliente->id_cliente)>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cliente') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    <small class="text-tinta-suave text-xs mt-1 block">El chofer estará asociado a este cliente</small>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="apellido" class="block text-sm font-semibold text-tinta mb-1.5">Apellido <span class="text-tierra">*</span></label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" required
                            class="form-input @error('apellido') border-tierra bg-tierra-suave @enderror"
                            placeholder="Apellido del chofer">
                        @error('apellido') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-tinta mb-1.5">Nombre <span class="text-tierra">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                            class="form-input @error('nombre') border-tierra bg-tierra-suave @enderror"
                            placeholder="Nombre del chofer">
                        @error('nombre') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="dni" class="block text-sm font-semibold text-tinta mb-1.5">DNI <span class="text-tierra">*</span></label>
                        <input type="text" name="dni" id="dni" value="{{ old('dni') }}" required
                            class="form-input @error('dni') border-tierra bg-tierra-suave @enderror"
                            placeholder="Ej: 12345678">
                        @error('dni') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="telefono" class="block text-sm font-semibold text-tinta mb-1.5">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                            class="form-input @error('telefono') border-tierra bg-tierra-suave @enderror"
                            placeholder="Ej: +54 9 11 1234-5678">
                        @error('telefono') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="estado" class="block text-sm font-semibold text-tinta mb-1.5">Estado</label>
                        <div class="flex items-center gap-3 mt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="estado" id="estado" value="activo" {{ old('estado', 'activo') == 'activo' ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-10 h-5 bg-arena rounded-full peer peer-checked:bg-pino peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                            <x-ui.badge variant="success">Activo</x-ui.badge>
                        </div>
                        <small class="text-tinta-suave text-xs mt-1 block">Los choferes inactivos no aparecerán en los formularios de carga</small>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('choferes.index') }}"
                        class="btn-secondary">
                        <flux:icon.x-mark class="size-4" />
                        Cancelar
                    </a>
                    <button type="submit"
                        class="btn-primary">
                        <flux:icon.check class="size-4" />
                        Guardar Chofer
                    </button>
                </div>
            </form>
        </div>
    </x-ui.card>
</div>
@endsection
