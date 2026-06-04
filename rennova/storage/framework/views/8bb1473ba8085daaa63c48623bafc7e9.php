<div>
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="fas fa-cog"></i> Configuración de Mantenimiento Automático
            </h5>
        </div>
        <div class="card-body">
            <?php if(session()->has('message')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo e(session('message')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session()->has('command_output')): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <strong>Resultado de la ejecución:</strong>
                    <pre class="mb-0 mt-2" style="white-space: pre-wrap;"><?php echo e(session('command_output')); ?></pre>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Configuración de Horarios -->
                <div class="col-md-6 mb-4">
                    <div class="card border-primary h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-clock"></i> Horarios de Ejecución</h6>
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="guardarConfiguracion">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Hora de Recordatorio Diario
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           wire:model="hora_recordatorio" 
                                           class="form-control <?php $__errorArgs = ['hora_recordatorio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['hora_recordatorio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <small class="text-muted">
                                        Hora en la que se enviarán recordatorios de mantenimientos programados para hoy
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Frecuencia de Verificación de Umbrales
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model.live="expresion_cron" class="form-select <?php $__errorArgs = ['expresion_cron'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="selectFrecuencia">
                                        <option value="*/15 * * * *">Cada 15 minutos</option>
                                        <option value="*/30 * * * *">Cada 30 minutos</option>
                                        <option value="0 * * * *">Cada hora</option>
                                        <option value="0 */2 * * *">Cada 2 horas</option>
                                        <option value="0 */4 * * *">Cada 4 horas</option>
                                        <option value="0 */6 * * *">Cada 6 horas</option>
                                        <option value="0 8,16 * * *">2 veces al día (8:00 y 16:00)</option>
                                        <option value="0 8 * * *">1 vez al día (8:00)</option>
                                        <option value="custom">Personalizado...</option>
                                    </select>
                                    <?php $__errorArgs = ['expresion_cron'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <small class="text-muted">
                                        Con qué frecuencia el sistema verifica si las maquinarias alcanzaron su umbral de toneladas
                                    </small>
                                </div>

                                <div class="mb-3" id="customCronDiv" style="display: none;">
                                    <label class="form-label fw-bold">Expresión Cron Personalizada</label>
                                    <input type="text" 
                                           wire:model="expresion_cron" 
                                           class="form-control font-monospace <?php $__errorArgs = ['expresion_cron'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           placeholder="* * * * *"
                                           id="customCronInput">
                                    <small class="text-muted">
                                        Formato: minuto hora día mes día_semana. 
                                        <a href="https://crontab.guru" target="_blank" rel="noopener">Ayuda con expresiones cron</a>
                                    </small>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const select = document.getElementById('selectFrecuencia');
                                        const customDiv = document.getElementById('customCronDiv');
                                        const customInput = document.getElementById('customCronInput');
                                        
                                        function toggleCustom() {
                                            if (select.value === 'custom') {
                                                customDiv.style.display = 'block';
                                                customInput.focus();
                                            } else {
                                                customDiv.style.display = 'none';
                                            }
                                        }
                                        
                                        select.addEventListener('change', toggleCustom);
                                        
                                        // Verificar al cargar si es custom
                                        const currentValue = select.value;
                                        const predefinedValues = ['*/15 * * * *', '*/30 * * * *', '0 * * * *', '0 */2 * * *', 
                                                                   '0 */4 * * *', '0 */6 * * *', '0 8,16 * * *', '0 8 * * *'];
                                        if (!predefinedValues.includes(currentValue) && currentValue !== '') {
                                            select.value = 'custom';
                                            customDiv.style.display = 'block';
                                        }
                                    });
                                </script>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Configuración
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Ejecución Manual -->
                <div class="col-md-6 mb-4">
                    <div class="card border-info h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-play-circle"></i> Ejecución Manual</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Ejecute manualmente los comandos de verificación para probar o forzar una ejecución inmediata.
                            </p>

                            <div class="d-grid gap-2">
                                <button type="button" 
                                        wire:click="ejecutarVerificacionUmbrales" 
                                        class="btn btn-outline-primary"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="ejecutarVerificacionUmbrales">
                                        <i class="fas fa-gauge-high"></i> Verificar Umbrales Ahora
                                    </span>
                                    <span wire:loading wire:target="ejecutarVerificacionUmbrales">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        Ejecutando...
                                    </span>
                                </button>

                                <button type="button" 
                                        wire:click="ejecutarVerificacionProgramados" 
                                        class="btn btn-outline-info"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="ejecutarVerificacionProgramados">
                                        <i class="fas fa-calendar-check"></i> Verificar Programados Ahora
                                    </span>
                                    <span wire:loading wire:target="ejecutarVerificacionProgramados">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        Ejecutando...
                                    </span>
                                </button>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Nota:</strong> La ejecución manual es útil para pruebas. 
                                    Los comandos se ejecutarán normalmente según la configuración del cron del servidor.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instrucciones de Configuración del Servidor -->
            <div class="card border-warning mt-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-server"></i> Configuración del Servidor (Cron)</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        Para que los comandos se ejecuten automáticamente, configure el cron del servidor con los siguientes comandos:
                    </p>

                    <div class="alert alert-light border">
                        <code class="d-block mb-2">
                            # Verificación de umbrales (según frecuencia configurada: <strong><?php echo e($expresion_cron); ?></strong>)<br>
                            <?php echo e($expresion_cron); ?> cd /ruta/al/proyecto && php artisan mantenimiento:check-umbrales
                        </code>
                        <code class="d-block">
                            # Recordatorio diario (a las <strong><?php echo e($hora_recordatorio); ?></strong>)<br>
                            <?php
                                [$hora, $minuto] = explode(':', $hora_recordatorio);
                            ?>
                            <?php echo e($minuto); ?> <?php echo e($hora); ?> * * * cd /ruta/al/proyecto && php artisan mantenimiento:check-programados
                        </code>
                    </div>

                    <p class="mb-0 text-muted">
                        <i class="fas fa-lightbulb"></i> 
                        <strong>Tip:</strong> En producción, agregue estos comandos al archivo <code>crontab</code> del servidor 
                        con el comando <code>crontab -e</code>. En desarrollo con Laravel Sail o similar, 
                        puede configurar el scheduler en <code>app/Console/Kernel.php</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/configuracion-mantenimiento.blade.php ENDPATH**/ ?>