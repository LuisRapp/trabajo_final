<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Rennova - Gestion Forestal Inteligente</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/welcome.css')); ?>">
</head>
<body id="welcome-page" class="antialiased !bg-slate-100">
    <div class="welcome-shell min-h-screen flex flex-col lg:flex-row">
        <!-- Lado Izquierdo - Imagen (Oculto en moviles) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <img src="<?php echo e(asset('images/welcome.jpeg')); ?>" alt="Operaciones Forestales" class="w-full h-full object-cover opacity-80">

            <!-- Overlay verde oscuro -->
            <div class="absolute inset-0 bg-gradient-to-r from-green-950/90 to-green-900/80"></div>

            <!-- Contenido -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-8">
                <!-- Logo -->
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500/30 rounded-2xl">
                        <svg class="w-12 h-12 text-green-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>

                <!-- Titulo -->
                <h1 class="text-5xl font-bold text-white mb-2">Rennova</h1>
                <h2 class="text-xl text-green-200 mb-6">Gestion Forestal Inteligente</h2>
            </div>
        </div>

        <!-- Lado Derecho - Login -->
        <div id="login" class="welcome-panel relative w-full lg:w-1/2 overflow-hidden !bg-gradient-to-br !from-slate-50 !via-emerald-50 !to-white flex items-center justify-center px-6 py-12 lg:py-0">
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-green-300/30 blur-3xl"></div>

            <div class="welcome-card relative z-10 w-full max-w-md !rounded-2xl !border !border-emerald-100/70 !bg-white/85 p-8 !shadow-2xl !shadow-emerald-900/10 !backdrop-blur lg:p-10">
                <div class="absolute inset-x-0 -top-px h-1 rounded-t-2xl bg-gradient-to-r from-emerald-500 via-green-600 to-emerald-500"></div>

                <!-- Encabezado movil -->
                <div class="lg:hidden text-center mb-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-2xl mb-4 shadow-lg shadow-green-600/20">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Rennova</h1>
                </div>

                <!-- Titulo -->
                <h2 class="text-3xl font-bold !text-gray-900 mb-2">Ingresar</h2>
                <p class="!text-gray-600 text-sm mb-8">Accede a tu plataforma de gestion forestal</p>

                <!-- Formulario -->
                <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold !text-gray-700 mb-2">
                            Correo Electronico
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                <svg class="h-4.5 w-4.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" ry="2" />
                                    <path d="M3 7l9 6 9-6" />
                                </svg>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?php echo e(old('email')); ?>"
                                required
                                autofocus
                                class="welcome-input w-full pl-11 pr-4 py-3 !bg-white !text-gray-900 !border !border-gray-200 !rounded-xl !shadow-sm focus:outline-none focus:!ring-2 focus:!ring-emerald-500 focus:!border-transparent placeholder:text-gray-400 transition <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="correo@ejemplo.com"
                            >
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Contrasena -->
                    <div>
                        <label for="password" class="block text-sm font-semibold !text-gray-700 mb-2">
                            Contrasena
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                <svg class="h-4.5 w-4.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <rect x="4" y="11" width="16" height="9" rx="2" />
                                    <path d="M8 11V8a4 4 0 1 1 8 0v3" />
                                </svg>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                class="welcome-input w-full pl-11 pr-4 py-3 !bg-white !text-gray-900 !border !border-gray-200 !rounded-xl !shadow-sm focus:outline-none focus:!ring-2 focus:!ring-emerald-500 focus:!border-transparent placeholder:text-gray-400 transition <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> !border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="********"
                            >
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Recuerdame -->
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm !text-gray-700">
                            <input
                                type="checkbox"
                                name="remember"
                                class="welcome-checkbox h-4 w-4 !rounded !border !border-gray-300 !text-emerald-600 focus:!ring-emerald-500"
                            >
                            Recuerdame
                        </label>
                    </div>

                    <!-- Boton -->
                    <button
                        type="submit"
                        class="welcome-button w-full inline-flex items-center justify-center !rounded-xl !bg-gradient-to-r !from-emerald-600 !via-green-600 !to-emerald-600 px-4 py-3 !text-white font-bold !shadow-lg !shadow-emerald-600/30 transition hover:brightness-110 focus:outline-none focus:!ring-2 focus:!ring-emerald-500 focus:!ring-offset-2"
                    >
                        Ingresar
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/welcome.blade.php ENDPATH**/ ?>