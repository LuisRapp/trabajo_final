<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        @if ($notificacionId)
            <div class="flex items-start gap-3">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
                        <flux:icon.information-circle class="size-6" />
                        Detalle de Mantenimiento por Notificacion
                    </h1>
                    <p class="text-sm text-tinta-suave mt-1">
                        Esta orden ya fue generada automaticamente por una notificacion. Confirme la fecha para dejarla programada.
                    </p>
                </div>
            </div>
        @else
            <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
                <flux:icon.calendar-date-range class="size-6" />
                Programar Mantenimiento
            </h1>
            <p class="text-sm text-tinta-suave mt-1">
                Programe una nueva orden de mantenimiento preventivo o correctivo.
            </p>
        @endif
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-6">
            {{ session('message') }}
        </x-ui.alert>
    @endif
    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    @if (! $notificacionId)
        {{-- ALTA: formulario para programar una nueva orden --}}
        <x-ui.card class="overflow-hidden">
            <div class="bg-corteza-suave border-b border-arena px-6 py-4">
                <h5 class="flex items-center gap-2 text-lg font-semibold text-tinta mb-0">
                    <flux:icon.plus class="size-5" />
                    Nueva Orden de Mantenimiento
                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="programarOrden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="id_maquinaria" class="block text-sm font-semibold text-tinta mb-2">
                                Maquinaria <span class="text-tierra">*</span>
                            </label>
                            <select id="id_maquinaria" wire:model="id_maquinaria" class="form-input @error('id_maquinaria') ring-2 ring-tierra @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($maquinarias as $maquinaria)
                                    <option value="{{ $maquinaria->id_maquinaria }}" wire:key="option-{{ $maquinaria->id_maquinaria }}">
                                        {{ $maquinaria->modelo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_maquinaria') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                            @php
                                $maquinariaSeleccionada = $maquinarias?->firstWhere('id_maquinaria', $id_maquinaria);
                            @endphp
                            @if($maquinariaSeleccionada && $maquinariaSeleccionada->umbral_toneladas)
                                <small class="text-tinta-suave text-xs mt-1 block">
                                    <flux:icon.information-circle class="size-3 inline" />
                                    Toneladas acumuladas: {{ number_format($maquinariaSeleccionada->toneladas_acumuladas ?? 0, 0) }} / {{ number_format($maquinariaSeleccionada->umbral_toneladas, 0) }} (umbral de mantenimiento)
                                </small>
                            @endif
                        </div>
                        <div>
                            <label for="id_tipo_mantenimiento" class="block text-sm font-semibold text-tinta mb-2">
                                Tipo de Mantenimiento <span class="text-tierra">*</span>
                            </label>
                            <select id="id_tipo_mantenimiento" wire:model="id_tipo_mantenimiento" class="form-input @error('id_tipo_mantenimiento') ring-2 ring-tierra @enderror">
                                <option value="">Seleccione...</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_mantenimiento }}" wire:key="option-{{ $tipo->id_tipo_mantenimiento }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('id_tipo_mantenimiento') <p class="mt-1 text-sm text-tierra">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="fechaProgramada" class="block text-sm font-semibold text-tinta mb-1.5 flex items-center gap-2">
                                <flux:icon.calendar-date-range class="size-4" />
                                Fecha Programada <span class="text-tierra">*</span>
                            </label>
                            <input
                                type="date"
                                id="fechaProgramada"
                                class="form-input @error('fechaProgramada') border-tierra bg-tierra-suave @enderror"
                                wire:model="fechaProgramada"
                            >
                            @error('fechaProgramada')
                                <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">
                                <flux:icon.information-circle class="size-3 inline" />
                                Debe estar entre {{ \Carbon\Carbon::now()->format('d/m/Y') }} y {{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }}
                            </small>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-tinta mb-2">Estado</label>
                            <x-ui.badge variant="info">Programado</x-ui.badge>
                            <small class="text-tinta-suave text-xs mt-1 block">
                                La orden se creara en estado programado. Podra confirmarse desde el listado de mantenimientos.
                            </small>
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end">
                        <a href="{{ route('mantenimientos.index') }}" class="inline-flex items-center gap-1.5 rounded-sm border border-arena bg-white px-4 py-2.5 text-sm font-semibold text-tinta hover:bg-corteza-suave">
                            <flux:icon.x-mark class="size-4" />
                            Cancelar
                        </a>
                        <x-ui.button type="submit" icon="calendar-date-range">
                            Programar Orden
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    @else
        {{-- DETALLE: confirmacion de orden generada por notificacion --}}
        <x-ui.card class="overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <x-ui.alert variant="info" :dismissible="false">
                        <strong>{{ $notificacion->titulo }}</strong>
                        <p class="mt-1 text-xs">{{ $notificacion->mensaje }}</p>
                    </x-ui.alert>
                    <div class="bg-hueso rounded-sm p-4 border border-arena">
                        <h6 class="text-pino font-semibold mb-2 flex items-center gap-2">
                            <flux:icon.wrench class="size-4" />
                            Detalles del Mantenimiento
                        </h6>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="col-span-2">
                                <small class="text-tinta-suave">Maquinaria:</small>
                                <div class="font-semibold text-tinta">{{ $mantenimiento->maquinaria->modelo ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <small class="text-tinta-suave">Tipo:</small>
                                <div class="font-semibold text-tinta">{{ $mantenimiento->tipoMantenimiento->nombre ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <small class="text-tinta-suave">Estado:</small>
                                <div>
                                    @if($mantenimiento->estado === 'programado')
                                        <x-ui.badge variant="info">Programado</x-ui.badge>
                                    @elseif($mantenimiento->estado === 'en curso')
                                        <x-ui.badge variant="warning">En Curso</x-ui.badge>
                                    @elseif($mantenimiento->estado === 'completado')
                                        <x-ui.badge variant="success">Completado</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">{{ ucfirst($mantenimiento->estado) }}</x-ui.badge>
                                    @endif
                                </div>
                            </div>
                            <div class="col-span-2">
                                <small class="text-tinta-suave">Fecha de Inicio:</small>
                                <div class="font-semibold text-tinta">{{ $mantenimiento->fecha_inicio ? \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="border-arena my-6">
                <form wire:submit.prevent="guardarFecha">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="fechaProgramada" class="block text-sm font-semibold text-tinta mb-1.5 flex items-center gap-2">
                                <flux:icon.calendar-date-range class="size-4" />
                                Fecha Programada <span class="text-tierra">*</span>
                            </label>
                            <input
                                type="date"
                                id="fechaProgramada"
                                class="form-input @error('fechaProgramada') border-tierra bg-tierra-suave @enderror"
                                wire:model="fechaProgramada"
                                min="{{ $fechaMinima }}"
                                max="{{ $fechaMaxima }}"
                            >
                            @error('fechaProgramada')
                                <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <small class="text-tinta-suave text-xs mt-1 block">
                                <flux:icon.information-circle class="size-3 inline" />
                                La fecha debe estar dentro del rango permitido:
                                <strong>{{ \Carbon\Carbon::parse($fechaMinima)->format('d/m/Y') }}</strong>
                                a
                                <strong>{{ \Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y') }}</strong>
                                (7 dias desde la notificacion)
                            </small>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <a href="{{ route('mantenimientos.index') }}" class="inline-flex items-center gap-1.5 rounded-sm border border-arena bg-white px-4 py-2.5 text-sm font-semibold text-tinta hover:bg-corteza-suave">
                            <flux:icon.x-mark class="size-4" />
                            Cancelar
                        </a>
                        <x-ui.button type="submit" icon="check">
                            Confirmar y Programar
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    @endif
</div>
