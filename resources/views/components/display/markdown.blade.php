@props([
    'content' => '',
])

@php
    use App\Services\MarkdownService;
    
    $htmlContent = '';
    if ($content) {
        $htmlContent = (new MarkdownService())->markdownToHtml($content);
    }
@endphp

<div class="prose prose-sm max-w-none">
    @if($htmlContent)
        {!! $htmlContent !!}
    @else
        <span class="text-gray-500">—</span>
    @endif
</div>
