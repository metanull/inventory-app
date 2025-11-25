import { describe, it, expect } from 'vitest';
import { convertHtmlToMarkdown } from '../../src/utils/HtmlToMarkdownConverter.js';

/**
 * Unit tests for HtmlToMarkdownConverter
 * Validates bidirectional conversion: HTML → Markdown and Markdown → HTML
 */
describe('HtmlToMarkdownConverter', () => {
  describe('HTML to Markdown conversion', () => {
    it('should convert <br/> and <br> tags to newlines', () => {
      // Turndown converts <br> to "  \n" (two spaces + newline for hard line break in Markdown)
      const result1 = convertHtmlToMarkdown('Line 1<br/>Line 2');
      expect(result1).toContain('Line 1');
      expect(result1).toContain('Line 2');
      expect(result1).not.toContain('<br');
      
      const result2 = convertHtmlToMarkdown('Line 1<br>Line 2');
      expect(result2).not.toContain('<br');
      
      const result3 = convertHtmlToMarkdown('Line 1<br />Line 2');
      expect(result3).not.toContain('<br');
    });

    it('should convert <i> tags to *italic*', () => {
      expect(convertHtmlToMarkdown('This is <i>italic</i> text')).toBe('This is *italic* text');
    });

    it('should convert <em> tags to *italic*', () => {
      expect(convertHtmlToMarkdown('This is <em>emphasized</em> text')).toBe('This is *emphasized* text');
    });

    it('should convert <b> and <strong> tags to **bold**', () => {
      expect(convertHtmlToMarkdown('This is <b>bold</b> text')).toBe('This is **bold** text');
      expect(convertHtmlToMarkdown('This is <strong>strong</strong> text')).toBe('This is **strong** text');
    });

    it('should convert <p> tags to paragraphs', () => {
      expect(convertHtmlToMarkdown('<p>Paragraph 1</p><p>Paragraph 2</p>')).toBe('Paragraph 1\n\nParagraph 2');
    });

    it('should convert <ul> and <li> to unordered lists', () => {
      const html = '<ul><li>Item 1</li><li>Item 2</li></ul>';
      const result = convertHtmlToMarkdown(html);
      expect(result).toContain('Item 1');
      expect(result).toContain('Item 2');
      expect(result).toMatch(/[-*]\s+Item 1/);
      expect(result).toMatch(/[-*]\s+Item 2/);
    });

    it('should convert <a> tags to [text](url)', () => {
      expect(convertHtmlToMarkdown('<a href="https://example.com">Link</a>'))
        .toBe('[Link](https://example.com)');
    });

    it('should handle mixed HTML tags', () => {
      const html = 'This is <b>bold</b> and <i>italic</i> text.<br/>New line here.';
      const result = convertHtmlToMarkdown(html);
      expect(result).toContain('**bold**');
      expect(result).toContain('*italic*');
      expect(result).toContain('\n');
      expect(result).not.toContain('<br');
      expect(result).not.toContain('<b>');
      expect(result).not.toContain('<i>');
    });

    it('should handle complex real-world example from legacy database', () => {
      const html = `Polopostava ženy držící palmovou ratolest naznačuje, že jde o křesťanskou mučednici.<br/>
Atributy kalich s hostií, meč a věž v pozadí ji určují jako sv. Barboru.<br/>
Monochromní barevnost a osobitý rukopis upomínají na inspiraci holandskou a vlámskou malbou 17. století.<br/>`;
      
      const result = convertHtmlToMarkdown(html);
      
      // Turndown converts <br/> to "  \n" (hard line break in Markdown)
      const expected = `Polopostava ženy držící palmovou ratolest naznačuje, že jde o křesťanskou mučednici.  
Atributy kalich s hostií, meč a věž v pozadí ji určují jako sv. Barboru.  
Monochromní barevnost a osobitý rukopis upomínají na inspiraci holandskou a vlámskou malbou 17. století.`;
      
      expect(result).toBe(expected);
    });

    it('should return empty string for null/undefined', () => {
      expect(convertHtmlToMarkdown(null)).toBe('');
      expect(convertHtmlToMarkdown(undefined)).toBe('');
    });

    it('should return original text if no HTML tags present', () => {
      const text = 'Plain text without HTML';
      expect(convertHtmlToMarkdown(text)).toBe(text);
    });

    it('should trim whitespace', () => {
      expect(convertHtmlToMarkdown('  <b>text</b>  ')).toBe('**text**');
    });

    it('should handle malformed HTML gracefully', () => {
      const malformed = '<b>Bold without closing tag';
      const result = convertHtmlToMarkdown(malformed);
      expect(result).toBeTruthy();
      expect(typeof result).toBe('string');
    });
  });

  describe('Markdown preservation (idempotency)', () => {
    it('should preserve Markdown that is already Markdown', () => {
      const markdown = 'This is **bold** and *italic* text.\n\nNew paragraph.';
      const result = convertHtmlToMarkdown(markdown);
      // Should not alter existing Markdown
      expect(result).toBe(markdown);
    });

    it('should preserve Markdown lists', () => {
      const markdown = '- Item 1\n- Item 2\n- Item 3';
      const result = convertHtmlToMarkdown(markdown);
      expect(result).toBe(markdown);
    });

    it('should preserve Markdown links', () => {
      const markdown = '[Link text](https://example.com)';
      const result = convertHtmlToMarkdown(markdown);
      expect(result).toBe(markdown);
    });
  });

  describe('Edge cases', () => {
    it('should handle empty string', () => {
      expect(convertHtmlToMarkdown('')).toBe('');
    });

    it('should handle HTML entities', () => {
      const html = 'Test &amp; test &lt; test &gt; test';
      const result = convertHtmlToMarkdown(html);
      // Turndown should handle entities
      expect(result).toBeTruthy();
    });

    it('should handle nested tags', () => {
      const html = '<p><b>Bold <i>and italic</i> text</b></p>';
      const result = convertHtmlToMarkdown(html);
      expect(result).toContain('**');
      expect(result).toContain('*');
      expect(result).not.toContain('<');
    });

    it('should handle multiple consecutive line breaks', () => {
      const html = 'Line 1<br/><br/><br/>Line 2';
      const result = convertHtmlToMarkdown(html);
      expect(result).not.toContain('<br');
      expect(result).toContain('Line 1');
      expect(result).toContain('Line 2');
    });
  });

  describe('Real legacy database samples', () => {
    it('should convert description with mixed HTML tags', () => {
      const html = `یظهر اسم سلطان قایتبای، مالک اللوحة<br/>
، في المنبر الذي صنع للمسجد اثناء حکمه.<br/>`;
      
      const result = convertHtmlToMarkdown(html);
      const expected = `یظهر اسم سلطان قایتبای، مالک اللوحة  
، في المنبر الذي صنع للمسجد اثناء حکمه.`;
      
      expect(result).toBe(expected);
    });

    it('should convert bibliography with italic tags', () => {
      const html = 'La iluminación de los tallos de hojas en espirales que decora este <i>tugra</i>';
      const result = convertHtmlToMarkdown(html);
      const expected = 'La iluminación de los tallos de hojas en espirales que decora este *tugra*';
      
      expect(result).toBe(expected);
    });
  });

  describe('Performance and reliability', () => {
    it('should handle large text without errors', () => {
      const largeHtml = '<p>' + 'Lorem ipsum '.repeat(1000) + '</p>';
      expect(() => convertHtmlToMarkdown(largeHtml)).not.toThrow();
    });

    it('should be deterministic (same input = same output)', () => {
      const html = 'Test <b>bold</b> and <i>italic</i><br/>text';
      const result1 = convertHtmlToMarkdown(html);
      const result2 = convertHtmlToMarkdown(html);
      expect(result1).toBe(result2);
    });
  });
});
