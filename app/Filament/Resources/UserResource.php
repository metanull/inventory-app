<?php

namespace App\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\User;
use App\Services\UserPasswordResetService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', User::class) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->searchable()
                    ->sortable(false),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null)
                    ->sortable(),
                IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->hasEnabledTwoFactorAuthentication())
                    ->sortable(false),
                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('suspended_at')
                    ->label('Suspended')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'name')->toArray())
                    ->query(fn (Builder $query, array $data): Builder => $data['value']
                        ? $query->whereHas('roles', fn (Builder $q) => $q->where('name', $data['value']))
                        : $query),
                Filter::make('two_factor_enabled')
                    ->label('2FA Enabled')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('two_factor_confirmed_at')),
                Filter::make('email_verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Filter::make('pending_approval')
                    ->label('Pending Approval')
                    ->query(fn (Builder $query): Builder => $query->whereNull('approved_at')),
                Filter::make('suspended')
                    ->label('Suspended')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('suspended_at')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('resendVerification')
                    ->label('Resend Verification')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn (User $record): bool => ! $record->hasVerifiedEmail())
                    ->action(function (User $record): void {
                        $record->sendEmailVerificationNotification();
                        Notification::make()->success()->title('Verification email sent')->send();
                    }),
                Action::make('markEmailVerified')
                    ->label('Mark Email Verified')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => ! $record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill(['email_verified_at' => now()])->save();
                        Notification::make()->success()->title('Email marked as verified')->send();
                    }),
                Action::make('clearEmailVerification')
                    ->label('Clear Email Verification')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (User $record): bool => $record->hasVerifiedEmail())
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill(['email_verified_at' => null])->save();
                        Notification::make()->success()->title('Email verification cleared')->send();
                    }),
                Action::make('generatePassword')
                    ->label('Generate New Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Generate New Password')
                    ->modalDescription('A new password will be generated and emailed to the user. You will also see it once in the notification.')
                    ->action(function (User $record): void {
                        $password = app(UserPasswordResetService::class)->generateAndEmail($record);
                        Notification::make()
                            ->success()
                            ->title('New password generated')
                            ->body('Password: '.$password)
                            ->persistent()
                            ->send();
                    }),
                Action::make('assignRole')
                    ->label('Assign Role')
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->form([
                        Select::make('role_id')
                            ->label('Role')
                            ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $role = Role::findById((int) $data['role_id']);
                        $record->syncRoles([$role]);
                        Notification::make()->success()->title('Role assigned')->send();
                    }),
                Action::make('disableTwoFactor')
                    ->label('Disable Two-Factor')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->hasEnabledTwoFactorAuthentication())
                    ->requiresConfirmation()
                    ->modalHeading('Disable Two-Factor Authentication')
                    ->modalDescription('This will disable two-factor authentication for this user. Use this as an admin recovery action when the user has lost their device.')
                    ->action(function (User $record): void {
                        app(DisableTwoFactorAuthentication::class)($record);
                        Notification::make()->success()->title('Two-factor authentication disabled')->send();
                    }),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->approved_at === null)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill(['approved_at' => now()])->save();
                        Notification::make()->success()->title('User approved')->send();
                    }),
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->suspended_at === null)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill(['suspended_at' => now()])->save();
                        Notification::make()->success()->title('User suspended')->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        $records->each(fn (User $user) => $user->forceFill(['approved_at' => now()])->save());
                        Notification::make()->success()->title('Users approved')->send();
                    }),
                BulkAction::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records): void {
                        /** @var User $authUser */
                        $authUser = auth()->user();
                        $records
                            ->reject(fn (User $user) => $user->is($authUser))
                            ->each(fn (User $user) => $user->forceFill(['suspended_at' => now()])->save());
                        Notification::make()->success()->title('Users suspended')->send();
                    }),
                BulkAction::make('assignRole')
                    ->label('Assign Role')
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->form([
                        Select::make('role_id')
                            ->label('Role')
                            ->options(fn (): array => Role::query()->orderBy('name')->pluck('name', 'id')->toArray())
                            ->required(),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data): void {
                        $role = Role::findById((int) $data['role_id']);
                        $records->each(fn (User $user) => $user->syncRoles([$role]));
                        Notification::make()->success()->title('Role assigned')->send();
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('email'),
                TextEntry::make('roles.name')
                    ->label('Roles')
                    ->badge(),
                IconEntry::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null),
                IconEntry::make('two_factor_confirmed_at')
                    ->label('2FA Enabled')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->hasEnabledTwoFactorAuthentication()),
                TextEntry::make('approved_at')
                    ->label('Approved')
                    ->dateTime(),
                TextEntry::make('suspended_at')
                    ->label('Suspended')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUser::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => ViewUser::route('/{record}'),
        ];
    }
}
