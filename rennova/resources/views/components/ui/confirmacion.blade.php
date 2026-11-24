{{-- Diálogo de confirmación único para toda la aplicación. Evita el confirm() nativo del navegador. --}}
<div
    x-data="{
        abierto: false,
        componente: null,
        accion: null,
        parametros: [],
        titulo: 'Confirmar eliminación',
        mensaje: '',
        detalle: null,
        etiqueta: 'Eliminar',
        procesando: false,
        abrir(evento) {
            const origen = evento.target instanceof Element ? evento.target : null;
            const raiz = origen ? origen.closest('[wire\\:id]') : null;

            if (!raiz || !window.Livewire) {
                return;
            }

            const datos = evento.detail || {};

            this.componente = raiz.getAttribute('wire:id');
            this.accion = datos.accion;
            this.parametros = JSON.parse(datos.parametros || '[]');
            this.titulo = datos.titulo || 'Confirmar eliminación';
            this.mensaje = datos.mensaje || '';
            this.detalle = datos.detalle || null;
            this.etiqueta = datos.etiqueta || 'Eliminar';
            this.abierto = true;

            this.$nextTick(() => this.$refs.cancelar?.focus());
        },
        cerrar() {
            if (!this.procesando) {
                this.abierto = false;
            }
        },
        async confirmar() {
            const componente = window.Livewire?.find(this.componente);

            if (!componente) {
                this.abierto = false;

                return;
            }

            this.procesando = true;

            try {
                await componente.call(this.accion, ...this.parametros);
            } catch (error) {
                console.error('No se pudo ejecutar la acción confirmada', error);
            } finally {
                this.procesando = false;
                this.abierto = false;
            }
        },
    }"
    x-on:confirmar-accion.window="abrir($event)"
    x-on:keydown.escape.window="cerrar()"
    x-cloak
>
    <div
        x-show="abierto"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-tinta/50 p-4 backdrop-blur-sm"
        x-on:click.self="cerrar()"
    >
        <div
            x-show="abierto"
            x-transition.scale.origin.center.duration.150ms
            class="w-full max-w-md overflow-hidden rounded-sm border border-arena bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            :aria-label="titulo"
        >
            <div class="flex items-start gap-4 px-6 py-5">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-tierra-suave text-tierra">
                    <flux:icon.exclamation-triangle class="size-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-semibold text-tinta" x-text="titulo"></h2>
                    <p class="mt-1.5 text-sm text-tinta" x-text="mensaje"></p>
                    <p x-show="detalle" x-text="detalle" class="mt-2 text-xs leading-relaxed text-tinta-suave"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-arena bg-hueso px-6 py-3">
                <button
                    type="button"
                    x-ref="cancelar"
                    x-on:click="cerrar()"
                    :disabled="procesando"
                    class="rounded-sm border border-arena bg-white px-3 py-2 text-sm font-medium text-tinta transition-colors hover:bg-corteza-suave disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    x-on:click="confirmar()"
                    :disabled="procesando"
                    class="inline-flex items-center justify-center gap-1.5 rounded-sm bg-tierra px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-[#7f1d1d] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <flux:icon.arrow-path class="size-4 animate-spin" x-show="procesando" x-cloak />
                    <span x-text="procesando ? 'Procesando…' : etiqueta"></span>
                </button>
            </div>
        </div>
    </div>
</div>
