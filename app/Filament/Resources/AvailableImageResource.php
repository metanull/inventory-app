<?php

namespace App\Filament\Resources;

use App\Events\ImageUploadEvent;
use App\Filament\Resources\AvailableImageResource\Pages\EditAvailableImage;
use App\Filament\Resources\AvailableImageResource\Pages\ListAvailableImages;
use App\Filament\Resources\AvailableImageResource\Pages\UploadImage;
use App\Filament\Resources\AvailableImageResource\Pages\ViewAvailableImage;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AvailableImageResource extends Resource
{
    protected static ?string $model = AvailableImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Images';

    protected static ?string $recordTitleAttribute = 'path';

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
                TextEntry::make('id')
                    ->label('UUID'),
                TextEntry::make('path')
                    ->label('Filename'),
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
                TextColumn::make('path')
                    ->label('Filename')
                    ->searchable()
                    ->sortable(),
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

                        try {
                            ImageUploadEvent::dispatch($imageUpload);
                        } catch (\Exception $e) {
                            // If AvailableImageListener fails (pre-existing known issue),
                            // ImageUploadListener may have already created the AvailableImage.
                            report($e);
                        }

                        $availableImage = AvailableImage::find($imageUpload->id);

                        if ($availableImage) {
                            Notification::make()
                                ->success()
                                ->title('Image uploaded and available')
                                ->send();
                        } else {
                            Notification::make()
                                ->warning()
                                ->title('Image upload submitted')
                                ->body('The image is being processed. It will appear in the list shortly.')
                                ->send();
                        }
                    }),
            ])
            ->actions([
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
            'index' => ListAvailableImages::route('/'),
            'upload' => UploadImage::route('/upload'),
            'view' => ViewAvailableImage::route('/{record}'),
            'edit' => EditAvailableImage::route('/{record}/edit'),
        ];
    }
}
