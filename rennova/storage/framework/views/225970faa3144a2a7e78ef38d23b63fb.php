<!-- Modal de Justificación por Día No Operativo -->
<div
    x-cloak
    x-show="openOverrideModal"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/40 backdrop-blur-[1px] p-4 sm:p-6"
    @keydown.escape.window="openOverrideModal = false"
>
    <div
        class="w-[42rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl bg-white shadow-2xl border border-slate-200"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-3 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2 scale-[0.98]"
        @click.away="openOverrideModal = false"
    >
        <div class="px-6 py-2 border-b border-amber-200 bg-amber-50 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>
                <h3 class="text-base font-semibold text-slate-900 leading-tight">Justificacion por dia no operativo</h3>
            </div>
            <button
                type="button"
                class="p-0 m-0 border-0 bg-transparent shadow-none text-slate-400 hover:text-slate-700 leading-none transition-colors focus:outline-none"
                @click="openOverrideModal = false"
                aria-label="Cerrar"
            >
                <i class="bi bi-x text-lg"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm text-slate-700 mb-4">
                Para continuar, debes indicar el motivo por el cual se decide operar en este dia.
            </p>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Motivo <span class="text-red-500">*</span></label>
            <textarea
                x-model="overrideMotivo"
                rows="4"
                class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-200 focus:outline-none"
                placeholder="Ej: compromiso logistico con cliente, ventana operativa segura, prioridad de entrega"
            ></textarea>
            <p x-show="modalError" x-text="modalError" class="mt-2 text-sm text-red-600"></p>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button
                type="button"
                class="px-5 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-700 font-medium hover:bg-slate-100 transition-colors"
                @click="openOverrideModal = false"
            >
                Cancelar
            </button>
            <button
                type="button"
                class="px-5 py-2.5 rounded-lg bg-green-700 text-white font-semibold hover:bg-green-800 transition-colors"
                @click.prevent="
                    if (!overrideMotivo || !overrideMotivo.trim()) {
                        modalError = 'Debes ingresar un motivo.';
                        return;
                    }
                    modalError = '';
                    overrideConfirmado = true;
                    openOverrideModal = false;
                    $wire.guardar();
                "
            >
                Confirmar y guardar
            </button>
        </div>
    </div>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/partials/clima-override-modal.blade.php ENDPATH**/ ?>