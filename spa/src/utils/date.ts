/**
 * Date utilities for formatting timestamps and dates
 */

export const formatTimestamp = (timestamp: string | null): string => {
  if (!timestamp) return 'Not available'

  try {
    const date = new Date(timestamp)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return 'Invalid date'
  }
}

export const formatDate = (dateString: string | null): string => {
  if (!dateString) return 'Not set'

  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  } catch {
    return 'Invalid date'
  }
}
