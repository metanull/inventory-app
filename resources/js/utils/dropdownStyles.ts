/**
 * Utility functions for consistent dropdown option styling across the application
 */

/**
 * Dropdown option style classes for consistent appearance
 */
export const DROPDOWN_OPTION_STYLES = {
  // Current/selected option
  current: 'font-semibold text-indigo-700',

  // System default option
  systemDefault: 'font-medium text-blue-600',

  // No default/empty option (when not selected)
  noDefault: 'text-red-600',

  // Normal option (default styling)
  normal: '',
} as const

/**
 * Dropdown option labels for consistent text across the application
 */
export const DROPDOWN_OPTION_LABELS = {
  current: ' (Current)',
  systemDefault: ' (System Default)',
  noDefaultContext: 'No default context',
  noDefaultLanguage: 'No default language',
} as const

/**
 * Get CSS classes for a dropdown option based on its state
 * @param isCurrent - Whether this option is currently selected
 * @param isSystemDefault - Whether this option is the system default
 * @param isNoDefault - Whether this is a "no default" option
 * @returns CSS class string
 */
export function getDropdownOptionClasses(
  isCurrent: boolean,
  isSystemDefault: boolean,
  isNoDefault: boolean = false
): string {
  const classes: string[] = []

  if (isCurrent) {
    classes.push(DROPDOWN_OPTION_STYLES.current)
  } else if (isNoDefault) {
    classes.push(DROPDOWN_OPTION_STYLES.noDefault)
  }

  if (isSystemDefault && !isCurrent) {
    classes.push(DROPDOWN_OPTION_STYLES.systemDefault)
  }

  return classes.join(' ')
}

/**
 * Get the appropriate label suffix for a dropdown option
 * @param isCurrent - Whether this option is currently selected
 * @param isSystemDefault - Whether this option is the system default
 * @returns Label suffix string
 */
export function getDropdownOptionLabel(isCurrent: boolean, isSystemDefault: boolean): string {
  const labels: string[] = []

  if (isCurrent) {
    labels.push(DROPDOWN_OPTION_LABELS.current)
  }

  if (isSystemDefault) {
    labels.push(DROPDOWN_OPTION_LABELS.systemDefault)
  }

  return labels.join('')
}

/**
 * Type definitions for dropdown option data
 */
export interface DropdownOption {
  id: string
  internal_name: string
  is_default?: boolean
}

/**
 * Helper function to create styled dropdown option props
 * @param option - The dropdown option data
 * @param currentValue - The currently selected value
 * @param isNoDefault - Whether this is a "no default" option
 * @returns Object with classes and label for the option
 */
export function createDropdownOptionProps(
  option: DropdownOption | null,
  currentValue: string,
  isNoDefault: boolean = false
) {
  const isCurrent = option?.id === currentValue || (isNoDefault && currentValue === '')
  const isSystemDefault = option?.is_default || false

  return {
    classes: getDropdownOptionClasses(isCurrent, isSystemDefault, isNoDefault),
    label: getDropdownOptionLabel(isCurrent, isSystemDefault),
    isCurrent,
    isSystemDefault,
  }
}
