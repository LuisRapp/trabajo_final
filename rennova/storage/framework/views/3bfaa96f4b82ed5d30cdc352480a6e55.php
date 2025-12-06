<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="bi bi-clipboard-check"></i> Partes Diarios</h1>
    </div>

    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Pestañas (Tabs) -->
    <ul class="nav nav-tabs mb-4" id="partesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-parte" type="button" role="tab" aria-controls="nuevo-parte" aria-selected="false">
                <i class="bi bi-plus-circle"></i> Nuevo Parte Diario
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-partes" type="button" role="tab" aria-controls="listado-partes" aria-selected="true">
                <i class="bi bi-list-ul"></i> Listado de Partes Diarios
            </button>
        </li>
    </ul>

    <div class="tab-content" id="partesTabContent">
        <!-- Pestaña 1: Nuevo Parte Diario -->
        <div class="tab-pane fade" id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab">
            
            <!-- SECCIÓN 1: Datos Maestros -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo e($parte_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($parte_id ? 'Modificar Parte Diario' : 'Nuevo Parte Diario'); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <!-- Fila 1: Lote, Fecha, Día Caído -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                            <select wire:model.live="id_lote" class="form-select <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione un lote...</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lote->id_lote); ?>"><?php echo e($lote->propietario); ?> - <?php echo e($lote->ubicacion); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            <div wire:loading wire:target="id_lote" class="text-muted small mt-1">
                                <i class="bi bi-arrow-repeat"></i> Cargando maquinarias y empleados...
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" 
                                   wire:model="fecha" 
                                   max="<?php echo e(date('Y-m-d')); ?>" 
                                   min="<?php echo e(date('Y-m-d', strtotime('-7 days'))); ?>"
                                   class="form-control <?php $__errorArgs = ['fecha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['fecha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Día Caído</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="diaCaidoSwitch" wire:model.live="es_dia_caido">
                                <label class="form-check-label" for="diaCaidoSwitch">
                                    <span class="badge <?php echo e($es_dia_caido ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                        <?php echo e($es_dia_caido ? 'SÍ - Jornal' : 'NO - Destajo'); ?>

                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Observaciones -->
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea wire:model="observaciones" class="form-control" rows="2" placeholder="Observaciones adicionales"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: Registro de Producción (Si NO es día caído) -->
            <!--[if BLOCK]><![endif]--><?php if(!$es_dia_caido): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Registro de Producción</h5>
                    </div>
                    <div class="card-body">
                        <!-- Errores generales de validación de carga -->
                        <!--[if BLOCK]><![endif]--><?php if($errors->has('carga_id_categoria_madera') || $errors->has('carga_ticket') || $errors->has('carga_peso_bruto') || $errors->has('carga_tara') || $errors->has('carga_peso_neto') || $errors->has('carga_id_chofer') || $errors->has('carga_destino') || $errors->has('carga_empleados') || $errors->has('carga_maquinarias')): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Errores en la carga:</strong>
                                <ul class="mb-0 mt-2">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_peso_neto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </ul>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!-- Formulario agregar carga -->
                        <div class="border rounded p-3 mb-3 bg-light" x-data="{
                            bruto: <?php if ((object) ('carga_peso_bruto') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('carga_peso_bruto'->value()); ?>')<?php echo e('carga_peso_bruto'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('carga_peso_bruto'); ?>')<?php endif; ?>.live,
                            tara: <?php if ((object) ('carga_tara') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('carga_tara'->value()); ?>')<?php echo e('carga_tara'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('carga_tara'); ?>')<?php endif; ?>.live,
                            get neto() {
                                return (parseFloat(this.bruto) || 0) - (parseFloat(this.tara) || 0);
                            }
                        }" x-init="$watch('neto', value => $wire.set('carga_peso_neto', value))">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle-fill"></i> Registrar Carga</h6>
                            
                            <!-- Fila 1: Categoría, Maquinaria -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                    <select wire:model="carga_id_categoria_madera" class="form-select <?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">Seleccione...</option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->categoriasMadera; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cat->id_categoria_madera); ?>"><?php echo e($cat->nombre); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Maquinarias <span class="text-danger">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm"></span> Cargando maquinarias...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border rounded p-2 <?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 120px; overflow-y: auto;">
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->maquinariasFiltrada; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="<?php echo e($maq->id_maquinaria); ?>" id="maq-<?php echo e($maq->id_maquinaria); ?>" wire:model="carga_maquinarias">
                                                    <label class="form-check-label" for="maq-<?php echo e($maq->id_maquinaria); ?>">
                                                        <?php echo e($maq->modelo); ?> - <small class="text-muted"><?php echo e($maq->tipoMaquinaria->nombre ?? 'Sin tipo'); ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <div class="text-muted small p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver maquinarias disponibles
                                                </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            </div>

                            <!-- Fila 2: Chofer y Cliente con búsqueda -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chofer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_chofer" class="form-control mb-1 <?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Buscar chofer...">
                                    <select wire:model="carga_id_chofer" class="form-select" size="3">
                                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->choferesFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chofer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($chofer->id_chofer); ?>"><?php echo e($chofer->apellido); ?>, <?php echo e($chofer->nombre); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="" disabled>No hay resultados</option>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Destino (Cliente) <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live.debounce.500ms="busqueda_cliente" class="form-control mb-1 <?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Buscar cliente...">
                                    <select wire:model="carga_destino" class="form-select" size="3">
                                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->clientesFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($cliente->id_cliente); ?>"><?php echo e($cliente->razon_social); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="" disabled>No hay resultados</option>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>

                            <!-- Fila 3: Pesajes con cálculo reactivo Alpine -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Ticket <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="carga_ticket" class="form-control <?php $__errorArgs = ['carga_ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="TKT-12345">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_ticket'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Bruto (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" x-model.number="bruto" step="0.1" min="0" class="form-control <?php $__errorArgs = ['carga_peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_peso_bruto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tara (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" x-model.number="tara" step="0.1" min="0" class="form-control <?php $__errorArgs = ['carga_tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_tara'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Neto (Ton) <span class="text-info">Calculado</span></label>
                                    <input type="text" x-text="neto.toFixed(2)" class="form-control bg-light" readonly>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_peso_neto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback d-block"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>

                            <!-- Fila 4: Empleados -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Empleados <span class="text-danger">*</span></label>
                                    <div wire:loading wire:target="id_lote" class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm"></span> Cargando empleados...
                                    </div>
                                    <div wire:loading.remove wire:target="id_lote">
                                        <div class="border rounded p-2 <?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 150px; overflow-y: auto;">
                                            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="<?php echo e($emp->id_empleado); ?>" id="emp-<?php echo e($emp->id_empleado); ?>" wire:model="carga_empleados">
                                                    <label class="form-check-label" for="emp-<?php echo e($emp->id_empleado); ?>">
                                                        <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <small class="text-muted"><?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <div class="text-muted small p-2">
                                                    <i class="bi bi-info-circle"></i> Seleccione un lote para ver empleados disponibles
                                                </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" wire:click.prevent="agregarCarga" class="btn btn-primary" wire:loading.attr="disabled" wire:target="agregarCarga">
                                    <span wire:loading.remove wire:target="agregarCarga"><i class="bi bi-plus-circle"></i> Agregar Carga</span>
                                    <span wire:loading wire:target="agregarCarga"><span class="spinner-border spinner-border-sm"></span> Agregando...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Listado cargas -->
                        <!--[if BLOCK]><![endif]--><?php if(count($cargas) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Categoría</th>
                                            <th>Neto (Ton)</th>
                                            <th>Chofer</th>
                                            <th>Destino</th>
                                            <th>Empleados</th>
                                            <th>Maquinarias</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cargas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $carga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?php echo e($carga['ticket']); ?></span></td>
                                                <td>
                                                    <?php
                                                        $cat = $this->categoriasMadera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                                    ?>
                                                    <?php echo e($cat->nombre ?? '-'); ?>

                                                </td>
                                                <td><strong><?php echo e(number_format($carga['peso_neto'], 2)); ?></strong></td>
                                                <td>
                                                    <?php
                                                        $chofer = $this->choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                                    ?>
                                                    <?php echo e($chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-'); ?>

                                                </td>
                                                <td><?php echo e($carga['destino_nombre'] ?? '-'); ?></td>
                                                <td><small><?php echo e(count($carga['empleados'] ?? [])); ?> emp</small></td>
                                                <td><small><?php echo e(count($carga['maquinarias'] ?? [])); ?> maq</small></td>
                                                <td class="text-center">
                                                    <button type="button" wire:click.prevent="eliminarCarga(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total:</td>
                                            <td class="text-center fw-bold text-primary"><?php echo e(number_format($total_toneladas, 2)); ?> Ton</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Sin cargas registradas. Agregue al menos una carga.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!-- SECCIÓN 3: Jornales (Si ES día caído) -->
            <!--[if BLOCK]><![endif]--><?php if($es_dia_caido): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Asignación de Jornales</h5>
                    </div>
                    <div class="card-body">
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-person-plus-fill"></i> Agregar Empleado al Jornal</h6>
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                                    <select wire:model="jornal_id_empleado" class="form-select <?php $__errorArgs = ['jornal_id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">Seleccione...</option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($emp->id_empleado); ?>">
                                                <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?>

                                                <!--[if BLOCK]><![endif]--><?php if(isset($jornal_por_empleado[$emp->id_empleado])): ?>
                                                    (Jornal: $<?php echo e(number_format($jornal_por_empleado[$emp->id_empleado], 2)); ?>)
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['jornal_id_empleado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" wire:click.prevent="agregarJornal" class="btn btn-warning w-100" wire:loading.attr="disabled" wire:target="agregarJornal">
                                        <span wire:loading.remove wire:target="agregarJornal"><i class="bi bi-plus-circle"></i> Agregar</span>
                                        <span wire:loading wire:target="agregarJornal"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!--[if BLOCK]><![endif]--><?php if(count($jornales) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Rol</th>
                                            <th>Jornal</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $jornales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $jornal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($jornal['nombre_completo'] ?? '-'); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo e($jornal['rol'] ?? '-'); ?></span></td>
                                                <td class="text-success fw-bold">$<?php echo e(number_format($jornal['jornal_diario'] ?? 0, 2)); ?></td>
                                                <td class="text-center">
                                                    <button type="button" wire:click.prevent="eliminarJornal(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total Jornales:</td>
                                            <td class="text-center fw-bold text-warning">$<?php echo e(number_format(array_sum(array_column($jornales, 'jornal_diario')), 2)); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-info-circle"></i> Sin empleados asignados. Agregue al menos un empleado.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!-- SECCIÓN 4: Movimientos de Insumos -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Movimientos de Insumos</h5>
                </div>
                <div class="card-body">
                    <div id="alertaMovimiento"></div>
                    
                    <!-- Errores generales de validación de movimiento -->
                    <!--[if BLOCK]><![endif]--><?php if($errors->has('movimiento_id_insumo') || $errors->has('movimiento_cantidad') || $errors->has('movimiento_motivo')): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Errores en el movimiento:</strong>
                            <ul class="mb-0 mt-2">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <li><?php echo e($message); ?></li> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </ul>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-box-arrow-right"></i> Registrar Consumo</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                                <select wire:model.live="movimiento_id_insumo" class="form-select <?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($insumo->id_insumo); ?>"><?php echo e($insumo->nombre); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php if($stock_disponible_insumo !== null): ?>
                                    <small class="text-muted mt-1 d-block">
                                        Stock disponible: <strong class="<?php echo e($stock_disponible_insumo > 0 ? 'text-success' : 'text-danger'); ?>"><?php echo e($stock_disponible_insumo); ?></strong>
                                    </small>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" wire:model="movimiento_cantidad" step="0.1" min="0" 
                                       <?php if($stock_disponible_insumo !== null): ?> max="<?php echo e($stock_disponible_insumo); ?>" <?php endif; ?>
                                       class="form-control <?php $__errorArgs = ['movimiento_cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0.00">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_cantidad'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                                <select wire:model="movimiento_motivo" class="form-select <?php $__errorArgs = ['movimiento_motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="Producción">Producción</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                    <option value="Varios">Varios</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" wire:click.prevent="agregarMovimiento" class="btn btn-success w-100" wire:loading.attr="disabled" wire:target="agregarMovimiento">
                                    <span wire:loading.remove wire:target="agregarMovimiento"><i class="bi bi-plus-circle"></i> Agregar</span>
                                    <span wire:loading wire:target="agregarMovimiento"><span class="spinner-border spinner-border-sm"></span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if(count($movimientos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><strong><?php echo e($mov['nombre_insumo']); ?></strong></td>
                                            <td><?php echo e(number_format($mov['cantidad'], 2)); ?> <?php echo e($mov['unidad'] ?? ''); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo e($mov['motivo']); ?></span></td>
                                            <td><small><?php echo e($mov['observaciones'] ?? '-'); ?></small></td>
                                            <td class="text-center">
                                                <button type="button" wire:click.prevent="eliminarMovimiento(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle"></i> Sin movimientos registrados. Esta sección es opcional.
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- BOTÓN GUARDAR -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" wire:click.prevent="resetCampos" class="btn btn-secondary btn-lg" wire:loading.attr="disabled">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="button" wire:click.prevent="guardar" class="btn btn-primary btn-lg" wire:loading.attr="disabled" wire:target="guardar">
                            <span wire:loading.remove wire:target="guardar"><i class="bi bi-check-circle"></i> Guardar Parte Diario</span>
                            <span wire:loading wire:target="guardar"><span class="spinner-border spinner-border-sm"></span> Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado -->
        <div class="tab-pane fade show active" id="listado-partes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Partes Diarios Registrados</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Buscar por Propietario</label>
                            <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-control" placeholder="Ej: Juan Pérez...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Buscar por Fecha</label>
                            <input type="date" wire:model.live="busqueda_fecha" class="form-control">
                        </div>
                    </div>
                    
                    <!--[if BLOCK]><![endif]--><?php if($partes && count($partes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Lote</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $partes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><span class="badge bg-secondary">#<?php echo e($parte->id_parte_diario); ?></span></td>
                                            <td><?php echo e($parte->lote?->propietario ?? '-'); ?></td>
                                            <td><?php echo e($parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : '-'); ?></td>
                                            <td>
                                                <!--[if BLOCK]><![endif]--><?php if($parte->es_dia_caido): ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-calendar-x"></i> Día Caído</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><i class="bi bi-truck"></i> Producción</span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                            <td><small><?php echo e($parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 40) : '-'); ?></small></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" wire:click.prevent="editar(<?php echo e($parte->id_parte_diario); ?>)" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" wire:click.prevent="eliminar(<?php echo e($parte->id_parte_diario); ?>)" wire:confirm="¿Está seguro de eliminar este parte diario?" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if(isset($partes)): ?>
                            <div class="mt-3" wire:key="pagination-<?php echo e(now()); ?>">
                                <?php echo e($partes->links()); ?>

                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="alert alert-info text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mb-0 mt-2">No hay partes diarios registrados.</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Evento para cambiar a la pestaña de listado después de guardar
    document.addEventListener('livewire:init', () => {
        Livewire.on('parteDiarioGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            if(window.bootstrap && listadoTab) {
                const tabInstance = new bootstrap.Tab(listadoTab);
                tabInstance.show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        // Evento para cambiar a la pestaña de formulario al editar
        Livewire.on('editandoParteDiario', () => {
            const nuevoTab = document.getElementById('nuevo-tab');
            if(window.bootstrap && nuevoTab) {
                const tabInstance = new bootstrap.Tab(nuevoTab);
                tabInstance.show();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/partes-diarios.blade.php ENDPATH**/ ?>