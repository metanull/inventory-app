import { describe, expect, it } from 'vitest';

import {
  transformHcrEvent,
  transformHcrEventTranslation,
} from '../../src/domain/transformers/timeline-transformer.js';
import type { LegacyHcrEvent } from '../../src/domain/types/index.js';

const hcrEvent = (overrides: Partial<LegacyHcrEvent> = {}): LegacyHcrEvent => ({
  hcr_id: 101,
  lang_id: 'en',
  name: 'Portugal Renaissance',
  description: 'Baroque art period',
  datedesc_ah: null,
  datedesc_ad: '1500-1800',
  ...overrides,
});

describe('transformHcrEvent', () => {
  it('derives the plain mwnf3 backward_compatibility key', () => {
    const result = transformHcrEvent({
      hcr_id: 101,
      country_id: 'pt',
      name: 'Portugal 1500-1800',
      from_ad: 1500,
      to_ad: 1800,
      from_ah: null,
      to_ah: null,
    });

    expect(result.backwardCompatibility).toBe('mwnf3:hcr:101');
  });
});

describe('transformHcrEventTranslation', () => {
  it('defaults to the mwnf3 hcr_events backward_compatibility key', () => {
    const result = transformHcrEventTranslation(hcrEvent());
    expect(result.data.backward_compatibility).toBe('mwnf3:hcr_events:101:en');
  });

  it('keeps the mwnf3 key unchanged when called without a prefix (Step 1 call site)', () => {
    const result = transformHcrEventTranslation(hcrEvent({ hcr_id: 202, lang_id: 'fr' }));
    expect(result.data.backward_compatibility).toBe('mwnf3:hcr_events:202:fr');
  });

  it('namespaces the BAR translation under its own prefix for the same hcr_id/lang', () => {
    const legacy = hcrEvent({ hcr_id: 101, lang_id: 'en' });

    const mwnf3Translation = transformHcrEventTranslation(legacy);
    const barTranslation = transformHcrEventTranslation(legacy, 'mwnf3:hcr_events:bar');

    // Same source row, same language — but the keys (and therefore the
    // deterministic ids SqlStrategy derives from them) must differ, otherwise
    // the BAR insert collides with the mwnf3 event's translation and is
    // silently dropped as a duplicate primary key.
    expect(mwnf3Translation.data.backward_compatibility).toBe('mwnf3:hcr_events:101:en');
    expect(barTranslation.data.backward_compatibility).toBe('mwnf3:hcr_events:bar:101:en');
    expect(barTranslation.data.backward_compatibility).not.toBe(
      mwnf3Translation.data.backward_compatibility
    );
  });

  it('preserves the row content regardless of the prefix used', () => {
    const legacy = hcrEvent({ name: 'Iberia Baroque', description: 'A description' });
    const barTranslation = transformHcrEventTranslation(legacy, 'mwnf3:hcr_events:bar');

    expect(barTranslation.data.name).toBe('Iberia Baroque');
    expect(barTranslation.data.description).toBe('A description');
  });
});
