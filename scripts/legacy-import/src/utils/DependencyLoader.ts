/**
 * Loads tracker data from previously imported entities
 * This allows importers to run independently without re-running their dependencies
 */

import { InventoryApiClient } from '../api/InventoryApiClient.js';
import { BackwardCompatibilityTracker } from './BackwardCompatibilityTracker.js';
import { Logger } from './Logger.js';

export class DependencyLoader {
  private logger: Logger;

  constructor(
    private apiClient: InventoryApiClient,
    private tracker: BackwardCompatibilityTracker
  ) {
    this.logger = new Logger();
  }

  /**
   * Load all entities of a specific type into the tracker
   */
  async loadDependency(importerKey: string): Promise<void> {
    this.logger.info(`  Loading dependency: ${importerKey}`, '⚙️');

    switch (importerKey) {
      case 'language':
        await this.loadLanguages();
        break;
      case 'language-translation':
        await this.loadLanguageTranslations();
        break;
      case 'country':
        await this.loadCountries();
        break;
      case 'country-translation':
        await this.loadCountryTranslations();
        break;
      case 'default-context':
        await this.loadDefaultContext();
        break;
      case 'project':
        await this.loadProjects();
        break;
      case 'partner':
        await this.loadPartners();
        break;
      case 'object':
        await this.loadItems('object');
        break;
      case 'monument':
        await this.loadItems('monument');
        break;
      default:
        this.logger.warning(`Unknown dependency: ${importerKey}`);
    }
  }

  /**
   * Load languages into tracker
   * Note: Languages are seeded data, not imported, so we just load from API
   */
  private async loadLanguages(): Promise<void> {
    const response = await this.apiClient.language.languageIndex();
    const languages = response.data.data;

    for (const language of languages) {
      // Languages don't have backward_compatibility, but we register them by their ID
      // This allows importers to verify language_id exists
      this.tracker.register({
        uuid: language.id,
        backwardCompatibility: `system:language:${language.id}`,
        entityType: 'context', // Use 'context' as valid entityType
        createdAt: new Date(),
      });
    }

    this.logger.info(`    Loaded ${languages.length} languages`, '✓');
  }

  /**
   * Load language translations into tracker
   */
  private async loadLanguageTranslations(): Promise<void> {
    const response = await this.apiClient.languageTranslation.languageTranslationIndex();
    const translations = response.data.data;

    for (const translation of translations) {
      if (translation.backward_compatibility) {
        this.tracker.register({
          uuid: translation.backward_compatibility, // Use backward_compat as UUID
          backwardCompatibility: translation.backward_compatibility,
          entityType: 'context', // Use 'context' as valid entityType
          createdAt: new Date(),
        });
      }
    }

    this.logger.info(`    Loaded ${translations.length} language translations`, '✓');
  }

  /**
   * Load countries into tracker
   * Note: Countries are seeded data, not imported, so we just load from API
   */
  private async loadCountries(): Promise<void> {
    const response = await this.apiClient.country.countryIndex();
    const countries = response.data.data;

    for (const country of countries) {
      // Countries don't have backward_compatibility, but we register them by their ID
      // This allows importers to verify country_id exists
      this.tracker.register({
        uuid: country.id,
        backwardCompatibility: `system:country:${country.id}`,
        entityType: 'context', // Use 'context' as valid entityType
        createdAt: new Date(),
      });
    }

    this.logger.info(`    Loaded ${countries.length} countries`, '✓');
  }

  /**
   * Load country translations into tracker
   */
  private async loadCountryTranslations(): Promise<void> {
    const response = await this.apiClient.countryTranslation.countryTranslationIndex();
    const translations = response.data.data;

    for (const translation of translations) {
      if (translation.backward_compatibility) {
        this.tracker.register({
          uuid: translation.backward_compatibility, // Use backward_compat as UUID
          backwardCompatibility: translation.backward_compatibility,
          entityType: 'context', // Use 'context' as valid entityType
          createdAt: new Date(),
        });
      }
    }

    this.logger.info(`    Loaded ${translations.length} country translations`, '✓');
  }

