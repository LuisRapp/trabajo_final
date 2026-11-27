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
                    El servidor corre el scheduler de Laravel; estas frecuencias se aplican sin tocar código.
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
                    <label for="expresion_cron" class="mb-1.5 block text-sm font-semibold text-tinta">Verificación de umbrales</label>
                    <input autocomplete="off" type="text" id="expresion_cron" wire:model="expresion_cron"
                        class="form-input font-mono @error('expresion_cron') border-tierra bg-tierra-suave @enderror"
                        placeholder="minuto hora día mes día_semana">
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach (['30 6 * * *' => 'Diario 06:30', '0 8 * * *' => 'Diario 08:00', '0 */6 * * *' => 'Cada 6 h', '0 */12 * * *' => 'Cada 12 h'] as $cron => $etiqueta)
                            <button type="button" wire:click="setCronUmbrales('{{ $cron }}')"
                                class="rounded-sm border border-arena bg-white px-2 py-1 text-xs text-tinta hover:bg-corteza-suave">
                                {{ $etiqueta }}
                            </button>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-tinta-suave">
                        @if ($proximaUmbrales)
                            Próxima ejecución: <strong>{{ $proximaUmbrales }}</strong>.
                        @endif
                        Conviene después de las 06:00, cuando corre el análisis climático.
                    </p>
                    @error('expresion_cron') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="cron_programados" class="mb-1.5 block text-sm font-semibold text-tinta">Revisión de órdenes programadas</label>
                    <input autocomplete="off" type="text" id="cron_programados" wire:model="cron_programados"
                        class="form-input font-mono @error('cron_programados') border-tierra bg-tierra-suave @enderror"
                        placeholder="minuto hora día mes día_semana">
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach (['0 */4 * * *' => 'Cada 4 h', '0 */6 * * *' => 'Cada 6 h', '0 8 * * *' => 'Diario 08:00', '0 8,16 * * *' => '8 y 16 h'] as $cron => $etiqueta)
                            <button type="button" wire:click="setCronProgramados('{{ $cron }}')"
                                class="rounded-sm border border-arena bg-white px-2 py-1 text-xs text-tinta hover:bg-corteza-suave">
                                {{ $etiqueta }}
                            </button>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-tinta-suave">
                        @if ($proximaProgramados)
                            Próxima ejecución: <strong>{{ $proximaProgramados }}</strong>.
                        @endif
                        Marca vencidas y envía el recordatorio diario.
                    </p>
                    @error('cron_programados') <p class="mt-1 text-xs text-tierra">{{ $message }}</p> @enderror
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
