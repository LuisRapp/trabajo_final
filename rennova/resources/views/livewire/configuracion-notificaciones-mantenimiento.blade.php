<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <x-ui.card class="overflow-hidden">
        <div class="bg-corteza text-white px-6 py-4">
            <h5 class="text-lg font-semibold flex items-center gap-2">
                <flux:icon.bell class="size-5" />
                Configuracion de Notificaciones de Mantenimiento
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

            <p class="text-tinta-suave mb-4">
                Seleccione que usuarios recibiran notificaciones por email para cada tipo de evento de mantenimiento.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.card class="overflow-hidden border-pino/30">
                    <div class="bg-pino text-white px-6 py-4">
                        <h6 class="font-semibold flex items-center gap-2">
                            <flux:icon.chart-bar class="size-4" />
                            Umbral Alcanzado
                        </h6>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-tinta-suave">
                            Notifica cuando una maquinaria alcanza su umbral de toneladas y se genera una orden automatica.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Usuarios a notificar:</label>
                            @foreach($usuarios as $usuario)
                                <div class="flex items-center gap-2 py-1" wire:key="user-{{ $usuario->id }}">
                                    <input class="rounded border-arena text-pino focus:ring-pino/20" type="checkbox"
                                           wire:model="usuariosUmbral"
                                           value="{{ $usuario->id }}"
                                           id="umbral_{{ $usuario->id }}">
                                    <label class="text-sm text-tinta" for="umbral_{{ $usuario->id }}">
                                        {{ $usuario->name }} <small class="text-tinta-suave">({{ $usuario->email }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-resina/30">
                    <div class="bg-resina text-white px-6 py-4">
                        <h6 class="font-semibold flex items-center gap-2">
                            <flux:icon.archive-box class="size-4" />
                            Stock Insuficiente
                        </h6>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-tinta-suave">
                            Notifica cuando se crea una orden pero faltan insumos en el kit de mantenimiento preventivo.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Usuarios a notificar:</label>
                            @foreach($usuarios as $usuario)
                                <div class="flex items-center gap-2 py-1" wire:key="user-{{ $usuario->id }}">
                                    <input class="rounded border-arena text-pino focus:ring-pino/20" type="checkbox"
                                           wire:model="usuariosStock"
                                           value="{{ $usuario->id }}"
                                           id="stock_{{ $usuario->id }}">
                                    <label class="text-sm text-tinta" for="stock_{{ $usuario->id }}">
                                        {{ $usuario->name }} <small class="text-tinta-suave">({{ $usuario->email }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-pino/30">
                    <div class="bg-pino text-white px-6 py-4">
                        <h6 class="font-semibold flex items-center gap-2">
                            <flux:icon.calendar-date-range class="size-4" />
                            Recordatorio Diario
                        </h6>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-tinta-suave">
                            Notifica diariamente sobre mantenimientos programados para el dia actual que deben confirmarse.
                        </p>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-tinta mb-1.5">Usuarios a notificar:</label>
                            @foreach($usuarios as $usuario)
                                <div class="flex items-center gap-2 py-1" wire:key="user-{{ $usuario->id }}">
                                    <input class="rounded border-arena text-pino focus:ring-pino/20" type="checkbox"
                                           wire:model="usuariosRecordatorio"
                                           value="{{ $usuario->id }}"
                                           id="recordatorio_{{ $usuario->id }}">
                                    <label class="text-sm text-tinta" for="recordatorio_{{ $usuario->id }}">
                                        {{ $usuario->name }} <small class="text-tinta-suave">({{ $usuario->email }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <div class="mt-6">
                <x-ui.button type="button" icon="document-arrow-down" wire:click="guardarConfiguracion" class="w-full justify-center">
                    Guardar Configuracion
                </x-ui.button>
            </div>

            <x-ui.alert variant="info" class="mt-6" :dismissible="false">
                <div>
                    <strong>Nota:</strong> Los comandos que generan estas notificaciones ya estan registrados en <code class="bg-pino-suave px-1 rounded-sm text-xs">routes/console.php</code>:
                    <ul class="mt-2 space-y-1">
                        <li><code class="bg-pino-suave px-1 rounded-sm text-xs">php artisan mantenimiento:check-umbrales</code> - genera la orden automatica y notifica el umbral alcanzado</li>
                        <li><code class="bg-pino-suave px-1 rounded-sm text-xs">php artisan mantenimiento:check-programados</code> - envia los recordatorios diarios y notifica los vencimientos</li>
                    </ul>
                    Para que se ejecuten automaticamente, el cron del servidor debe correr el scheduler de Laravel:
                    <code class="bg-pino-suave px-1 rounded-sm text-xs block mt-2">* * * * * php artisan schedule:run</code>
                </div>
            </x-ui.alert>
        </div>
    </x-ui.card>
</div>
