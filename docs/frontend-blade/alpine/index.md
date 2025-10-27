---
layout: default
title: Alpine.js
nav_order: 3
parent: Blade/Livewire Frontend
has_children: false
---

# Alpine.js Patterns

Alpine.js provides lightweight JavaScript interactions for the frontend.

## What is Alpine.js?

Alpine.js is a minimal JavaScript framework for adding interactivity:

- **Declarative** - Define behavior in HTML attributes
- **Lightweight** - Only ~15kb gzipped
- **No build step** - Works directly in the browser
- **Reactive** - Automatically updates the DOM

## Basic Syntax

Alpine uses special HTML attributes:

- `x-data` - Define component state
- `x-show` - Toggle visibility
- `x-if` - Conditional rendering
- `x-for` - Loop through arrays
- `x-on` (or `@`) - Event listeners
- `x-bind` (or `:`) - Bind attributes
- `x-model` - Two-way data binding

## Common Patterns

### Toggle Visibility

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">
        Toggle
    </button>
    
    <div x-show="open">
        This content is toggleable
    </div>
</div>
```

### Dropdown Menu

```blade
<div x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open">
        Menu
    </button>
    
    <div x-show="open" x-transition>
        <a href="#">Item 1</a>
        <a href="#">Item 2</a>
        <a href="#">Item 3</a>
    </div>
</div>
```

### Tabs

```blade
<div x-data="{ activeTab: 'tab1' }">
    <div class="tabs">
        <button 
            @click="activeTab = 'tab1'"
            :class="{ 'active': activeTab === 'tab1' }"
        >
            Tab 1
        </button>
        <button 
            @click="activeTab = 'tab2'"
            :class="{ 'active': activeTab === 'tab2' }"
        >
            Tab 2
        </button>
    </div>
    
    <div x-show="activeTab === 'tab1'">
        Tab 1 content
    </div>
    
    <div x-show="activeTab === 'tab2'">
        Tab 2 content
    </div>
</div>
```

### Modal Dialog

```blade
<div x-data="{ open: false }">
    <button @click="open = true">
        Open Modal
    </button>
    
    <div 
        x-show="open" 
        @click.self="open = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center"
    >
        <div 
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90"
            class="bg-white rounded-lg p-6 max-w-md"
        >
            <h2 class="text-xl font-bold mb-4">Modal Title</h2>
            <p class="mb-4">Modal content</p>
            <button @click="open = false">Close</button>
        </div>
    </div>
</div>
```

### Form Validation

```blade
<div x-data="{ 
    email: '', 
    valid: false,
    validate() {
        this.valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)
    }
}">
    <input 
        x-model="email" 
        @input="validate()"
        type="email" 
        placeholder="Enter email"
    />
    
    <span x-show="!valid && email.length > 0" class="text-red-500">
        Invalid email
    </span>
    
    <button :disabled="!valid">
        Submit
    </button>
</div>
```

### Search Filter

```blade
<div x-data="{ 
    search: '',
    items: @js($items),
    get filteredItems() {
        return this.items.filter(item => 
            item.name.toLowerCase().includes(this.search.toLowerCase())
        )
    }
}">
    <input x-model="search" type="text" placeholder="Search..." />
    
    <template x-for="item in filteredItems" :key="item.id">
        <div x-text="item.name"></div>
    </template>
</div>
```

### Accordion

```blade
<div x-data="{ active: null }">
    <div class="accordion-item">
        <button @click="active = active === 1 ? null : 1">
            Section 1
        </button>
        <div x-show="active === 1" x-collapse>
            Content for section 1
        </div>
    </div>
    
    <div class="accordion-item">
        <button @click="active = active === 2 ? null : 2">
            Section 2
        </button>
        <div x-show="active === 2" x-collapse>
            Content for section 2
        </div>
    </div>
</div>
```

## Integration with Livewire

Alpine and Livewire work together seamlessly:

```blade
<div 
    x-data="{ open: false }" 
    @item-saved.window="open = false; $wire.refreshList()"
>
    <button @click="open = true">Add Item</button>
    
    <div x-show="open">
        <form wire:submit="save">
            <input wire:model="name" type="text" />
            <button type="submit">Save</button>
        </form>
    </div>
</div>
```

Access Livewire from Alpine:
{% raw %}
```blade
<button @click="$wire.delete({{ $id }})">
    Delete
</button>
```
{% endraw %}

## Advanced Patterns

### Component Composition

```blade
<div x-data="dropdown()">
    <button @click="toggle()">Toggle</button>
    <div x-show="isOpen()">Content</div>
</div>

<script>
function dropdown() {
    return {
        open: false,
        toggle() {
            this.open = !this.open
        },
        isOpen() {
            return this.open
        }
    }
}
</script>
```

### Global State (Alpine Store)

```blade
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        add(item) {
            this.items.push(item)
        },
        remove(index) {
            this.items.splice(index, 1)
        }
    })
})
</script>

<div x-data>
    <button @click="$store.cart.add({ id: 1, name: 'Item' })">
        Add to Cart
    </button>
    
    <div>
        Items: <span x-text="$store.cart.items.length"></span>
    </div>
</div>
```

## Best Practices

1. **Keep it simple** - Use Alpine for UI interactions, not complex logic
2. **Combine with Livewire** - Let Livewire handle server communication
3. **Use transitions** - Add `x-transition` for smooth animations
4. **Click away** - Use `@click.away` to close dropdowns/modals
5. **Avoid global state** - Prefer component-level `x-data`
6. **Use `x-cloak`** - Hide content until Alpine is ready

## Common Directives

| Directive | Purpose |
|-----------|---------|
| `x-data` | Define component state |
| `x-show` | Toggle visibility (CSS) |
| `x-if` | Conditional rendering (DOM) |
| `x-for` | Loop through arrays |
| `x-on` / `@` | Event listeners |
| `x-bind` / `:` | Bind attributes |
| `x-model` | Two-way binding |
| `x-text` | Set text content |
| `x-html` | Set HTML content |
| `x-ref` | Reference elements |
| `x-cloak` | Hide until ready |
| `x-transition` | Add transitions |
| `x-effect` | Run code on change |

## Related Documentation

- [Official Alpine.js Documentation](https://alpinejs.dev/)
- [Livewire + Alpine Integration](https://livewire.laravel.com/docs/alpine)
- [Blade Components]({{ '/frontend-blade/components/' | relative_url }})
