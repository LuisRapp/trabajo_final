<div class="w-full px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-tinta">
            <flux:icon.bell class="size-6 text-pino" />
            Notificaciones de mantenimiento
        </h1>
        <p class="mt-1 text-sm text-tinta-suave">
            Elegí qué usuario recibe cada tipo de aviso. Una misma persona puede recibir varios.
        </p>
    </div>

    @if (session()->has('message'))
        <x-ui.alert variant="success" class="mb-4">{{ session('message') }}</x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="danger" class="mb-4" :dismissible="false">{{ session('error') }}</x-ui.alert>
    @endif

    <form autocomplete="off" wire:submit.prevent="guardarConfiguracion">
        <x-ui.card class="overflow-hidden">
            @if ($usuarios->isEmpty())
                <div class="px-5 py-6 text-sm text-tinta-suave">
                    No hay usuarios cargados para configurar avisos.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-corteza-suave text-left text-xs uppercase tracking-wide text-tinta-suave">
                                <th class="px-4 py-3 font-semibold">Usuario</th>
                                <th class="px-4 py-3 text-center font-semibold" title="Cuando una máquina alcanza el umbral de toneladas y se genera la orden.">
                                    Umbral
                                </th>
                                <th class="px-4 py-3 text-center font-semibold" title="Cuando falta stock del kit preventivo al crear la orden.">
                                    Stock
                                </th>
                                <th class="px-4 py-3 text-center font-semibold" title="Recordatorio diario de las órdenes programadas para hoy.">
                                    Recordatorio
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-arena">
                            @foreach ($usuarios as $usuario)
                                <tr class="hover:bg-corteza-suave/60" wire:key="usuario-{{ $usuario->id }}">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-tinta">{{ $usuario->name }}</div>
                                        <div class="text-xs text-tinta-suave">{{ $usuario->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input autocomplete="off" type="checkbox" class="rounded border-arena text-pino focus:ring-pino/20"
                                            wire:model="usuariosUmbral" value="{{ $usuario->id }}"
                                            aria-label="Umbral para {{ $usuario->name }}">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input autocomplete="off" type="checkbox" class="rounded border-arena text-pino focus:ring-pino/20"
                                            wire:model="usuariosStock" value="{{ $usuario->id }}"
                                            aria-label="Stock para {{ $usuario->name }}">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input autocomplete="off" type="checkbox" class="rounded border-arena text-pino focus:ring-pino/20"
                                            wire:model="usuariosRecordatorio" value="{{ $usuario->id }}"
                                            aria-label="Recordatorio para {{ $usuario->name }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

        <div class="mt-4 flex justify-end">
            <x-ui.button type="submit" icon="check">Guardar configuración</x-ui.button>
        </div>
    </form>
</div>
