@props([
    'cancelRoute' => '',
    'cancelLabel' => 'Cancel',
    'saveLabel' => 'Save',
    'entity' => null,
])

@php($c = $entityColor($entity))

<div class="px-4 py-4 sm:px-6 flex items-center justify-between bg-gray-50">
    <a href="{{ $cancelRoute }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-sm">
        {{ $cancelLabel }}
    </a>
    <button type="submit" 
            class="inline-flex items-center px-4 py-2 rounded-md {{ $c['button'] ?? 'bg-indigo-600 hover:bg-indigo-700 text-white' }} text-sm font-medium shadow-sm disabled:opacity-75 disabled:cursor-not-allowed">
        <!-- Loading spinner (hidden by default) -->
        <x-ui.loading size="sm" color="white" class="mr-2" style="display: none;" />
        <span class="submit-text">{{ $saveLabel }}</span>
    </button>
</div>

<script>
// Add loading state to form submissions
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.animate-spin');
            const text = submitButton.querySelector('.submit-text');
            
            if (submitButton && spinner && text) {
                submitButton.disabled = true;
                spinner.style.display = 'block';
                text.textContent = 'Saving...';
            }
        });
    });
});
</script>