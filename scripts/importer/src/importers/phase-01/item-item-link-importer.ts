/**
 * Item-Item Link Importer
 *
 * Imports relationships between items from legacy relationship tables.
 * This includes object-object, object-monument, monument-monument,
 * and monument-object links.
 *
 * Also imports justification translations from:
 * - mwnf3.objects_objects_justification
 * - mwnf3.objects_monuments_justification
 * - mwnf3.monuments_monuments_justification
 * - mwnf3.monuments_objects_justification
 *
 * Legacy schema:
 * - mwnf3.objects_objects (o1_*, o2_* - object to object links)
 * - mwnf3.objects_monuments (o1_*, m1_* - object to monument links)
 * - mwnf3.monuments_monuments (m1_*, m2_* - monument to monument links)
 * - mwnf3.monuments_objects (m1_*, o1_* - monument to object links)
 *
 * New schema:
 * - item_item_links (source_id, target_id, context_id, backward_compatibility)
 * - item_item_link_translations (item_item_link_id, language_id, description, reciprocal_description)
 *
 * Dependencies:
 * - ObjectImporter (must run first to create object items)
 * - MonumentImporter (must run first to create monument items)
 * - DefaultContextImporter (for default context)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult, ItemItemLinkData } from '../../core/types.js';

/**
 * Legacy object-object relationship
 */
interface LegacyObjectObject {
  id: number;
  o1_project_id: string;
  o1_country_id: string;
  o1_museum_id: string;
  o1_number: number;
  o2_project_id: string;
  o2_country_id: string;
  o2_museum_id: string;
  o2_number: number;
}

/**
 * Legacy object-monument relationship
 */
interface LegacyObjectMonument {
  id: number;
  o1_project_id: string;
  o1_country_id: string;
  o1_museum_id: string;
  o1_number: number;
  m1_project_id: string;
  m1_country_id: string;
  m1_institution_id: string;
  m1_number: number;
}

/**
 * Legacy monument-monument relationship
 */
interface LegacyMonumentMonument {
  id: number;
  m1_project_id: string;
  m1_country_id: string;
  m1_institution_id: string;
  m1_number: number;
  m2_project_id: string;
  m2_country_id: string;
  m2_institution_id: string;
  m2_number: number;
}

/**
 * Legacy monument-object relationship
 */
interface LegacyMonumentObject {
  id: number;
  m1_project_id: string;
  m1_country_id: string;
  m1_institution_id: string;
  m1_number: number;
  o1_project_id: string;
  o1_country_id: string;
  o1_museum_id: string;
  o1_number: number;
}

/**
 * Legacy justification text for item-item links
 */
interface LegacyJustification {
  relation_id: number;
  lang_id: string;
  justification: string | null;
}

export class ItemItemLinkImporter extends BaseImporter {
  private defaultContextId!: string;

  getName(): string {
    return 'ItemItemLinkImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing item-item links...');

      // Get default context for MWNF links
      // First try tracker metadata (set by DefaultContextImporter)
      let defaultContextId = this.context.tracker.getMetadata('default_context_id');

      if (!defaultContextId) {
        // Fallback: look up by backward compatibility
        const defaultContextBackwardCompat = '__default_context__';
        defaultContextId = await this.getEntityUuidAsync(defaultContextBackwardCompat, 'context');
      }

      if (!defaultContextId) {
        throw new Error('Default context not found. Run DefaultContextImporter first.');
      }

      this.defaultContextId = defaultContextId;
      this.logInfo(`Found default context: ${this.defaultContextId}`);

      // Import object-object links
      await this.importObjectObjectLinks(result);

      // Import object-monument links
      await this.importObjectMonumentLinks(result);

      // Import monument-monument links
      await this.importMonumentMonumentLinks(result);

      // Import monument-object links (reverse direction)
      await this.importMonumentObjectLinks(result);

