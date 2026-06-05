@props(['model' => 'busqueda', 'placeholder' => 'Buscar...'])

<div class="mb-6">
    <div class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg bg-slate-50">
        <span class="text-slate-400">🔍</span>
        <input type="text"
            class="flex-1 bg-slate-50 border-0 focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400"
            placeholder="{{ $placeholder }}"
            wire:model.live="{{ $model }}">
    </div>
</div>
