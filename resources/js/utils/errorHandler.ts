import { reactive } from 'vue'
import type { AxiosError } from 'axios'

// Error types that can occur in the application
export interface ApiError {
  message: string
  code?: string
  status?: number
  field?: string
  details?: Record<string, unknown>
}

export interface ValidationError {
  field: string
  message: string
}

export interface ErrorState {
  errors: ApiError[]
  validationErrors: ValidationError[]
  isVisible: boolean
}

// Global error state
const errorState = reactive<ErrorState>({
  errors: [],
  validationErrors: [],
  isVisible: false,
})

// Auto-hide timer
let hideTimer: ReturnType<typeof setTimeout> | null = null

export class ErrorHandler {
  static handleError(error: unknown, context?: string): void {
    console.error('Error occurred:', error, 'Context:', context)

    const apiError = this.parseError(error, context)
    this.addError(apiError)

    // Auto-hide after 10 seconds for non-critical errors
    if (apiError.status !== 401 && apiError.status !== 403) {
      this.scheduleAutoHide()
    }
  }

  static handleValidationError(error: unknown, context?: string): ValidationError[] {
    const validationErrors = this.parseValidationError(error)
    errorState.validationErrors = validationErrors

    if (validationErrors.length > 0) {
      this.addError({
        message: `Validation failed: ${validationErrors.map(e => e.message).join(', ')}`,
        code: 'VALIDATION_ERROR',
        status: 422,
        details: { context, validationErrors },
      })
    }

    return validationErrors
  }

  static parseError(error: unknown, context?: string): ApiError {
    // Handle Axios errors
    if (this.isAxiosError(error)) {
      return this.parseAxiosError(error as AxiosError, context)
    }

    // Handle network errors
    if ((this.hasProperty(error, 'code') && error.code === 'NETWORK_ERROR') || !navigator.onLine) {
      return {
        message: 'Network connection error. Please check your internet connection.',
        code: 'NETWORK_ERROR',
        status: 0,
        details: { context },
      }
    }

    // Handle generic JavaScript errors
    return {
      message:
        this.hasProperty(error, 'message') && typeof error.message === 'string'
          ? error.message
          : 'An unexpected error occurred',
      code: 'UNKNOWN_ERROR',
      details: { context, originalError: error },
    }
  }

  static isAxiosError(error: unknown): error is AxiosError {
    return (
      typeof error === 'object' &&
      error !== null &&
      ('isAxiosError' in error || ('response' in error && 'request' in error))
    )
  }

  static hasProperty<T extends PropertyKey>(obj: unknown, prop: T): obj is Record<T, unknown> {
    return typeof obj === 'object' && obj !== null && prop in obj
  }

  static parseAxiosError(error: AxiosError, context?: string): ApiError {
    const response = error.response
    const status = response?.status || 0

    // Handle different HTTP status codes
    switch (status) {
      case 400:
        return {
          message:
            this.extractErrorMessage(response?.data) || 'Invalid request. Please check your input.',
          code: 'BAD_REQUEST',
          status: 400,
          details: { context, responseData: response?.data },
        }

      case 401:
        return {
          message: 'Authentication required. Please log in again.',
          code: 'UNAUTHORIZED',
          status: 401,
          details: { context },
        }

      case 403:
        return {
          message: 'You do not have permission to perform this action.',
          code: 'FORBIDDEN',
          status: 403,
          details: { context },
        }

      case 404:
        return {
          message: 'The requested resource was not found.',
          code: 'NOT_FOUND',
          status: 404,
          details: { context },
        }

      case 422:
        return {
          message:
            this.extractErrorMessage(response?.data) ||
            'Validation failed. Please check your input.',
          code: 'VALIDATION_ERROR',
          status: 422,
          details: { context, responseData: response?.data },
        }

      case 429:
        return {
          message: 'Too many requests. Please wait a moment and try again.',
          code: 'RATE_LIMITED',
          status: 429,
          details: { context },
        }

      case 500:
        return {
          message: 'Server error. Please try again later.',
          code: 'SERVER_ERROR',
          status: 500,
          details: { context },
        }

      default:
        return {
          message: `Request failed with status ${status}. ${this.extractErrorMessage(response?.data) || ''}`,
          code: 'HTTP_ERROR',
          status,
          details: { context, responseData: response?.data },
        }
    }
  }

