import { computed, type ComputedRef } from 'vue'

// Entity color configuration
export const ENTITY_COLORS = {
  contexts: 'green',
  projects: 'orange', 
  partners: 'yellow',
  languages: 'purple',
  items: 'teal',
  countries: 'blue',
  collections: 'indigo',
} as const

export type EntityType = keyof typeof ENTITY_COLORS
export type ColorName = typeof ENTITY_COLORS[EntityType] | 'red' | 'pink' | 'gray'

// Comprehensive color class definitions
export interface ColorClasses {
  // Icon colors
  icon: string
  
  // Button and interactive states
  button: string
  buttonHover: string
  focus: string
  ring: string
  
  // Badge colors
  badge: string          // -600 variant for filter buttons and icons
  badgeText: string      // -800 variant for badge text
  badgeBackground: string // -100 variant for badge backgrounds
  
  // Background colors for active/inactive states
  activeBackground: string
  activeBadge: string
  inactiveBackground: string
  inactiveIcon: string
  
  // Hover effects
  hover: string
  
  // Border colors
  border: string
  borderHover: string
}

// Complete color mapping with all possible class variations
export const COLOR_MAP: Record<ColorName, ColorClasses> = {
  blue: {
    icon: 'text-blue-600',
    button: 'bg-blue-600 hover:bg-blue-700 text-white',
    buttonHover: 'hover:bg-blue-50',
    focus: 'focus:border-blue-500 focus:ring-blue-500',
    ring: 'focus:ring-blue-500',
    badge: 'text-blue-600',
    badgeText: 'text-blue-800',
    badgeBackground: 'bg-blue-100',
    activeBackground: 'bg-blue-100',
    activeBadge: 'text-blue-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-blue-50',
    border: 'border-blue-300',
    borderHover: 'hover:border-blue-400',
  },
  teal: {
    icon: 'text-teal-600',
    button: 'bg-teal-600 hover:bg-teal-700 text-white',
    buttonHover: 'hover:bg-teal-50',
    focus: 'focus:border-teal-500 focus:ring-teal-500',
    ring: 'focus:ring-teal-500',
    badge: 'text-teal-600',
    badgeText: 'text-teal-800',
    badgeBackground: 'bg-teal-100',
    activeBackground: 'bg-teal-100',
    activeBadge: 'text-teal-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-teal-50',
    border: 'border-teal-300',
    borderHover: 'hover:border-teal-400',
  },
  green: {
    icon: 'text-green-600',
    button: 'bg-green-600 hover:bg-green-700 text-white',
    buttonHover: 'hover:bg-green-50',
    focus: 'focus:border-green-500 focus:ring-green-500',
    ring: 'focus:ring-green-500',
    badge: 'text-green-600',
    badgeText: 'text-green-800',
    badgeBackground: 'bg-green-100',
    activeBackground: 'bg-green-100',
    activeBadge: 'text-green-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-green-50',
    border: 'border-green-300',
    borderHover: 'hover:border-green-400',
  },
  purple: {
    icon: 'text-purple-600',
    button: 'bg-purple-600 hover:bg-purple-700 text-white',
    buttonHover: 'hover:bg-purple-50',
    focus: 'focus:border-purple-500 focus:ring-purple-500',
    ring: 'focus:ring-purple-500',
    badge: 'text-purple-600',
    badgeText: 'text-purple-800',
    badgeBackground: 'bg-purple-100',
    activeBackground: 'bg-purple-100',
    activeBadge: 'text-purple-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-purple-50',
    border: 'border-purple-300',
    borderHover: 'hover:border-purple-400',
  },
  orange: {
    icon: 'text-orange-600',
    button: 'bg-orange-600 hover:bg-orange-700 text-white',
    buttonHover: 'hover:bg-orange-50',
    focus: 'focus:border-orange-500 focus:ring-orange-500',
    ring: 'focus:ring-orange-500',
    badge: 'text-orange-600',
    badgeText: 'text-orange-800',
    badgeBackground: 'bg-orange-100',
    activeBackground: 'bg-orange-100',
    activeBadge: 'text-orange-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-orange-50',
    border: 'border-orange-300',
    borderHover: 'hover:border-orange-400',
  },
  yellow: {
    icon: 'text-yellow-600',
    button: 'bg-yellow-600 hover:bg-yellow-700 text-white',
    buttonHover: 'hover:bg-yellow-50',
    focus: 'focus:border-yellow-500 focus:ring-yellow-500',
    ring: 'focus:ring-yellow-500',
    badge: 'text-yellow-600',
    badgeText: 'text-yellow-800',
    badgeBackground: 'bg-yellow-100',
    activeBackground: 'bg-yellow-100',
    activeBadge: 'text-yellow-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-yellow-50',
    border: 'border-yellow-300',
    borderHover: 'hover:border-yellow-400',
  },
  indigo: {
    icon: 'text-indigo-600',
    button: 'bg-indigo-600 hover:bg-indigo-700 text-white',
    buttonHover: 'hover:bg-indigo-50',
    focus: 'focus:border-indigo-500 focus:ring-indigo-500',
    ring: 'focus:ring-indigo-500',
    badge: 'text-indigo-600',
    badgeText: 'text-indigo-800',
    badgeBackground: 'bg-indigo-100',
    activeBackground: 'bg-indigo-100',
    activeBadge: 'text-indigo-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-indigo-50',
    border: 'border-indigo-300',
    borderHover: 'hover:border-indigo-400',
  },
  red: {
    icon: 'text-red-600',
    button: 'bg-red-600 hover:bg-red-700 text-white',
    buttonHover: 'hover:bg-red-50',
    focus: 'focus:border-red-500 focus:ring-red-500',
    ring: 'focus:ring-red-500',
    badge: 'text-red-600',
    badgeText: 'text-red-800',
    badgeBackground: 'bg-red-100',
    activeBackground: 'bg-red-100',
    activeBadge: 'text-red-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-red-50',
    border: 'border-red-300',
    borderHover: 'hover:border-red-400',
  },
  pink: {
    icon: 'text-pink-600',
    button: 'bg-pink-600 hover:bg-pink-700 text-white',
    buttonHover: 'hover:bg-pink-50',
    focus: 'focus:border-pink-500 focus:ring-pink-500',
    ring: 'focus:ring-pink-500',
    badge: 'text-pink-600',
    badgeText: 'text-pink-800',
    badgeBackground: 'bg-pink-100',
    activeBackground: 'bg-pink-100',
    activeBadge: 'text-pink-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-pink-50',
    border: 'border-pink-300',
    borderHover: 'hover:border-pink-400',
  },
  gray: {
    icon: 'text-gray-600',
    button: 'bg-gray-600 hover:bg-gray-700 text-white',
    buttonHover: 'hover:bg-gray-50',
    focus: 'focus:border-gray-500 focus:ring-gray-500',
    ring: 'focus:ring-gray-500',
    badge: 'text-gray-600',
    badgeText: 'text-gray-800',
    badgeBackground: 'bg-gray-100',
    activeBackground: 'bg-gray-100',
    activeBadge: 'text-gray-600',
    inactiveBackground: 'bg-gray-100',
    inactiveIcon: 'text-gray-600',
    hover: 'hover:bg-gray-50',
    border: 'border-gray-300',
    borderHover: 'hover:border-gray-400',
  },
}

/**
 * Composable for getting color classes based on color name
 * @param color - The color name to get classes for
 * @returns Computed color classes object
 */
export function useColors(color: ComputedRef<ColorName> | ColorName): ComputedRef<ColorClasses> {
  return computed(() => {
    const colorValue = typeof color === 'string' ? color : color.value
    return COLOR_MAP[colorValue] || COLOR_MAP.gray
  })
}

/**
 * Get color classes for a specific entity type
 * @param entityType - The entity type to get colors for
 * @returns Computed color classes object
 */
export function useEntityColors(entityType: EntityType): ComputedRef<ColorClasses> {
  return useColors(ENTITY_COLORS[entityType])
}

/**
 * Get the color name for a specific entity type
 * @param entityType - The entity type to get color name for
 * @returns Color name
 */
export function getEntityColor(entityType: EntityType): ColorName {
  return ENTITY_COLORS[entityType]
}

/**
 * Simple color classes for backward compatibility with existing components
 * @param color - The color name
 * @returns Simple color mapping (for components that only need icon colors)
 */
export function getSimpleColorClasses(color: ColorName): Record<string, string> {
  const colorClasses = COLOR_MAP[color] || COLOR_MAP.gray
  return {
    [color]: colorClasses.icon,
  }
}
