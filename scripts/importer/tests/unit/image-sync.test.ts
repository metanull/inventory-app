import { promises as fs } from 'fs';
import os from 'os';
import path from 'path';
import { afterEach, describe, expect, it } from 'vitest';
import type { Connection } from 'mysql2/promise';

import { ImageSyncTool, type ImageSyncLogger, type ImageSyncOptions } from '../../src/tools/image-sync.js';

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

class FakeLogger implements ImageSyncLogger {
  public messages: string[] = [];
  public errors: string[] = [];

  info(message: string): void {
    this.messages.push(message);
  }

  error(context: string, error: unknown): void {
    this.errors.push(`${context}: ${String(error)}`);
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
