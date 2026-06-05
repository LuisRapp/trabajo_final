<nav class="shrink-0 bg-[#1f5f3b] shadow-sm h-[var(--navbar-height)] flex items-center z-50 relative">
    <div class="w-full flex items-center px-3 gap-3">
        <!-- Sidebar Toggle -->
        <button @click="toggleSidebar()" class="text-white hover:opacity-80 p-0 bg-transparent border-none cursor-pointer">
            <span class="text-base" :class="{ 'inline-block rotate-90 transition-transform duration-300': collapsed }">☰</span>
        </button>

        <!-- Brand -->
        <a href="{{ route('dashboard') }}" class="text-slate-100 font-bold text-sm no-underline cursor-pointer select-none hover:opacity-90">
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
                    👤 {{ $displayName }}
                    <span class="text-[10px]">▼</span>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                     style="display: none;">
                    <a href="{{ route('dashboard') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 no-underline">⚡ Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 no-underline">👤 Perfil</a>
                    <hr class="border-slate-200 my-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 bg-transparent border-none cursor-pointer">
                            🚪 Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
