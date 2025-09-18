@props([
    'id',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmClass' => 'bg-red-600 hover:bg-red-700 text-white',
    'cancelClass' => 'bg-gray-300 hover:bg-gray-400 text-gray-800'
])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300" onclick="closeModal('{{ $id }}')"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95" id="{{ $id }}-content">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            </div>
            
            <!-- Body -->
            <div class="px-4 sm:px-6 py-4">
                <p class="text-sm text-gray-600">{{ $message }}</p>
            </div>
            
            <!-- Footer with mobile-optimized buttons -->
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                <button type="button" 
                        onclick="closeModal('{{ $id }}')" 
                        class="w-full sm:w-auto px-4 py-3 sm:py-2 rounded-md text-sm font-medium {{ $cancelClass }} transition-colors duration-200 touch:min-h-[44px]">
                    {{ $cancelText }}
                </button>
                <button type="button" 
                        onclick="confirmAction('{{ $id }}')" 
                        class="w-full sm:w-auto px-4 py-3 sm:py-2 rounded-md text-sm font-medium {{ $confirmClass }} transition-colors duration-200 touch:min-h-[44px]">
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    modal.classList.remove('hidden');
    // Trigger animation
    setTimeout(() => {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalId + '-content');
    
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function confirmAction(modalId) {
    // This will be overridden by specific implementations
    closeModal(modalId);
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('[role="dialog"]:not(.hidden)');
        modals.forEach(modal => closeModal(modal.id));
    }
});
</script>