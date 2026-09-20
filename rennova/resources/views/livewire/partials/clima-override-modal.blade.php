<!-- Modal de Justificación por Día No Operativo -->
<div
    x-cloak
    x-show="openOverrideModal"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-end justify-center bg-tinta/40 backdrop-blur-[1px] p-4 sm:p-6"
    @keydown.escape.window="openOverrideModal = false"
>
    <div
        class="w-[42rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl bg-blanco shadow-2xl border border-arena"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-3 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2 scale-[0.98]"
        @click.away="openOverrideModal = false"
    >
        <div class="px-6 py-2 border-b border-resina bg-resina-suave flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-resina/10 text-resina">
                    <flux:icon.exclamation-triangle class="size-4" />
                </span>
                <h3 class="text-base font-semibold text-tinta leading-tight">Justificacion por dia no operativo</h3>
            </div>
            <button
                type="button"
                class="p-0 m-0 border-0 bg-transparent shadow-none text-tinta-suave hover:text-tinta leading-none transition-colors focus:outline-hidden"
                @click="openOverrideModal = false"
                aria-label="Cerrar"
            >
                <flux:icon.x-mark class="size-4" />
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm text-tinta-suave mb-4">
                Para continuar, debes indicar el motivo por el cual se decide operar en este dia.
            </p>
            <label class="block text-sm font-semibold text-tinta mb-2">Motivo <span class="text-tierra">*</span></label>
            <textarea
                x-model="overrideMotivo"
                rows="4"
                class="w-full px-4 py-3 border border-arena rounded-lg bg-blanco focus:border-resina focus:ring-2 focus:ring-resina/20 focus:outline-hidden"
                placeholder="Ej: compromiso logistico con cliente, ventana operativa segura, prioridad de entrega"
            ></textarea>
            <p x-show="modalError" x-text="modalError" class="mt-2 text-sm text-tierra"></p>
        </div>
        <div class="px-6 py-4 border-t border-arena bg-corteza-suave flex justify-end gap-3">
            <button
                type="button"
                class="px-5 py-2.5 rounded-lg bg-blanco border border-arena text-tinta font-medium hover:bg-corteza-suave transition-colors"
                @click="openOverrideModal = false"
            >
                Cancelar
            </button>
            <button
                type="button"
                class="px-5 py-2.5 rounded-lg bg-musgo text-white font-semibold hover:bg-musgo/90 transition-colors"
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
</div>
