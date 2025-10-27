---
layout: default
title: Tables
nav_order: 3
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Table Components

Table components provide a consistent interface for displaying tabular data with sorting, pagination, and row actions.

## Component Structure

Table components are organized in `resources/views/components/table/`:

```
resources/views/components/table/
├── row-actions.blade.php    # View/Edit/Delete action buttons
├── delete-button.blade.php  # Delete button with modal trigger
├── delete-modal.blade.php   # Confirmation modal for deletions
└── mobile-card.blade.php    # Mobile-optimized card layout
```

## Basic Table Structure

{% raw %}
```blade
<div class="bg-white shadow sm:rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $item->name }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <x-table.row-actions
                                :view="route('items.show', $item)"
                                :edit="route('items.edit', $item)"
                                :delete="route('items.destroy', $item)"
                                entity="items"
                                :record-id="$item->id"
                                :record-name="$item->internal_name"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">
                            No items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <x-layout.pagination 
        :paginator="$items" 
        entity="items"
        param-page="page"
    />
</div>
```
{% endraw %}

## Row Actions Component

The `<x-table.row-actions>` component provides View, Edit, and Delete buttons with proper permissions and entity color coding.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `view` | string | No | URL for view action |
| `edit` | string | No | URL for edit action |
| `delete` | string | No | URL for delete action (form submit) |
| `deleteConfirm` | string | No | Confirmation message (default: "Delete this record?") |
| `deleteLabel` | string | No | Delete button label (default: "Delete") |
| `entity` | string | No | Entity key for color theming |
| `recordId` | string/int | Yes | Unique identifier for the record |
| `recordName` | string | Yes | Display name for the record |

### Usage

{% raw %}
```blade
<x-table.row-actions
    :view="route('items.show', $item)"
    :edit="route('items.edit', $item)"
    :delete="route('items.destroy', $item)"
    delete-confirm="Delete this item?"
    entity="items"
    :record-id="$item->id"
    :record-name="$item->internal_name"
/>
```
{% endraw %}

### Permissions

Each action respects the following permissions:
- **View**: `Permission::VIEW_DATA`
- **Edit**: `Permission::UPDATE_DATA`
- **Delete**: `Permission::DELETE_DATA`

## Delete Modal Component

The `<x-table.delete-modal>` component provides a confirmation dialog for table row deletions.

### Usage

{% raw %}
```blade
<!-- At the end of your table component -->
<x-table.delete-modal />
```
{% endraw %}

### JavaScript Integration

The delete modal is triggered via JavaScript:

```javascript
function openTableDeleteModal(id, entity, name, action) {
    const modalId = 'table-delete-modal';
    currentTableDeleteModal = modalId;
    currentTableDeleteAction = action;
    
    // Update modal content
    const modal = document.getElementById(modalId);
    const title = modal.querySelector('h3');
    const message = modal.querySelector('p');
    const form = document.getElementById(modalId + '-form');
    
    title.textContent = `Delete ${entity.replace(/[-_]/g, ' ')}`;
    message.textContent = `Are you sure you want to delete "${name}"? This action cannot be undone.`;
    form.action = action;
    
    // Show modal
    modal.classList.remove('hidden');
}
```

## Mobile-Optimized Cards

For mobile devices, tables can use the `<x-table.mobile-card>` component for better UX.

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `title` | string | Yes | Card title/heading |
| `subtitle` | string | No | Optional subtitle |
| `fields` | array | No | Array of field data ['label' => 'value'] |
| `actions` | slot | No | Action buttons slot |
| `entity` | string | No | Entity for color theming |

### Usage

{% raw %}
```blade
<x-table.mobile-card
    :title="$item->internal_name"
    :subtitle="$item->type"
    :fields="[
        'Partner' => $item->partner->internal_name ?? 'N/A',
        'Country' => $item->country->internal_name ?? 'N/A',
        'Created' => $item->created_at->format('Y-m-d'),
    ]"
    entity="items"
>
    <x-slot name="actions">
        <x-table.row-actions
            :view="route('items.show', $item)"
            :edit="route('items.edit', $item)"
            :delete="route('items.destroy', $item)"
            entity="items"
            :record-id="$item->id"
            :record-name="$item->internal_name"
        />
    </x-slot>
</x-table.mobile-card>
```
{% endraw %}

## Livewire Tables

For dynamic tables with sorting, filtering, and pagination, use Livewire components.

### Component Structure

Livewire table components are in `resources/views/livewire/tables/`:

```
livewire/tables/
├── items-table.blade.php
├── partners-table.blade.php
├── tags-table.blade.php
└── ...
```

### Sortable Columns

{% raw %}
```blade
<th wire:click="sortBy('internal_name')" class="cursor-pointer px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-100">
    Name
    @if($sortField === 'internal_name')
        @if($sortDirection === 'asc')
            <x-heroicon-o-chevron-up class="inline w-4 h-4" />
        @else
            <x-heroicon-o-chevron-down class="inline w-4 h-4" />
        @endif
    @endif
</th>
```
{% endraw %}

### Livewire Table Component Example

```php
class ItemsTable extends Component
{
    use WithPagination;

    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $items = Item::query()
            ->when($this->search, function ($query) {
                $query->where('internal_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.tables.items-table', compact('items'));
    }
}
```

## Responsive Design

Tables automatically adapt to mobile devices:

- **Desktop**: Full table layout with all columns
- **Tablet**: Hide non-essential columns using `hidden lg:table-cell`
- **Mobile**: Switch to card layout using `<x-table.mobile-card>`

### Responsive Column Classes

```blade
<td class="hidden lg:table-cell px-4 py-3">
    <!-- Only visible on large screens -->
</td>

<td class="hidden sm:table-cell px-4 py-3">
    <!-- Hidden on mobile, visible on small screens and up -->
</td>
```

## Styling Patterns

### Row Hover Effects

```blade
<tr class="hover:bg-gray-50 transition-colors">
```

### Alternating Row Colors

```blade
<tr class="even:bg-gray-50">
```

### Clickable Rows

{% raw %}
```blade
<tr onclick="window.location='{{ route('items.show', $item) }}'" class="cursor-pointer hover:bg-gray-50">
```
{% endraw %}

## Best Practices

1. **Always include empty states** - Show helpful messages when no data exists
2. **Use pagination** - Don't load all records at once
3. **Implement sorting** - Allow users to sort by common columns
4. **Add loading states** - Show spinners during AJAX requests
5. **Test on mobile** - Ensure tables are usable on small screens
6. **Use permissions** - Respect user permissions for actions
7. **Confirm deletions** - Always use modal confirmation for destructive actions
8. **Entity colors** - Use entity color system for visual consistency

## Related Documentation

- [Livewire Tables]({{ '/frontend-blade/livewire/' | relative_url }})
- [Pagination Component]({{ '/frontend-blade/components/layouts' | relative_url }}#pagination)
- [Row Actions]({{ '/frontend-blade/components/tables' | relative_url }}#row-actions-component)
- [Entity Colors]({{ '/frontend-blade/styling/' | relative_url }}#entity-colors)
