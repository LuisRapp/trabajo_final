<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.cloud class="size-6 text-pino" />
            Parámetros climáticos
        </h1>
        <p class="mt-1 text-sm text-tinta-suave">
            Umbrales que usa el proceso automático para decidir días operativos, calcular el déficit y sugerir mantenimiento.
        </p>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">{{ session('error') }}</x-ui.alert>
    @endif

    <form autocomplete="off" wire:submit.prevent="guardar" class="space-y-4">
        <x-ui.card class="p-5">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-tinta">Detección de lluvia</h2>
                <p class="mt-0.5 text-xs text-tinta-suave">
                    Un día es no operativo si supera el umbral diario y llueve en la madrugada o durante la jornada.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="umbral_lluvia_mm" class="mb-1.5 block text-sm font-semibold text-tinta">Lluvia diaria (mm)</label>
                    <input autocomplete="off" type="number" id="umbral_lluvia_mm" wire:model="umbral_lluvia_mm" step="0.1" min="0"
                        class="form-input @error('umbral_lluvia_mm') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Acumulado del día que ya se considera lluvia significativa.</p>
                    @error('umbral_lluvia_mm') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="lluvia_madrugada_mm" class="mb-1.5 block text-sm font-semibold text-tinta">Lluvia de madrugada (mm)</label>
                    <input autocomplete="off" type="number" id="lluvia_madrugada_mm" wire:model="lluvia_madrugada_mm" step="0.1" min="0"
                        class="form-input @error('lluvia_madrugada_mm') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Con esta lluvia entre 0 y 6 h se forma barro.</p>
                    @error('lluvia_madrugada_mm') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="lluvia_diurna_mm" class="mb-1.5 block text-sm font-semibold text-tinta">Lluvia diurna (mm)</label>
                    <input autocomplete="off" type="number" id="lluvia_diurna_mm" wire:model="lluvia_diurna_mm" step="0.1" min="0"
                        class="form-input @error('lluvia_diurna_mm') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Lluvia entre 6 y 18 h que impide operar.</p>
                    @error('lluvia_diurna_mm') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-tinta">Barro y secado</h2>
                <p class="mt-0.5 text-xs text-tinta-suave">
                    La saturación combina la lluvia del día con el 50% de la del día anterior; el viento y la evapotranspiración secan el suelo.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="saturacion_mm" class="mb-1.5 block text-sm font-semibold text-tinta">Saturación (mm)</label>
                    <input autocomplete="off" type="number" id="saturacion_mm" wire:model="saturacion_mm" step="0.1" min="0"
                        class="form-input @error('saturacion_mm') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Índice de suelo saturado.</p>
                    @error('saturacion_mm') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="umbral_nubosidad_pct" class="mb-1.5 block text-sm font-semibold text-tinta">Nubosidad (%)</label>
                    <input autocomplete="off" type="number" id="umbral_nubosidad_pct" wire:model="umbral_nubosidad_pct" step="1" min="0" max="100"
                        class="form-input @error('umbral_nubosidad_pct') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Nubosidad que impide secar el suelo.</p>
                    @error('umbral_nubosidad_pct') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="secado_viento_kmh" class="mb-1.5 block text-sm font-semibold text-tinta">Viento de secado (km/h)</label>
                    <input autocomplete="off" type="number" id="secado_viento_kmh" wire:model="secado_viento_kmh" step="0.1" min="0"
                        class="form-input @error('secado_viento_kmh') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">A partir de este viento el suelo seca.</p>
                    @error('secado_viento_kmh') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="secado_et0_mm" class="mb-1.5 block text-sm font-semibold text-tinta">Secado por ET0 (mm)</label>
                    <input autocomplete="off" type="number" id="secado_et0_mm" wire:model="secado_et0_mm" step="0.1" min="0"
                        class="form-input @error('secado_et0_mm') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Evapotranspiración que seca el suelo.</p>
                    @error('secado_et0_mm') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-tinta">Plan de recuperación y mantenimiento</h2>
                <p class="mt-0.5 text-xs text-tinta-suave">
                    El aumento máximo reparte el déficit entre los días operativos; el umbral de mantenimiento decide cuándo sugerir una máquina.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="aumento_maximo_pct" class="mb-1.5 block text-sm font-semibold text-tinta">Aumento máximo por día (%)</label>
                    <input autocomplete="off" type="number" id="aumento_maximo_pct" wire:model="aumento_maximo_pct" step="1" min="0" max="100"
                        class="form-input @error('aumento_maximo_pct') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Tope de sobreproducción para recuperar el déficit.</p>
                    @error('aumento_maximo_pct') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="forecast_dias" class="mb-1.5 block text-sm font-semibold text-tinta">Días de pronóstico</label>
                    <input autocomplete="off" type="number" id="forecast_dias" wire:model="forecast_dias" step="1" min="1" max="16"
                        class="form-input @error('forecast_dias') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Ventana que pide a Open-Meteo (1 a 16 días).</p>
                    @error('forecast_dias') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="mantenimiento_umbral_pct" class="mb-1.5 block text-sm font-semibold text-tinta">Umbral de mantenimiento (%)</label>
                    <input autocomplete="off" type="number" id="mantenimiento_umbral_pct" wire:model="mantenimiento_umbral_pct" step="1" min="0" max="100"
                        class="form-input @error('mantenimiento_umbral_pct') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">Porcentaje del umbral de toneladas para sugerir la máquina.</p>
                    @error('mantenimiento_umbral_pct') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-ui.card>

        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-ui.confirmador
                accion="restaurar"
                mensaje="¿Restaurar los parámetros climáticos por defecto?"
                detalle="Se descartan los valores editados."
                titulo="Restaurar parámetros"
                etiqueta="Restaurar"
                texto="Restaurar valores por defecto"
                variante="secondary"
                icono="arrow-uturn-left"
                tituloBoton="Restaurar valores por defecto"
            />
            <x-ui.button type="submit" icon="check">Guardar parámetros</x-ui.button>
        </div>
    </form>
</div>
