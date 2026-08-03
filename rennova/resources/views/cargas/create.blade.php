@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <flux:icon.plus-circle class="size-6" />
            Nueva Carga
        </h1>
        <a href="{{ route('cargas.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
            <flux:icon.arrow-left class="size-4" />
            Volver
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="p-6">
            <form method="POST" action="{{ route('cargas.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label for="id_lote" class="block text-sm font-semibold text-slate-700 mb-1.5">Lote <span class="text-red-500">*</span></label>
                        <select name="id_lote" id="id_lote" required
                            class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand @error('id_lote') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror">
                            <option value="">Seleccione un lote...</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->id_lote }}" @selected(old('id_lote') == $lote->id_lote)>
                                    Lote #{{ $lote->id_lote }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_lote') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="id_categoria_madera" class="block text-sm font-semibold text-slate-700 mb-1.5">Categoría Madera</label>
                        <select name="id_categoria_madera" id="id_categoria_madera"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand @error('id_categoria_madera') border-red-300 ring-1 ring-red-300 @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria_madera }}" @selected(old('id_categoria_madera') == $cat->id_categoria_madera)>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_categoria_madera') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="id_parte_diario" class="block text-sm font-semibold text-slate-700 mb-1.5">Parte Diario</label>
                        <select name="id_parte_diario" id="id_parte_diario"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand @error('id_parte_diario') border-red-300 ring-1 ring-red-300 @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($partes as $parte)
                                <option value="{{ $parte->id_parte_diario }}" @selected(old('id_parte_diario') == $parte->id_parte_diario)>
                                    Parte #{{ $parte->id_parte_diario }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_parte_diario') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="md:col-span-2">
                        <label for="id_chofer" class="block text-sm font-semibold text-slate-700 mb-1.5">Chofer</label>
                        <select name="id_chofer" id="id_chofer"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand @error('id_chofer') border-red-300 ring-1 ring-red-300 @enderror">
                            <option value="">Seleccione un chofer...</option>
                            @foreach($choferes as $ch)
                                <option value="{{ $ch->id_chofer }}" @selected(old('id_chofer') == $ch->id_chofer)>
                                    {{ $ch->cliente?->nombre }} - {{ $ch->apellido }} {{ $ch->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_chofer') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ticket" class="block text-sm font-semibold text-slate-700 mb-1.5">Ticket</label>
                        <input type="text" name="ticket" id="ticket" value="{{ old('ticket') }}" maxlength="20"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('ticket') border-red-300 ring-1 ring-red-300 @enderror"
                            placeholder="Número de ticket">
                        @error('ticket') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="fecha_carga" class="block text-sm font-semibold text-slate-700 mb-1.5">Fecha Carga <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_carga" id="fecha_carga" value="{{ old('fecha_carga', now()->toDateString()) }}" required max="{{ now()->toDateString() }}"
                            class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm focus:border-brand focus:ring-1 focus:ring-brand @error('fecha_carga') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror">
                        @error('fecha_carga') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label for="peso_bruto" class="block text-sm font-semibold text-slate-700 mb-1.5">Peso Bruto (kg)</label>
                        <input type="number" step="0.1" min="0" name="peso_bruto" id="peso_bruto" value="{{ old('peso_bruto') }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('peso_bruto') border-red-300 ring-1 ring-red-300 @enderror"
                            placeholder="0.00">
                        @error('peso_bruto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tara" class="block text-sm font-semibold text-slate-700 mb-1.5">Tara (kg)</label>
                        <input type="number" step="0.1" min="0" name="tara" id="tara" value="{{ old('tara') }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('tara') border-red-300 ring-1 ring-red-300 @enderror"
                            placeholder="0.00">
                        @error('tara') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="peso_neto" class="block text-sm font-semibold text-slate-700 mb-1.5">Peso Neto (kg)</label>
                        <input type="number" step="0.1" min="0" name="peso_neto" id="peso_neto" value="" readonly
                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 shadow-sm">
                        <small class="text-slate-500 text-xs mt-1 block">Calculado automáticamente</small>
                    </div>

                    <div>
                        <label for="destino" class="block text-sm font-semibold text-slate-700 mb-1.5">Destino</label>
                        <input type="text" name="destino" id="destino" value="{{ old('destino') }}" maxlength="100"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-brand focus:ring-1 focus:ring-brand @error('destino') border-red-300 ring-1 ring-red-300 @enderror"
                            placeholder="Lugar de destino">
                        @error('destino') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('cargas.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        <flux:icon.x-circle class="size-4" />
                        Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-hover transition-colors">
                        <flux:icon.check-circle class="size-4" />
                        Guardar Carga
                    </button>
                </div>
            </form>
        </div>
    </div>
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
