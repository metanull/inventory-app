<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

class TwoFactorSetup extends SimplePage
{
    use InteractsWithFormActions;

    protected static string $view = 'filament.auth.two-factor-setup';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public string $step = 'setup';

    public string $qrCodeSvg = '';

    public string $setupKey = '';

    /**
     * @var array<int, string>
     */
    public array $recoveryCodes = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::guard(Filament::getAuthGuard())->user();

        if (! is_null($user->two_factor_confirmed_at)) {
            $this->redirect(Filament::getUrl());

            return;
        }

        if (empty($user->two_factor_secret)) {
            app(EnableTwoFactorAuthentication::class)($user);
            $user->refresh();
        }

        $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
        $this->setupKey = decrypt($user->two_factor_secret);
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int|string, string|Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Authenticator Code'))
                            ->placeholder('000 000')
                            ->required()
                            ->maxLength(8)
                            ->autocomplete('one-time-code')
                            ->autofocus()
                            ->extraInputAttributes(['tabindex' => 1]),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function confirm(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = Auth::guard(Filament::getAuthGuard())->user();

        try {
            app(ConfirmTwoFactorAuthentication::class)($user, (string) ($data['code'] ?? ''));
        } catch (ValidationException) {
            throw ValidationException::withMessages([
                'data.code' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        $user->refresh();
        $this->recoveryCodes = $user->recoveryCodes();
        $this->step = 'recovery-codes';
    }

    public function complete(): void
    {
        $this->redirect(Filament::getUrl());
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('confirm')
                ->label(__('Confirm Setup'))
                ->submit('confirm'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Set Up Two-Factor Authentication');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Set Up Two-Factor Authentication');
    }

    public static function getSlug(): string
    {
        return 'two-factor-setup';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.two-factor-setup';
    }
}
