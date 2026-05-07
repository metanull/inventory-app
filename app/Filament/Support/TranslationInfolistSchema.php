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

                return self::wrapForDirection($html, $record?->language_id, wrapInProse: true);
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

                return self::wrapForDirection(e($state), $record?->language_id, wrapInProse: false);
            });

        if ($label !== null) {
            $entry->label($label);
        }

        return $entry;
    }

    /**
     * Wraps the given HTML content in an appropriate direction container
     * when the language is Arabic ('ara').
     */
    private static function wrapForDirection(string $content, ?string $languageId, bool $wrapInProse): string
    {
        if ($languageId === 'ara') {
            $class = $wrapInProse ? 'text-right prose max-w-none' : 'block text-right';
            $tag = $wrapInProse ? 'div' : 'span';

            return "<{$tag} dir=\"rtl\" class=\"{$class}\">{$content}</{$tag}>";
        }

        if ($wrapInProse) {
            return '<div class="prose max-w-none">'.$content.'</div>';
        }

        return $content;
    }
}
