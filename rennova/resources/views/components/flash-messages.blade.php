@if (session()->has('message'))
    <x-ui.alert variant="success" class="mb-6">
        {{ session('message') }}
    </x-ui.alert>
@endif

@if (session()->has('error'))
    <x-ui.alert variant="danger" class="mb-6">
        {{ session('error') }}
    </x-ui.alert>
@endif

@if (session()->has('warning'))
    <x-ui.alert variant="warning" class="mb-6">
        {{ session('warning') }}
    </x-ui.alert>
@endif

@if (session()->has('info'))
    <x-ui.alert variant="info" class="mb-6">
        {{ session('info') }}
    </x-ui.alert>
@endif
