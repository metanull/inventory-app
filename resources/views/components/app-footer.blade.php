@php
    $versionInfo = is_callable($app_version_info) ? $app_version_info() : (array)($app_version_info ?? []);
    $appName = config('app.name', 'Inventory App');
    $appVersion = $versionInfo['app_version'] ?? 'dev';
    $apiClientVersion = $versionInfo['api_client_version'] ?? null;
    $buildTimestamp = $versionInfo['build_timestamp'] ?? null;
    
    // Format build timestamp if available
    $formattedBuildDate = null;
    if ($buildTimestamp) {
        try {
            // Handle CI/CD format using the "value" field to avoid locale issues
            if (is_array($buildTimestamp) && isset($buildTimestamp['value'])) {
                // Parse .NET DateTime format: "/Date(1757794373908)/"
                $value = $buildTimestamp['value'];
                if (preg_match('/\/Date\((\d+)\)\//', $value, $matches)) {
                    $timestamp = intval($matches[1]) / 1000; // Convert milliseconds to seconds
                    $date = new DateTime();
                    $date->setTimestamp($timestamp);
                    $formattedBuildDate = $date->format('M j, Y');
                }
            } elseif (is_string($buildTimestamp)) {
                // Simple string format fallback
                $date = new DateTime($buildTimestamp);
                $formattedBuildDate = $date->format('M j, Y');
            }
        } catch (Exception $e) {
            // If date parsing fails, leave as null
            $formattedBuildDate = null;
        }
    }
@endphp

<footer class="bg-white border-t py-4 mt-8 border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-gray-500 text-sm">
        <div>
            <span class="font-semibold text-gray-700">{{ $appName }}</span>
            <span class="mx-2">|</span>
            <span>Version: {{ $appVersion }}</span>
            @if ($formattedBuildDate)
                <span> ({{ $formattedBuildDate }})</span>
            @endif
        </div>
        @if ($apiClientVersion)
            <div>
                <span>API Client Version: {{ $apiClientVersion }}</span>
            </div>
        @endif
    </div>
</footer>