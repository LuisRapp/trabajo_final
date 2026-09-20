@props([
    'tabs' => [],
    'activeTab' => '',
    'tabProperty' => 'tab_activo',
])

@php
$legacyIconMap = [
    'plus-circle' => 'plus',
    'list-ul' => 'list-bullet',
];
@endphp

<div class="mb-6 flex gap-0" role="tablist">
    @foreach($tabs as $tab)
        @if(($tab['can'] ?? true) === true)
            @php
                $id = $tab['id'] ?? ($tab['value'] ?? null);
                $isActive = $activeTab === ($tab['value'] ?? $id);
                $label = $tab['label'] ?? '';
                $icon = $tab['icon'] ?? null;
                $resolvedIcon = $icon ? ($legacyIconMap[$icon] ?? $icon) : null;
                $href = $tab['href'] ?? null;
                $wireClick = $tab['wire:click'] ?? $tab['wireClick'] ?? null;

                if (!$href && !$wireClick && $id !== null) {
                    $wireClick = "\$set('{$tabProperty}', '{$id}')";
                }

                $firstClass = $loop->first ? 'rounded-l-sm' : '';
                $lastClass = $loop->last ? 'rounded-r-sm' : '';
                $borderLeftClass = !$loop->first ? 'border-l-0' : '';
                $activeClass = $isActive
                    ? 'bg-pino text-white border-pino'
                    : 'bg-white text-tinta-suave border-arena hover:bg-corteza-suave';
            @endphp

            @if($href)
                <a
                    href="{{ $href }}"
                    class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border transition-all {{ $firstClass }} {{ $borderLeftClass }} {{ $lastClass }} {{ $activeClass }}"
                    role="tab"
                    @if($isActive) aria-selected="true" @endif
                >
                    @if($resolvedIcon)
                        <x-dynamic-component component="flux::icon.{{ $resolvedIcon }}" class="size-4" />
                    @endif
                    {{ $label }}
                </a>
            @else
                <button
                    type="button"
                    wire:click="{{ $wireClick }}"
                    class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border transition-all {{ $firstClass }} {{ $borderLeftClass }} {{ $lastClass }} {{ $activeClass }}"
                    role="tab"
                    @if($isActive) aria-selected="true" @endif
                >
                    @if($resolvedIcon)
                        <x-dynamic-component component="flux::icon.{{ $resolvedIcon }}" class="size-4" />
                    @endif
                    {{ $label }}
                </button>
            @endif
        @endif
    @endforeach
</div>
