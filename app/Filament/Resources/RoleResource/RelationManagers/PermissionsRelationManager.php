<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Support\Filament\CriticalPermissions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $title = 'Permissions';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => $query->orderBy('name')),
                Action::make('createPermission')
                    ->label('Create Permission')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        TextInput::make('name')
                            ->label('Permission name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data): void {
                        $permission = Permission::firstOrCreate([
                            'name' => $data['name'],
                            'guard_name' => 'web',
                        ]);

                        $this->getOwnerRecord()->givePermissionTo($permission);

                        Notification::make()->success()->title('Permission created and attached')->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Permission name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->using(function (Model $record, array $data): Model {
                        $record->update(['name' => $data['name']]);

                        return $record;
                    }),
                DetachAction::make(),
                Action::make('deletePermission')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->disabled(fn (Permission $record): bool => in_array($record->name, CriticalPermissions::names(), true))
                    ->tooltip(fn (Permission $record): ?string => in_array($record->name, CriticalPermissions::names(), true)
                        ? 'This permission is used by system policies and cannot be deleted.'
                        : null)
                    ->action(function (Permission $record): void {
                        if (in_array($record->name, CriticalPermissions::names(), true)) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot delete critical permission')
                                ->body('This permission is used by system policies and cannot be deleted.')
                                ->send();

                            return;
                        }

                        $record->delete();
                        Notification::make()->success()->title('Permission deleted')->send();
                    }),
            ]);
    }
}
