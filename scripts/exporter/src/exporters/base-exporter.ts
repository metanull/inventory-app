import { writeFile } from 'fs/promises'
import { resolve } from 'path'
import type { ExportContext, ExportResult } from '../core/types.js'

export abstract class BaseExporter {
  protected context: ExportContext
  private _langCodeMap: Map<string, string> | null = null

  constructor(context: ExportContext) {
    this.context = context
  }

  abstract getName(): string
  abstract export(): Promise<ExportResult>

  protected get db() {
    return this.context.db
  }

  protected get projectIds() {
    return this.context.projectIds
  }

  protected get baseUrl() {
    return this.context.baseUrl
  }

  protected get logger() {
    return this.context.logger
  }

  /**
   * Returns a map from language id (3-char ISO, e.g. 'eng') to the 2-char
   * backward_compatibility code used as the key in exported JSON (e.g. 'en').
   * Result is cached within the exporter instance.
   */
  protected async buildLangCodeMap(): Promise<Map<string, string>> {
    if (this._langCodeMap) return this._langCodeMap

    const rows = await this.db.query<{ id: string; backward_compatibility: string | null }>(
      `SELECT id, backward_compatibility FROM languages`
    )
    this._langCodeMap = new Map(
      rows
        .filter(r => r.backward_compatibility !== null)
        .map(r => [r.id, r.backward_compatibility as string])
    )
    return this._langCodeMap
  }

  protected async writeJson(filename: string, data: unknown): Promise<void> {
    const filePath = resolve(this.context.outputDir, filename)
    await writeFile(filePath, JSON.stringify(data, null, 2), 'utf-8')
  }

  protected placeholders(count: number): string {
    return Array.from({ length: count }, () => '?').join(', ')
  }

  protected imageUrl(path: string): string {
    return `${this.baseUrl}/${path}`
  }
}
