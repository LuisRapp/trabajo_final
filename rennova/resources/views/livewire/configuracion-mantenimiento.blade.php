<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza text-white px-6 py-4">
            <h5 class="text-lg font-semibold flex items-center gap-2">
                <flux:icon.cog class="size-5" />
                Configuracion de Mantenimiento Automatico
            </h5>
        </div>
        <div class="p-6">
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

            @if (session()->has('command_output'))
                <x-ui.alert variant="info" class="mb-6" :dismissible="false">
                    <strong>Resultado de la ejecucion:</strong>
                    <pre class="mt-2 text-sm whitespace-pre-wrap">{{ session('command_output') }}</pre>
                </x-ui.alert>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.card class="overflow-hidden border-pino/30">
                    <div class="bg-pino text-white px-6 py-4">
                        <h6 class="font-semibold flex items-center gap-2">
                            <flux:icon.clock class="size-4" />
                            Horarios de Ejecucion
                        </h6>
                    </div>
                    <div class="p-6">
                        <form wire:submit.prevent="guardarConfiguracion">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-tinta mb-1.5">
                                    Hora de Recordatorio Diario
                                    <span class="text-tierra">*</span>
                                </label>
                                <input type="time"
                                       wire:model="hora_recordatorio"
                                       class="form-input @error('hora_recordatorio') border-tierra bg-tierra-suave @enderror">
                                @error('hora_recordatorio')
                                    <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <small class="text-tinta-suave text-xs mt-1 block">
                                    Hora en la que se enviaran recordatorios de mantenimientos programados para hoy
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-tinta mb-1.5">
                                    Frecuencia de Verificacion de Umbrales
                                    <span class="text-tierra">*</span>
                                </label>
                                <select wire:model.live="expresion_cron"
                                    class="form-input @error('expresion_cron') border-tierra bg-tierra-suave @enderror"
                                    id="selectFrecuencia">
                                    <option value="*/15 * * * *">Cada 15 minutos</option>
                                    <option value="*/30 * * * *">Cada 30 minutos</option>
                                    <option value="0 * * * *">Cada hora</option>
                                    <option value="0 */2 * * *">Cada 2 horas</option>
                                    <option value="0 */4 * * *">Cada 4 horas</option>
                                    <option value="0 */6 * * *">Cada 6 horas</option>
                                    <option value="0 8,16 * * *">2 veces al dia (8:00 y 16:00)</option>
                                    <option value="0 8 * * *">1 vez al dia (8:00)</option>
                                    <option value="custom">Personalizado...</option>
                                </select>
                                @error('expresion_cron')
                                    <p class="text-tierra text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <small class="text-tinta-suave text-xs mt-1 block">
                                    Con que frecuencia el sistema verifica si las maquinarias alcanzaron su umbral de toneladas
                                </small>
                            </div>

                            <div class="mb-4 hidden" id="customCronDiv">
                                <label class="block text-sm font-semibold text-tinta mb-1.5">Expresion Cron Personalizada</label>
                                <input type="text"
                                       wire:model="expresion_cron"
                                       class="form-input font-mono @error('expresion_cron') border-tierra bg-tierra-suave @enderror"
                                       placeholder="* * * * *"
                                       id="customCronInput">
                                <small class="text-tinta-suave text-xs mt-1 block">
                                    Formato: minuto hora dia mes dia_semana.
                                    <a href="https://crontab.guru" target="_blank" rel="noopener" class="text-pino underline">Ayuda con expresiones cron</a>
                                </small>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const select = document.getElementById('selectFrecuencia');
                                    const customDiv = document.getElementById('customCronDiv');
                                    const customInput = document.getElementById('customCronInput');

                                    function toggleCustom() {
                                        if (select.value === 'custom') {
                                            customDiv.classList.remove('hidden');
                                            customInput.focus();
                                        } else {
                                            customDiv.classList.add('hidden');
                                        }
                                    }

                                    select.addEventListener('change', toggleCustom);

                                    const currentValue = select.value;
                                    const predefinedValues = ['*/15 * * * *', '*/30 * * * *', '0 * * * *', '0 */2 * * *',
                                                               '0 */4 * * *', '0 */6 * * *', '0 8,16 * * *', '0 8 * * *'];
                                    if (!predefinedValues.includes(currentValue) && currentValue !== '') {
                                        select.value = 'custom';
                                        customDiv.classList.remove('hidden');
                                    }
                                });
                            </script>

                            <div>
                                <x-ui.button type="submit" icon="document-arrow-down" class="w-full justify-center">
                                    Guardar Configuracion
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-pino/30">
                    <div class="bg-pino text-white px-6 py-4">
                        <h6 class="font-semibold flex items-center gap-2">
                            <flux:icon.play class="size-4" />
                            Ejecucion Manual
                        </h6>
                    </div>
                    <div class="p-6">
                        <p class="text-tinta-suave">
                            Ejecute manualmente los comandos de verificacion para probar o forzar una ejecucion inmediata.
                        </p>

                        <div class="flex flex-col gap-2 mt-4">
                            <x-ui.button type="button" variant="secondary" icon="chart-bar" wire:click="ejecutarVerificacionUmbrales" wire:loading.attr="disabled" class="justify-center">
                                <span wire:loading.remove wire:target="ejecutarVerificacionUmbrales">
                                    Verificar Umbrales Ahora
                                </span>
                                <span wire:loading wire:target="ejecutarVerificacionUmbrales">
                                    <flux:icon.arrow-path class="size-4 inline animate-spin mr-1" />
                                    Ejecutando...
                                </span>
                            </x-ui.button>

                            <x-ui.button type="button" variant="secondary" icon="calendar-date-range" wire:click="ejecutarVerificacionProgramados" wire:loading.attr="disabled" class="justify-center">
                                <span wire:loading.remove wire:target="ejecutarVerificacionProgramados">
                                    Verificar Programados Ahora
                                </span>
                                <span wire:loading wire:target="ejecutarVerificacionProgramados">
                                    <flux:icon.arrow-path class="size-4 inline animate-spin mr-1" />
                                    Ejecutando...
                                </span>
                            </x-ui.button>
                        </div>

                        <x-ui.alert variant="warning" class="mt-4" :dismissible="false">
                            <small>
                                <strong>Nota:</strong> La ejecucion manual es util para pruebas.
                                Los comandos se ejecutaran normalmente segun la configuracion del cron del servidor.
                            </small>
                        </x-ui.alert>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card class="overflow-hidden border-resina/30 mt-6">
                <div class="bg-resina text-white px-6 py-4">
                    <h6 class="font-semibold flex items-center gap-2">
                        <flux:icon.computer-desktop class="size-4" />
                        Configuracion del Servidor (Cron)
                    </h6>
                </div>
                <div class="p-6">
                    <p class="mb-2 text-tinta">
                        Para que las tareas se ejecuten automaticamente, el servidor debe correr el scheduler de Laravel a traves del cron del sistema:
                    </p>

                    <div class="bg-hueso border border-arena rounded-sm p-4 font-mono text-sm">
                        <code class="block">
                            * * * * * php artisan schedule:run
                        </code>
                    </div>

                    <p class="mt-4 text-tinta-suave">
                        <flux:icon.information-circle class="size-4 inline mr-1" />
                        Laravel 12 no incluye el Kernel.php de consola: las tareas programadas se registran en <code class="bg-corteza-suave px-1 rounded-sm text-xs">routes/console.php</code>.
                        En este proyecto ya estan registrados los siguientes comandos:
                    </p>

                    <ul class="mt-3 space-y-2 text-sm">
                        <li class="flex items-start gap-2">
                            <flux:icon.chart-bar class="size-4 text-pino mt-0.5 shrink-0" />
                            <span>
                                <code class="bg-corteza-suave px-1 rounded-sm text-xs">php artisan mantenimiento:check-umbrales</code>
                                — verifica umbrales de toneladas, programa por clima y genera la orden de mantenimiento con personal asignado.
                            </span>
                        </li>
                        <li class="flex items-start gap-2">
                            <flux:icon.calendar-date-range class="size-4 text-pino mt-0.5 shrink-0" />
                            <span>
                                <code class="bg-corteza-suave px-1 rounded-sm text-xs">php artisan mantenimiento:check-programados</code>
                                — envia recordatorios de los mantenimientos del dia y marca como vencidos los no confirmados.
                            </span>
                        </li>
                    </ul>

                    <p class="mt-4 text-tinta-suave">
                        <flux:icon.information-circle class="size-4 inline mr-1" />
                        <strong>Tip:</strong> Para activar el scheduler agregue la linea <code class="bg-corteza-suave px-1 rounded-sm text-xs">* * * * * php artisan schedule:run</code>
                        al <code class="bg-corteza-suave px-1 rounded-sm text-xs">crontab</code> del servidor (comando <code class="bg-corteza-suave px-1 rounded-sm text-xs">crontab -e</code>).
                        Laravel ejecuta cada tarea segun la frecuencia definida en <code class="bg-corteza-suave px-1 rounded-sm text-xs">routes/console.php</code>.
                        Para cambiar la frecuencia, ajuste el registro de la tarea en ese archivo.
                    </p>
                </div>
            </x-ui.card>
        </div>
    </x-ui.card>
</div>
