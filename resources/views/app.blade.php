<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventory Management') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- SPA Build Assets -->
        @php
            $manifest = json_decode(file_get_contents(public_path('cli/manifest.json')), true);
            $entryAssets = $manifest['index.html'];
            $jsFile = $entryAssets['file'];
            $cssFiles = $entryAssets['css'] ?? [];
        @endphp
        @foreach($cssFiles as $cssFile)
            <link rel="stylesheet" href="{{ asset('cli/' . $cssFile) }}" />
        @endforeach
    </head>
    <body class="font-sans antialiased">
        <div id="app"></div>
        <script type="module" src="{{ asset('cli/' . $jsFile) }}"></script>
    </body>
</html>
