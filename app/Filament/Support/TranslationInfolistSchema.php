<?php

namespace App\Filament\Support;

use App\Services\MarkdownService;
use Filament\Infolists\Components\TextEntry;

class TranslationInfolistSchema
{
    /**
     * Returns a TextEntry that renders its value as Markdown HTML.
     * When the record's language_id is 'ara', the rendered HTML is wrapped
     * in a right-to-left container so Arabic text displays correctly.
     */
    public static function markdownEntry(string $name, ?string $label = null, bool $columnSpanFull = false): TextEntry
    {
        $entry = TextEntry::make($name)
            ->html()
            ->formatStateUsing(function (?string $state, mixed $record): string {
                if (! filled($state)) {
                    return '';
                }
                // MarkdownService strips all HTML input (html_input='strip') and disallows
                // unsafe links, so its output is safe to render with ->html().
                $html = app(MarkdownService::class)->markdownToHtml($state);

                return ($record?->language_id === 'ara')
                    ? '<div dir="rtl" class="text-right prose max-w-none">' . $html . '</div>'
                    : '<div class="prose max-w-none">' . $html . '</div>';
            });

        if ($label !== null) {
            $entry->label($label);
        }

        if ($columnSpanFull) {
            $entry->columnSpanFull();
        }

        return $entry;
    }

    /**
     * Returns a TextEntry that applies RTL direction for Arabic records.
     * The value is HTML-escaped so it is displayed as plain text.
     */
    public static function rtlTextEntry(string $name, ?string $label = null): TextEntry
    {
        $entry = TextEntry::make($name)
            ->html()
            ->formatStateUsing(function (?string $state, mixed $record): string {
                if (! filled($state)) {
                    return '';
                }

                return ($record?->language_id === 'ara')
                    ? '<span dir="rtl" class="block text-right">' . e($state) . '</span>'
                    : e($state);
            });

        if ($label !== null) {
            $entry->label($label);
        }

        return $entry;
    }
}
