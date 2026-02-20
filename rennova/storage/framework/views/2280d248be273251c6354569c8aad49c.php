<div class="mx-auto max-w-7xl px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <h1 class="flex items-center gap-2 text-3xl font-bold text-slate-800">
            <i class="bi bi-cash-coin"></i> Adelantos
        </h1>
    </div>

    <!-- Mensaje de éxito -->
    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span class="flex-1 font-medium"><?php echo e(session('message')); ?></span>
            <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Tabs -->
    <div class="mb-6 flex gap-0">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-adelantos', 'editar-adelantos'])): ?>
        <button type="button" wire:click="$set('tab_activo','nuevo')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border border-r-0 rounded-l-lg transition-all <?php echo e($tab_activo === 'nuevo' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'nuevo' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-plus-circle"></i> Nuevo Adelanto
        </button>
        <?php endif; ?>
        <button type="button" wire:click="$set('tab_activo','listado')"
            class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border rounded-r-lg transition-all <?php echo e($tab_activo === 'listado' ? 'text-white' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>"
            style="<?php echo e($tab_activo === 'listado' ? 'background-color: #2d7a4f; border-color: #2d7a4f' : ''); ?>">
            <i class="bi bi-list-ul"></i> Listado de Adelantos
        </button>
    </div>

    <!-- Tab 1: Nuevo Adelanto -->
    <!--[if BLOCK]><![endif]--><?php if($tab_activo === 'nuevo'): ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-adelantos', 'editar-adelantos'])): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800 mb-0">
                    <i class="bi bi-<?php echo e($adelanto_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                    <?php echo e($adelanto_id ? 'Editar Adelanto' : 'Nuevo Adelanto'); ?>

                </h5>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="guardar">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- Empleado -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Empleado <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="id_empleado" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 bg-white transition-colors <?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione...</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ($empleados ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($empleado->id_empleado); ?>"><?php echo e($empleado->apellido); ?>, <?php echo e($empleado->nombre); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Monto -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Monto <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-0">
                                <span class="px-3 py-3 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-slate-600 font-semibold">$</span>
                                <input type="number" wire:model="monto" step="0.1" min="0" class="flex-1 px-4 py-3 border border-default rounded-r-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['monto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['monto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Fecha Adelanto -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Fecha de Adelanto <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="fecha_emision" class="w-full px-4 py-3 border border-default rounded-lg focus:border-green-700 focus:ring-2 focus:ring-green-600 transition-colors <?php $__errorArgs = ['fecha_emision'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> ring-2 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha_emision'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2 justify-end">
                        <!--[if BLOCK]><![endif]--><?php if($adelanto_id): ?>
                            <button type="button" wire:click="resetCampos" class="px-6 py-3 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-adelantos', 'editar-adelantos'])): ?>
                        <button type="submit" class="px-6 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors font-medium">
                            <i class="bi bi-check-circle"></i> <?php echo e($adelanto_id ? 'Actualizar' : 'Guardar'); ?>

                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Tab 2: Listado de Adelantos -->
    <!--[if BLOCK]><![endif]--><?php if($tab_activo === 'listado'): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-100 border-b border-slate-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-slate-800 mb-0">Listado de Adelantos</h5>
            </div>
            <div class="p-6">
                <!-- Buscador -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
                        <i class="bi bi-search text-slate-500"></i>
                        <input type="text" wire:model.live="busqueda" placeholder="Buscar por empleado, monto o fecha..." class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">ID</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Empleado</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Monto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Fecha Adelanto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase text-slate-600">Estado</th>
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase text-slate-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = ($adelantos ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adelanto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo e($adelanto->id_adelanto); ?></span></td>
                                    <td class="px-3 py-3 font-semibold text-slate-800"><?php echo e($adelanto->empleado?->apellido ?? 'N/A'); ?>, <?php echo e($adelanto->empleado?->nombre ?? ''); ?></td>
                                    <td class="px-3 py-3 text-slate-600">$<?php echo e(number_format($adelanto->monto, 2, ',', '.')); ?></td>
                                    <td class="px-3 py-3 text-slate-600"><?php echo e($adelanto->fecha_emision ? \Carbon\Carbon::parse($adelanto->fecha_emision)->format('d/m/Y') : 'N/A'); ?></td>
                                    <td class="px-3 py-3">
                                        <!--[if BLOCK]><![endif]--><?php if($adelanto->activo): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Activo</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Inactivo</span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <div class="flex gap-1 justify-center">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-adelantos')): ?>
                                            <button type="button" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200" wire:click="editar(<?php echo e($adelanto->id_adelanto); ?>)" title="Editar">
                                                <i class="bi bi-pencil text-sm"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-adelantos')): ?>
                                            <button type="button" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200" wire:click="eliminar(<?php echo e($adelanto->id_adelanto); ?>)" onclick="return confirm('¿Está seguro de eliminar este adelanto?')" title="Eliminar">
                                                <i class="bi bi-trash text-sm"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-3 py-8 text-center">
                                        <i class="bi bi-inbox text-slate-300 block mb-2" style="font-size: 2rem;"></i>
                                        <p class="text-slate-500 font-medium">No hay adelantos registrados.</p>
                                    </td>
                                </tr>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </tbody>
                    </table>
                </div>

                <!-- Paginación si existe -->
                <!--[if BLOCK]><![endif]--><?php if($adelantos->hasPages()): ?>
                    <div class="mt-6">
                        <?php echo e($adelantos->links('pagination::tailwind')); ?>

                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('adelantoGuardado', () => {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('tab_activo', 'listado');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/adelantos.blade.php ENDPATH**/ ?>