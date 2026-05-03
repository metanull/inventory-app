<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPassword extends SimplePage
{
    use InteractsWithFormActions;

    protected static string $view = 'filament.auth.reset-password';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public string $token = '';

    public function mount(string $token): void
    {
        if (Filament::auth()->check()) {
            $this->redirect(Filament::getUrl());

            return;
        }

        $this->token = $token;

        $this->form->fill([
            'email' => request()->query('email', ''),
        ]);
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
                        TextInput::make('email')
                            ->label(__('Email address'))
                            ->email()
                            ->required()
                            ->readOnly(),
                        TextInput::make('password')
                            ->label(__('New password'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->confirmed()
                            ->extraInputAttributes(['tabindex' => 1]),
                        TextInput::make('password_confirmation')
                            ->label(__('Confirm new password'))
                            ->password()
                            ->revealable()
                            ->required()
                            ->extraInputAttributes(['tabindex' => 2]),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.email' => [__('We could not find a user with that email address.')],
            ]);
        }

        $tokenRepository = Password::broker(config('fortify.passwords'))->getRepository();
        if (! $tokenRepository->exists($user, $this->token)) {
            throw ValidationException::withMessages([
                'data.email' => [__('This password reset token is invalid or has expired.')],
            ]);
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            session()->put('filament.admin.password_reset.user_id', $user->id);
            session()->put('filament.admin.password_reset.password_hash', Hash::make($data['password']));
            session()->put('filament.admin.password_reset.token', $this->token);

            $this->redirect(
                Filament::getCurrentPanel()->route('auth.password.reset.mfa')
            );

            return;
        }

        $this->applyPasswordReset($user, $data['password']);
    }

    protected function applyPasswordReset(User $user, string $password): void
    {
        $status = Password::broker(config('fortify.passwords'))->reset(
            ['email' => $user->email, 'password' => $password, 'password_confirmation' => $password, 'token' => $this->token],
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Notification::make()
                ->title(__('Your password has been reset. You may now log in.'))
                ->success()
                ->send();

            $this->redirect(Filament::getLoginUrl());

            return;
        }

        throw ValidationException::withMessages([
            'data.email' => [__('This password reset token is invalid or has expired.')],
        ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label(__('Reset password'))
                ->submit('submit'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getTitle(): string|Htmlable
    {
        return __('Reset Password');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Reset your password');
    }

    public static function getSlug(): string
    {
        return 'reset-password';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.password.reset';
    }
}
