<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">
    <head>
        <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>
    <body class="min-h-screen bg-gray-900 antialiased">
        <div class="flex min-h-svh w-screen">
            <!-- Columna Izquierda - Imagen de Fondo (Solo Desktop) -->
            <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-shrink-10">
                <img 
                    src="<?php echo e(asset('images/welcome.jpeg')); ?>" 
                    alt="Rennova Background"
                    class="w-full h-full object-cover"
                />
                <!-- Overlay Oscuro -->
                <div class="absolute inset-0 bg-black/35"></div>
                
                <!-- Contenido Decorativo en la Imagen (Opcional) -->
                <div class="relative flex flex-col items-center justify-center w-full h-full text-white p-12">
                    <div class="text-center max-w-md">
                        <h1 class="text-5xl font-bold mb-4">Rennova</h1>
                        <p class="text-lg text-gray-200">Gestión Forestal Inteligente</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha - Formulario de Login -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center bg-gray-900 p-6 md:p-10">
                <div class="w-full max-w-md flex flex-col gap-8">
                    
                    <!-- Logo y Título (Visible en móvil y desktop) -->
                    <div class="flex flex-col items-center gap-6">
                        <a href="<?php echo e(route('welcome')); ?>" class="flex flex-col items-center gap-3" wire:navigate>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-700 p-2">
                                <?php if (isset($component)) { $__componentOriginal159d6670770cb479b1921cea6416c26c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal159d6670770cb479b1921cea6416c26c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-logo-icon','data' => ['class' => 'size-8 fill-current text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-logo-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-8 fill-current text-white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $attributes = $__attributesOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__attributesOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal159d6670770cb479b1921cea6416c26c)): ?>
<?php $component = $__componentOriginal159d6670770cb479b1921cea6416c26c; ?>
<?php unset($__componentOriginal159d6670770cb479b1921cea6416c26c); ?>
<?php endif; ?>
                            </div>
                            <span class="sr-only"><?php echo e(config('app.name', 'Rennova')); ?></span>
                        </a>
                        
                        <div class="text-center">
                            <h2 class="text-3xl md:text-4xl font-bold text-white mb-2">Bienvenido</h2>
                            <p class="text-gray-400 text-sm md:text-base">Accede a tu cuenta</p>
                        </div>
                    </div>

                    <!-- Mensaje de Sesión -->
                    <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'text-center','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-center','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>

                    <!-- Formulario -->
                    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
                        <?php echo csrf_field(); ?>
                        <!-- Email Address -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="text-sm font-medium text-gray-300">
                                <?php echo e(__('Correo electrónico')); ?>

                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                wire:model="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="admin@rennova.com"
                                class="px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-600 transition"
                            />
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-sm text-red-500"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2 relative">
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-gray-300">
                                    <?php echo e(__('Contraseña')); ?>

                                </label>
                                <?php if(Route::has('password.request')): ?>
                                    <a href="<?php echo e(route('password.request')); ?>" wire:navigate class="text-sm text-green-500 hover:text-green-400 transition">
                                        <?php echo e(__('¿Olvidaste tu contraseña?')); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                wire:model="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-600 focus:ring-2 focus:ring-green-600 transition"
                            />
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-sm text-red-500"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Remember Me -->
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="remember"
                                class="w-4 h-4 accent-green-600 bg-gray-800 border border-gray-700 rounded cursor-pointer"
                            />
                            <span class="text-sm text-gray-300"><?php echo e(__('Recuérdame')); ?></span>
                        </label>

                        <!-- Botón de Login -->
                        <button
                            type="submit"
                            data-test="login-button"
                            class="w-full py-3 px-4 bg-green-700 hover:bg-green-600 text-white font-semibold rounded-lg transition duration-200 mt-2"
                        >
                            <?php echo e(__('Ingresar')); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php app('livewire')->forceAssetInjection(); ?>
<?php echo app('flux')->scripts(); ?>

    </body>
</html>
<?php /**PATH D:\trabajo_final\rennova\resources\views\components\layouts\auth\login.blade.php ENDPATH**/ ?>