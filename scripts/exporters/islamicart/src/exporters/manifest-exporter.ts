import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

export class ManifestExporter extends BaseExporter {
  getName(): string {
    return 'Manifest'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Writing manifest.json...')

    const langRows = await this.db.query<{ backward_compatibility: string }>(
      `SELECT backward_compatibility FROM languages WHERE backward_compatibility IS NOT NULL ORDER BY id`
    )

    const manifest = {
      generatedAt: new Date().toISOString(),
      projectKeys: this.context.projectKeys,
      projectIds: this.context.projectIds,
      version: '1.0.0',
      languages: langRows.map(r => r.backward_compatibility),
    }

    await this.writeJson('manifest.json', manifest)
    this.logger.success('manifest.json')

    return { file: 'manifest.json', count: 1 }
  }
}
