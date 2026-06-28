import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

export class ManifestExporter extends BaseExporter {
  getName(): string {
    return 'Manifest'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Writing manifest.json...')

    const manifest = {
      generatedAt: new Date().toISOString(),
      projectKeys: this.context.projectKeys,
      projectIds: this.context.projectIds,
      version: '1.0.0',
    }

    await this.writeJson('manifest.json', manifest)
    this.logger.success('manifest.json')

    return { file: 'manifest.json', count: 1 }
  }
}
