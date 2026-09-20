<nav class="shrink-0 bg-pino shadow-sm h-[var(--navbar-height)] flex items-center z-50 relative">
    <div class="w-full flex items-center px-3 gap-3">
        <!-- Sidebar Toggle -->
        <button @click="toggleSidebar()" class="text-white hover:opacity-80 p-0 bg-transparent border-none cursor-pointer">
            <flux:icon.bars-3 class="size-5" />
        </button>

        <!-- Brand -->
        <a href="{{ route('dashboard') }}" class="text-white font-semibold text-sm no-underline cursor-pointer select-none hover:opacity-90">
            Rennova
        </a>

        <!-- Right side -->
        <div class="ml-auto flex items-center gap-3">
            @auth
                @livewire('notificaciones-campana')
            @endauth

            <!-- User Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                @php
                    $user = auth()->user();
                    $displayName = $user?->name
                        ?? trim(($user?->nombre ?? '') . ' ' . ($user?->apellido ?? ''))
                        ?: 'Usuario';
                @endphp
                <button @click="open = !open" class="text-white text-xs bg-transparent border-none cursor-pointer flex items-center gap-1 hover:opacity-80">
                    <flux:icon.user class="size-3.5" />
                    <span>{{ $displayName }}</span>
                    <flux:icon.chevron-down class="size-3" />
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1 w-48 bg-white rounded-sm shadow-lg border border-arena py-1 z-50">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-1.5 text-xs text-tinta hover:bg-corteza-suave no-underline">
                        <flux:icon.bolt class="size-3.5" /> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-1.5 text-xs text-tinta hover:bg-corteza-suave no-underline">
                        <flux:icon.user class="size-3.5" /> Perfil
                    </a>
                    <hr class="border-arena my-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-1.5 text-xs text-tinta hover:bg-corteza-suave bg-transparent border-none cursor-pointer">
                            <flux:icon.arrow-right-start-on-rectangle class="size-3.5" />
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
