@props(['tabs' => [], 'activeTab' => '', 'tabProperty' => 'tab_activo'])

@php
$iconMap = [
    'plus-circle' => '➕',
    'list-ul' => '📋',
    'pencil-square' => '✏️',
    'check-circle' => '✓',
];
@endphp

<div class="mb-6 flex gap-0">
    @foreach($tabs as $tab)
        @if(($tab['can'] ?? true) === true)
            @php
                $isActive = $activeTab === $tab['value'];
                $emoji = $iconMap[$tab['icon'] ?? ''] ?? '';
            @endphp
            <button type="button"
                wire:click="$set('{{ $tabProperty }}', '{{ $tab['value'] }}')"
                class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border transition-all {{ $loop->first ? 'rounded-l-lg' : '' }} {{ !$loop->first ? 'border-l-0' : '' }} {{ $loop->last ? 'rounded-r-lg' : '' }} {{ $isActive ? 'bg-green-800 text-white border-green-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                @if(isset($tab['icon']))
                    {{ $emoji }}
                @endif
                {{ $tab['label'] }}
            </button>
        @endif
    @endforeach
</div>
