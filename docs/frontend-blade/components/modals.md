---
layout: default
title: Modals
nav_order: 4
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Modal Components

Modal components provide overlay dialogs for confirmations, forms, and important messages.

## Component Structure

Modal components are in `resources/views/components/`:

```
components/
├── modal.blade.php                  # Base modal component
├── dialog-modal.blade.php           # Dialog-style modal
├── confirmation-modal.blade.php     # Confirmation dialog
├── table/
│   └── delete-modal.blade.php       # Table row deletion modal
└── ui/
    ├── modal.blade.php              # UI modal variant
    └── delete-modal.blade.php       # Generic delete modal
```

## Base Modal Component

The `<x-modal>` component provides the foundation for all modal dialogs.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `name` | string | Yes | Unique modal identifier |
| `show` | boolean | No | Initial visibility state |
| `maxWidth` | string | No | Modal max width: 'sm', 'md', 'lg', 'xl', '2xl' (default: '2xl') |

### Usage

{% raw %}
```blade
<x-modal name="example-modal" :show="$errors->isNotEmpty()">
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">Modal Title</h2>
        <p class="mt-1 text-sm text-gray-600">Modal content goes here.</p>
        
        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button @click="show = false">
                Cancel
            </x-secondary-button>
            <x-button>
                Confirm
            </x-button>
        </div>
    </div>
</x-modal>
```
{% endraw %}

### Opening/Closing with Alpine.js

{% raw %}
```blade
<!-- Trigger button -->
<x-button @click="$dispatch('open-modal', 'example-modal')">
    Open Modal
</x-button>

<!-- Modal -->
<x-modal name="example-modal">
    <!-- Content -->
    <x-secondary-button @click="$dispatch('close-modal', 'example-modal')">
        Close
    </x-secondary-button>
</x-modal>
```
{% endraw %}

## Dialog Modal

The `<x-dialog-modal>` component is designed for informational dialogs with a title, content, and footer.

### Slots

| Slot | Required | Description |
|------|----------|-------------|
| `title` | Yes | Modal title |
| `content` | Yes | Main content area |
| `footer` | Yes | Action buttons area |

### Usage

{% raw %}
```blade
<x-dialog-modal wire:model="confirmingDeletion">
    <x-slot name="title">
        Delete Account
    </x-slot>

    <x-slot name="content">
        Are you sure you want to delete your account? This action cannot be undone.
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$toggle('confirmingDeletion')">
            Cancel
        </x-secondary-button>

        <x-danger-button wire:click="deleteAccount" class="ml-3">
            Delete Account
        </x-danger-button>
    </x-slot>
</x-dialog-modal>
```
{% endraw %}

## Confirmation Modal

The `<x-confirmation-modal>` component is specifically for user confirmations.

### Usage

{% raw %}
```blade
<x-confirmation-modal wire:model="confirmingAction">
    <x-slot name="title">
        Confirm Action
    </x-slot>

    <x-slot name="content">
        Are you sure you want to proceed?
    </x-slot>

    <x-slot name="footer">
        <x-secondary-button wire:click="$set('confirmingAction', false)">
            Cancel
        </x-secondary-button>

        <x-button wire:click="performAction" class="ml-3">
            Confirm
        </x-button>
    </x-slot>
</x-confirmation-modal>
```
{% endraw %}

## Delete Modal (Table)

The `<x-table.delete-modal>` component provides a reusable deletion confirmation for table rows.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | No | Modal ID (default: 'table-delete-modal') |

### Usage

{% raw %}
```blade
<!-- Include once at the end of your table -->
<x-table.delete-modal />

<!-- Trigger from row actions -->
<x-table.delete-button 
    :id="'item-' . $item->id"
    entity="items"
    :name="$item->internal_name"
    :action="route('items.destroy', $item)"
/>
```
{% endraw %}

### JavaScript API

```javascript
// Open the modal
openTableDeleteModal(id, entity, name, action)

// Example:
openTableDeleteModal(
    'item-123',
    'items',
    'Sample Item',
    '/items/123'
)
```

## UI Delete Modal

The `<x-ui.delete-modal>` component is a generic delete confirmation modal.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes | Unique modal identifier |
| `entity` | string | Yes | Entity name for context |
| `name` | string | Yes | Record name to display |
| `action` | string | Yes | Form action URL |

### Usage

