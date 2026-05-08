<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;

/**
 * Factory methods for the reusable `extra` JSON editor (form) and pretty-printer (infolist).
 *
 * The `extra` column is cast to `object` on every model that carries it.  Filament receives
 * the hydrated stdClass value, and this helper normalises the round-trip:
 *
 * - Form: stdClass → JSON string for editing; JSON string → array for persistence.
 * - Infolist: stdClass → pretty-printed JSON wrapped in <pre>.
 */
class ExtraJsonField
{
    /**
     * Returns a Textarea form component configured for multidimensional JSON editing.
     *
     * Normalisation:
     *   - afterStateHydrated  : model value (stdClass / array / string / null) → JSON string
     *   - dehydrateStateUsing : JSON string → PHP array (or null when empty) for the model
     * Validation rejects non-empty input that is not valid JSON.
     */
    public static function formComponent(string $name = 'extra'): Textarea
    {
        return Textarea::make($name)
            ->label('Extra metadata')
            ->rows(6)
            ->placeholder("{\n    \"key\": \"value\"\n}")
            ->extraInputAttributes(['class' => 'font-mono text-sm'])
            ->afterStateHydrated(static function (Textarea $component, mixed $state): void {
                if ($state === null) {
                    $component->state('');

                    return;
                }

                if ($state instanceof \stdClass) {
                    $state = json_decode(json_encode($state), true);
                } elseif (is_string($state)) {
                    $decoded = json_decode($state, true);
                    if (is_array($decoded)) {
                        $state = $decoded;
                    }
                }

                if (is_array($state)) {
                    $component->state(
                        json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    );
                } elseif (is_string($state) && $state !== '') {
                    $component->state($state);
                } else {
                    $component->state('');
                }
            })
            ->dehydrateStateUsing(static function (mixed $state): ?array {
                if ($state === null || $state === '') {
                    return null;
                }

                if (is_array($state)) {
                    return $state;
                }

                $decoded = json_decode((string) $state, true);

                return is_array($decoded) ? $decoded : null;
            })
            ->rules([
                static function (): \Closure {
                    return static function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! filled($value)) {
                            return;
                        }

                        json_decode((string) $value);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('The extra metadata must be valid JSON.');
                        }
                    };
                },
            ])
            ->columnSpanFull();
    }

    /**
     * Returns a TextEntry infolist component that pretty-prints the `extra` JSON value.
     *
     * The model value (stdClass / array / string / null) is normalised to a
     * pretty-printed JSON string and rendered inside a <pre> block.
     */
    public static function infolistEntry(string $name = 'extra'): TextEntry
    {
        return TextEntry::make($name)
            ->label('Extra metadata')
            ->html()
            ->formatStateUsing(static function (mixed $state): string {
                if ($state === null || $state === '') {
                    return '';
                }

                if ($state instanceof \stdClass) {
                    $state = json_decode(json_encode($state), true);
                } elseif (is_string($state)) {
                    $decoded = json_decode($state, true);
                    if ($decoded !== null) {
                        $state = $decoded;
                    }
                }

                $json = json_encode(
                    $state,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                return '<pre class="text-xs font-mono whitespace-pre-wrap break-all bg-gray-50 p-2 rounded">'
                    .e($json)
                    .'</pre>';
            })
            ->columnSpanFull();
    }
}
