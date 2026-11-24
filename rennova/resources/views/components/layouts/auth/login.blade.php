<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-hueso antialiased">
        <div class="grid min-h-svh lg:grid-cols-2">
            {{-- Panel de marca: la imagen es fondo absoluto, no compite por el ancho --}}
            <aside class="relative hidden overflow-hidden bg-pino-oscuro lg:flex lg:flex-col lg:justify-between lg:p-12">
                <img
                    src="{{ asset('images/welcome.jpeg') }}"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover opacity-50"
                />
                <div class="absolute inset-0 bg-gradient-to-b from-pino-oscuro/85 via-pino-oscuro/55 to-pino-oscuro"></div>

                <div class="relative flex items-center gap-3 text-hueso">
                    <span class="flex size-10 items-center justify-center rounded-sm bg-hueso/10 ring-1 ring-hueso/25">
                        <x-app-logo-icon class="size-6 fill-current text-hueso" />
                    </span>
                    <span class="text-lg font-semibold tracking-wide">Rennova</span>
                </div>

                <div class="relative max-w-sm">
                    <h1 class="text-4xl font-bold leading-tight text-hueso">
                        Gestión forestal<br>de punta a punta
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-arena">
                        Lotes, maquinaria, insumos, personal y pagos en un solo sistema.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-arena">
                        <li class="flex items-center gap-2.5">
                            <flux:icon.check-circle class="size-4 shrink-0 text-pino-suave" />
                            Control de inventario con costo FIFO
                        </li>
                        <li class="flex items-center gap-2.5">
                            <flux:icon.check-circle class="size-4 shrink-0 text-pino-suave" />
                            Mantenimiento preventivo automático
                        </li>
                        <li class="flex items-center gap-2.5">
                            <flux:icon.check-circle class="size-4 shrink-0 text-pino-suave" />
                            Indicadores operativos y financieros
                        </li>
                    </ul>
                </div>

                <p class="relative text-xs text-arena-oscura">
                    &copy; {{ date('Y') }} Rapp Luis. Todos los derechos reservados.
                </p>
            </aside>

            {{-- Panel de acceso --}}
            <main class="flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-sm">
                    <div class="mb-8 flex flex-col items-center gap-4 lg:items-start">
                        <a href="{{ route('welcome') }}" wire:navigate class="flex items-center gap-2 lg:hidden">
                            <span class="flex size-10 items-center justify-center rounded-sm bg-pino">
                                <x-app-logo-icon class="size-6 fill-current text-hueso" />
                            </span>
                            <span class="text-lg font-semibold text-tinta">Rennova</span>
                        </a>

                        <div class="text-center lg:text-left">
                            <h2 class="text-2xl font-bold text-tinta">Iniciar sesión</h2>
                            <p class="mt-1 text-sm text-tinta-suave">Accedé con tu cuenta para continuar.</p>
                        </div>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" wire:submit="login" class="flex flex-col gap-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-xs font-semibold text-tinta">
                                Correo electrónico <span class="text-tierra">*</span>
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                wire:model="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="usuario@rennova.com"
                                class="form-input @error('email') border-tierra bg-tierra-suave @enderror"
                            />
                            @error('email')
                                <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="password" class="block text-xs font-semibold text-tinta">
                                    Contraseña <span class="text-tierra">*</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-medium text-pino hover:text-pino-oscuro hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                wire:model="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="form-input @error('password') border-tierra bg-tierra-suave @enderror"
                            />
                            @error('password')
                                <p class="mt-1 text-xs text-tierra">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-tinta-suave">
                            <input
                                type="checkbox"
                                wire:model="remember"
                                class="size-4 cursor-pointer rounded-sm border-arena accent-pino"
                            />
                            Mantener la sesión iniciada
                        </label>

                        <button
                            type="submit"
                            data-test="login-button"
                            class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-sm bg-pino px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-pino-oscuro focus:outline-hidden focus:ring-2 focus:ring-pino/30"
                        >
                            Ingresar
                        </button>
                    </form>

                    <p class="mt-8 text-center text-xs text-arena-oscura lg:hidden">
                        &copy; {{ date('Y') }} Rapp Luis. Todos los derechos reservados.
                    </p>
                </div>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