{% raw %}
```blade
<!-- Modal -->
<x-ui.delete-modal 
    :id="'delete-item-modal'"
    entity="item"
    :name="$item->internal_name"
    :action="route('items.destroy', $item)" 
/>

<!-- Trigger button -->
<button type="button" 
        onclick="openModal('delete-item-modal')" 
        class="text-red-600 hover:text-red-900">
    Delete
</button>
```
{% endraw %}

## UI General Modal

The `<x-ui.modal>` component is a flexible modal for various use cases.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | Yes | Unique modal identifier |
| `title` | string | No | Modal title |
| `message` | string | No | Modal message/content |
| `confirmText` | string | No | Confirm button text (default: 'Confirm') |
| `cancelText` | string | No | Cancel button text (default: 'Cancel') |
| `confirmClass` | string | No | Confirm button CSS classes |

### Usage

{% raw %}
```blade
<x-ui.modal 
    id="custom-modal"
    title="Custom Action"
    message="Please confirm this action."
    confirm-text="Proceed"
    cancel-text="Go Back"
    confirm-class="bg-indigo-600 hover:bg-indigo-700 text-white"
/>

<button onclick="openModal('custom-modal')">
    Open Modal
</button>
```
{% endraw %}

## Modal JavaScript Functions

The application provides global JavaScript functions for modal control:

### openModal(modalId)

Opens a modal by ID.

```javascript
openModal('my-modal-id')
```

### closeModal(modalId)

Closes a modal by ID.

```javascript
closeModal('my-modal-id')
```

### Modal Event Listeners

Modals listen for Alpine.js events:

{% raw %}
```blade
<!-- Dispatch event to open -->
<button @click="$dispatch('open-modal', 'modal-name')">
    Open
</button>

<!-- Dispatch event to close -->
<button @click="$dispatch('close-modal', 'modal-name')">
    Close
</button>
```
{% endraw %}

## Livewire Integration

Modals work seamlessly with Livewire using `wire:model`:

### Example Livewire Component

```php
class DeleteItem extends Component
{
    public $confirmingDeletion = false;
    public $itemToDelete;

    public function confirmDeletion($itemId)
    {
        $this->itemToDelete = $itemId;
        $this->confirmingDeletion = true;
    }

    public function deleteItem()
    {
        Item::findOrFail($this->itemToDelete)->delete();
        $this->confirmingDeletion = false;
        
        session()->flash('message', 'Item deleted successfully.');
    }

    public function render()
    {
        return view('livewire.delete-item');
    }
}
```

### Livewire Modal Template

{% raw %}
```blade
<div>
    <x-danger-button wire:click="confirmDeletion({{ $item->id }})">
        Delete Item
    </x-danger-button>

    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            Delete Item
        </x-slot>

        <x-slot name="content">
            Are you sure you want to delete this item?
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmingDeletion', false)">
                Cancel
            </x-secondary-button>

            <x-danger-button wire:click="deleteItem" class="ml-3">
                Delete
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
```
{% endraw %}

## Accessibility

All modal components follow accessibility best practices:

- **Focus trap**: Focus stays within the modal when open
- **Escape key**: Closes modal when pressed
- **ARIA attributes**: Proper `role`, `aria-modal`, `aria-labelledby`
- **Background click**: Closes modal when clicking outside
- **Keyboard navigation**: Tab through interactive elements

## Styling

### Modal Overlay

```css
/* Semi-transparent dark background */
.modal-overlay {
    background-color: rgba(0, 0, 0, 0.5);
}
```

### Modal Container

```css
/* Centered white container */
.modal-container {
    background-color: white;
    max-width: 2xl; /* Configurable */
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}
```

### Transitions

Modals use Alpine.js transitions for smooth animations:

```blade
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0"
```

## Best Practices

1. **Use appropriate modal types** - Choose the right modal for the use case
2. **Keep content concise** - Modal content should be brief and clear
3. **Provide clear actions** - Always offer clear Cancel and Confirm options
4. **Confirm destructive actions** - Always use modals for delete operations
5. **Test accessibility** - Ensure keyboard and screen reader support
6. **Avoid nesting** - Don't open modals from within modals
7. **Close on success** - Close modals after successful operations
8. **Show loading states** - Indicate when actions are processing

## Related Documentation

- [Buttons]({{ '/frontend-blade/components/buttons' | relative_url }})
- [Alpine.js Patterns]({{ '/frontend-blade/alpine/' | relative_url }})
- [Livewire Components]({{ '/frontend-blade/livewire/' | relative_url }})
- [Form Components]({{ '/frontend-blade/components/forms' | relative_url }})
