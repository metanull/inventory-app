import type { Database } from './database.js'
import type { Logger } from './logger.js'

export interface ExportContext {
  db: Database
  outputDir: string
  projectIds: string[]
  contextIds: string[]
  projectKeys: string[]
  baseUrl: string
  logger: Logger
}

export interface ExportResult {
  file: string
  count: number
}

export interface TranslationMap {
  [langCode: string]: Record<string, string | null>
}
