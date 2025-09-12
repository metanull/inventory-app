---
layout: default
title: Theme and Colors
parent: Frontend
nav_order: 6
---

# Theme and Color System

This project centralizes Tailwind color fragments and theme tokens in a single composable to ensure consistency across the UI and to keep Tailwind JIT-friendly literal class fragments.

Key files and concepts

- `resources/js/composables/useColors.ts` — The single source of truth for color tokens and helpers. Exposes:
  - `useColors(color)` — composable returning a `ColorClasses` computed object with fields like `icon`, `button`, `badgeBackground`, `activeBackground`, etc.
  - `COLOR_MAP` — mapping of color names (`blue`, `green`, `teal`, `gray`, etc.) to concrete Tailwind class fragments.
  - `ENTITY_COLORS` — default color for entities (items, projects, contexts, ...).
  - `useThemeColors(themeToken)` and `THEME_CLASS_MAP` — semantic layout tokens for header, nav, and modal fragments.

Why this exists

- Tailwind's JIT requires literal class fragments for reliable generation. Centralizing these strings avoids sprinkling `bg-blue-100` and similar fragments throughout the codebase.
- It enforces consistent color usage across components (icons, buttons, badges, borders).
- It makes it easy to change a project's color palette in one place.

How to use

1. Simple usage in components:

```ts
import { useColors } from "@/composables/useColors";
const colorClasses = useColors("green");

// Then in template
// <svg :class="colorClasses.icon" />
// <button :class="colorClasses.button">Save</button>
```

2. Use entity-specific helpers:

```ts
import { useEntityColors } from "@/composables/useColors";
const projectColors = useEntityColors("projects");
```

3. Theme tokens for layout fragments (headers, modals):

```ts
import { getThemeClass } from '@/composables/useColors'
<h2 :class="getThemeClass('modalTitle')">Title</h2>
```

When to add a new color

- Add a new color name in `COLOR_MAP` with the full set of Tailwind fragments. Keep fragments literal to ensure JIT picks them up.
- Add any semantic theme tokens to `THEME_CLASS_MAP` when required by layout components.

Notes for tests

- Tests should mock the `useColors` composable when they rely on colors to avoid fragile assertions; a mock helper is available in `resources/js/components/__tests__/test-utils/useColorsMock.ts`.

Recommendations

- Use `useColors` for any component-level color needs (icons, badges, buttons).
- Use `getThemeClass` for layout and header tokens.
- Avoid inline Tailwind color fragments outside of styles or central mapping to keep the system consistent.
