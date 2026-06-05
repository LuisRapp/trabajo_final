<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1f5f3b;">
    <div class="container-fluid">
        <button class="text-white mr-2 p-0 hover:opacity-80" id="sidebarToggle">
            <span id="toggleIcon" class="text-base">☰</span>
        </button>
        <a class="navbar-brand fw-bold text-slate-100 text-sm" href="{{ route('dashboard') }}">Rennova</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            @if(auth()->check())
                @livewire('notificaciones-campana')
            @endif
            <div class="dropdown">
                @php
                    $user = auth()->user();
                    $displayName = $user?->name
                        ?? trim(($user?->nombre ?? '') . ' ' . ($user?->apellido ?? ''))
                        ?: 'Usuario';
                @endphp
                <a class="text-white no-underline dropdown-toggle text-xs" href="#" role="button" data-bs-toggle="dropdown">
                    👤 {{ $displayName }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end text-xs">
                    <li><a class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 rounded" href="{{ route('dashboard') }}">⚡ Dashboard</a></li>
                    <li><a class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 rounded" href="{{ route('profile.edit') }}">👤 Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 rounded">🚪 Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
