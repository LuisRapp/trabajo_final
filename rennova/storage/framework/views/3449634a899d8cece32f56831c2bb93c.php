<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-list-check"></i> Planificar tareas del Lote #<?php echo e($lote->id_lote); ?></h4>
            <div class="text-muted small">
                Definí qué actividades vas a realizar (ej: 5 ha raleo + 5 ha tala rasa). Esto alimenta el histórico y dispara recomendaciones.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="<?php echo e(route('lotes.index')); ?>">
                <i class="bi bi-arrow-left"></i> Volver a Lotes
            </a>
            <a class="btn btn-outline-primary" href="<?php echo e(route('lotes.recomendaciones', ['loteId' => $lote->id_lote])); ?>">
                <i class="bi bi-magic"></i> Ver recomendaciones
            </a>
        </div>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-tree"></i> Lote</strong>
            <span class="badge bg-secondary"><?php echo e($lote->especie ?? 'Sin especie'); ?></span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Ubicación</div>
                    <div class="fw-semibold"><?php echo e($lote->ubicacion); ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Superficie</div>
                    <div class="fw-semibold"><?php echo e(number_format((float) ($lote->superficie ?? 0), 2)); ?> ha</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Estado</div>
                    <div>
                        <span class="badge bg-<?php echo e($lote->estado === 'en_proceso' ? 'primary' : 'success'); ?>"><?php echo e($lote->estado); ?></span>
                    </div>
                </div>
            </div>

            <hr>

            <?php $__errorArgs = ['tareas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="alert alert-danger"><small><?php echo e($message); ?></small></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 220px;">Tipo de tarea</th>
                            <th style="width: 160px;" class="text-end">Superficie (ha)</th>
                            <th>Observaciones</th>
                            <th style="width: 80px;" class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tareas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <select class="form-select" wire:model.live="tareas.<?php echo e($i); ?>.tipo_tarea" <?php if($guardando): ?> disabled <?php endif; ?>>
                                        <?php $__currentLoopData = $taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tt->value); ?>"><?php echo e($tt->label()); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['tareas.' . $i . '.tipo_tarea'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="form-control text-end" wire:model.live="tareas.<?php echo e($i); ?>.superficie_afectada_ha" placeholder="(opcional)" <?php if($guardando): ?> disabled <?php endif; ?>>
                                    <?php $__errorArgs = ['tareas.' . $i . '.superficie_afectada_ha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </td>
                                <td>
                                    <input type="text" class="form-control" wire:model.live="tareas.<?php echo e($i); ?>.observaciones" placeholder="Opcional" <?php if($guardando): ?> disabled <?php endif; ?>>
                                    <?php $__errorArgs = ['tareas.' . $i . '.observaciones'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-outline-danger btn-sm" type="button" wire:click="removeTareaRow(<?php echo e($i); ?>)" <?php if($guardando): ?> disabled <?php endif; ?>>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <button class="btn btn-outline-secondary" type="button" wire:click="addTareaRow" <?php if($guardando): ?> disabled <?php endif; ?>>
                                        <i class="bi bi-plus"></i> Agregar tarea
                                    </button>
                                    <div class="text-muted small">
                                        Total planificado: <strong><?php echo e(number_format($this->totalSuperficie, 2)); ?> ha</strong>
                                        · Superficie lote: <strong><?php echo e(number_format((float) ($lote->superficie ?? 0), 2)); ?> ha</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-info mb-0">
                <small>
                    Tip: si dejás la superficie en blanco, se asume la del lote al estimar (pero para dividir 5/5 completá superficies).
                </small>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <button class="btn btn-primary" type="button" wire:click="guardar" <?php if($guardando): ?> disabled <?php endif; ?>>
                <i class="bi bi-check2-circle"></i> Guardar y generar recomendaciones
            </button>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/lote-planificacion-tareas.blade.php ENDPATH**/ ?>