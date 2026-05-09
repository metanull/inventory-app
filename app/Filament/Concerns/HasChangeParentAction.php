<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasChangeParentAction
{
    /**
     * @return class-string<Model>
     */
    abstract protected static function changeParentModelClass(): string;

    abstract protected static function changeParentSelectLabel(): string;

    abstract protected static function changeParentPluralLabel(): string;

    /**
     * Override to restrict the search query for the row action (e.g., excluding descendants).
     */
    protected static function changeParentRowQueryScope(Builder $query, Model $record): Builder
    {
        return $query;
    }

    protected static function changeParentAction(): Action
    {
        $modelClass = static::changeParentModelClass();
        $resourceName = class_basename(static::class);
        $idKey = Str::snake(class_basename($modelClass)).'_id';

        return Action::make('changeParent')
            ->label('Change parent')
            ->icon('heroicon-o-arrow-uturn-up')
            ->form(fn (Model $record): array => [
                Select::make('parent_id')
                    ->label(static::changeParentSelectLabel())
                    ->nullable()
                    ->getSearchResultsUsing(fn (string $search): array => static::changeParentRowQueryScope(
                        $modelClass::query(), $record)
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orWhere('backward_compatibility', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => $modelClass::find($value)?->internal_name ?? $value)
                    ->searchable(),
            ])
            ->action(function (Model $record, array $data) use ($resourceName, $idKey): void {
                try {
                    $record->parent_id = $data['parent_id'] ?? null;
                    $record->save();

                    Notification::make()
                        ->success()
                        ->title('Parent updated')
                        ->send();
                } catch (\RuntimeException $e) {
                    logger()->warning($resourceName.': changeParent failed', [
                        $idKey => $record->id,
                        'new_parent_id' => $data['parent_id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);

                    Notification::make()
                        ->danger()
                        ->title('Cannot change parent')
                        ->body('The selected parent would create a circular hierarchy. Please choose a different parent.')
                        ->send();
                }
            });
    }

    protected static function moveToParentAction(): BulkAction
    {
        $modelClass = static::changeParentModelClass();
        $resourceName = class_basename(static::class);
        $idKey = Str::snake(class_basename($modelClass)).'_id';
        $pluralLabel = static::changeParentPluralLabel();

        return BulkAction::make('moveToParent')
            ->label('Move to parent')
            ->icon('heroicon-o-arrow-uturn-up')
            ->form([
                Select::make('parent_id')
                    ->label(static::changeParentSelectLabel())
                    ->nullable()
                    ->getSearchResultsUsing(fn (string $search): array => $modelClass::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orWhere('backward_compatibility', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => $modelClass::find($value)?->internal_name ?? $value)
                    ->searchable(),
            ])
            ->action(function (EloquentCollection $records, array $data) use ($resourceName, $idKey, $pluralLabel): void {
                $errors = [];
                foreach ($records as $record) {
                    try {
                        $record->parent_id = $data['parent_id'] ?? null;
                        $record->save();
                    } catch (\RuntimeException $e) {
                        logger()->warning($resourceName.': moveToParent failed', [
                            $idKey => $record->id,
                            'new_parent_id' => $data['parent_id'] ?? null,
                            'error' => $e->getMessage(),
                        ]);
                        $errors[] = $record->internal_name;
                    }
                }

                if (empty($errors)) {
                    Notification::make()
                        ->success()
                        ->title($pluralLabel.' moved')
                        ->send();
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Some '.strtolower($pluralLabel).' could not be moved')
                        ->body('The following '.strtolower($pluralLabel).' would create a circular hierarchy: '.implode(', ', $errors))
                        ->send();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}
