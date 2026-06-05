<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-600 text-white px-6 py-4">
            <h5 class="text-lg font-semibold">
                ⚙️ Configuración de Mantenimiento Automático
            </h5>
        </div>
        <div class="p-6">
            @if (session()->has('message'))
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
                    <span class="text-emerald-600">✓</span>
                    <span class="flex-1 text-sm font-medium">{{ session('message') }}</span>
                    <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
                </div>
            @endif

            @if (session()->has('error'))
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
                    <span class="text-red-600">⚠</span>
                    <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
                    <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
                </div>
            @endif

            @if (session()->has('command_output'))
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="mb-6 flex items-start gap-3 rounded-xl border border-cyan-200 bg-cyan-50 px-5 py-3 text-cyan-800 shadow-sm" role="alert">
                    <span class="text-cyan-600">ℹ️</span>
                    <div class="flex-1">
                        <strong>Resultado de la ejecución:</strong>
                        <pre class="mt-2 text-sm whitespace-pre-wrap">{{ session('command_output') }}</pre>
                    </div>
                    <button type="button" class="text-cyan-600 hover:text-cyan-800" @click="open = false">✕</button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Configuración de Horarios -->
                <div class="bg-white rounded-xl shadow-sm border border-brand/30 overflow-hidden">
                    <div class="bg-brand text-white px-6 py-4">
                        <h6 class="font-semibold">🕐 Horarios de Ejecución</h6>
                    </div>
                    <div class="p-6">
                        <form wire:submit.prevent="guardarConfiguracion">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Hora de Recordatorio Diario
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="time"
                                       wire:model="hora_recordatorio"
                                       class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('hora_recordatorio') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror">
                                @error('hora_recordatorio')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <small class="text-slate-500 text-xs mt-1 block">
                                    Hora en la que se enviarán recordatorios de mantenimientos programados para hoy
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Frecuencia de Verificación de Umbrales
                                    <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="expresion_cron"
                                    class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors @error('expresion_cron') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                    id="selectFrecuencia">
                                    <option value="*/15 * * * *">Cada 15 minutos</option>
                                    <option value="*/30 * * * *">Cada 30 minutos</option>
                                    <option value="0 * * * *">Cada hora</option>
                                    <option value="0 */2 * * *">Cada 2 horas</option>
                                    <option value="0 */4 * * *">Cada 4 horas</option>
                                    <option value="0 */6 * * *">Cada 6 horas</option>
                                    <option value="0 8,16 * * *">2 veces al día (8:00 y 16:00)</option>
                                    <option value="0 8 * * *">1 vez al día (8:00)</option>
                                    <option value="custom">Personalizado...</option>
                                </select>
                                @error('expresion_cron')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <small class="text-slate-500 text-xs mt-1 block">
                                    Con qué frecuencia el sistema verifica si las maquinarias alcanzaron su umbral de toneladas
                                </small>
                            </div>

                            <div class="mb-4 hidden" id="customCronDiv">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Expresión Cron Personalizada</label>
                                <input type="text"
                                       wire:model="expresion_cron"
                                       class="w-full px-4 py-2.5 border rounded-lg text-sm font-mono transition-colors @error('expresion_cron') border-red-400 bg-red-50 @else border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 @enderror"
                                       placeholder="* * * * *"
                                       id="customCronInput">
                                <small class="text-slate-500 text-xs mt-1 block">
                                    Formato: minuto hora día mes día_semana.
                                    <a href="https://crontab.guru" target="_blank" rel="noopener" class="text-brand underline">Ayuda con expresiones cron</a>
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

                                    // Verificar al cargar si es custom
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
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors w-full justify-center">
                                    💾 Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Ejecución Manual -->
                <div class="bg-white rounded-xl shadow-sm border border-cyan-300 overflow-hidden">
                    <div class="bg-cyan-600 text-white px-6 py-4">
                        <h6 class="font-semibold">▶️ Ejecución Manual</h6>
                    </div>
                    <div class="p-6">
                        <p class="text-slate-500">
                            Ejecute manualmente los comandos de verificación para probar o forzar una ejecución inmediata.
                        </p>

                        <div class="flex flex-col gap-2 mt-4">
                            <button type="button"
                                    wire:click="ejecutarVerificacionUmbrales"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 border border-brand bg-white text-brand rounded-lg text-sm font-medium hover:bg-brand/5 transition-colors justify-center"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="ejecutarVerificacionUmbrales">
                                    📊 Verificar Umbrales Ahora
                                </span>
                                <span wire:loading wire:target="ejecutarVerificacionUmbrales">
                                    <span class="animate-spin inline-block w-4 h-4 border-2 border-brand border-t-transparent rounded-full" role="status"></span>
                                    Ejecutando...
                                </span>
                            </button>

                            <button type="button"
                                    wire:click="ejecutarVerificacionProgramados"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 border border-cyan-600 bg-white text-cyan-600 rounded-lg text-sm font-medium hover:bg-cyan-50 transition-colors justify-center"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="ejecutarVerificacionProgramados">
                                    📅 Verificar Programados Ahora
                                </span>
                                <span wire:loading wire:target="ejecutarVerificacionProgramados">
                                    <span class="animate-spin inline-block w-4 h-4 border-2 border-cyan-600 border-t-transparent rounded-full" role="status"></span>
                                    Ejecutando...
                                </span>
                            </button>
                        </div>

                        <div class="mt-4 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-5 py-3 text-sm">
                            <span>ℹ️</span>
                            <small>
                                <strong>Nota:</strong> La ejecución manual es útil para pruebas.
                                Los comandos se ejecutarán normalmente según la configuración del cron del servidor.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instrucciones de Configuración del Servidor -->
            <div class="bg-white rounded-xl shadow-sm border border-amber-300 overflow-hidden mt-6">
                <div class="bg-amber-500 text-white px-6 py-4">
                    <h6 class="font-semibold">🖥️ Configuración del Servidor (Cron)</h6>
                </div>
                <div class="p-6">
                    <p class="mb-2 text-slate-700">
                        Para que los comandos se ejecuten automáticamente, configure el cron del servidor con los siguientes comandos:
                    </p>

                    <div class="bg-slate-100 border border-slate-300 rounded-lg p-4 font-mono text-sm">
                        <code class="block mb-2">
                            # Verificación de umbrales (según frecuencia configurada: <strong>{{ $expresion_cron }}</strong>)<br>
                            {{ $expresion_cron }} cd /ruta/al/proyecto && php artisan mantenimiento:check-umbrales
                        </code>
                        <code class="block">
                            # Recordatorio diario (a las <strong>{{ $hora_recordatorio }}</strong>)<br>
                            @php
                                [$hora, $minuto] = explode(':', $hora_recordatorio);
                            @endphp
                            {{ $minuto }} {{ $hora }} * * * cd /ruta/al/proyecto && php artisan mantenimiento:check-programados
                        </code>
                    </div>

                    <p class="mt-4 text-slate-500">
                        💡
                        <strong>Tip:</strong> En producción, agregue estos comandos al archivo <code class="bg-slate-200 px-1 rounded text-xs">crontab</code> del servidor
                        con el comando <code class="bg-slate-200 px-1 rounded text-xs">crontab -e</code>. En desarrollo con Laravel Sail o similar,
                        puede configurar el scheduler en <code class="bg-slate-200 px-1 rounded text-xs">app/Console/Kernel.php</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
