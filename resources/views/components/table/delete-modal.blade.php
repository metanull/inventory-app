@props([
    'id' => 'table-delete-modal'
])

<x-ui.modal 
    :id="$id"
    title="Delete Item"
    message="Are you sure you want to delete this item? This action cannot be undone."
    confirm-text="Delete"
    cancel-text="Cancel"
    confirm-class="bg-red-600 hover:bg-red-700 text-white"
/>

<!-- Hidden form for deletion -->
<form id="{{ $id }}-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
let currentTableDeleteModal = '';
let currentTableDeleteAction = '';

function openTableDeleteModal(id, entity, name, action) {
    const modalId = '{{ $id }}';
    currentTableDeleteModal = modalId;
    currentTableDeleteAction = action;
    
    // Update modal content
    const modal = document.getElementById(modalId);
    const title = modal.querySelector('h3');
    const message = modal.querySelector('p');
    const form = document.getElementById(modalId + '-form');
    
    title.textContent = 'Delete ' + entity.charAt(0).toUpperCase() + entity.slice(1);
    message.textContent = 'Are you sure you want to delete ' + (name ? '"' + name + '"' : 'this ' + entity) + '? This action cannot be undone.';
    form.action = action;
    
    openModal(modalId);
}

function confirmAction(modalId) {
    if (modalId === currentTableDeleteModal && currentTableDeleteAction) {
        const form = document.getElementById(modalId + '-form');
        form.action = currentTableDeleteAction;
        form.submit();
    }
}
</script>