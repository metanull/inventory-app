{{--
    Date Display Component
    Displays dates in various formats with optional time
    Based on resources/js/components/format/Date.vue
    
    Usage:
    <x-format.date :date="$model->created_at" format="short" />
    <x-format.date :date="$model->updated_at" format="medium" show-time />
    <x-format.date :date="$model->created_at" format="long" />
    <x-format.date :date="$model->updated_at" format="full" show-time />
--}}

@props([
    'date' => null,
    'format' => 'medium',  // 'short' | 'medium' | 'long' | 'full'
    'showTime' => false,
    'className' => '',
    'variant' => 'default', // 'default' | 'small-dark'
])

@php
    use Carbon\Carbon;
    
    $formattedDate = 'N/A';
    $fullDate = 'No date available';
    
    if ($date) {
        try {
            $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
            
            // Format mapping based on Intl.DateTimeFormat equivalents
            $dateFormats = [
                'short' => 'M j, Y',      // e.g., "Jan 1, 2024"
                'medium' => 'M j, Y',     // e.g., "Jan 1, 2024"
                'long' => 'F j, Y',       // e.g., "January 1, 2024"
                'full' => 'l, F j, Y',    // e.g., "Monday, January 1, 2024"
            ];
            
            $dateFormat = $dateFormats[$format] ?? $dateFormats['medium'];
            
            if ($showTime) {
                $formattedDate = $carbonDate->format($dateFormat . ' g:i A');
            } else {
                $formattedDate = $carbonDate->format($dateFormat);
            }
            
            // Full date for tooltip
            $fullDate = $carbonDate->format('l, F j, Y g:i:s A');
        } catch (\Exception $e) {
            $formattedDate = 'Invalid Date';
            $fullDate = 'Invalid date';
        }
    }
    
    $baseClasses = match($variant) {
        'small-dark' => 'text-sm text-gray-900',
        default => '',
    };
    
    $allClasses = trim($baseClasses . ' ' . $className);
@endphp

<span class="{{ $allClasses }}" title="{{ $fullDate }}">
    {{ $formattedDate }}
</span>
