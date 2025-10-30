import { vi } from 'vitest'
import { computed, type ComputedRef } from 'vue'

// Local minimal type mirroring the production ColorClasses shape
interface ColorClassesMock {
  icon: string
  iconHover: string
  button: string
  buttonHover: string
  focus: string
  ring: string
  badge: string
  badgeText: string
  badgeBackground: string
  activeBackground: string
  activeBadge: string
  inactiveBackground: string
  inactiveIcon: string
  hover: string
  border: string
  borderHover: string
}

function resolveColor(c: string | ComputedRef<string> | { value?: string } | undefined) {
  const valid = [
    'blue',
    'teal',
    'green',
    'purple',
    'orange',
    'yellow',
    'indigo',
    'red',
    'pink',
    'gray',
  ]
  let name = 'gray'
  if (typeof c === 'string') name = c
  else if (c && typeof (c as { value?: unknown }).value === 'string')
    name = (c as { value?: string }).value || name
  return valid.includes(name) ? name : 'gray'
}

function buildColor(name: string): ColorClassesMock {
  return {
    icon: `text-${name}-600`,
    iconHover: `hover:text-${name}-800`,
    button: `bg-${name}-600 hover:bg-${name}-700 text-white`,
    buttonHover: `hover:bg-${name}-50`,
    focus: `focus:border-${name}-500 focus:ring-${name}-500`,
    ring: `focus:ring-${name}-500`,
    badge: `text-${name}-600`,
    badgeText: `text-${name}-700`,
    badgeBackground: `bg-${name}-100`,
    activeBackground: `bg-${name}-100`,
    activeBadge: `text-${name}-600`,
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: `hover:bg-${name}-50`,
    border: `border-${name}-300`,
    borderHover: `hover:border-${name}-400`,
  }
}

const VALID_COLORS = [
  'blue',
  'teal',
  'green',
  'purple',
  'orange',
  'yellow',
  'indigo',
  'red',
  'pink',
  'gray',
]
const COLOR_MAP: Record<string, ColorClassesMock> = {}
for (const c of VALID_COLORS) COLOR_MAP[c] = buildColor(c)

vi.mock('@/composables/useColors', () => ({
  COLOR_MAP,
  getThemeClass: (name: string) => {
    const map: Record<string, string> = {
      // common tokens used across components
      navLinkColor: 'text-gray-500 hover:text-gray-900',
      dropdownBorder: 'border-gray-200',
      formBorder: 'border-gray-300',
      inputFocus: 'focus:border-indigo-500 focus:ring-indigo-500',
      modalTitle: 'text-gray-900',
      modalDescription: 'text-sm text-gray-500',
      headerAccentBorder: 'border-blue-500',
      messageError: 'bg-red-50 border-red-200 text-red-800',
      messageWarning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
      messageInfo: 'bg-blue-50 border-blue-200 text-blue-800',
      messageErrorText: 'text-red-700',
      inputText: 'text-gray-900',
      placeholderText: 'placeholder-gray-500',
      primaryButton:
        'inline-flex px-3 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm',
      secondaryButton:
        'inline-flex px-3 py-2 text-sm font-semibold bg-white text-gray-900 hover:bg-gray-50 ring-1 ring-inset ring-gray-300',
      // Use px-4 here to match tests that expect Delete/Edit buttons to use px-4
      dangerButton:
        'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed',
      toggleActiveBg: 'bg-indigo-600',
      toggleInactiveBg: 'bg-gray-200',
      toggleActiveIcon: 'text-indigo-600',
      toggleInactiveIcon: 'text-gray-400',
      toggleFocusRing: 'focus:ring-indigo-600',
      modeEditText: 'text-blue-800',
      modeCreateText: 'text-green-800',
      neutralText: 'text-gray-500',
      footerBg: 'bg-white',
      footerBorderTop: 'border-t',
      mobileBorderColor: 'border-gray-200',
    }
    return map[name] || ''
  },
  useColors: (color: string | ComputedRef<string> | { value?: string } | undefined) =>
    computed(() => {
      const name = resolveColor(color)
      return buildColor(name)
    }),
  useUIColors: (type: string) =>
    computed(() => {
      // Map UI semantic types to a representative color name
      const map: Record<string, string> = {
        primary: 'blue',
        secondary: 'gray',
        success: 'green',
        warning: 'yellow',
        danger: 'red',
        info: 'blue',
        filter: 'purple',
      }
      const name = map[type] || 'blue'
      return buildColor(name)
    }),
}))

export {}
