<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-tinta antialiased">
        <div class="flex min-h-svh w-screen">
            <!-- Columna Izquierda - Imagen de Fondo (Solo Desktop) -->
            <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden flex-shrink-10">
                <img
                    src="{{ asset('images/welcome.jpeg') }}"
                    alt="Rennova Background"
                    class="w-full h-full object-cover"
                />
                <!-- Overlay Oscuro -->
                <div class="absolute inset-0 bg-pino-oscuro/60"></div>

                <!-- Contenido Decorativo en la Imagen (Opcional) -->
                <div class="relative flex flex-col items-center justify-center w-full h-full text-hueso p-12">
                    <div class="text-center max-w-md">
                        <h1 class="text-5xl font-bold mb-4">Rennova</h1>
                        <p class="text-lg text-arena">Gestión Forestal Inteligente</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha - Formulario de Login -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center bg-tinta p-6 md:p-10">
                <div class="w-full max-w-md flex flex-col gap-8">

                    <!-- Logo y Título (Visible en móvil y desktop) -->
                    <div class="flex flex-col items-center gap-6">
                        <a href="{{ route('welcome') }}" class="flex flex-col items-center gap-3" wire:navigate>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-pino p-2">
                                <x-app-logo-icon class="size-8 fill-current text-hueso" />
                            </div>
                            <span class="sr-only">{{ config('app.name', 'Rennova') }}</span>
                        </a>

                        <div class="text-center">
                            <h2 class="text-3xl md:text-4xl font-bold text-hueso mb-2">Bienvenido</h2>
                            <p class="text-arena text-sm md:text-base">Accede a tu cuenta</p>
                        </div>
                    </div>

                    <!-- Mensaje de Sesión -->
                    <x-auth-session-status class="text-center" :status="session('status')" />

                    <!-- Formulario -->
                    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
                        @csrf
                        <!-- Email Address -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="text-sm font-medium text-arena">
                                {{ __('Correo electrónico') }}
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
                                class="px-4 py-3 bg-corteza border border-corteza rounded-lg text-hueso placeholder-arena-oscura focus:outline-hidden focus:border-pino focus:ring-2 focus:ring-pino transition"
                            />
                            @error('email')
                                <span class="text-sm text-tierra">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2 relative">
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-arena">
                                    {{ __('Contraseña') }}
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-pino-suave hover:text-hueso transition">
                                        {{ __('¿Olvidaste tu contraseña?') }}
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
                                class="px-4 py-3 bg-corteza border border-corteza rounded-lg text-hueso placeholder-arena-oscura focus:outline-hidden focus:border-pino focus:ring-2 focus:ring-pino transition"
                            />
                            @error('password')
                                <span class="text-sm text-tierra">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="remember"
                                class="w-4 h-4 accent-pino bg-corteza border border-corteza rounded cursor-pointer"
                            />
                            <span class="text-sm text-arena">{{ __('Recuérdame') }}</span>
                        </label>

                        <!-- Botón de Login -->
                        <button
                            type="submit"
                            data-test="login-button"
                            class="w-full py-3 px-4 bg-pino hover:bg-pino-oscuro text-hueso font-semibold rounded-lg transition duration-200 mt-2"
                        >
                            {{ __('Ingresar') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
