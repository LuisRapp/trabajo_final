@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center text-hueso">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:subheading class="text-arena">{{ $description }}</flux:subheading>
</div>
