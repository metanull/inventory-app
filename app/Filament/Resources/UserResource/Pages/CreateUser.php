<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\UserPasswordResetService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Admin-created users are approved immediately and get a random placeholder password.
        // The invitation email will let them set their own password.
        $data['approved_at'] = now();
        $data['password'] = Hash::make(Str::random(32));

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        app(UserPasswordResetService::class)->sendResetLink($user);

        Notification::make()
            ->success()
            ->title('User created and invitation sent')
            ->body('A password reset link has been sent to '.$user->email.'.')
            ->send();
    }
}
