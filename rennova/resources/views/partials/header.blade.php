<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #2A6041;">
    <div class="container-fluid">
        <button class="btn text-white me-2" id="sidebarToggle">
            <i class="bi bi-list fs-4" id="toggleIcon"></i>
        </button>
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">Rennova</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            @if(auth()->check())
                @livewire('notificaciones-campana')
            @endif
            <div class="dropdown">
                <a class="text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name ?? 'Usuario' }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