  static parseValidationError(error: unknown): ValidationError[] {
    if (!this.hasProperty(error, 'response')) {
      return []
    }

    const response = error.response
    if (!this.hasProperty(response, 'status') || response.status !== 422) {
      return []
    }

    const data = this.hasProperty(response, 'data') ? response.data : null
    const validationErrors: ValidationError[] = []

    // Handle Laravel-style validation errors
    if (
      this.hasProperty(data, 'errors') &&
      typeof data.errors === 'object' &&
      data.errors !== null
    ) {
      Object.entries(data.errors).forEach(([field, messages]) => {
        const messageArray = Array.isArray(messages) ? messages : [messages]
        messageArray.forEach((message: unknown) => {
          if (typeof message === 'string') {
            validationErrors.push({ field, message })
          }
        })
      })
    }

    // Handle other validation error formats
    if (this.hasProperty(data, 'message') && typeof data.message === 'string') {
      validationErrors.push({ field: 'general', message: data.message })
    }

    return validationErrors
  }

  static extractErrorMessage(data: unknown): string | null {
    if (!data) return null

    // Try different common error message formats
    if (typeof data === 'string') return data
    if (this.hasProperty(data, 'message') && typeof data.message === 'string') return data.message
    if (this.hasProperty(data, 'error') && typeof data.error === 'string') return data.error
    if (this.hasProperty(data, 'detail') && typeof data.detail === 'string') return data.detail

    return null
  }

  static addError(error: ApiError): void {
    errorState.errors.push(error)
    errorState.isVisible = true

    // Handle authentication errors by redirecting to login
    if (error.status === 401) {
      this.handleAuthenticationError()
    }
  }

  static handleAuthenticationError(): void {
    // Clear any stored authentication data
    localStorage.removeItem('auth_token')

    // Redirect to login page
    window.location.href = '/login'
  }

  static clearErrors(): void {
    errorState.errors = []
    errorState.validationErrors = []
    errorState.isVisible = false

    if (hideTimer) {
      clearTimeout(hideTimer)
      hideTimer = null
    }
  }

  static clearError(index: number): void {
    errorState.errors.splice(index, 1)
    if (errorState.errors.length === 0) {
      errorState.isVisible = false
    }
  }

  static clearValidationErrors(): void {
    errorState.validationErrors = []
  }

  static scheduleAutoHide(delay: number = 10000): void {
    if (hideTimer) {
      clearTimeout(hideTimer)
    }

    hideTimer = setTimeout(() => {
      errorState.isVisible = false
    }, delay)
  }

  static getErrorState(): ErrorState {
    return errorState
  }

  // Utility method to check if there are validation errors for a specific field
  static hasFieldError(fieldName: string): boolean {
    return errorState.validationErrors.some(error => error.field === fieldName)
  }

  static getFieldError(fieldName: string): string | null {
    const error = errorState.validationErrors.find(error => error.field === fieldName)
    return error ? error.message : null
  }
}

// Composable for Vue components
export function useErrorHandler() {
  return {
    errorState: readonly(errorState),
    handleError: ErrorHandler.handleError.bind(ErrorHandler),
    handleValidationError: ErrorHandler.handleValidationError.bind(ErrorHandler),
    clearErrors: ErrorHandler.clearErrors.bind(ErrorHandler),
    clearError: ErrorHandler.clearError.bind(ErrorHandler),
    clearValidationErrors: ErrorHandler.clearValidationErrors.bind(ErrorHandler),
    hasFieldError: ErrorHandler.hasFieldError.bind(ErrorHandler),
    getFieldError: ErrorHandler.getFieldError.bind(ErrorHandler),
  }
}

// Auto-import for readonly
import { readonly } from 'vue'
