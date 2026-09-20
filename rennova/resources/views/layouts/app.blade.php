<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet">

    <!-- Scripts & Styles (Tailwind CSS via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-hueso flex h-screen flex-col">
    <div x-data="layoutState()" x-cloak>
        @include('partials.header')

        <!-- Sidebar Overlay (for mobile) -->
        <div class="sidebar-overlay" :class="{ 'show': mobileOpen }" @click="mobileOpen = false"></div>

        <div class="layout-container">
            @include('partials.sidebar')

            <!-- Main Content + Footer -->
            <div class="page-wrapper grow flex flex-col bg-hueso" id="pageWrapper">
                <main class="main-content flex-1" id="mainContent">
                    <div class="flex-1">
                        @if(session('status'))
                            <x-ui.alert variant="success" class="mb-4">
                                {{ session('status') }}
                            </x-ui.alert>
                        @endif

                        @if(session('error'))
                            <x-ui.alert variant="danger" class="mb-4">
                                {{ session('error') }}
                            </x-ui.alert>
                        @endif

                        @if(session('warning'))
                            <x-ui.alert variant="warning" class="mb-4">
                                {{ session('warning') }}
                            </x-ui.alert>
                        @endif

                        @if(session('info'))
                            <x-ui.alert variant="info" class="mb-4">
                                {{ session('info') }}
                            </x-ui.alert>
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
