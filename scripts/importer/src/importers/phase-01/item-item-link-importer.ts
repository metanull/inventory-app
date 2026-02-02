/**
 * Item-Item Link Importer
 *
 * Imports relationships between items from legacy relationship tables.
 * This includes object-object, object-monument, and monument-monument links.
 *
 * Legacy schema:
 * - mwnf3.objects_objects (o1_*, o2_* - object to object links)
 * - mwnf3.objects_monuments (o1_*, m1_* - object to monument links)
 * - mwnf3.monuments_monuments (m1_*, m2_* - monument to monument links)
 *
 * New schema:
 * - item_item_links (source_id, target_id, context_id, backward_compatibility)
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

export class ItemItemLinkImporter extends BaseImporter {
  private defaultContextId: string | null = null;

  getName(): string {
    return 'ItemItemLinkImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing item-item links...');

      // Get default context for MWNF links
      const defaultContextBackwardCompat = 'mwnf3:context:default';
      this.defaultContextId = await this.getEntityUuidAsync(
        defaultContextBackwardCompat,
        'context'
      );

      if (!this.defaultContextId) {
        throw new Error(
          `Default context not found (${defaultContextBackwardCompat}). Run DefaultContextImporter first.`
        );
      }

      this.logInfo(`Found default context: ${this.defaultContextId}`);

      // Import object-object links
      await this.importObjectObjectLinks(result);

      // Import object-monument links
      await this.importObjectMonumentLinks(result);

      // Import monument-monument links
      await this.importMonumentMonumentLinks(result);

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

  private getObjectBackwardCompat(
    projectId: string,
    countryId: string,
    museumId: string,
    number: number
  ): string {
    return `mwnf3:object:${projectId}:${countryId}:${museumId}:${number}`;
  }

  private getMonumentBackwardCompat(
    projectId: string,
    countryId: string,
    institutionId: string,
    number: number
  ): string {
    return `mwnf3:monument:${projectId}:${countryId}:${institutionId}:${number}`;
  }

  private async importObjectObjectLink(link: LegacyObjectObject): Promise<boolean> {
    const backwardCompat = this.getObjectObjectBackwardCompat(link);

    // Check if already imported
    if (this.entityExists(backwardCompat, 'item_item_link')) {
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
      context_id: this.defaultContextId!,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    this.registerEntity(backwardCompat, backwardCompat, 'item_item_link');

    return true;
  }

  private async importObjectMonumentLink(link: LegacyObjectMonument): Promise<boolean> {
    const backwardCompat = this.getObjectMonumentBackwardCompat(link);

    // Check if already imported
    if (this.entityExists(backwardCompat, 'item_item_link')) {
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
      context_id: this.defaultContextId!,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    this.registerEntity(backwardCompat, backwardCompat, 'item_item_link');

    return true;
  }

  private async importMonumentMonumentLink(link: LegacyMonumentMonument): Promise<boolean> {
    const backwardCompat = this.getMonumentMonumentBackwardCompat(link);

    // Check if already imported
    if (this.entityExists(backwardCompat, 'item_item_link')) {
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
      context_id: this.defaultContextId!,
      backward_compatibility: backwardCompat,
    };

    await this.context.strategy.writeItemItemLink(linkData);
    this.registerEntity(backwardCompat, backwardCompat, 'item_item_link');

    return true;
  }
}
