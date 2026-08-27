import type { ExportResult } from '../core/types.js'
import { BaseExporter } from './base-exporter.js'

interface TimelineRow {
  id: string
  internal_name: string
  backward_compatibility: string | null
  country_id: string | null
}

interface TimelineEventRow {
  id: string
  timeline_id: string
  year_from: number | null
  year_to: number | null
  year_from_ah: number | null
  year_to_ah: number | null
  date_from: string | null
  date_to: string | null
  display_order: number
}

interface TimelineEventTranslationRow {
  timeline_event_id: string
  language_id: string
  name: string | null
  description: string | null
  date_from_description: string | null
  date_to_description: string | null
  date_from_ah_description: string | null
}

interface TimelineEventImageRow {
  timeline_event_id: string
  path: string
  alt_text: string | null
  display_order: number
}

interface TimelineEventItemRow {
  timeline_event_id: string
  item_id: string
  display_order: number
}

/**
 * The worldwide chronology every DXA gallery shows.
 *
 * The gallery timeline is NOT gallery-specific: legacy serves it from
 * `mwnf3.hcr`, keyed by country and independent of any project
 * (app/MWNF/SQL/mwnf3/Events.blade.php), which is why the live amulets site
 * answers `/events/countries` with the full worldwide list even though its
 * `hasCountryBasedTimeline` flag is false. Every gallery package therefore
 * carries the same 18 per-country timelines (~1,075 events — small).
 *
 * The BAR family (`mwnf3:hcr:bar:country:*`) is a separate Baroque Art
 * chronology and is deliberately not included.
 */
const GLOBAL_TIMELINE_PREFIX = 'mwnf3:hcr:country:%'

export class TimelineExporter extends BaseExporter {
  getName(): string {
    return 'Timelines'
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting timelines.json / timeline_events.json...')

    const timelines = await this.db.query<TimelineRow>(
      `SELECT id, internal_name, backward_compatibility, country_id
       FROM timelines
       WHERE backward_compatibility LIKE ?
       ORDER BY country_id, internal_name`,
      [GLOBAL_TIMELINE_PREFIX]
    )

    if (timelines.length === 0) {
      await this.writeJson('timelines.json', [])
      await this.writeJson('timeline_events.json', [])
      this.logger.warning('timelines.json (0 — no global country timelines found)')
      return { file: 'timelines.json', count: 0 }
    }

    const timelineIds = timelines.map(t => t.id)
    const events = await this.db.query<TimelineEventRow>(
      `SELECT id, timeline_id, year_from, year_to, year_from_ah, year_to_ah,
              date_from, date_to, display_order
       FROM timeline_events
       WHERE timeline_id IN (${this.placeholders(timelineIds.length)})
       ORDER BY timeline_id, display_order`,
      timelineIds
    )

    const timelineOutput = timelines.map(timeline => ({
      id: timeline.id,
      backward_compatibility: timeline.backward_compatibility,
      internal_name: timeline.internal_name,
      country_id: timeline.country_id,
    }))

    if (events.length === 0) {
      await this.writeJson('timelines.json', timelineOutput)
      await this.writeJson('timeline_events.json', [])
      this.logger.success(`timelines.json (${timelines.length} timelines, 0 events)`)
      return { file: 'timelines.json', count: timelines.length }
    }

    const eventIds = events.map(e => e.id)
    const eventPh = this.placeholders(eventIds.length)
    const langCodeMap = await this.buildLangCodeMap()

    const [translations, images, itemLinks] = await Promise.all([
      this.db.query<TimelineEventTranslationRow>(
        `SELECT timeline_event_id, language_id, name, description,
                date_from_description, date_to_description, date_from_ah_description
         FROM timeline_event_translations
         WHERE timeline_event_id IN (${eventPh})`,
        eventIds
      ),
      this.db.query<TimelineEventImageRow>(
        `SELECT timeline_event_id, path, alt_text, display_order
         FROM timeline_event_images
         WHERE timeline_event_id IN (${eventPh})
         ORDER BY timeline_event_id, display_order`,
        eventIds
      ),
      // Only members: an event may name items from any project, but this site
      // can only open the ones it ships.
      this.memberItemIds.length > 0
        ? this.db.query<TimelineEventItemRow>(
            `SELECT tei.timeline_event_id, tei.item_id, tei.display_order
             FROM timeline_event_item tei
             WHERE tei.timeline_event_id IN (${eventPh})
               AND tei.item_id IN (${this.placeholders(this.memberItemIds.length)})
             ORDER BY tei.timeline_event_id, tei.display_order`,
            [...eventIds, ...this.memberItemIds]
          )
        : Promise.resolve([]),
    ])

    const byLang = new Map<string, Record<string, unknown>>()
    for (const row of translations) {
      const code = langCodeMap.get(row.language_id)
      if (!code) continue
      const bucket = byLang.get(code) ?? {}
      bucket[row.timeline_event_id] = this.stripNulls({
        name: row.name,
        description: row.description,
        date_from_description: row.date_from_description,
        date_to_description: row.date_to_description,
        date_from_ah_description: row.date_from_ah_description,
      })
      byLang.set(code, bucket)
    }
    await this.writeTranslationFiles('timeline_events', byLang)

    const imageMap = new Map<string, unknown[]>()
    for (const image of images) {
      const entry = {
        url: this.imageUrl(image.path),
        alt_text: image.alt_text,
        display_order: image.display_order,
      }
      const bucket = imageMap.get(image.timeline_event_id)
      if (bucket) bucket.push(entry)
      else imageMap.set(image.timeline_event_id, [entry])
    }

    const itemMap = new Map<string, string[]>()
    for (const link of itemLinks) {
      const bucket = itemMap.get(link.timeline_event_id)
      if (bucket) bucket.push(link.item_id)
      else itemMap.set(link.timeline_event_id, [link.item_id])
    }

    const countryByTimeline = new Map(timelines.map(t => [t.id, t.country_id]))

    const eventOutput = events.map(event => ({
      id: event.id,
      timeline_id: event.timeline_id,
      country_id: countryByTimeline.get(event.timeline_id) ?? null,
      year_from: event.year_from,
      year_to: event.year_to,
      year_from_ah: event.year_from_ah,
      year_to_ah: event.year_to_ah,
      date_from: event.date_from,
      date_to: event.date_to,
      display_order: event.display_order,
      images: imageMap.get(event.id) ?? [],
      item_ids: itemMap.get(event.id) ?? [],
    }))

    await this.writeJson('timelines.json', timelineOutput)
    await this.writeJson('timeline_events.json', eventOutput)
    this.logger.success(
      `timelines.json (${timelines.length} timelines, ${eventOutput.length} events)`
    )

    return { file: 'timelines.json', count: timelines.length }
  }
}
