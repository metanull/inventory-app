/**
 * `stripHtml` returns the plain text of a value that may carry HTML, Markdown
 * or both. Its one caller is the tag helper: a tag name is an identifier and a
 * label, so markup in it is never wanted.
 *
 * It used to run the value through fourteen chained `.replace()` calls. Two of
 * the cases below are what that produced, run before this was rewritten:
 *
 *   'snake_case_name'    -> 'snakecasename'
 *   'Inventory 1998---A' -> 'Inventory 1998A'
 *
 * The rest of the chain happened to be right on the values tried, which is the
 * problem with it: nothing about `.replace(/_(.+?)_/g, '$1')` says which of the
 * two it will be for a value nobody has tried yet.
 */

import { describe, it, expect } from 'vitest';
import { stripHtml } from '../../src/utils/html-to-markdown.js';

describe('stripping markup from a value', () => {
  it('returns the text of an HTML fragment', () => {
    expect(stripHtml('<i>Fantasy in Egyptian Gallery</i>')).toBe('Fantasy in Egyptian Gallery');
    expect(stripHtml('<p>Ceramic <b>tile</b> panel</p>')).toBe('Ceramic tile panel');
  });

  it('returns the text of a Markdown value', () => {
    expect(stripHtml('**Bold** and *italic*')).toBe('Bold and italic');
    expect(stripHtml('# Heading')).toBe('Heading');
    expect(stripHtml('[Museum](https://example.org)')).toBe('Museum');
  });

  it('leaves a plain value alone', () => {
    expect(stripHtml('Ceramic tile panel')).toBe('Ceramic tile panel');
  });

  it('keeps an unpaired marker, which is a character and not formatting', () => {
    expect(stripHtml('**bold')).toBe('**bold');
    expect(stripHtml('*a* and *b*')).toBe('a and b');
  });

  it('keeps an underscore inside a word', () => {
    // Was 'snakecasename': `_(.+?)_ → $1` ate the middle of any identifier
    // carrying two underscores.
    expect(stripHtml('snake_case_name')).toBe('snake_case_name');
  });

  it('keeps a dash run that is not a horizontal rule', () => {
    // Was 'Inventory 1998A': `---` was removed wherever it appeared, including
    // inside an inventory number.
    expect(stripHtml('Room #3')).toBe('Room #3');
    expect(stripHtml('Inventory 1998---A')).toBe('Inventory 1998---A');
  });

  it('reads markup nested inside markup', () => {
    expect(stripHtml('<p>A <b>very <i>old</i></b> jar</p>')).toBe('A very old jar');
  });

  it('collapses the whitespace a block structure leaves behind', () => {
    expect(stripHtml('<ul><li>One</li><li>Two</li></ul>')).toBe('One Two');
    expect(stripHtml('<p>One</p><p>Two</p>')).toBe('One Two');
  });

  it('returns an empty string for nothing at all', () => {
    expect(stripHtml(null)).toBe('');
    expect(stripHtml(undefined)).toBe('');
    expect(stripHtml('')).toBe('');
    expect(stripHtml('<p></p>')).toBe('');
  });
});
