<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-tags"></i> Lista de Precios por Cliente</h1>
    </div>

    <?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="preciosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lista-precios', 'editar-lista-precios'])): ?>
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-precio" type="button" role="tab" aria-controls="nuevo-precio" aria-selected="false">
                <i class="bi bi-plus-circle"></i> Nuevo Precio
            </button>
            <?php endif; ?>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-precios" type="button" role="tab" aria-controls="listado-precios" aria-selected="true">
                <i class="bi bi-list-ul"></i> Listado de Precios
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="preciosTabContent">
        <!-- Pestaña 1: Nuevo Precio (Formulario) -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lista-precios', 'editar-lista-precios'])): ?>
        <div class="tab-pane fade" id="nuevo-precio" role="tabpanel" aria-labelledby="nuevo-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo e($precio_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($precio_id ? 'Modificar Precio' : 'Nuevo Precio'); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardar">
                        <!-- Fila 1: Cliente, Categoría, Precio (3 columnas) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                                <select wire:model="cliente_id" class="form-select <?php $__errorArgs = ['cliente_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione un cliente...</option>
                                    <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cliente->id_cliente); ?>" wire:key="option-<?php echo e($cliente->id_cliente); ?>"><?php echo e($cliente->razon_social); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['cliente_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Categoría de Madera <span class="text-danger">*</span></label>
                                <select wire:model="categoria_id" class="form-select <?php $__errorArgs = ['categoria_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione una categoría...</option>
                                    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($categoria->id_categoria_madera); ?>" wire:key="option-<?php echo e($categoria->id_categoria_madera); ?>"><?php echo e($categoria->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['categoria_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Precio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" wire:model="precio" step="0.1" min="0" class="form-control <?php $__errorArgs = ['precio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                    <?php $__errorArgs = ['precio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 2: Vigencia Desde y Vigencia Hasta -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Vigencia Desde <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fecha_desde" class="form-control <?php $__errorArgs = ['fecha_desde'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_desde'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-muted">Fecha desde la cual este precio entra en vigencia</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Vigencia Hasta</label>
                                <input type="date" wire:model="fecha_hasta" class="form-control <?php $__errorArgs = ['fecha_hasta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_hasta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="text-muted">Dejar en blanco si es el precio actualmente vigente</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <?php if($precio_id): ?>
                                <button type="button" wire:click="resetCampos" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['crear-lista-precios', 'editar-lista-precios'])): ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> <?php echo e($precio_id ? 'Actualizar' : 'Guardar'); ?>

                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pestaña 2: Listado de Precios (Tabla) -->
        <div class="tab-pane fade show active" id="listado-precios" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Lista de Precios</h5>
                    <div class="d-flex gap-3 align-items-center">
                        <!-- Toggle para mostrar histórico -->
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="mostrarHistorico" wire:model.live="mostrar_historico">
                            <label class="form-check-label" for="mostrarHistorico">
                                <i class="bi bi-clock-history"></i> Incluir Histórico
                            </label>
                        </div>
                        <!-- Campo de búsqueda -->
                        <div style="min-width: 300px;">
                            <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por cliente, categoría o precio...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(count($precios) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Vigencia Desde</th>
                                        <th>Vigencia Hasta</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $precios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $precioItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="<?php echo e($precioItem->fecha_hasta ? 'table-secondary' : ''); ?>" wire:key="row-<?php echo e($precioItem->id); ?>">
                                            <td><span class="badge bg-secondary"><?php echo e($index + 1); ?></span></td>
                                            <td><strong><?php echo e($precioItem->cliente->razon_social); ?></strong></td>
                                            <td><span class="badge bg-info"><?php echo e($precioItem->categoria->nombre); ?></span></td>
                                            <td class="fw-bold text-success fs-5">$<?php echo e(number_format($precioItem->precio, 2)); ?></td>
                                            <td>
                                                <i class="bi bi-calendar-check text-primary"></i> 
                                                <?php echo e($precioItem->fecha_desde->format('d/m/Y')); ?>

                                            </td>
                                            <td>
                                                <?php if($precioItem->fecha_hasta): ?>
                                                    <i class="bi bi-calendar-x text-danger"></i> 
                                                    <?php echo e($precioItem->fecha_hasta->format('d/m/Y')); ?>

                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if(!$precioItem->fecha_hasta): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Vigente
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-archive"></i> Histórico
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('editar-lista-precios')): ?>
                                                    <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($precioItem->id); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('eliminar-lista-precios')): ?>
                                                    <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($precioItem->id); ?>)" onclick="return confirm('¿Está seguro de eliminar este precio? Esta acción no se puede deshacer.')" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> 
                            <?php if($busqueda): ?>
                                No hay precios que coincidan con la búsqueda "<?php echo e($busqueda); ?>".
                            <?php elseif(!$mostrar_historico): ?>
                                No hay precios vigentes registrados. Active "Incluir Histórico" para ver todos los registros.
                            <?php else: ?>
                                No hay precios registrados. Cree el primer precio desde la pestaña "Nuevo Precio".
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Función para cambiar a la pestaña de formulario al editar
        function cambiarAPestanaFormulario() {
            var tab = new bootstrap.Tab(document.querySelector('#nuevo-tab'));
            tab.show();
        }

        // Listener para cambiar a pestaña de listado después de guardar
        window.addEventListener('precioGuardado', event => {
            var tab = new bootstrap.Tab(document.querySelector('#listado-tab'));
            tab.show();
        });
    </script>
    <?php $__env->stopPush(); ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/lista-precios.blade.php ENDPATH**/ ?>