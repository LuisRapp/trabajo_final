@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-tinta flex items-center gap-2">
            <flux:icon.pencil-square class="size-6" />
            Editar Carga #{{ $carga->id_carga }}
        </h1>
        <a href="{{ route('cargas.index') }}"
            class="btn-secondary">
            <flux:icon.arrow-left class="size-4" />
            Volver
        </a>
    </div>

    <x-ui.card>
        <div class="p-6">
            <form method="POST" action="{{ route('cargas.update', $carga) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="id_lote" class="block text-sm font-semibold text-tinta mb-1.5">Lote <span class="text-tierra">*</span></label>
                        <select name="id_lote" id="id_lote" required
                            class="form-input @error('id_lote') border-tierra ring-1 ring-tierra @enderror">
                            <option value="">Seleccione un lote...</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->id_lote }}" @selected(old('id_lote', $carga->id_lote) == $lote->id_lote)>
                                    Lote #{{ $lote->id_lote }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_lote') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="id_categoria_madera" class="block text-sm font-semibold text-tinta mb-1.5">Categoría Madera</label>
                        <select name="id_categoria_madera" id="id_categoria_madera"
                            class="form-input @error('id_categoria_madera') border-tierra ring-1 ring-tierra @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria_madera }}" @selected(old('id_categoria_madera', $carga->id_categoria_madera) == $cat->id_categoria_madera)>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_categoria_madera') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="id_parte_diario" class="block text-sm font-semibold text-tinta mb-1.5">Parte Diario</label>
                        <select name="id_parte_diario" id="id_parte_diario"
                            class="form-input @error('id_parte_diario') border-tierra ring-1 ring-tierra @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($partes as $parte)
                                <option value="{{ $parte->id_parte_diario }}" @selected(old('id_parte_diario', $carga->id_parte_diario) == $parte->id_parte_diario)>
                                    Parte #{{ $parte->id_parte_diario }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_parte_diario') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="md:col-span-2">
                        <label for="id_chofer" class="block text-sm font-semibold text-tinta mb-1.5">Chofer</label>
                        <select name="id_chofer" id="id_chofer"
                            class="form-input @error('id_chofer') border-tierra ring-1 ring-tierra @enderror">
                            <option value="">Seleccione un chofer...</option>
                            @foreach($choferes as $ch)
                                <option value="{{ $ch->id_chofer }}" @selected(old('id_chofer', $carga->id_chofer) == $ch->id_chofer)>
                                    {{ $ch->cliente?->nombre }} - {{ $ch->apellido }} {{ $ch->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_chofer') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ticket" class="block text-sm font-semibold text-tinta mb-1.5">Ticket</label>
                        <input type="text" name="ticket" id="ticket" value="{{ old('ticket', $carga->ticket) }}" maxlength="20"
                            class="form-input @error('ticket') border-tierra ring-1 ring-tierra @enderror"
                            placeholder="Número de ticket">
                        @error('ticket') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="fecha_carga" class="block text-sm font-semibold text-tinta mb-1.5">Fecha Carga <span class="text-tierra">*</span></label>
                        <input type="date" name="fecha_carga" id="fecha_carga" value="{{ old('fecha_carga', $carga->fecha_carga) }}" required max="{{ now()->toDateString() }}"
                            class="form-input @error('fecha_carga') border-tierra ring-1 ring-tierra @enderror">
                        @error('fecha_carga') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label for="peso_bruto" class="block text-sm font-semibold text-tinta mb-1.5">Peso Bruto (kg)</label>
                        <input type="number" step="0.1" min="0" name="peso_bruto" id="peso_bruto" value="{{ old('peso_bruto', $carga->peso_bruto) }}"
                            class="form-input @error('peso_bruto') border-tierra ring-1 ring-tierra @enderror"
                            placeholder="0.00">
                        @error('peso_bruto') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tara" class="block text-sm font-semibold text-tinta mb-1.5">Tara (kg)</label>
                        <input type="number" step="0.1" min="0" name="tara" id="tara" value="{{ old('tara', $carga->tara) }}"
                            class="form-input @error('tara') border-tierra ring-1 ring-tierra @enderror"
                            placeholder="0.00">
                        @error('tara') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="peso_neto" class="block text-sm font-semibold text-tinta mb-1.5">Peso Neto (kg)</label>
                        <input type="number" step="0.1" min="0" name="peso_neto" id="peso_neto" value="{{ old('peso_neto', $carga->peso_neto) }}" readonly
                            class="form-input bg-corteza-suave text-tinta-suave">
                        <small class="text-tinta-suave text-xs mt-1 block">Calculado automáticamente</small>
                    </div>

                    <div>
                        <label for="id_cliente" class="block text-sm font-semibold text-tinta mb-1.5">Cliente</label>
                        <select name="id_cliente" id="id_cliente"
                            class="form-input @error('id_cliente') border-tierra ring-1 ring-tierra @enderror">
                            <option value="">-- Seleccionar cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente', $carga->id_cliente) == $cliente->id_cliente)>
                                    {{ $cliente->razon_social }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_cliente') <p class="text-tierra text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('cargas.index') }}"
                        class="btn-secondary">
                        <flux:icon.x-circle class="size-4" />
                        Cancelar
                    </a>
                    <button type="submit"
                        class="btn-primary">
                        <flux:icon.check-circle class="size-4" />
                        Actualizar Carga
                    </button>
                </div>
            </form>
        </div>
    </x-ui.card>
</div>

@push('scripts')
<script>
    function calcularPesoNeto() {
        const bruto = parseFloat(document.querySelector('[name=peso_bruto]').value) || 0;
        const tara = parseFloat(document.querySelector('[name=tara]').value) || 0;
        document.getElementById('peso_neto').value = (bruto - tara).toFixed(2);
    }
    document.querySelector('[name=peso_bruto]').addEventListener('input', calcularPesoNeto);
    document.querySelector('[name=tara]').addEventListener('input', calcularPesoNeto);
    window.addEventListener('DOMContentLoaded', calcularPesoNeto);
</script>
@endpush
@endsection
