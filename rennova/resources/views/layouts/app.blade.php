<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    <!-- Scripts & Styles (Tailwind CSS via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-slate-50 flex flex-col" style="height: 100vh;">
    <div x-data="layoutState()" x-cloak>
        @include('partials.header')

        <!-- Sidebar Overlay (for mobile) -->
        <div class="sidebar-overlay" :class="{ 'show': mobileOpen }" @click="mobileOpen = false"></div>

        <div class="layout-container">
            @include('partials.sidebar')

            <!-- Main Content + Footer -->
            <div class="page-wrapper grow flex flex-col bg-slate-50" id="pageWrapper">
                <main class="main-content flex-1" id="mainContent">
                    <div class="flex-1">
                        @if(session('status'))
                            <div class="max-w-7xl mx-auto px-4 py-2">
                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 flex items-center justify-between" role="alert">
                                    <span><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</span>
                                    <button type="button" class="text-emerald-600 hover:text-emerald-800 ml-4" @click="$el.closest('[role=alert]').remove()">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="max-w-7xl mx-auto px-4 py-2">
                                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 flex items-center justify-between" role="alert">
                                    <span><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</span>
                                    <button type="button" class="text-red-600 hover:text-red-800 ml-4" @click="$el.closest('[role=alert]').remove()">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if (isset($slot))
                            {{ $slot }}
                        @else
                            @yield('content')
                        @endif

                    </div>
                </main>
                @include('partials.footer')
            </div>
        </div>
    </div>

    @stack('scripts')

    <!-- Layout State (Alpine.js) -->
    <script>
        function layoutState() {
            return {
                collapsed: false,
                mobileOpen: false,
                init() {
                    this.collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                },
                toggleSidebar() {
                    if (window.innerWidth <= 768) {
                        this.mobileOpen = !this.mobileOpen;
                    } else {
                        this.collapsed = !this.collapsed;
                        localStorage.setItem('sidebarCollapsed', this.collapsed);
                    }
                }
            }
        }
    </script>

    <!-- Livewire Scripts (incluye Alpine.js automáticamente en v3) -->
    @livewireScripts
</body>
</html>