  /**
   * Load default context into tracker
   */
  private async loadDefaultContext(): Promise<void> {
    try {
      const response = await this.apiClient.context.contextGetDefault();
      const defaultContext = response.data.data;

      if (defaultContext && defaultContext.backward_compatibility) {
        this.tracker.register({
          uuid: defaultContext.id,
          backwardCompatibility: defaultContext.backward_compatibility,
          entityType: 'context',
          createdAt: new Date(),
        });
        this.logger.info(`    Loaded default context: ${defaultContext.internal_name}`, '✓');
      }
    } catch (error) {
      // If 404, default context doesn't exist yet - that's okay
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number } };
        if (axiosError.response?.status === 404) {
          this.logger.info(`    No default context found (not yet created)`, '⚠️');
          return;
        }
      }
      throw error; // Re-throw other errors
    }
  }

  /**
   * Load projects (contexts, projects, collections) into tracker
   */
  private async loadProjects(): Promise<void> {
    // Load Contexts
    let contextPage = 1;
    const contextsPerPage = 100;
    let hasMoreContexts = true;

    while (hasMoreContexts) {
      const response = await this.apiClient.context.contextIndex(
        contextPage,
        contextsPerPage,
        undefined // include parameter
      );
      const contexts = response.data.data;

      for (const context of contexts) {
        if (context.backward_compatibility) {
          this.tracker.register({
            uuid: context.id,
            backwardCompatibility: context.backward_compatibility,
            entityType: 'context',
            createdAt: new Date(),
          });
        }
      }

      hasMoreContexts = contexts.length === contextsPerPage;
      contextPage++;
    }

    // Load Collections (load all, then filter by context)
    let collectionPage = 1;
    const collectionsPerPage = 100;
    let hasMoreCollections = true;

    while (hasMoreCollections) {
      const response = await this.apiClient.collection.collectionIndex(
        collectionPage,
        collectionsPerPage,
        undefined // include
      );
      const collections = response.data.data;

      for (const collection of collections) {
        if (collection.backward_compatibility) {
          this.tracker.register({
            uuid: collection.id,
            backwardCompatibility: collection.backward_compatibility,
            entityType: 'collection',
            createdAt: new Date(),
          });
        }
      }

      hasMoreCollections = collections.length === collectionsPerPage;
      collectionPage++;
    }

    // Load Projects
    let projectPage = 1;
    const projectsPerPage = 100;
    let hasMoreProjects = true;

    while (hasMoreProjects) {
      const response = await this.apiClient.project.projectIndex(
        projectPage,
        projectsPerPage,
        undefined // include
      );
      const projects = response.data.data;

      for (const project of projects) {
        if (project.backward_compatibility) {
          this.tracker.register({
            uuid: project.id,
            backwardCompatibility: project.backward_compatibility,
            entityType: 'project',
            createdAt: new Date(),
          });
        }
      }

      hasMoreProjects = projects.length === projectsPerPage;
      projectPage++;
    }

    this.logger.info(
      `    Loaded ${this.tracker.getByType('context').length} contexts, ${this.tracker.getByType('collection').length} collections, ${this.tracker.getByType('project').length} projects`,
      '✓'
    );
  }

  /**
   * Load partners into tracker
   */
  private async loadPartners(): Promise<void> {
    let page = 1;
    const perPage = 100;
    let hasMore = true;

    while (hasMore) {
      const response = await this.apiClient.partner.partnerIndex(
        page,
        perPage,
        undefined, // include
        undefined // contextId
      );
      const partners = response.data.data;

      for (const partner of partners) {
        if (partner.backward_compatibility) {
          this.tracker.register({
            uuid: partner.id,
            backwardCompatibility: partner.backward_compatibility,
            entityType: 'partner',
            createdAt: new Date(),
          });
        }
      }

      hasMore = partners.length === perPage;
      page++;
    }

    this.logger.info(`    Loaded ${this.tracker.getByType('partner').length} partners`, '✓');
  }

  /**
   * Load items of a specific type into tracker
   */
  private async loadItems(type: 'object' | 'monument'): Promise<void> {
    // Use itemByType endpoint to get items filtered by type
    const response = await this.apiClient.item.itemByType(
      type,
      type,
      undefined // include
    );
    const items = response.data.data;

    for (const item of items) {
      if (item.backward_compatibility) {
        this.tracker.register({
          uuid: item.id,
          backwardCompatibility: item.backward_compatibility,
          entityType: 'item',
          createdAt: new Date(),
        });
      }
    }

    this.logger.info(`    Loaded ${items.length} items (${type})`, '✓');
  }
}
