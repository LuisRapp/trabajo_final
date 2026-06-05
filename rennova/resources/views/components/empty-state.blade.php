@props(['colspan' => '1', 'message' => 'No hay registros', 'icon' => 'inbox'])

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-8 text-center">
        <i class="bi bi-{{ $icon }} text-slate-300 block mb-2" style="font-size: 2rem;"></i>
        <p class="text-slate-500 font-medium">{{ $message }}</p>
    </td>
</tr>
