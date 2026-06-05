@props(['colspan' => '1', 'message' => 'No hay registros', 'icon' => 'inbox'])

@php
$iconMap = [
    'inbox' => '📭',
    'archive' => '📦',
    'list-ul' => '📋',
    'person' => '👤',
    'truck' => '🚛',
    'exclamation-triangle' => '⚠️',
];
$emoji = $iconMap[$icon] ?? '📭';
@endphp

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-8 text-center">
        <span class="text-3xl text-slate-300 block mb-2">{{ $emoji }}</span>
        <p class="text-slate-500 font-medium">{{ $message }}</p>
    </td>
</tr>