      // Import justification translations for all link types
      await this.importJustifications(result);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import item-item links: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importObjectObjectLinks(result: ImportResult): Promise<void> {
    this.logInfo('Importing object-object links...');

    const links = await this.context.legacyDb.query<LegacyObjectObject>(
      `SELECT id, o1_project_id, o1_country_id, o1_museum_id, o1_number,
              o2_project_id, o2_country_id, o2_museum_id, o2_number
       FROM mwnf3.objects_objects
       ORDER BY id`
    );

    this.logInfo(`Found ${links.length} object-object links`);

    for (const link of links) {
      try {
        const imported = await this.importObjectObjectLink(link);
        if (imported) {
          result.imported++;
          this.showProgress();
        } else {
          result.skipped++;
          this.showSkipped();
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const backwardCompat = this.getObjectObjectBackwardCompat(link);
        result.errors.push(`${backwardCompat}: ${message}`);
        this.logError(`ObjectObject Link ${backwardCompat}`, message);
        this.showError();
      }
    }
  }

  private async importObjectMonumentLinks(result: ImportResult): Promise<void> {
    this.logInfo('Importing object-monument links...');

    const links = await this.context.legacyDb.query<LegacyObjectMonument>(
      `SELECT id, o1_project_id, o1_country_id, o1_museum_id, o1_number,
              m1_project_id, m1_country_id, m1_institution_id, m1_number
       FROM mwnf3.objects_monuments
       ORDER BY id`
    );

    this.logInfo(`Found ${links.length} object-monument links`);

    for (const link of links) {
      try {
        const imported = await this.importObjectMonumentLink(link);
        if (imported) {
          result.imported++;
          this.showProgress();
        } else {
          result.skipped++;
          this.showSkipped();
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const backwardCompat = this.getObjectMonumentBackwardCompat(link);
        result.errors.push(`${backwardCompat}: ${message}`);
        this.logError(`ObjectMonument Link ${backwardCompat}`, message);
        this.showError();
      }
    }
  }

  private async importMonumentMonumentLinks(result: ImportResult): Promise<void> {
    this.logInfo('Importing monument-monument links...');

    const links = await this.context.legacyDb.query<LegacyMonumentMonument>(
      `SELECT id, m1_project_id, m1_country_id, m1_institution_id, m1_number,
              m2_project_id, m2_country_id, m2_institution_id, m2_number
       FROM mwnf3.monuments_monuments
       ORDER BY id`
    );

    this.logInfo(`Found ${links.length} monument-monument links`);

    for (const link of links) {
      try {
        const imported = await this.importMonumentMonumentLink(link);
        if (imported) {
          result.imported++;
          this.showProgress();
        } else {
          result.skipped++;
          this.showSkipped();
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const backwardCompat = this.getMonumentMonumentBackwardCompat(link);
        result.errors.push(`${backwardCompat}: ${message}`);
        this.logError(`MonumentMonument Link ${backwardCompat}`, message);
        this.showError();
      }
    }
  }

  private getObjectObjectBackwardCompat(link: LegacyObjectObject): string {
    return `mwnf3:link:object_object:${link.o1_project_id}:${link.o1_country_id}:${link.o1_museum_id}:${link.o1_number}:${link.o2_project_id}:${link.o2_country_id}:${link.o2_museum_id}:${link.o2_number}`;
  }

  private getObjectMonumentBackwardCompat(link: LegacyObjectMonument): string {
    return `mwnf3:link:object_monument:${link.o1_project_id}:${link.o1_country_id}:${link.o1_museum_id}:${link.o1_number}:${link.m1_project_id}:${link.m1_country_id}:${link.m1_institution_id}:${link.m1_number}`;
  }

  private getMonumentMonumentBackwardCompat(link: LegacyMonumentMonument): string {
    return `mwnf3:link:monument_monument:${link.m1_project_id}:${link.m1_country_id}:${link.m1_institution_id}:${link.m1_number}:${link.m2_project_id}:${link.m2_country_id}:${link.m2_institution_id}:${link.m2_number}`;
  }

  private getMonumentObjectBackwardCompat(link: LegacyMonumentObject): string {
    return `mwnf3:link:monument_object:${link.m1_project_id}:${link.m1_country_id}:${link.m1_institution_id}:${link.m1_number}:${link.o1_project_id}:${link.o1_country_id}:${link.o1_museum_id}:${link.o1_number}`;
  }

  private getObjectBackwardCompat(
    projectId: string,
    countryId: string,
    museumId: string,
    number: number
  ): string {
    return `mwnf3:objects:${projectId}:${countryId}:${museumId}:${number}`;
  }

  private getMonumentBackwardCompat(
    projectId: string,
    countryId: string,
    institutionId: string,
    number: number
  ): string {
    return `mwnf3:monuments:${projectId}:${countryId}:${institutionId}:${number}`;
  }

  private async importObjectObjectLink(link: LegacyObjectObject): Promise<boolean> {
    const backwardCompat = this.getObjectObjectBackwardCompat(link);

    // Check if already imported
    if (await this.entityExistsAsync(backwardCompat, 'item_item_link')) {
      return false;
    }

    // Get source object item
    const sourceBackwardCompat = this.getObjectBackwardCompat(
      link.o1_project_id,
      link.o1_country_id,
      link.o1_museum_id,
      link.o1_number
    );
    const sourceId = await this.getEntityUuidAsync(sourceBackwardCompat, 'item');
    if (!sourceId) {
      throw new Error(`Source object not found: ${sourceBackwardCompat}`);
    }

    // Get target object item
    const targetBackwardCompat = this.getObjectBackwardCompat(
      link.o2_project_id,
      link.o2_country_id,
      link.o2_museum_id,
      link.o2_number
    );
    const targetId = await this.getEntityUuidAsync(targetBackwardCompat, 'item');
    if (!targetId) {
      throw new Error(`Target object not found: ${targetBackwardCompat}`);
    }

    // Collect sample
    this.collectSample('object_object_link', link as unknown as Record<string, unknown>, 'success');

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import object-object link: ${backwardCompat}`
      );
      this.registerEntity(backwardCompat, `sample-${backwardCompat}`, 'item_item_link');
      return true;
    }

    // Create the link
    const linkData: ItemItemLinkData = {
      source_id: sourceId,
      target_id: targetId,
      context_id: this.defaultContextId,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    // writeItemItemLink already registers in tracker via backward_compatibility

    return true;
  }

  private async importObjectMonumentLink(link: LegacyObjectMonument): Promise<boolean> {
    const backwardCompat = this.getObjectMonumentBackwardCompat(link);

    // Check if already imported
    if (await this.entityExistsAsync(backwardCompat, 'item_item_link')) {
      return false;
    }

    // Get source object item
    const sourceBackwardCompat = this.getObjectBackwardCompat(
      link.o1_project_id,
      link.o1_country_id,
      link.o1_museum_id,
      link.o1_number
    );
    const sourceId = await this.getEntityUuidAsync(sourceBackwardCompat, 'item');
    if (!sourceId) {
      throw new Error(`Source object not found: ${sourceBackwardCompat}`);
    }

    // Get target monument item
    const targetBackwardCompat = this.getMonumentBackwardCompat(
      link.m1_project_id,
      link.m1_country_id,
      link.m1_institution_id,
      link.m1_number
    );
    const targetId = await this.getEntityUuidAsync(targetBackwardCompat, 'item');
    if (!targetId) {
      throw new Error(`Target monument not found: ${targetBackwardCompat}`);
    }

    // Collect sample
    this.collectSample(
      'object_monument_link',
      link as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import object-monument link: ${backwardCompat}`
      );
      this.registerEntity(backwardCompat, `sample-${backwardCompat}`, 'item_item_link');
      return true;
    }

    // Create the link
    const linkData: ItemItemLinkData = {
      source_id: sourceId,
      target_id: targetId,
      context_id: this.defaultContextId,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    // writeItemItemLink already registers in tracker via backward_compatibility

    return true;
  }

  private async importMonumentMonumentLink(link: LegacyMonumentMonument): Promise<boolean> {
    const backwardCompat = this.getMonumentMonumentBackwardCompat(link);

    // Check if already imported
    if (await this.entityExistsAsync(backwardCompat, 'item_item_link')) {
      return false;
    }

    // Get source monument item
    const sourceBackwardCompat = this.getMonumentBackwardCompat(
      link.m1_project_id,
      link.m1_country_id,
      link.m1_institution_id,
      link.m1_number
    );
    const sourceId = await this.getEntityUuidAsync(sourceBackwardCompat, 'item');
    if (!sourceId) {
      throw new Error(`Source monument not found: ${sourceBackwardCompat}`);
    }

    // Get target monument item
    const targetBackwardCompat = this.getMonumentBackwardCompat(
      link.m2_project_id,
      link.m2_country_id,
      link.m2_institution_id,
      link.m2_number
    );
    const targetId = await this.getEntityUuidAsync(targetBackwardCompat, 'item');
    if (!targetId) {
      throw new Error(`Target monument not found: ${targetBackwardCompat}`);
    }

    // Collect sample
    this.collectSample(
      'monument_monument_link',
      link as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import monument-monument link: ${backwardCompat}`
      );
      this.registerEntity(backwardCompat, `sample-${backwardCompat}`, 'item_item_link');
      return true;
    }

    // Create the link
    const linkData: ItemItemLinkData = {
      source_id: sourceId,
      target_id: targetId,
      context_id: this.defaultContextId,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    // writeItemItemLink already registers in tracker via backward_compatibility

    return true;
  }

  // =========================================================================
  // Monument-Object Links (Story 16.4)
  // =========================================================================

  private async importMonumentObjectLinks(result: ImportResult): Promise<void> {
    this.logInfo('Importing monument-object links...');

    const links = await this.context.legacyDb.query<LegacyMonumentObject>(
      `SELECT id, m1_project_id, m1_country_id, m1_institution_id, m1_number,
              o1_project_id, o1_country_id, o1_museum_id, o1_number
       FROM mwnf3.monuments_objects
       ORDER BY id`
    );

    this.logInfo(`Found ${links.length} monument-object links`);

    for (const link of links) {
      try {
        const imported = await this.importMonumentObjectLink(link);
        if (imported) {
          result.imported++;
          this.showProgress();
        } else {
          result.skipped++;
          this.showSkipped();
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const backwardCompat = this.getMonumentObjectBackwardCompat(link);
        result.errors.push(`${backwardCompat}: ${message}`);
        this.logError(`MonumentObject Link ${backwardCompat}`, message);
        this.showError();
      }
    }
  }

  private async importMonumentObjectLink(link: LegacyMonumentObject): Promise<boolean> {
    const backwardCompat = this.getMonumentObjectBackwardCompat(link);

    // Check if already imported
    if (await this.entityExistsAsync(backwardCompat, 'item_item_link')) {
      return false;
    }

    // Get source monument item
    const sourceBackwardCompat = this.getMonumentBackwardCompat(
      link.m1_project_id,
      link.m1_country_id,
      link.m1_institution_id,
      link.m1_number
    );
    const sourceId = await this.getEntityUuidAsync(sourceBackwardCompat, 'item');
    if (!sourceId) {
      throw new Error(`Source monument not found: ${sourceBackwardCompat}`);
    }

    // Get target object item
    const targetBackwardCompat = this.getObjectBackwardCompat(
      link.o1_project_id,
      link.o1_country_id,
      link.o1_museum_id,
      link.o1_number
    );
    const targetId = await this.getEntityUuidAsync(targetBackwardCompat, 'item');
    if (!targetId) {
      throw new Error(`Target object not found: ${targetBackwardCompat}`);
    }

    // Collect sample
    this.collectSample(
      'monument_object_link',
      link as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import monument-object link: ${backwardCompat}`
      );
      this.registerEntity(backwardCompat, `sample-${backwardCompat}`, 'item_item_link');
      return true;
    }

    // Create the link
    const linkData: ItemItemLinkData = {
      source_id: sourceId,
      target_id: targetId,
      context_id: this.defaultContextId,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    // writeItemItemLink already registers in tracker via backward_compatibility

    return true;
  }

  // =========================================================================
  // Justification Translations (Story 16.3)
  // =========================================================================

  private async importJustifications(result: ImportResult): Promise<void> {
    const tables: Array<{
      table: string;
      linkTable: string;
    }> = [
      {
        table: 'mwnf3.objects_objects_justification',
        linkTable: 'mwnf3.objects_objects',
      },
      {
        table: 'mwnf3.objects_monuments_justification',
        linkTable: 'mwnf3.objects_monuments',
      },
      {
        table: 'mwnf3.monuments_monuments_justification',
        linkTable: 'mwnf3.monuments_monuments',
      },
      {
        table: 'mwnf3.monuments_objects_justification',
        linkTable: 'mwnf3.monuments_objects',
      },
    ];

    for (const { table, linkTable } of tables) {
      await this.importJustificationTable(table, linkTable, result);
    }
  }

  private async importJustificationTable(
    table: string,
    linkTable: string,
    result: ImportResult
  ): Promise<void> {
    this.logInfo(`Importing justifications from ${table}...`);

    const justifications = await this.context.legacyDb.query<LegacyJustification>(
      `SELECT j.relation_id, j.lang_id, j.justification
       FROM ${table} j
       INNER JOIN ${linkTable} l ON l.id = j.relation_id
       ORDER BY j.relation_id, j.lang_id`
    );

    this.logInfo(`Found ${justifications.length} justification rows in ${table}`);

    // Build a map of relation_id → backward_compatibility by querying each link table
    // The link tables have an `id` that matches the justification's relation_id
    const linkBcMap = await this.buildLinkBackwardCompatMap(linkTable);

    let justificationsImported = 0;
    for (const justification of justifications) {
      try {
        if (!justification.justification || !justification.justification.trim()) {
          continue; // Skip empty justifications
        }

        const linkBackwardCompat = linkBcMap.get(justification.relation_id);
        if (!linkBackwardCompat) {
          this.logWarning(
            `No backward compatibility found for ${table} relation_id=${justification.relation_id}`
          );
          result.warnings!.push(
            `No backward compatibility for ${table} relation_id=${justification.relation_id}`
          );
          continue;
        }

        // Find the item_item_link UUID via tracker/DB
        const linkId = await this.getEntityUuidAsync(linkBackwardCompat, 'item_item_link');
        if (!linkId) {
          this.logWarning(
            `Item-item link not found for ${linkBackwardCompat}, skipping justification`
          );
          result.warnings!.push(`Item-item link not found for ${linkBackwardCompat}`);
          continue;
        }

        // Resolve language
        const languageId = await this.getLanguageIdByLegacyCodeAsync(justification.lang_id);
        if (!languageId) {
          this.logWarning(
            `Unknown language code '${justification.lang_id}' in ${table} relation_id=${justification.relation_id}, skipping`
          );
          result.warnings!.push(
            `Unknown language '${justification.lang_id}' in ${table} relation_id=${justification.relation_id}`
          );
          continue;
        }

        const justificationBackwardCompat = `${linkBackwardCompat}:justification:${justification.lang_id}`;

        if (
          await this.entityExistsAsync(justificationBackwardCompat, 'item_item_link_translation')
        ) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (this.isDryRun || this.isSampleOnlyMode) {
          justificationsImported++;
          continue;
        }

        await this.context.strategy.writeItemItemLinkTranslation({
          item_item_link_id: linkId,
          language_id: languageId,
          description: justification.justification.trim(),
          reciprocal_description: null,
          backward_compatibility: justificationBackwardCompat,
        });

        justificationsImported++;
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        this.logWarning(
          `Failed justification ${table} relation_id=${justification.relation_id} lang=${justification.lang_id}: ${message}`
        );
        result.warnings!.push(
          `Failed justification ${table} relation_id=${justification.relation_id}: ${message}`
        );
      }
    }

    this.logInfo(`Imported ${justificationsImported} justifications from ${table}`);
  }

  private async buildLinkBackwardCompatMap(linkTable: string): Promise<Map<number, string>> {
    const map = new Map<number, string>();

    // Determine columns based on link table type
    if (linkTable === 'mwnf3.objects_objects') {
      const rows = await this.context.legacyDb.query<LegacyObjectObject>(
        `SELECT id, o1_project_id, o1_country_id, o1_museum_id, o1_number,
                o2_project_id, o2_country_id, o2_museum_id, o2_number
         FROM ${linkTable}`
      );
      for (const row of rows) {
        map.set(row.id, this.getObjectObjectBackwardCompat(row));
      }
    } else if (linkTable === 'mwnf3.objects_monuments') {
      const rows = await this.context.legacyDb.query<LegacyObjectMonument>(
        `SELECT id, o1_project_id, o1_country_id, o1_museum_id, o1_number,
                m1_project_id, m1_country_id, m1_institution_id, m1_number
         FROM ${linkTable}`
      );
      for (const row of rows) {
        map.set(row.id, this.getObjectMonumentBackwardCompat(row));
      }
    } else if (linkTable === 'mwnf3.monuments_monuments') {
      const rows = await this.context.legacyDb.query<LegacyMonumentMonument>(
        `SELECT id, m1_project_id, m1_country_id, m1_institution_id, m1_number,
                m2_project_id, m2_country_id, m2_institution_id, m2_number
         FROM ${linkTable}`
      );
      for (const row of rows) {
        map.set(row.id, this.getMonumentMonumentBackwardCompat(row));
      }
    } else if (linkTable === 'mwnf3.monuments_objects') {
      const rows = await this.context.legacyDb.query<LegacyMonumentObject>(
        `SELECT id, m1_project_id, m1_country_id, m1_institution_id, m1_number,
                o1_project_id, o1_country_id, o1_museum_id, o1_number
         FROM ${linkTable}`
      );
      for (const row of rows) {
        map.set(row.id, this.getMonumentObjectBackwardCompat(row));
      }
    }

    return map;
  }
}
