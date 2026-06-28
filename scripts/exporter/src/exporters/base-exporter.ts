import { writeFile } from 'fs/promises';
import { resolve } from 'path';
import type { ExportContext, ExportResult } from '../core/types.js';

export abstract class BaseExporter {
  protected context: ExportContext;

  constructor(context: ExportContext) {
    this.context = context;
  }

  abstract getName(): string;
  abstract export(): Promise<ExportResult>;

  protected get db() {
    return this.context.db;
  }

  protected get projectIds() {
    return this.context.projectIds;
  }

  protected get baseUrl() {
    return this.context.baseUrl;
  }

  protected get logger() {
    return this.context.logger;
  }

  protected async writeJson(filename: string, data: unknown): Promise<void> {
    const filePath = resolve(this.context.outputDir, filename);
    await writeFile(filePath, JSON.stringify(data, null, 2), 'utf-8');
  }

  protected placeholders(count: number): string {
    return Array.from({ length: count }, () => '?').join(', ');
  }

  protected imageUrl(path: string): string {
    return `${this.baseUrl}/${path}`;
  }
}
