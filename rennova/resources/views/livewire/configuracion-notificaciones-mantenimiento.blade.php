<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-600 text-white px-6 py-4">
            <h5 class="text-lg font-semibold">
                🔔 Configuración de Notificaciones de Mantenimiento
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

            <p class="text-slate-500 mb-4">
                Seleccione qué usuarios recibirán notificaciones por email para cada tipo de evento de mantenimiento.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Notificaciones de Umbral Alcanzado -->
                <div class="bg-white rounded-xl shadow-sm border border-brand/30 overflow-hidden">
                    <div class="bg-brand text-white px-6 py-4">
                        <h6 class="font-semibold">📊 Umbral Alcanzado</h6>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-500">
                            Notifica cuando una maquinaria alcanza su umbral de toneladas y se genera una orden automática.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Usuarios a notificar:</label>
                            @foreach($usuarios as $usuario)
                                <div class="flex items-center gap-2 py-1" wire:key="user-{{ $usuario->id }}">
                                    <input class="rounded border-slate-300 text-brand focus:ring-brand/20" type="checkbox"
                                           wire:model="usuariosUmbral"
                                           value="{{ $usuario->id }}"
                                           id="umbral_{{ $usuario->id }}">
                                    <label class="text-sm text-slate-700" for="umbral_{{ $usuario->id }}">
                                        {{ $usuario->name }} <small class="text-slate-500">({{ $usuario->email }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Notificaciones de Stock Insuficiente -->
                <div class="bg-white rounded-xl shadow-sm border border-amber-300 overflow-hidden">
                    <div class="bg-amber-500 text-white px-6 py-4">
                        <h6 class="font-semibold">📦 Stock Insuficiente</h6>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-500">
                            Notifica cuando se crea una orden pero faltan insumos en el kit de mantenimiento preventivo.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Usuarios a notificar:</label>
                            @foreach($usuarios as $usuario)
                                <div class="flex items-center gap-2 py-1" wire:key="user-{{ $usuario->id }}">
                                    <input class="rounded border-slate-300 text-brand focus:ring-brand/20" type="checkbox"
                                           wire:model="usuariosStock"
                                           value="{{ $usuario->id }}"
                                           id="stock_{{ $usuario->id }}">
                                    <label class="text-sm text-slate-700" for="stock_{{ $usuario->id }}">
                                        {{ $usuario->name }} <small class="text-slate-500">({{ $usuario->email }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Notificaciones de Recordatorio -->
                <div class="bg-white rounded-xl shadow-sm border border-cyan-300 overflow-hidden">
                    <div class="bg-cyan-600 text-white px-6 py-4">
                        <h6 class="font-semibold">📅 Recordatorio Diario</h6>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-slate-500">
                            Notifica diariamente sobre mantenimientos programados para el día actual que deben confirmarse.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Usuarios a notificar:</label>
                            @foreach($usuarios as $usuario)
                                <div class="flex items-center gap-2 py-1" wire:key="user-{{ $usuario->id }}">
                                    <input class="rounded border-slate-300 text-brand focus:ring-brand/20" type="checkbox"
                                           wire:model="usuariosRecordatorio"
                                           value="{{ $usuario->id }}"
                                           id="recordatorio_{{ $usuario->id }}">
                                    <label class="text-sm text-slate-700" for="recordatorio_{{ $usuario->id }}">
                                        {{ $usuario->name }} <small class="text-slate-500">({{ $usuario->email }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button wire:click="guardarConfiguracion"
                    class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors w-full justify-center">
                    💾 Guardar Configuración
                </button>
            </div>

            <div class="mt-6 flex items-start gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                <span>ℹ️</span>
                <div>
                    <strong>Nota:</strong> Los comandos programados deben estar configurados en el cron del servidor:
                    <ul class="mt-2 space-y-1">
                        <li><code class="bg-cyan-100 px-1 rounded text-xs">php artisan mantenimiento:check-umbrales</code> - Verificación de umbrales (ejecutar periódicamente)</li>
                        <li><code class="bg-cyan-100 px-1 rounded text-xs">php artisan mantenimiento:check-programados</code> - Verificación diaria (ejecutar 1 vez al día)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
