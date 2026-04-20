import { promises as fs } from 'fs';
import os from 'os';
import path from 'path';
import { afterEach, describe, expect, it } from 'vitest';
import type { Connection } from 'mysql2/promise';

import { ImageSyncTool, type ImageSyncOptions } from '../../src/tools/image-sync.js';
import type { ILogger } from '../../src/core/base-importer.js';

class FakeConnection {
  private readonly selectRows: Array<{
    id: string;
    path: string;
    size: number;
    original_name: string | null;
    alt_text: string | null;
    mime_type: string;
  }>;

  public updateCalls = 0;

  constructor(
    selectRows: Array<{
      id: string;
      path: string;
      size: number;
      original_name: string | null;
      alt_text: string | null;
      mime_type: string;
    }>
  ) {
    this.selectRows = selectRows;
  }

  async execute(query: string): Promise<[unknown[], unknown]> {
    if (query.includes('SELECT')) {
      return [this.selectRows, []];
    }
    if (query.includes('UPDATE')) {
      this.updateCalls += 1;
      return [[], []];
    }
    return [[], []];
  }
}

class FakeLogger implements ILogger {
  public messages: string[] = [];
  public errors: string[] = [];
  public warnings: string[] = [];
  public skips: string[] = [];

  info(message: string): void {
    this.messages.push(message);
  }

  warning(message: string, details?: unknown): void {
    const detailsStr = details ? ` (${JSON.stringify(details)})` : '';
    this.warnings.push(`${message}${detailsStr}`);
  }

  skip(message: string): void {
    this.skips.push(message);
  }

  error(context: string, message: string, additionalContext?: Record<string, unknown>): void {
    const contextStr = additionalContext ? ` (${JSON.stringify(additionalContext)})` : '';
    this.errors.push(`${context}: ${message}${contextStr}`);
  }

  exception(context: string, error: Error, additionalContext?: Record<string, unknown>): void {
    const contextStr = additionalContext ? ` (${JSON.stringify(additionalContext)})` : '';
    this.errors.push(`EXCEPTION ${context}: ${error.message}${contextStr}`);
  }

  showProgress(): void {
    // no-op in test
  }

  showSkipped(): void {
    // no-op in test
  }

  showError(): void {
    // no-op in test
  }

  showSummary(_imported: number, _skipped: number, _errors: number): void {
    // no-op in test
  }
}

const tempDirs: string[] = [];

afterEach(async () => {
  await Promise.all(
    tempDirs.splice(0).map(async (dir) => {
      await fs.rm(dir, { recursive: true, force: true });
    })
  );
});

async function createTempDir(prefix: string): Promise<string> {
  const dir = await fs.mkdtemp(path.join(os.tmpdir(), prefix));
  tempDirs.push(dir);
  return dir;
}

function createOptions(overrides: Partial<ImageSyncOptions>): ImageSyncOptions {
  return {
    useSymlink: true,
    legacyImagesRoot: '/tmp/legacy',
    newImagesRoot: '/tmp/new',
    ...overrides,
  };
}

describe('ImageSyncTool destination clearing', () => {
  it('clears destination folder before sync when clearDestination is true', async () => {
    const legacyRoot = await createTempDir('importer-legacy-');
    const newRoot = await createTempDir('importer-new-');

    const oldFile = path.join(newRoot, 'old-file.jpg');
    await fs.writeFile(oldFile, 'old');

    const sourceRelativePath = 'source-folder/test-image.jpg';
    const sourcePath = path.join(legacyRoot, sourceRelativePath);
    await fs.mkdir(path.dirname(sourcePath), { recursive: true });
    await fs.writeFile(sourcePath, 'new-content');

    const db = new FakeConnection([
      {
        id: '123e4567-e89b-12d3-a456-426614174000',
        path: sourceRelativePath,
        size: 1,
        original_name: null,
        alt_text: null,
        mime_type: 'image/jpeg',
      },
    ]) as unknown as Connection;

    const logger = new FakeLogger();

    const tool = new ImageSyncTool(
      db,
      createOptions({
        legacyImagesRoot: legacyRoot,
        newImagesRoot: newRoot,
        clearDestination: true,
      }),
      logger
    );

    const result = await tool.run();

    expect(result.success).toBe(true);
    await expect(fs.access(oldFile)).rejects.toThrow();
    const entries = await fs.readdir(newRoot);
    expect(entries).toEqual(['123e4567-e89b-12d3-a456-426614174000.jpg']);
  });

  it('does not clear destination folder in dry-run mode', async () => {
    const newRoot = await createTempDir('importer-new-dry-');
    const oldFile = path.join(newRoot, 'keep-me.txt');
    await fs.writeFile(oldFile, 'keep');

    const db = new FakeConnection([]) as unknown as Connection;
    const logger = new FakeLogger();

    const tool = new ImageSyncTool(
      db,
      createOptions({
        newImagesRoot: newRoot,
        clearDestination: true,
        dryRun: true,
      }),
      logger
    );

    const result = await tool.run();

    expect(result.success).toBe(true);
    await expect(fs.access(oldFile)).resolves.toBeUndefined();
    expect(logger.messages).toContain(`[DRY-RUN] Would clear destination directory: ${newRoot}`);
  });
});
