<?php if(session()->has('message')): ?>
    <div x-data="{ open: true }" x-show="open" x-transition
        class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 shadow-sm" role="alert">
        <span class="text-emerald-600">✓</span>
        <span class="flex-1 text-sm font-medium"><?php echo e(session('message')); ?></span>
        <button type="button" class="text-emerald-600 hover:text-emerald-800" @click="open = false">✕</button>
    </div>
<?php endif; ?>
<?php if(session()->has('error')): ?>
    <div x-data="{ open: true }" x-show="open" x-transition
        class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3 text-red-800 shadow-sm" role="alert">
        <span class="text-red-600">⚠️</span>
        <span class="flex-1 text-sm font-medium"><?php echo e(session('error')); ?></span>
        <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">✕</button>
    </div>
<?php endif; ?>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/flash-messages.blade.php ENDPATH**/ ?>