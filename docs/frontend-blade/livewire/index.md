---
layout: default
title: Livewire
nav_order: 2
parent: Blade/Livewire Frontend
has_children: false
---

# Livewire Component Patterns

Livewire enables reactive components without writing JavaScript. This guide covers the patterns used in this application.

## What is Livewire?

Livewire is a framework for building reactive Laravel components:

- **Server-rendered** - Components render on the server
- **Reactive** - Updates happen without page refresh
- **No JavaScript** - Write PHP instead of JavaScript
- **Laravel-native** - Uses familiar Laravel concepts

## Component Structure

Livewire components have two parts:

### 1. PHP Class (Logic)

```php
<?php

namespace App\Http\Livewire\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;

class ItemTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.item.item-table', [
            'items' => Item::where('internal_name', 'like', "%{$this->search}%")
                ->paginate($this->perPage),
        ]);
    }
}
```

### 2. Blade View (Template)

{% raw %}

```blade
<div>
    <div class="mb-4">
        <input
            wire:model.live="search"
            type="text"
            placeholder="Search items..."
            class="border rounded px-4 py-2"
        />
    </div>

    <table class="w-full">
        <thead>
            <tr>
                <th>Name</th>
                <th>Partner</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->internal_name }}</td>
                    <td>{{ $item->partner->name }}</td>
                    <td>
                        <a href="{{ route('item.edit', $item) }}">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $items->links() }}
</div>
```

{% endraw %}

## Common Patterns

### Real-Time Search

```blade
<input wire:model.live="search" type="text" placeholder="Search..." />
```

```php
public $search = '';

public function updatingSearch()
{
    $this->resetPage(); // Reset to first page when searching
}
```

### Form Handling

{% raw %}

```blade
<form wire:submit="save">
    <input wire:model="name" type="text" />
    @error('name') <span class="error">{{ $message }}</span> @enderror

    <button type="submit">Save</button>
</form>
```

{% endraw %}

```php
public $name = '';

protected $rules = [
    'name' => 'required|min:3',
];

public function save()
{
    $this->validate();

    Item::create([
        'name' => $this->name,
    ]);

    session()->flash('message', 'Item created successfully.');
    return redirect()->route('item.index');
}
```

### Pagination

```php
use Livewire\WithPagination;

class ItemList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.item-list', [
            'items' => Item::paginate(15),
        ]);
    }
}
```

{% raw %}

```blade
{{ $items->links() }}
```

{% endraw %}

### Loading States

{% raw %}

```blade
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>

<div wire:loading wire:target="search">
    Searching...
</div>
```

{% endraw %}

### Confirmation Dialogs

{% raw %}

```blade
<button
    wire:click="delete({{ $item->id }})"
    wire:confirm="Are you sure you want to delete this item?"
>
    Delete
</button>
```

{% endraw %}

```php
public function delete($id)
{
    Item::findOrFail($id)->delete();
    session()->flash('message', 'Item deleted.');
}
```

## Built-in Components

### Markdown Editor

The `MarkdownEditor` component provides a rich text editing experience with live preview:

```php
<?php

namespace App\Livewire;

use App\Services\MarkdownService;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class MarkdownEditor extends Component
{
    #[Modelable]
    public string $content = '';

    public string $mode = 'edit'; // 'edit' or 'preview'
    public bool $showHelp = false;

    public function switchToEdit(): void
    {
        $this->mode = 'edit';
    }

    public function switchToPreview(): void
    {
        $this->mode = 'preview';
    }

    public function toggleHelp(): void
    {
        $this->showHelp = !$this->showHelp;
    }

    public function getPreviewProperty(): string
    {
        if (empty($this->content)) {
            return '<span class="text-gray-500 italic">Preview will appear here as you type...</span>';
        }

        return app(MarkdownService::class)->markdownToHtml($this->content);
    }
}
```

**Usage in Forms:**

{% raw %}

```blade
<x-form.markdown-editor-livewire
    name="description"
    label="Description"
    :value="old('description', $item->description ?? '')"
    rows="6"
    helpText="Use Markdown formatting."
/>
```

{% endraw %}

**Key Features:**

- Edit/Preview mode switching
- Live preview with 300ms debounce
- Built-in markdown syntax help
- Server-side rendering using `MarkdownService`
- Comprehensive test coverage (18 tests)

**Testing:**
The component is fully testable without browser automation:

```php
public function test_can_switch_to_preview_mode(): void
{
    Livewire::test(MarkdownEditor::class)
        ->assertSet('mode', 'edit')
        ->call('switchToPreview')
        ->assertSet('mode', 'preview');
}

public function test_preview_renders_markdown_as_html(): void
{
    Livewire::test(MarkdownEditor::class, [
        'initialContent' => '# Heading',
    ])
        ->set('mode', 'preview')
        ->assertSee('<h1>Heading</h1>', false);
}
```

