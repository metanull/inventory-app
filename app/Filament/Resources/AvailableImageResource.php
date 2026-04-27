<?php

namespace App\Filament\Resources;

use App\Events\ImageUploadEvent;
use App\Filament\Resources\AvailableImageResource\Pages\EditAvailableImage;
use App\Filament\Resources\AvailableImageResource\Pages\ListAvailableImage;
use App\Filament\Resources\AvailableImageResource\Pages\ViewAvailableImage;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AvailableImageResource extends Resource
{
    protected static ?string $model = AvailableImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Images';

    protected static ?string $recordTitleAttribute = 'path';

    public static function getGloballySearchableAttributes(): array
    {
        return ['path', 'comment'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('comment')
                    ->label('Comment / Alt text')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ImageEntry::make('preview')
                    ->label('Preview')
                    ->getStateUsing(fn ($record) => route('filament.admin.available-image.view', [
                        'availableImage' => $record->id,
                    ]))
                    ->height(200)
                    ->columnSpanFull(),
                TextEntry::make('id')
                    ->label('UUID'),
                TextEntry::make('path')
                    ->label('Filename'),
                TextEntry::make('original_name')
                    ->label('Original name'),
                TextEntry::make('mime_type')
                    ->label('MIME type'),
                TextEntry::make('size')
                    ->label('Size (bytes)')
                    ->numeric(),
                TextEntry::make('comment')
                    ->label('Comment / Alt text'),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview')
                    ->label('Preview')
                    ->getStateUsing(fn ($record) => route('filament.admin.available-image.view', [
                        'availableImage' => $record->id,
                    ]))
                    ->height(64)
                    ->url(fn ($record) => route('filament.admin.available-image.view', [
                        'availableImage' => $record->id,
                    ]))
                    ->openUrlInNewTab()
                    ->defaultImageUrl(null),
                TextColumn::make('path')
                    ->label('Filename')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('original_name')
                    ->label('Original name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('mime_type')
                    ->label('MIME type')
                    ->toggleable(),
                TextColumn::make('size')
                    ->label('Size (bytes)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('comment')
                    ->label('Comment')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('id')
                    ->label('UUID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label('Upload image')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('file')
                            ->label('Image file')
                            ->disk('local')
                            ->directory(config('localstorage.uploads.images.directory', 'image_uploads'))
                            ->visibility('private')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize((int) config('localstorage.uploads.images.max_size', 20480))
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $path = $data['file'];

                        $imageUpload = ImageUpload::create([
                            'path' => $path,
                            'name' => basename($path),
                            'extension' => pathinfo($path, PATHINFO_EXTENSION),
                            'mime_type' => null,
                            'size' => null,
                        ]);

                        ImageUploadEvent::dispatch($imageUpload);

                        $availableImage = AvailableImage::find($imageUpload->id);

                        if ($availableImage) {
                            Notification::make()
                                ->success()
                                ->title('Image uploaded and available')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Image processing failed')
                                ->body('The uploaded file could not be processed as a valid image. It has not been added to the available image pool.')
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Action::make('view_image')
                    ->label('View image')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (AvailableImage $record) => route('filament.admin.available-image.view', [
                        'availableImage' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (AvailableImage $record) => route('filament.admin.available-image.download', [
                        'availableImage' => $record->id,
                    ])),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (AvailableImage $record): void {
                        $disk = $record->imageDisk();
                        $path = $record->imageStoragePath();

                        if (Storage::disk($disk)->exists($path)) {
                            Storage::disk($disk)->delete($path);
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvailableImage::route('/'),
            'view' => ViewAvailableImage::route('/{record}'),
            'edit' => EditAvailableImage::route('/{record}/edit'),
        ];
    }
}
