@props(['model' => 'busqueda', 'placeholder' => 'Buscar...'])

<div class="mb-6">
    <div class="flex items-center gap-2 px-4 py-3 border border-slate-300 rounded-lg bg-slate-50">
        <i class="bi bi-search text-slate-500"></i>
        <input type="text"
            class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400"
            placeholder="{{ $placeholder }}"
            wire:model.live="{{ $model }}">
    </div>
</div>
