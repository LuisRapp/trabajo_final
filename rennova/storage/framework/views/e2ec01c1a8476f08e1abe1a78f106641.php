<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">
            📅 Programar Mantenimiento
        </h1>
    </div>

    <?php if(session()->has('success')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
            <span class="text-emerald-600">✓</span>
            <span class="flex-1 text-sm font-medium"><?php echo e(session('success')); ?></span>
            <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
        </div>
    <?php endif; ?>
    <?php if(session()->has('error')): ?>
        <div x-data="{ open: true }" x-show="open" x-transition
            class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
            <span class="text-red-600">⚠</span>
            <span class="flex-1 text-sm font-medium"><?php echo e(session('error')); ?></span>
            <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="flex items-start gap-3 bg-cyan-50 border border-cyan-200 text-cyan-800 rounded-xl px-5 py-3 text-sm">
                    <span class="text-2xl">ℹ️</span>
                    <div>
                        <strong><?php echo e($notificacion->titulo); ?></strong>
                        <p class="mt-1 text-xs"><?php echo e($notificacion->mensaje); ?></p>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-lg p-4">
                    <h6 class="text-brand font-semibold mb-2">
                        🔧 Detalles del Mantenimiento
                    </h6>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="col-span-2">
                            <small class="text-slate-500">Maquinaria:</small>
                            <div class="font-semibold"><?php echo e($mantenimiento->maquinaria->nombre ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <small class="text-slate-500">Tipo:</small>
                            <div class="font-semibold"><?php echo e($mantenimiento->tipoMantenimiento->nombre ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <small class="text-slate-500">Estado:</small>
                            <div>
                                <?php if($mantenimiento->estado === 'programado'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-700">Programado</span>
                                <?php elseif($mantenimiento->estado === 'en curso'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">En Curso</span>
                                <?php elseif($mantenimiento->estado === 'completado'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Completado</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?php echo e(ucfirst($mantenimiento->estado)); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-span-2">
                            <small class="text-slate-500">Fecha de Inicio:</small>
                            <div class="font-semibold"><?php echo e(\Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y')); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="border-slate-200 my-6">
            <form wire:submit.prevent="guardarFecha">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="fechaProgramada" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            📅 Fecha Programada <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="fechaProgramada"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm transition-colors <?php $__errorArgs = ['fechaProgramada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php else: ?> border-slate-300 focus:border-brand focus:ring-2 focus:ring-brand/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            wire:model="fechaProgramada"
                            min="<?php echo e($fechaMinima); ?>"
                            max="<?php echo e($fechaMaxima); ?>"
                        >
                        <?php $__errorArgs = ['fechaProgramada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-600 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-slate-500 text-xs mt-1 block">
                            ℹ️
                            La fecha debe estar dentro del rango permitido:
                            <strong><?php echo e(\Carbon\Carbon::parse($fechaMinima)->format('d/m/Y')); ?></strong>
                            a
                            <strong><?php echo e(\Carbon\Carbon::parse($fechaMaxima)->format('d/m/Y')); ?></strong>
                            (7 días desde la notificación)
                        </small>
                    </div>
                </div>
                <div class="flex gap-2 justify-end">
                    <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-slate-300 bg-white text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                        ← Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-brand hover:bg-brand-hover text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                        ✓ Confirmar y Programar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/programar-mantenimiento.blade.php ENDPATH**/ ?>