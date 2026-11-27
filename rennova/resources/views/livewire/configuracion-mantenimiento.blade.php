<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.cog class="size-6 text-pino" />
            Automatización de mantenimiento
        </h1>
        <p class="mt-1 text-sm text-tinta-suave">
            Cuándo se ejecutan las tareas automáticas de mantenimiento. Los valores se aplican al scheduler del sistema.
        </p>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">{{ session('error') }}</x-ui.alert>
    @endif

    @if (session()->has('command_output'))
        <x-ui.alert variant="info" class="mb-4" :dismissible="false">
            <strong>Resultado de la ejecución:</strong>
            <pre class="mt-2 text-sm whitespace-pre-wrap">{{ session('command_output') }}</pre>
        </x-ui.alert>
    @endif

    <form autocomplete="off" wire:submit.prevent="guardarConfiguracion" class="space-y-4">
        <x-ui.card class="p-5">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-tinta">Frecuencias automáticas</h2>
                <p class="mt-0.5 text-xs text-tinta-suave">
                    Elegí cada cuánto se ejecuta cada tarea. El servidor corre el scheduler de Laravel; estos valores se aplican sin tocar código.
                </p>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                <div>
                    <label for="hora_recordatorio" class="mb-1.5 block text-sm font-semibold text-tinta">Recordatorio diario</label>
                    <input autocomplete="off" type="time" id="hora_recordatorio" wire:model="hora_recordatorio"
                        class="form-input @error('hora_recordatorio') border-tierra bg-tierra-suave @enderror">
                    <p class="mt-1 text-xs text-tinta-suave">
                        Hora a partir de la cual se envía el recordatorio de las órdenes del día (una vez por día).
                    </p>
                    @error('hora_recordatorio') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="modoUmbrales" class="mb-1.5 block text-sm font-semibold text-tinta">Verificación de umbrales</label>
                    <select id="modoUmbrales" wire:model.live="modoUmbrales"
                        class="form-input @error('expresion_cron') border-tierra bg-tierra-suave @enderror">
                        @foreach ($presetsUmbrales as $cron => $etiqueta)
                            <option value="{{ $cron }}">{{ $etiqueta }}</option>
                        @endforeach
                        <option value="custom">Personalizado…</option>
                    </select>

                    @if ($modoUmbrales === 'custom')
                        <input autocomplete="off" type="text" wire:model.live.debounce.500ms="expresion_cron"
                            class="form-input mt-2 font-mono @error('expresion_cron') border-tierra bg-tierra-suave @enderror"
                            placeholder="minuto hora día mes día_semana">
                        @error('expresion_cron') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                    @endif

                    <p class="mt-2 text-sm text-tinta">
                        Se ejecuta: <strong>{{ $descripcionUmbrales }}</strong>
                    </p>
                    <p class="text-xs text-tinta-suave">
                        @if ($proximaUmbrales)
                            Próxima ejecución: {{ $proximaUmbrales }}.
                        @endif
                        Revisa si alguna máquina superó su umbral de toneladas.
                    </p>
                </div>

                <div>
                    <label for="modoProgramados" class="mb-1.5 block text-sm font-semibold text-tinta">Revisión de órdenes programadas</label>
                    <select id="modoProgramados" wire:model.live="modoProgramados"
                        class="form-input @error('cron_programados') border-tierra bg-tierra-suave @enderror">
                        @foreach ($presetsProgramados as $cron => $etiqueta)
                            <option value="{{ $cron }}">{{ $etiqueta }}</option>
                        @endforeach
                        <option value="custom">Personalizado…</option>
                    </select>

                    @if ($modoProgramados === 'custom')
                        <input autocomplete="off" type="text" wire:model.live.debounce.500ms="cron_programados"
                            class="form-input mt-2 font-mono @error('cron_programados') border-tierra bg-tierra-suave @enderror"
                            placeholder="minuto hora día mes día_semana">
                        @error('cron_programados') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                    @endif

                    <p class="mt-2 text-sm text-tinta">
                        Se ejecuta: <strong>{{ $descripcionProgramados }}</strong>
                    </p>
                    <p class="text-xs text-tinta-suave">
                        @if ($proximaProgramados)
                            Próxima ejecución: {{ $proximaProgramados }}.
                        @endif
                        Marca vencidas las órdenes y envía el recordatorio diario.
                    </p>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <x-ui.button type="submit" icon="check">Guardar configuración</x-ui.button>
            </div>
        </x-ui.card>
    </form>

    <x-ui.card class="mt-4 p-5">
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-tinta">Ejecución manual</h2>
            <p class="mt-0.5 text-xs text-tinta-suave">
                Corre las tareas ahora, sin esperar la próxima ejecución programada.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-ui.button type="button" variant="secondary" icon="chart-bar" wire:click="ejecutarVerificacionUmbrales" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="ejecutarVerificacionUmbrales">Verificar umbrales ahora</span>
                <span wire:loading wire:target="ejecutarVerificacionUmbrales">
                    <flux:icon.arrow-path class="size-4 inline animate-spin mr-1" />
                    Ejecutando...
                </span>
            </x-ui.button>

            <x-ui.button type="button" variant="secondary" icon="calendar-date-range" wire:click="ejecutarVerificacionProgramados" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="ejecutarVerificacionProgramados">Revisar órdenes ahora</span>
                <span wire:loading wire:target="ejecutarVerificacionProgramados">
                    <flux:icon.arrow-path class="size-4 inline animate-spin mr-1" />
                    Ejecutando...
                </span>
            </x-ui.button>
        </div>

        <p class="mt-3 text-xs text-tinta-suave">
            La revisión manual de órdenes envía el recordatorio sin esperar la hora configurada.
        </p>
    </x-ui.card>
</div>
