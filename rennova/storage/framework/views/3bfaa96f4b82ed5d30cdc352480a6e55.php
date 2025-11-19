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
            <button class="nav-link active" id="nuevo-tab" data-bs-toggle="tab" data-bs-target="#nuevo-parte" type="button" role="tab" aria-controls="nuevo-parte" aria-selected="true">
                <i class="bi bi-plus-circle"></i> Nuevo Parte Diario
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado-partes" type="button" role="tab" aria-controls="listado-partes" aria-selected="false">
                <i class="bi bi-list-ul"></i> Listado de Partes Diarios
            </button>
        </li>
    </ul>

    <div class="tab-content" id="partesTabContent">
        <!-- Pestaña 1: Nuevo Parte Diario (Formulario Maestro-Detalle) -->
        <div class="tab-pane fade show active" id="nuevo-parte" role="tabpanel" aria-labelledby="nuevo-tab">
            
            <!-- 1. TARJETA PRINCIPAL: Datos Maestros -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-<?php echo e($parte_id ? 'pencil-square' : 'plus-circle'); ?>"></i> 
                        <?php echo e($parte_id ? 'Modificar Parte Diario' : 'Nuevo Parte Diario'); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                            <select wire:model="id_lote" class="form-select <?php $__errorArgs = ['id_lote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Seleccione un lote...</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" wire:model="fecha" class="form-control <?php $__errorArgs = ['fecha'];
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
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Actividad Realizada <span class="text-danger">*</span></label>
                            <textarea wire:model="actividad_realizada" class="form-control <?php $__errorArgs = ['actividad_realizada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="2" placeholder="Descripción de la actividad realizada"></textarea>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['actividad_realizada'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="esDiaCaido" wire:model.live="es_dia_caido">
                                <label class="form-check-label fw-semibold" for="esDiaCaido">
                                    <i class="bi bi-calendar-x"></i> ¿Día Caído?
                                    <span class="badge <?php echo e($es_dia_caido ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                        <?php echo e($es_dia_caido ? 'SÍ - Pago por Jornal' : 'NO - Pago por Destajo'); ?>

                                    </span>
                                </label>
                            </div>
                            <small class="text-muted">Si es día caído, se pagará jornal fijo. Si no, se paga por tonelada extraída.</small>
                        </div>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($es_dia_caido): ?>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Motivo del Día Caído <span class="text-danger">*</span></label>
                                <textarea wire:model="motivo_dia_caido" class="form-control <?php $__errorArgs = ['motivo_dia_caido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3" placeholder="Explique el motivo del día caído (lluvia, falla mecánica, etc.)"></textarea>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['motivo_dia_caido'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Observaciones Generales</label>
                            <textarea wire:model="observaciones" class="form-control" rows="2" placeholder="Observaciones adicionales del parte diario"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2A. SECCIÓN CONDICIONAL: REGISTRO DE PRODUCCIÓN (Cargas) - Solo si NO es día caído -->
            <!--[if BLOCK]><![endif]--><?php if(!$es_dia_caido): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Registro de Producción (Cargas)</h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulario para agregar carga -->
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle-fill"></i> Registrar Nueva Carga</h6>
                            
                            <!-- Fila 1: Lote, Categoría, Maquinaria -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Lote <span class="text-danger">*</span></label>
                                    <select disabled class="form-select" title="El lote se establece en los datos maestros del Parte Diario">
                                        <option value=""><?php echo e($id_lote ? $lotes->firstWhere('id_lote', $id_lote)->propietario . ' - ' . $lotes->firstWhere('id_lote', $id_lote)->ubicacion : 'Seleccione lote arriba'); ?></option>
                                    </select>
                                    <small class="text-muted">El lote se configura en la sección principal</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Categoría Madera <span class="text-danger">*</span></label>
                                    <select wire:model="carga_id_categoria_madera" class="form-select <?php $__errorArgs = ['carga_id_categoria_madera'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">Seleccione...</option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $categorias_madera; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Maquinarias Utilizadas
                                        <!--[if BLOCK]><![endif]--><?php if(!empty($maquinarias_asignadas_ids) && count($maquinarias_asignadas_ids) === 1): ?>
                                            <span class="badge bg-success ms-1" title="Auto-seleccionada (única asignada al lote)">
                                                <i class="bi bi-check-circle"></i> Auto
                                            </span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </label>
                                    <div class="border rounded p-2 <?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 180px; overflow-y: auto; background-color: #f8f9fa;">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->maquinariasFiltrada; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="<?php echo e($maq->id_maquinaria); ?>" id="maq-<?php echo e($maq->id_maquinaria); ?>" wire:model="carga_maquinarias">
                                                <label class="form-check-label" for="maq-<?php echo e($maq->id_maquinaria); ?>">
                                                    <?php echo e($maq->modelo); ?> - <small class="text-muted"><?php echo e($maq->tipoMaquinaria->nombre ?? 'Sin tipo'); ?></small>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_maquinarias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <!--[if BLOCK]><![endif]--><?php if(empty($maquinarias_asignadas_ids)): ?>
                                        <small class="text-muted"><i class="bi bi-info-circle"></i> Sin lote seleccionado. Se muestran todas las maquinarias.</small>
                                    <?php elseif(count($maquinarias_asignadas_ids) > 0): ?>
                                        <small class="text-success"><i class="bi bi-funnel"></i> Mostrando solo maquinarias asignadas al lote (<?php echo e(count($maquinarias_asignadas_ids)); ?>)</small>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>

                            <!-- Fila 2: Chofer y Cliente -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Chofer <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="busqueda_chofer" class="form-control mb-1" placeholder="Buscar chofer...">
                                    <select wire:model="carga_id_chofer" class="form-select <?php $__errorArgs = ['carga_id_chofer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" size="3" style="height: auto;">
                                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->choferesFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chofer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($chofer->id_chofer); ?>"><?php echo e($chofer->apellido); ?>, <?php echo e($chofer->nombre); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="">No hay resultados</option>
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
                                    <input type="text" wire:model.live="busqueda_cliente" class="form-control mb-1" placeholder="Buscar cliente...">
                                    <select wire:model="carga_destino" class="form-select <?php $__errorArgs = ['carga_destino'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" size="3" style="height: auto;">
                                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $this->clientesFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($cliente->id_cliente); ?>"><?php echo e($cliente->razon_social); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="">No hay resultados</option>
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

                            <!-- Fila 3: Pesajes y Tickets -->
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
unset($__errorArgs, $__bag); ?>" placeholder="Ej: TKT-12345">
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
                                    <label class="form-label fw-semibold">Peso Bruto (Ton) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="carga_peso_bruto" step="0.01" class="form-control <?php $__errorArgs = ['carga_peso_bruto'];
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
                                    <input type="number" wire:model.live="carga_tara" step="0.01" class="form-control <?php $__errorArgs = ['carga_tara'];
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
                                    <label class="form-label fw-semibold">Peso Neto (Ton) <span class="text-info">Calculado</span></label>
                                    <input type="text" value="<?php echo e($carga_peso_neto ? number_format($carga_peso_neto, 2) : '0.00'); ?>" class="form-control bg-light" readonly>
                                    <small class="text-muted">Peso Bruto - Tara</small>
                                </div>
                            </div>
                            
                            <!-- Sección de Personal Involucrado -->
                            <div class="row g-3 mt-3">
                                <div class="col-md-12">
                                    <h6 class="fw-semibold border-bottom pb-2 mb-2"><i class="bi bi-people-fill"></i> Personal Involucrado</h6>
                                    <label class="form-label fw-semibold">Empleados que participaron en esta carga <span class="text-danger">*</span></label>
                                    <div class="border rounded p-2 <?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height: 180px; overflow-y: auto; background-color: #f8f9fa;">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="<?php echo e($emp->id_empleado); ?>" id="emp-<?php echo e($emp->id_empleado); ?>" wire:model="carga_empleados">
                                                <label class="form-check-label" for="emp-<?php echo e($emp->id_empleado); ?>">
                                                    <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <small class="text-muted"><?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?></small>
                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['carga_empleados'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    <small class="text-muted d-block mt-1"><i class="bi bi-info-circle"></i> Seleccione todos los empleados que trabajaron en la extracción de esta carga. El pago se calculará por destajo (toneladas extraídas).</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" wire:click="agregarCarga" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Agregar Carga
                                </button>
                            </div>
                        </div>

                        <!-- Listado de cargas registradas -->
                        <!--[if BLOCK]><![endif]--><?php if(count($cargas) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket</th>
                                            <th>Categoría</th>
                                            <th>Peso Neto (Ton)</th>
                                            <th>Chofer</th>
                                            <th>Destino (Cliente)</th>
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
                                                        $cat = $categorias_madera->firstWhere('id_categoria_madera', $carga['id_categoria_madera']);
                                                    ?>
                                                    <?php echo e($cat->nombre ?? '-'); ?>

                                                </td>
                                                <td><strong><?php echo e(number_format($carga['peso_neto'], 2)); ?></strong> <small class="text-muted">(B: <?php echo e(number_format($carga['peso_bruto'], 2)); ?> - T: <?php echo e(number_format($carga['tara'], 2)); ?>)</small></td>
                                                <td>
                                                    <?php
                                                        $chofer = $choferes->firstWhere('id_chofer', $carga['id_chofer']);
                                                    ?>
                                                    <?php echo e($chofer ? $chofer->apellido . ', ' . $chofer->nombre : '-'); ?>

                                                </td>
                                                <td>
                                                    <?php echo e($carga['destino_nombre'] ?? '-'); ?>

                                                </td>
                                                <td>
                                                    <small>
                                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $carga['empleados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp_id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php
                                                                $emp = $empleados->firstWhere('id_empleado', $emp_id);
                                                            ?>
                                                            <!--[if BLOCK]><![endif]--><?php if($emp): ?>
                                                                <span class="badge bg-info"><?php echo e($emp->apellido); ?></span>
                                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                    </small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <!--[if BLOCK]><![endif]--><?php if(isset($carga['maquinarias'])): ?>
                                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $carga['maquinarias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maq_id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php
                                                                    $m = $maquinarias->firstWhere('id_maquinaria', $maq_id);
                                                                ?>
                                                                <!--[if BLOCK]><![endif]--><?php if($m): ?>
                                                                    <span class="badge bg-secondary"><?php echo e($m->modelo); ?></span>
                                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="eliminarCarga(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger" title="Eliminar carga">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Total de Toneladas Extraídas Hoy:</td>
                                            <td class="text-center fw-bold text-primary fs-5"><?php echo e(number_format($total_toneladas, 2)); ?> Ton</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> No hay cargas registradas. Agregue al menos una carga para continuar.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!-- 2B. SECCIÓN CONDICIONAL: ASIGNACIÓN DE JORNAL (Día Caído) - Solo si ES día caído -->
            <!--[if BLOCK]><![endif]--><?php if($es_dia_caido): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Asignación de Jornal por Día Caído</h5>
                    </div>
                    <div class="card-body">
                        <!-- Formulario para agregar empleado al jornal -->
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-person-plus-fill"></i> Añadir Empleado</h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Empleado <span class="text-danger">*</span></label>
                                    <select wire:model="jornal_id_empleado" class="form-select">
                                        <option value="">Seleccione un empleado...</option>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->empleadosFiltrados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($emp->id_empleado); ?>">
                                                <?php echo e($emp->apellido); ?>, <?php echo e($emp->nombre); ?> - <?php echo e($emp->rolLaboral->nombre ?? 'Sin rol'); ?> 
                                                (Jornal: $<?php echo e(number_format($jornal_por_empleado[$emp->id_empleado] ?? ($emp->rolLaboral->jornal_diario ?? 0), 2)); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Observaciones</label>
                                    <input type="text" wire:model="jornal_observaciones" class="form-control" placeholder="Obs. de pago">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" wire:click="agregarJornal" class="btn btn-warning">
                                    <i class="bi bi-plus-circle"></i> Agregar Empleado
                                </button>
                            </div>
                        </div>

                        <!-- Listado de jornales -->
                        <!--[if BLOCK]><![endif]--><?php if(count($jornales) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Rol Laboral</th>
                                            <th>Valor Jornal Diario</th>
                                            <th>Observaciones</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $jornales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $jornal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($jornal['nombre_completo']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo e($jornal['rol']); ?></span></td>
                                                <td class="text-end fw-bold text-success">$<?php echo e(number_format($jornal['jornal_diario'], 2)); ?></td>
                                                <td><?php echo e($jornal['observaciones'] ?? '-'); ?></td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="eliminarJornal(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> No hay empleados asignados al jornal. Agregue al menos un empleado.
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!-- 3. SECCIÓN FIJA: MOVIMIENTO DE INSUMOS -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Movimiento de Insumos</h5>
                </div>
                <div class="card-body">
                    <!-- Formulario para agregar movimiento -->
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-arrow-left-right"></i> Registrar Movimiento de Stock</h6>
                        
                        <!-- Fila 1: Identificación y Tipo -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Insumo <span class="text-danger">*</span></label>
                                <select wire:model="movimiento_id_insumo" class="form-select <?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="">Seleccione un insumo...</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insumo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($insumo->id_insumo); ?>"><?php echo e($insumo->nombre); ?> (Stock: <?php echo e($insumo->stock); ?> <?php echo e($insumo->unidadMedida->nombre ?? ''); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_id_insumo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select wire:model="movimiento_tipo" class="form-select <?php $__errorArgs = ['movimiento_tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="entrada">Entrada / Compra</option>
                                    <option value="salida">Salida / Consumo</option>
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movimiento_tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" wire:model="movimiento_cantidad" step="0.01" class="form-control <?php $__errorArgs = ['movimiento_cantidad'];
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
                        </div>

                        <!-- Fila 2: Motivo y Fecha -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fecha <span class="text-info">Automática</span></label>
                                <input type="text" value="<?php echo e($fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : 'Seleccione fecha arriba'); ?>" class="form-control bg-light" readonly>
                                <small class="text-muted">Toma la fecha del Parte Diario</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Observaciones</label>
                                <input type="text" wire:model="movimiento_observaciones" class="form-control" placeholder="Notas específicas (opcional)">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" wire:click="agregarMovimiento" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Agregar Movimiento
                            </button>
                        </div>
                    </div>

                    <!-- Listado de movimientos -->
                    <!--[if BLOCK]><![endif]--><?php if(count($movimientos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Tipo de Movimiento</th>
                                        <th>Cantidad</th>
                                        <th>Motivo</th>
                                        <th>Observaciones</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo e($mov['nombre_insumo']); ?></strong>
                                                <br><small class="text-muted"><?php echo e($mov['unidad']); ?></small>
                                            </td>
                                            <td>
                                                <!--[if BLOCK]><![endif]--><?php if($mov['tipo'] == 'entrada'): ?>
                                                    <span class="badge bg-primary"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><i class="bi bi-arrow-up-circle"></i> Salida</span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                            <td class="fw-bold"><?php echo e(number_format($mov['cantidad'], 2)); ?></td>
                                            <td>
                                                <!--[if BLOCK]><![endif]--><?php if($mov['motivo'] == 'Producción'): ?>
                                                    <span class="badge bg-info"><?php echo e($mov['motivo']); ?></span>
                                                <?php elseif($mov['motivo'] == 'Mantenimiento'): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo e($mov['motivo']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo e($mov['motivo']); ?></span>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </td>
                                            <td><?php echo e($mov['observaciones'] ?? '-'); ?></td>
                                            <td class="text-center">
                                                <button type="button" wire:click="eliminarMovimiento(<?php echo e($index); ?>)" class="btn btn-sm btn-outline-danger" title="Eliminar movimiento">
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
                            <i class="bi bi-info-circle"></i> No hay movimientos de insumos registrados. Esta sección es opcional.
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- BOTÓN FINAL PARA GUARDAR TODO -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-end">
                        <!--[if BLOCK]><![endif]--><?php if($parte_id): ?>
                            <button type="button" wire:click="resetCampos" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <button type="button" wire:click="guardar" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> <?php echo e($parte_id ? 'Actualizar Parte Diario' : 'Guardar Parte Diario Completo'); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña 2: Listado de Partes Diarios (Tabla) -->
        <div class="tab-pane fade" id="listado-partes" role="tabpanel" aria-labelledby="listado-tab">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Listado de Partes Diarios</h5>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" wire:model.live="busqueda" class="form-control" placeholder="Buscar por lote o fecha...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Lote</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Observaciones</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $partes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo e($parte->id_parte_diario); ?></span></td>
                                        <td class="fw-semibold"><?php echo e($parte->lote?->propietario ?? 'N/A'); ?></td>
                                        <td><?php echo e($parte->fecha ? \Carbon\Carbon::parse($parte->fecha)->format('d/m/Y') : 'N/A'); ?></td>
                                        <td>
                                            <!--[if BLOCK]><![endif]--><?php if($parte->es_dia_caido): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-calendar-x"></i> Día Caído</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="bi bi-truck"></i> Producción</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        <td><?php echo e($parte->observaciones ? \Illuminate\Support\Str::limit($parte->observaciones, 30) : '-'); ?></td>
                                        <td>
                                            <!--[if BLOCK]><![endif]--><?php if($parte->activo): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary" wire:click="editar(<?php echo e($parte->id_parte_diario); ?>)" onclick="cambiarAPestanaFormulario()" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" wire:click="eliminar(<?php echo e($parte->id_parte_diario); ?>)" onclick="return confirm('¿Está seguro de eliminar este parte diario?')" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2">No hay partes diarios registrados.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para cambiar de pestaña al editar/guardar -->
<script>
    function cambiarAPestanaFormulario() {
        const nuevoTab = document.getElementById('nuevo-tab');
        const nuevoTabInstance = new bootstrap.Tab(nuevoTab);
        nuevoTabInstance.show();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('livewire:init', () => {
        Livewire.on('parteDiarioGuardado', () => {
            const listadoTab = document.getElementById('listado-tab');
            const listadoTabInstance = new bootstrap.Tab(listadoTab);
            listadoTabInstance.show();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
<?php /**PATH D:\trabajo_final\rennova\resources\views/livewire/partes-diarios.blade.php ENDPATH**/ ?>