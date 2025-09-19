/**
 * Composable to provide a consistent fallback image for broken/missing images
 */

export const useImageFallback = () => {
  /**
   * Base64-encoded SVG placeholder for images that fail to load
   * Shows a simple image icon with gray stroke/fill
   */
  const fallbackImageUrl =
    'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTMgMTZWOEMzIDYuMzQzMTUgNC4zNDMxNSA1IDYgNUgxOEMxOS42NTY5IDUgMjEgNi4zNDMxNSAyMSA4VjE2QzIxIDE3LjY1NjkgMTkuNjU2OSAxOSAxOCAxOUg2QzQuMzQzMTUgMTkgMyAxNy42NTY5IDMgMTZaIiBzdHJva2U9IiM5Q0EzQUYiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJNOSAxMEMxMC4xMDQ2IDEwIDExIDkuMTA0NTcgMTEgOEMxMSA2Ljg5NTQzIDEwLjEwNDYgNiA5IDZDNy44OTU0MyA2IDcgNi44OTU0MyA3IDhDNyA5LjEwNDU3IDcuODk1NDMgMTAgOSAxMFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0ibTIxIDE1LTMuNS0zLjUtMi41IDIuNS0zLTMtNCA0LjUiIHN0cm9rZT0iIzlDQTNBRiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+Cg=='

  return {
    fallbackImageUrl,
  }
}