### Key-Value Editor

The `KeyValueEditor` component manages key-value pairs for JSON data:

{% raw %}

```blade
@livewire('key-value-editor', [
    'initialData' => $item->extra ?? [],
    'componentName' => 'extra'
])
```

{% endraw %}

**Features:**

- Dynamic add/remove pairs
- JSON value support
- Minimum one empty pair
- Form integration via hidden inputs

### Confirmation Modal

The `ConfirmationModal` component provides a reusable confirmation dialog for destructive actions:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class ConfirmationModal extends Component
{
    public bool $show = false;
    public string $title = 'Are you sure?';
    public string $message = 'This operation cannot be undone.';
    public string $confirmLabel = 'Confirm';
    public string $cancelLabel = 'Cancel';
    public string $color = 'red';
    public ?string $action = null;
    public string $method = 'DELETE';

    protected $listeners = ['confirm-action' => 'showConfirmation'];

    public function showConfirmation(array $data): void
    {
        $this->title = $data['title'] ?? 'Are you sure?';
        $this->message = $data['message'] ?? 'This operation cannot be undone.';
        $this->confirmLabel = $data['confirmLabel'] ?? 'Confirm';
        $this->cancelLabel = $data['cancelLabel'] ?? 'Cancel';
        $this->color = $data['color'] ?? 'red';
        $this->action = $data['action'] ?? null;
        $this->method = $data['method'] ?? 'DELETE';
        $this->show = true;
    }

    public function confirm(): void
    {
        $this->dispatch('confirmed', [
            'action' => $this->action,
            'method' => $this->method,
        ]);
        $this->close();
    }

    public function close(): void
    {
        $this->show = false;
    }
}
```

**Usage with Confirm Button:**

{% raw %}

```blade
<x-ui.confirm-button
    :action="route('items.destroy', $item)"
    method="DELETE"
    confirmMessage="Are you sure you want to delete this item?"
    variant="danger"
    size="sm"
    icon="trash">
    Delete
</x-ui.confirm-button>
```

{% endraw %}

**Features:**

- Global modal (added to `layouts/app.blade.php`)
- Listens for `confirm-action` events
- Customizable title, message, and button labels
- Color variants (red for danger, indigo for warning)
- Form submission on confirmation
- Automatic state reset on close
- 12 comprehensive tests

**Testing:**

```php
public function test_can_show_confirmation(): void
{
    Livewire::test(ConfirmationModal::class)
        ->dispatch('confirm-action', [
            'title' => 'Delete Item',
            'message' => 'This will delete the item permanently',
        ])
        ->assertSet('show', true)
        ->assertSet('title', 'Delete Item')
        ->assertSee('Delete Item');
}
```

## File Upload

{% raw %}

```blade
<form wire:submit="save">
    <input type="file" wire:model="photo">

    @error('photo') <span class="error">{{ $message }}</span> @enderror

    @if ($photo)
        <img src="{{ $photo->temporaryUrl() }}" />
    @endif

    <button type="submit">Upload</button>
</form>
```

{% endraw %}

```php
use Livewire\WithFileUploads;

class UploadPhoto extends Component
{
    use WithFileUploads;

    public $photo;

    protected $rules = [
        'photo' => 'image|max:1024',
    ];

    public function save()
    {
        $this->validate();

        $path = $this->photo->store('photos');

        // Save to database...
    }
}
```

## Event Communication

### Emit Events

```php
public function save()
{
    // Save logic...

    $this->dispatch('item-created');
}
```

### Listen to Events

```php
protected $listeners = ['item-created' => 'refreshList'];

public function refreshList()
{
    // Refresh the list
}
```

In Blade:

```blade
<div wire:on="item-created">
    Item was created!
</div>
```

## Best Practices

1. **Keep components focused** - One responsibility per component
2. **Use properties wisely** - Public properties are automatically tracked
3. **Validate input** - Always validate user input
4. **Handle loading states** - Show feedback during operations
5. **Reset state** - Clear forms after successful submission
6. **Use computed properties** - For expensive calculations

## Performance Tips

1. **Lazy loading** - Use `wire:model.lazy` for less frequent updates
2. **Debounce** - Use `wire:model.debounce.500ms` for search inputs
3. **Throttle** - Limit update frequency on expensive operations
4. **Polling** - Use `wire:poll` sparingly
5. **Defer loading** - Load expensive data on-demand

## Related Documentation

- [Official Livewire Documentation](https://livewire.laravel.com/)
- [Blade Components]({{ '/frontend-blade/components/' | relative_url }})
- [Alpine.js Integration]({{ '/frontend-blade/alpine/' | relative_url }})
