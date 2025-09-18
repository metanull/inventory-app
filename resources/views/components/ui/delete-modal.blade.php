@props([
    'id' => 'delete-modal',
    'entity' => 'item',
    'name' => '',
    'action' => '',
    'method' => 'DELETE'
])

<x-ui.modal 
    :id="$id"
    title="Delete {{ ucfirst($entity) }}"
    :message="'Are you sure you want to delete ' . ($name ? '\"' . $name . '\"' : 'this ' . $entity) . '? This action cannot be undone.'"
    confirm-text="Delete"
    cancel-text="Cancel"
    confirm-class="bg-red-600 hover:bg-red-700 text-white"
/>

<!-- Hidden form for deletion -->
<form id="{{ $id }}-form" method="POST" action="{{ $action }}" style="display: none;">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
</form>

<script>
function confirmAction(modalId) {
    if (modalId === '{{ $id }}') {
        document.getElementById('{{ $id }}-form').submit();
    }
}
</script>