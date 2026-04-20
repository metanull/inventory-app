import { describe, expect, it, vi } from 'vitest';

import type { ILogger } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';
import type { ITracker } from '../../src/core/tracker.js';
import { AuthorHelper } from '../../src/helpers/author-helper.js';
import { sanitizeAllStrings } from '../../src/utils/html-to-markdown.js';

describe('AuthorHelper', () => {
  it('normalizes HTML-like names before lookup and duplicate retry', async () => {
    const rawName =
      'arch. Francesco Petrucci<br/>Copy-editor (Italian): Saverio Capozzi<br/>© Museum With No Frontiers (MWNF)';
    const normalizedName = sanitizeAllStrings({ name: rawName }).name.trim();

    const strategy = {
      findAuthorByName: vi.fn().mockResolvedValueOnce(null).mockResolvedValueOnce('author-uuid'),
      writeAuthor: vi.fn().mockRejectedValue(new Error('Duplicate entry')),
    } as unknown as IWriteStrategy;

    const logger = {
      warning: vi.fn(),
    } as unknown as ILogger;

    const tracker = {} as ITracker;

    const helper = new AuthorHelper(strategy, tracker, logger);

    const result = await helper.findOrCreate(rawName);

    expect(result).toBe('author-uuid');
    expect(strategy.findAuthorByName).toHaveBeenNthCalledWith(1, normalizedName);
    expect(strategy.writeAuthor).toHaveBeenCalledWith({
      name: normalizedName,
      internal_name: normalizedName,
      backward_compatibility: '',
    });
    expect(strategy.findAuthorByName).toHaveBeenNthCalledWith(2, normalizedName);
  });
});
