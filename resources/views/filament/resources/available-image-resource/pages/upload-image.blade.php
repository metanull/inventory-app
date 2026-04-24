<x-filament-panels::page>
    <form wire:submit="upload">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                {{ __('Upload') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
