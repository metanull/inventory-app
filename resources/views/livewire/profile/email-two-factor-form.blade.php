<div>
    <x-form-section submit="updatePreference">
        <x-slot name="title">
            {{ __('Email Two Factor Authentication') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Add additional security to your account using email-based two factor authentication. When enabled, you will receive verification codes via email during authentication.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Email 2FA Status -->
            <div class="col-span-6 sm:col-span-4">
                @if ($emailTwoFactorEnabled)
                    <div class="flex items-center">
                        <div class="text-sm text-gray-600">
                            {{ __('Email two factor authentication is currently enabled.') }}
                        </div>
                        <div class="ml-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    @if ($showingEmailTest)
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">
                                {{ __('Test Email Two Factor Authentication') }}
                            </h4>
                            <p class="text-sm text-blue-700 mb-3">
                                {{ __('Send a test verification code to your email to confirm email 2FA is working correctly.') }}
                            </p>
                            
                            <div class="flex items-center space-x-2">
                                <x-button type="button" wire:click="sendTestEmailCode" class="text-xs">
                                    {{ __('Send Test Code') }}
                                </x-button>
                            </div>

                            @if (session('status'))
                                <div class="mt-2 text-sm text-green-600">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @error('email')
                                <div class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="mt-3">
                                <x-label for="testEmailCode" value="{{ __('Enter verification code from email') }}" />
                                <div class="flex items-center space-x-2 mt-1">
                                    <x-input id="testEmailCode" type="text" class="block w-32 text-center font-mono"
                                             wire:model="testEmailCode" maxlength="6" placeholder="000000" />
                                    <x-button type="button" wire:click="verifyTestEmailCode" class="text-xs"
                                              :disabled="empty($testEmailCode)">
                                        {{ __('Verify') }}
                                    </x-button>
                                </div>
                                @error('testEmailCode')
                                    <div class="mt-1 text-sm text-red-600">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-sm text-gray-600">
                        {{ __('Email two factor authentication is not enabled.') }}
                    </div>
                @endif
            </div>

            <!-- 2FA Preference Selection -->
            @if (count($this->availablePreferences) > 1)
                <div class="col-span-6 sm:col-span-4">
                    <x-label for="preferred2faMethod" value="{{ __('Preferred Authentication Method') }}" />
                    <select id="preferred2faMethod" 
                            wire:model="preferred2faMethod" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($this->availablePreferences as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('preferred2faMethod')
                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                    <div class="mt-1 text-xs text-gray-500">
                        {{ __('Choose which two-factor authentication method to use when both are available.') }}
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="actions">
            @if ($emailTwoFactorEnabled)
                <x-confirms-password wire:then="disableEmailTwoFactor">
                    <x-danger-button wire:loading.attr="disabled">
                        {{ __('Disable Email 2FA') }}
                    </x-danger-button>
                </x-confirms-password>

                @if (!$showingEmailTest)
                    <x-button type="button" wire:click="$set('showingEmailTest', true)" class="ml-2">
                        {{ __('Test Email 2FA') }}
                    </x-button>
                @endif
            @else
                <x-confirms-password wire:then="enableEmailTwoFactor">
                    <x-button wire:loading.attr="disabled">
                        {{ __('Enable Email 2FA') }}
                    </x-button>
                </x-confirms-password>
            @endif

            @if (count($this->availablePreferences) > 1)
                <x-button type="submit" class="ml-2" wire:loading.attr="disabled">
                    {{ __('Update Preference') }}
                </x-button>
            @endif

            <x-action-message class="ml-3" on="email-2fa-enabled">
                {{ __('Email 2FA enabled.') }}
            </x-action-message>

            <x-action-message class="ml-3" on="email-2fa-disabled">
                {{ __('Email 2FA disabled.') }}
            </x-action-message>

            <x-action-message class="ml-3" on="2fa-preference-updated">
                {{ __('Preference updated.') }}
            </x-action-message>
        </x-slot>
    </x-form-section>
</div>
