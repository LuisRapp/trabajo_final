<!-- Listado de Partes Diarios Registrados -->
<div id="listado-partes" role="tabpanel" aria-labelledby="listado-tab" class="tab-pane-content">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-200">
        <div class="bg-slate-100 px-6 py-4 border-b border-slate-200">
            <h5 class="text-lg font-semibold text-slate-900 mb-0">Partes Diarios Registrados</h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar por Propietario</label>
                    <input type="text" wire:model.live.debounce.400ms="busqueda" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none" placeholder="Ej: Juan Pérez...">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar por Fecha</label>
                    <input type="date" wire:model.live="busqueda_fecha" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 focus:outline-none">
                </div>
            </div>
            
            <?php if($partes && count($partes) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead class="bg-slate-100 border-b border-slate-300">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">ID</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Lote</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Fecha</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Tipo</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-900">Observaciones</th>
                                <th class="px-4 py-2 text-center font-semibold text-slate-900">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $partes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-slate-200 hover:bg-slate-50" wire:key="row-<?php echo e($parte->id_parte_diario); ?>">
                                    <td class="px-4 py-2"><span class="inline-block px-3 py-1 bg-slate-200 text-slate-800 text-xs font-medium rounded">#<?php echo e($parte->id_parte_diario); ?></span></td>
                                    <td class="px-4 py-2"><?php echo e($parte->lote?->propietario ?? '-'); ?></td>
                                    <td class="px-4 py-2"><?php echo e($parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : '-'); ?></td>
                                    <td class="px-4 py-2">
                                        <?php if($parte->es_dia_caido): ?>
                                            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded">📅 Día Caído</span>
                                        <?php else: ?>
                                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">🚛 Producción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2"><small><?php echo e($parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 40) : '-'); ?></small></td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="inline-flex gap-2">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-partes-diarios')): ?>
                                            <button type="button" class="px-3 py-1 border border-blue-500 text-blue-600 rounded text-sm hover:bg-blue-50 transition-colors" wire:click.prevent="editar(<?php echo e($parte->id_parte_diario); ?>)" title="Editar">
                                                ✏️
                                            </button>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-partes-diarios')): ?>
                                            <button type="button" class="px-3 py-1 border border-red-500 text-red-600 rounded text-sm hover:bg-red-50 transition-colors" wire:click.prevent="eliminar(<?php echo e($parte->id_parte_diario); ?>)" wire:confirm="¿Está seguro de eliminar este parte diario?" title="Eliminar">
                                                🗑️
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php if(isset($partes)): ?>
                    <div class="mt-6">
                        <?php echo e($partes->links('pagination::tailwind')); ?>

                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-12">
                    <span class="text-6xl text-slate-300 block mb-4">📭</span>
                    <p class="text-slate-600">No hay partes diarios registrados.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/partials/partes-listado.blade.php ENDPATH**/ ?>