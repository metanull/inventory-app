<x-filament-panels::page.simple>
    @if ($step === 'recovery-codes')
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Store these recovery codes in a secure location. They will not be shown again.') }}
        </div>

        <div class="mb-6 space-y-1 rounded-lg bg-gray-50 p-4 font-mono text-sm dark:bg-gray-800">
            @foreach ($recoveryCodes as $code)
                <div>{{ $code }}</div>
            @endforeach
        </div>

        <x-filament::button
            wire:click="complete"
            color="primary"
            class="w-full"
        >
            {{ __('Continue to Dashboard') }}
        </x-filament::button>
    @else
        <div class="mb-4 flex justify-center">
            {!! $qrCodeSvg !!}
        </div>

        <div class="mb-4 text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('Setup Key:') }}
            <span class="font-mono">{{ $setupKey }}</span>
        </div>

        <x-filament-panels::form id="form" wire:submit="confirm">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @endif
</x-filament-panels::page.simple>
