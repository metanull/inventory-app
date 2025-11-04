---
layout: default
title: Forms
nav_order: 1
parent: Components
grand_parent: Blade/Livewire Frontend
---

# Form Components

Form components provide consistent input handling and validation display.

## Available Components

### Input Fields

#### Text Input

```blade
<x-form.input
    name="title"
    label="Title"
    :value="old('title', $item->title ?? '')"
    required
/>
```

#### Email Input

```blade
<x-form.input
    type="email"
    name="email"
    label="Email Address"
    placeholder="user@example.com"
/>
```

#### Number Input

```blade
<x-form.input
    type="number"
    name="quantity"
    label="Quantity"
    min="0"
    step="1"
/>
```

### Select Dropdown

```blade
<x-form.select
    name="country_id"
    label="Country"
    :options="$countries"
    :selected="old('country_id', $item->country_id ?? '')"
    required
/>
```

### Textarea

```blade
<x-form.textarea
    name="description"
    label="Description"
    :value="old('description', $item->description ?? '')"
    rows="5"
/>
```

### Markdown Editor (Livewire)

The markdown editor provides a rich editing experience with live preview and help guide, powered by Livewire for server-side rendering:

```blade
<x-form.markdown-editor-livewire
    name="description"
    label="Description"
    :value="old('description', $item->description ?? '')"
    rows="6"
    helpText="Use Markdown formatting. Preview updates in real-time."
    required
/>
```

**Features:**
- **Edit/Preview Tabs** - Switch between editing and preview modes
- **Live Preview** - See formatted output as you type (300ms debounce)
- **Help Guide** - Built-in markdown syntax reference
- **Server-side Rendering** - Uses `MarkdownService` for consistent preview
- **Comprehensive Testing** - 18 test cases ensure reliability

**Props:**
- `name` - Field name (default: 'description')
- `label` - Field label (default: 'Description')
- `value` - Initial content
- `rows` - Textarea height (default: 8)
- `required` - Make field required (default: false)
- `helpText` - Optional help text below the field

**Implementation:**
- Component: `App\Livewire\MarkdownEditor`
- Wrapper: `resources/views/components/form/markdown-editor-livewire.blade.php`
- Tests: `tests/Web/Livewire/MarkdownEditorTest.php`

### Checkbox

```blade
<x-form.checkbox
    name="is_active"
    label="Active"
    :checked="old('is_active', $item->is_active ?? false)"
/>
```

### Error Display

Validation errors are automatically displayed:

{% raw %}

```blade
<x-form.input name="email" label="Email" />
{{-- Error will show if validation fails --}}
```

{% endraw %}

Manual error display:
{% raw %}

```blade
<x-form.error name="email" />
```

{% endraw %}

## Form Patterns

### Standard CRUD Form

{% raw %}

```blade
<form method="POST" action="{{ route('item.store') }}">
    @csrf

    <x-form.input
        name="internal_name"
        label="Internal Name"
        required
    />

    <x-form.select
        name="partner_id"
        label="Partner"
        :options="$partners"
        required
    />

    <x-form.textarea
        name="description"
        label="Description"
    />

    <div class="flex gap-2">
        <x-button type="submit" color="green">
            Save
        </x-button>
        <x-button-link :href="route('item.index')" color="gray">
            Cancel
        </x-button-link>
    </div>
</form>
```

{% endraw %}

### Form with Livewire

```blade
<form wire:submit="save">
    <x-form.input
        wire:model="title"
        name="title"
        label="Title"
    />

    <x-form.select
        wire:model="category"
        name="category"
        label="Category"
        :options="$categories"
    />

    <x-button type="submit" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </x-button>
</form>
```

## Validation Display

### Field-Level Errors

Errors display automatically beneath fields:

{% raw %}

```blade
<x-form.input name="email" label="Email" />
{{-- If validation fails, error appears here --}}
```

{% endraw %}

### Summary Errors

Display all errors at the top of the form:

{% raw %}

```blade
@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-4">
        <h3 class="font-semibold mb-2">Please correct the following errors:</h3>
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

{% endraw %}

## Best Practices

1. **Use consistent naming** - Match form field names to model attributes
2. **Always include labels** - For accessibility and clarity
3. **Handle old input** - Use `old()` helper for form repopulation
4. **Show validation errors** - Display errors near relevant fields
5. **Disable submit on processing** - Prevent double submissions

## Related Documentation

- [Livewire Forms]({{ '/frontend-blade/livewire/' | relative_url }})
- [Validation](https://laravel.com/docs/validation)
- [Blade Components](https://laravel.com/docs/blade#components)
