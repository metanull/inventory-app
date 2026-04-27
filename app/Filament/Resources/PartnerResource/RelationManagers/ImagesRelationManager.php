<?php

namespace App\Filament\Resources\PartnerResource\RelationManagers;

use App\Models\AvailableImage;
use App\Models\PartnerImage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'partnerImages';

    protected static ?string $recordTitleAttribute = 'path';

    protected static ?string $title = 'Images';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('display_order', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                ImageColumn::make('preview')
                    ->label('Preview')
                    ->getStateUsing(fn ($record) => route('filament.admin.partner-image.view', [
                        'partner' => $record->partner_id,
                        'partnerImage' => $record->id,
                    ]))
                    ->height(64)
                    ->url(fn ($record) => route('filament.admin.partner-image.view', [
                        'partner' => $record->partner_id,
                        'partnerImage' => $record->id,
                    ]))
                    ->openUrlInNewTab()
                    ->defaultImageUrl(null),
                TextColumn::make('path')
                    ->label('Filename')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alt_text')
                    ->label('Alt text')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('mime_type')
                    ->label('Type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('size')
                    ->label('Size (bytes)')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Attached')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('attach')
                    ->label('Attach image')
                    ->icon('heroicon-o-paper-clip')
                    ->form([
                        Select::make('available_image_id')
                            ->label('Available image')
                            ->required()
                            ->getSearchResultsUsing(fn (string $search): array => AvailableImage::query()
                                ->where('path', 'like', "%{$search}%")
                                ->orWhere('comment', 'like', "%{$search}%")
                                ->orderBy('created_at', 'desc')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (AvailableImage $img) => [
                                    $img->id => $img->path.($img->comment ? ' — '.$img->comment : ''),
                                ])
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => AvailableImage::find($value)?->path ?? $value)
                            ->searchable(),
                        TextInput::make('alt_text')
                            ->label('Alt text')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        $availableImage = AvailableImage::find($data['available_image_id']);

                        if (! $availableImage) {
                            Notification::make()
                                ->danger()
                                ->title('Image not found')
                                ->body('The selected image is no longer available.')
                                ->send();

                            return;
                        }

                        $partner = $this->getOwnerRecord();

                        PartnerImage::attachFromAvailableImage(
                            $availableImage,
                            $partner->id,
                            $data['alt_text'] ?? null
                        );

                        Notification::make()
                            ->success()
                            ->title('Image attached')
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('view_image')
                    ->label('View image')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (PartnerImage $record) => route('filament.admin.partner-image.view', [
                        'partner' => $record->partner_id,
                        'partnerImage' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (PartnerImage $record) => route('filament.admin.partner-image.download', [
                        'partner' => $record->partner_id,
                        'partnerImage' => $record->id,
                    ])),
                EditAction::make()
                    ->form([
                        TextInput::make('alt_text')
                            ->label('Alt text')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('display_order')
                            ->label('Display order')
                            ->numeric()
                            ->integer()
                            ->nullable(),
                    ]),
                Action::make('detach')
                    ->label('Detach')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Detach image')
                    ->modalDescription('This will move the image back to the available image pool. You can re-attach it later.')
                    ->action(function (PartnerImage $record): void {
                        $record->detachToAvailableImage();

                        Notification::make()
                            ->success()
                            ->title('Image detached and returned to pool')
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('Delete permanently')
                    ->requiresConfirmation()
                    ->modalHeading('Delete image permanently')
                    ->modalDescription('The image file will be permanently deleted from storage and cannot be recovered. It will NOT be returned to the available image pool.')
                    ->before(function (PartnerImage $record): void {
                        Storage::disk($record->imageDisk())
                            ->delete($record->imageStoragePath());
                    }),
            ]);
    }
}
