@props([
    'variant' => 'primary',
    'size' => 'default',
    'icon' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'border border-arena bg-white text-tinta hover:bg-corteza-suave',
        'danger' => 'bg-tierra text-white hover:bg-[#7f1d1d]',
        'ghost' => 'text-tinta-suave hover:bg-corteza-suave hover:text-tinta',
        default => 'bg-pino text-white hover:bg-pino-oscuro',
    };

    $sizeClasses = match ($size) {
        'sm' => 'rounded-sm px-2.5 py-1.5 text-xs',
        'lg' => 'rounded-sm px-4 py-2.5 text-sm',
        default => 'rounded-sm px-3 py-2 text-sm',
    };

    $iconSize = match ($size) {
        'sm' => 'size-3.5',
        'lg' => 'size-5',
        default => 'size-4',
    };

    $classes = "inline-flex items-center justify-center gap-1.5 font-medium transition-colors focus:outline-hidden focus:ring-2 focus:ring-pino/30 disabled:opacity-50 disabled:cursor-not-allowed {$variantClasses} {$sizeClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
@endif
    @if($icon === 'plus')
        <flux:icon.plus class="{{ $iconSize }}" />
    @elseif($icon === 'check')
        <flux:icon.check class="{{ $iconSize }}" />
    @elseif($icon === 'x-mark')
        <flux:icon.x-mark class="{{ $iconSize }}" />
    @elseif($icon === 'pencil-square')
        <flux:icon.pencil-square class="{{ $iconSize }}" />
    @elseif($icon === 'trash')
        <flux:icon.trash class="{{ $iconSize }}" />
    @elseif($icon === 'eye')
        <flux:icon.eye class="{{ $iconSize }}" />
    @elseif($icon === 'calculator')
        <flux:icon.calculator class="{{ $iconSize }}" />
    @elseif($icon === 'banknotes')
        <flux:icon.banknotes class="{{ $iconSize }}" />
    @elseif($icon === 'document-text')
        <flux:icon.document-text class="{{ $iconSize }}" />
    @elseif($icon === 'document-arrow-down')
        <flux:icon.document-arrow-down class="{{ $iconSize }}" />
    @elseif($icon === 'arrow-path')
        <flux:icon.arrow-path class="{{ $iconSize }}" />
    @elseif($icon === 'magnifying-glass')
        <flux:icon.magnifying-glass class="{{ $iconSize }}" />
    @elseif($icon === 'flag')
        <flux:icon.flag class="{{ $iconSize }}" />
    @elseif($icon === 'play')
        <flux:icon.play class="{{ $iconSize }}" />
    @elseif($icon === 'calendar-date-range')
        <flux:icon.calendar-date-range class="{{ $iconSize }}" />
    @elseif($icon === 'chart-bar')
        <flux:icon.chart-bar class="{{ $iconSize }}" />
    @elseif($icon === 'arrow-uturn-left')
        <flux:icon.arrow-uturn-left class="{{ $iconSize }}" />
    @elseif($icon === 'arrow-left')
        <flux:icon.arrow-left class="{{ $iconSize }}" />
    @elseif($icon === 'cog')
        <flux:icon.cog class="{{ $iconSize }}" />
    @elseif($icon === 'cog-6-tooth')
        <flux:icon.cog-6-tooth class="{{ $iconSize }}" />
    @elseif($icon === 'wrench')
        <flux:icon.wrench class="{{ $iconSize }}" />
    @elseif($icon === 'wrench-screwdriver')
        <flux:icon.wrench-screwdriver class="{{ $iconSize }}" />
    @elseif($icon === 'truck')
        <flux:icon.truck class="{{ $iconSize }}" />
    @elseif($icon === 'cube')
        <flux:icon.cube class="{{ $iconSize }}" />
    @elseif($icon === 'link')
        <flux:icon.link class="{{ $iconSize }}" />
    @elseif($icon === 'bolt')
        <flux:icon.bolt class="{{ $iconSize }}" />
    @elseif($icon === 'user')
        <flux:icon.user class="{{ $iconSize }}" />
    @elseif($icon === 'users')
        <flux:icon.users class="{{ $iconSize }}" />
    @elseif($icon === 'identification')
        <flux:icon.identification class="{{ $iconSize }}" />
    @elseif($icon === 'briefcase')
        <flux:icon.briefcase class="{{ $iconSize }}" />
    @elseif($icon === 'clock')
        <flux:icon.clock class="{{ $iconSize }}" />
    @elseif($icon === 'scale')
        <flux:icon.scale class="{{ $iconSize }}" />
    @elseif($icon === 'tag')
        <flux:icon.tag class="{{ $iconSize }}" />
    @elseif($icon === 'currency-dollar')
        <flux:icon.currency-dollar class="{{ $iconSize }}" />
    @elseif($icon === 'archive-box')
        <flux:icon.archive-box class="{{ $iconSize }}" />
    @elseif($icon === 'bell')
        <flux:icon.bell class="{{ $iconSize }}" />
    @elseif($icon === 'shield-check')
        <flux:icon.shield-check class="{{ $iconSize }}" />
    @elseif($icon === 'map-pin')
        <flux:icon.map-pin class="{{ $iconSize }}" />
    @elseif($icon === 'home')
        <flux:icon.home class="{{ $iconSize }}" />
    @elseif($icon === 'list-bullet')
        <flux:icon.list-bullet class="{{ $iconSize }}" />
    @elseif($icon === 'information-circle')
        <flux:icon.information-circle class="{{ $iconSize }}" />
    @elseif($icon === 'exclamation-triangle')
        <flux:icon.exclamation-triangle class="{{ $iconSize }}" />
    @elseif($icon === 'check-circle')
        <flux:icon.check-circle class="{{ $iconSize }}" />
    @elseif($icon === 'x-circle')
        <flux:icon.x-circle class="{{ $iconSize }}" />
    @elseif($icon === 'computer-desktop')
        <flux:icon.computer-desktop class="{{ $iconSize }}" />
    @elseif($icon === 'square-3-stack-3d')
        <flux:icon.square-3-stack-3d class="{{ $iconSize }}" />
    @elseif($icon === 'sparkles')
        <flux:icon.sparkles class="{{ $iconSize }}" />
    @elseif($icon === 'calendar-days')
        <flux:icon.calendar-days class="{{ $iconSize }}" />
    @elseif($icon === 'clipboard-document-list')
        <flux:icon.clipboard-document-list class="{{ $iconSize }}" />
    @elseif($icon === 'clipboard-document-check')
        <flux:icon.clipboard-document-check class="{{ $iconSize }}" />
    @elseif($icon === 'cloud-arrow-down')
        <flux:icon.cloud-arrow-down class="{{ $iconSize }}" />
    @elseif($icon === 'ellipsis-vertical')
        <flux:icon.ellipsis-vertical class="{{ $iconSize }}" />
    @elseif($icon === 'arrow-right')
        <flux:icon.arrow-right class="{{ $iconSize }}" />
    @elseif($icon === 'arrow-up')
        <flux:icon.arrow-up class="{{ $iconSize }}" />
    @elseif($icon)
        <x-dynamic-component component="flux::icon.{{ $icon }}" class="{{ $iconSize }}" />
    @endif
    {{ $slot }}
@if($href)
    </a>
@else
    </button>
@endif
