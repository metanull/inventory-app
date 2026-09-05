/**
 * A data package holds Markdown, and every website renders it through one
 * pipeline that escapes raw HTML on sight. A tag that survived the conversion
 * would reach every website as the characters it is, so the conversion
 * refuses to hand one over: a leak fails the import, where the data is made,
 * rather than being worked around in a site.
 */

import { describe, expect, it } from 'vitest';
import {
  assertMarkdownOnly,
  containsHtml,
  convertHtmlToMarkdown,
} from '../../src/utils/html-to-markdown.js';

describe('containsHtml', () => {
  it('sees a tag', () => {
    expect(containsHtml('A <i>title</i>.')).toBe(true);
    expect(containsHtml('<div class="x">\n\nBlock\n\n</div>')).toBe(true);
  });

  it('does not mistake ordinary Markdown for markup', () => {
    expect(containsHtml('An *italic* title with a [link](https://example.org).')).toBe(false);
    expect(containsHtml('Write to <office@museumwnf.net> or see <https://example.org/>.')).toBe(false);
    expect(containsHtml('The code span `<div>` is text.')).toBe(false);
    expect(containsHtml('a < b and c > d')).toBe(false);
    expect(containsHtml('')).toBe(false);
    expect(containsHtml(null)).toBe(false);
  });
});

describe('assertMarkdownOnly', () => {
  it('names the field it refuses', () => {
    expect(() => assertMarkdownOnly('<b>x</b>', 'heading')).toThrow('heading');
    expect(() => assertMarkdownOnly('**x**', 'heading')).not.toThrow();
  });
});

describe('convertHtmlToMarkdown', () => {
  it('produces Markdown that passes its own guard', () => {
    const markdown = convertHtmlToMarkdown(
      '<p>See <a href="https://example.org">this</a>, <i>that</i> and <span style="color:red">the other</span>.<br/>Next line.</p>'
    );
    expect(containsHtml(markdown)).toBe(false);
    expect(markdown).toContain('[this](https://example.org)');
    expect(markdown).toContain('*that*');
    expect(markdown).toContain('the other');
  });
});
