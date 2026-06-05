
<?php if(session()->has('message')): ?>
    <div x-data="{ open: true }" x-show="open" x-transition
        class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <span class="flex-1 font-medium"><?php echo e(session('message')); ?></span>
        <button type="button" class="text-green-600 hover:text-green-800" @click="open = false">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
<?php endif; ?>
<?php if(session()->has('error')): ?>
    <div x-data="{ open: true }" x-show="open" x-transition
        class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span class="flex-1 font-medium"><?php echo e(session('error')); ?></span>
        <button type="button" class="text-red-600 hover:text-red-800" @click="open = false">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
<?php endif; ?>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/flash-messages.blade.php ENDPATH**/ ?>