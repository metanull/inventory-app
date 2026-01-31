#!/usr/bin/env node
/**
 * Unified Legacy Import CLI
 *
 * This is the main entry point for the unified import system.
 * Currently implements SQL-based import strategy.
 *
 * Usage:
 *   npm run import [options]
 *
 * Options:
 *   --dry-run          Simulate import without writing data
 *   --start-at <name>  Start from specific importer
 *   --stop-at <name>   Stop at specific importer
 *   --only <name>      Run only the specified importer
 *   --list-importers   List all available importers
 */

import dotenv from 'dotenv';
import { resolve } from 'path';
import { Command } from 'commander';
import chalk from 'chalk';
import mysql from 'mysql2/promise';

import { UnifiedTracker } from '../core/tracker.js';
import { SqlWriteStrategy } from '../strategies/sql-strategy.js';
import type { ImportContext, ILegacyDatabase, ILogger } from '../core/base-importer.js';
import type { ImportResult } from '../core/types.js';
import { FileLogger, type PhaseSummary } from '../core/file-logger.js';

import {
  DefaultContextImporter,
  LanguageImporter,
  LanguageTranslationImporter,
  CountryImporter,
  CountryTranslationImporter,
  ProjectImporter,
  PartnerImporter,
  ObjectImporter,
  MonumentImporter,
  MonumentDetailImporter,
  ObjectPictureImporter,
  MonumentPictureImporter,
  MonumentDetailPictureImporter,
  PartnerPictureImporter,
  // Phase 03: Sharing History
  ShProjectImporter,
  ShPartnerImporter,
  ShObjectImporter,
  ShMonumentImporter,
  ShMonumentDetailImporter,
  ShObjectPictureImporter,
  ShMonumentPictureImporter,
  ShMonumentDetailPictureImporter,
  // Phase 04: Glossary
  GlossaryImporter,
  GlossaryTranslationImporter,
  GlossarySpellingImporter,
  // Phase 06: Explore
  ExploreContextImporter,
  ExploreRootCollectionsImporter,
  ExploreThematicCycleImporter,
  ExploreCountryImporter,
  ExploreLocationImporter,
  ExploreMonumentImporter,
  ExploreItineraryImporter,
  // Phase 07: Travels
  TravelsContextImporter,
  TravelsRootCollectionImporter,
  TravelsTrailImporter,
  TravelsTrailTranslationImporter,
  TravelsItineraryImporter,
  TravelsItineraryTranslationImporter,
  TravelsLocationImporter,
  TravelsLocationTranslationImporter,
  TravelsMonumentImporter,
  TravelsMonumentTranslationImporter,
  // Phase 10: Thematic Galleries (runs last, after all other legacy DBs)
  ThgGalleryContextImporter,
  ThgGalleryImporter,
  ThgGalleryTranslationImporter,
  ThgThemeImporter,
  ThgThemeTranslationImporter,
  ThgThemeItemImporter,
  ThgThemeItemTranslationImporter,
  ThgItemRelatedImporter,
  ThgItemRelatedTranslationImporter,
  // Phase 10: Gallery-Item Link Importers
  ThgGalleryMwnf3ObjectImporter,
  ThgGalleryMwnf3MonumentImporter,
  ThgGalleryShObjectImporter,
  ThgGalleryShMonumentImporter,
  ThgGalleryTravelMonumentImporter,
} from '../importers/index.js';
import { ImageSyncTool } from '../tools/image-sync.js';

// Load environment variables
dotenv.config({ path: resolve(process.cwd(), '.env') });

// Importer registry
interface ImporterConfig {
  key: string;
  name: string;
  description: string;
  importerClass: new (
    context: ImportContext,
    logger?: ILogger
  ) => { import(): Promise<ImportResult>; getName(): string };
  dependencies?: string[];
}

const ALL_IMPORTERS: ImporterConfig[] = [
  // Phase 0: Reference Data
  {
    key: 'default-context',
    name: 'Default Context',
    description: 'Create default context with is_default=true',
    importerClass: DefaultContextImporter,
    dependencies: [],
  },
  {
    key: 'language',
    name: 'Languages',
    description: 'Import language reference data',
    importerClass: LanguageImporter,
    dependencies: [],
  },
  {
    key: 'language-translation',
    name: 'Language Translations',
    description: 'Import language name translations',
    importerClass: LanguageTranslationImporter,
    dependencies: ['language'],
  },
  {
    key: 'country',
    name: 'Countries',
    description: 'Import country reference data',
    importerClass: CountryImporter,
    dependencies: [],
  },
  {
    key: 'country-translation',
    name: 'Country Translations',
    description: 'Import country name translations',
    importerClass: CountryTranslationImporter,
    dependencies: ['language', 'country'],
  },
  // Phase 1: Core Data
  {
    key: 'project',
    name: 'Projects',
    description: 'Import projects (creates Context, Collection, Project)',
    importerClass: ProjectImporter,
    dependencies: ['default-context', 'language'],
  },
  {
    key: 'partner',
    name: 'Partners',
    description: 'Import museums and institutions',
    importerClass: PartnerImporter,
    dependencies: ['default-context', 'project', 'language', 'country'],
  },
  {
    key: 'object',
    name: 'Objects',
    description: 'Import object items',
    importerClass: ObjectImporter,
    dependencies: ['project', 'partner', 'language'],
  },
  {
    key: 'monument',
    name: 'Monuments',
    description: 'Import monument items',
    importerClass: MonumentImporter,
    dependencies: ['project', 'partner', 'language'],
  },
  {
    key: 'monument-detail',
    name: 'Monument Details',
    description: 'Import monument detail items (children of monuments)',
    importerClass: MonumentDetailImporter,
    dependencies: ['monument', 'default-context', 'language'],
  },
  // Phase 2: Images
  {
    key: 'object-picture',
    name: 'Object Pictures',
    description: 'Import object pictures (ItemImages + child picture Items)',
    importerClass: ObjectPictureImporter,
    dependencies: ['object', 'default-context', 'language'],
  },
  {
    key: 'monument-picture',
    name: 'Monument Pictures',
    description: 'Import monument pictures (ItemImages + child picture Items)',
    importerClass: MonumentPictureImporter,
    dependencies: ['monument', 'default-context', 'language'],
  },
  {
    key: 'monument-detail-picture',
    name: 'Monument Detail Pictures',
    description: 'Import monument detail pictures (ItemImages + child picture Items)',
    importerClass: MonumentDetailPictureImporter,
    dependencies: ['monument-detail', 'default-context', 'language'],
  },
  {
    key: 'partner-picture',
    name: 'Partner Pictures',
    description: 'Import museum and institution pictures (PartnerImages)',
    importerClass: PartnerPictureImporter,
    dependencies: ['partner'],
  },
  // Phase 3: Sharing History Data
  {
    key: 'sh-project',
    name: 'SH Projects',
    description: 'Import Sharing History projects (Context, Collection, Project)',
    importerClass: ShProjectImporter,
    dependencies: ['default-context', 'language'],
  },
  {
    key: 'sh-partner',
    name: 'SH Partners',
    description: 'Import Sharing History partners (reuses mwnf3 partners via mapping)',
    importerClass: ShPartnerImporter,
    dependencies: ['default-context', 'sh-project', 'partner', 'language', 'country'],
  },
  {
    key: 'sh-object',
    name: 'SH Objects',
    description: 'Import Sharing History object items',
    importerClass: ShObjectImporter,
    dependencies: ['sh-project', 'sh-partner', 'language'],
  },
  {
    key: 'sh-monument',
    name: 'SH Monuments',
    description: 'Import Sharing History monument items',
    importerClass: ShMonumentImporter,
    dependencies: ['sh-project', 'sh-partner', 'language'],
  },
  {
    key: 'sh-monument-detail',
    name: 'SH Monument Details',
    description: 'Import Sharing History monument detail items',
    importerClass: ShMonumentDetailImporter,
    dependencies: ['sh-monument', 'default-context', 'language'],
  },
  {
    key: 'sh-object-picture',
    name: 'SH Object Pictures',
    description: 'Import Sharing History object pictures',
    importerClass: ShObjectPictureImporter,
    dependencies: ['sh-object', 'default-context', 'language'],
  },
  {
    key: 'sh-monument-picture',
    name: 'SH Monument Pictures',
    description: 'Import Sharing History monument pictures',
    importerClass: ShMonumentPictureImporter,
    dependencies: ['sh-monument', 'default-context', 'language'],
  },
  {
    key: 'sh-monument-detail-picture',
    name: 'SH Monument Detail Pictures',
    description: 'Import Sharing History monument detail pictures',
    importerClass: ShMonumentDetailPictureImporter,
    dependencies: ['sh-monument-detail', 'default-context', 'language'],
  },
  // Phase 4: Glossary
  {
    key: 'glossary',
    name: 'Glossary Words',
    description: 'Import glossary words from legacy database',
    importerClass: GlossaryImporter,
    dependencies: ['language'],
  },
  {
    key: 'glossary-translation',
    name: 'Glossary Definitions',
    description: 'Import glossary definitions (translations)',
    importerClass: GlossaryTranslationImporter,
    dependencies: ['glossary', 'language'],
  },
  {
    key: 'glossary-spelling',
    name: 'Glossary Spellings',
    description: 'Import glossary spelling variants',
    importerClass: GlossarySpellingImporter,
    dependencies: ['glossary', 'language'],
  },
  // Phase 06: Explore
  {
    key: 'explore-context',
    name: 'Explore Context',
    description: 'Create context for Explore application',
    importerClass: ExploreContextImporter,
    dependencies: [],
  },
  {
    key: 'explore-root-collections',
    name: 'Explore Root Collections',
    description: 'Create root collections for Explore (by Theme, Country, Itinerary)',
    importerClass: ExploreRootCollectionsImporter,
    dependencies: ['explore-context', 'language'],
  },
  {
    key: 'explore-thematiccycle',
    name: 'Explore Thematic Cycles',
    description: 'Import thematic cycles from Explore database',
    importerClass: ExploreThematicCycleImporter,
    dependencies: ['explore-root-collections'],
  },
  {
    key: 'explore-country',
    name: 'Explore Countries',
    description: 'Import country collections from Explore locations',
    importerClass: ExploreCountryImporter,
    dependencies: ['explore-root-collections', 'country'],
  },
  {
    key: 'explore-location',
    name: 'Explore Locations',
    description: 'Import location collections (cities/places) from Explore',
    importerClass: ExploreLocationImporter,
    dependencies: ['explore-country'],
  },
  {
    key: 'explore-monument',
    name: 'Explore Monuments',
    description: 'Import monuments with geocoordinates from Explore',
    importerClass: ExploreMonumentImporter,
    dependencies: ['explore-location'],
  },
  {
    key: 'explore-itinerary',
    name: 'Explore Itineraries',
    description: 'Import itineraries (curated routes) from Explore',
    importerClass: ExploreItineraryImporter,
    dependencies: ['explore-root-collections', 'explore-thematiccycle'],
  },
  // Phase 07: Travels (virtual visits and exhibition trails)
  {
    key: 'travels-context',
    name: 'Travels Context',
    description: 'Create context for Travels application',
    importerClass: TravelsContextImporter,
    dependencies: [],
  },
  {
    key: 'travels-root-collection',
    name: 'Travels Root Collection',
    description: 'Create root collection for Travels',
    importerClass: TravelsRootCollectionImporter,
    dependencies: ['travels-context', 'language'],
  },
  {
    key: 'travels-trail',
    name: 'Travels Trails',
    description: 'Import trails (exhibition trails) from Travels database',
    importerClass: TravelsTrailImporter,
    dependencies: ['travels-root-collection', 'country'],
  },
  {
    key: 'travels-trail-translation',
    name: 'Travels Trail Translations',
    description: 'Import trail translations',
    importerClass: TravelsTrailTranslationImporter,
    dependencies: ['travels-trail', 'language'],
  },
  {
    key: 'travels-itinerary',
    name: 'Travels Itineraries',
    description: 'Import itineraries under trails',
    importerClass: TravelsItineraryImporter,
    dependencies: ['travels-trail'],
  },
  {
    key: 'travels-itinerary-translation',
    name: 'Travels Itinerary Translations',
    description: 'Import itinerary translations',
    importerClass: TravelsItineraryTranslationImporter,
    dependencies: ['travels-itinerary', 'language'],
  },
  {
    key: 'travels-location',
    name: 'Travels Locations',
    description: 'Import locations under itineraries',
    importerClass: TravelsLocationImporter,
    dependencies: ['travels-itinerary'],
  },
  {
    key: 'travels-location-translation',
    name: 'Travels Location Translations',
    description: 'Import location translations',
    importerClass: TravelsLocationTranslationImporter,
    dependencies: ['travels-location', 'language'],
  },
  {
    key: 'travels-monument',
    name: 'Travels Monuments',
    description: 'Import travel-specific monument items',
    importerClass: TravelsMonumentImporter,
    dependencies: ['travels-location', 'country'],
  },
  {
    key: 'travels-monument-translation',
    name: 'Travels Monument Translations',
    description: 'Import travel monument translations',
    importerClass: TravelsMonumentTranslationImporter,
    dependencies: ['travels-monument', 'language'],
  },
  // Phase 10: Thematic Galleries (runs last, after all other legacy DBs are imported)
  {
    key: 'thg-gallery-context',
    name: 'THG Gallery Contexts',
    description: 'Create contexts for thematic galleries/exhibitions',
    importerClass: ThgGalleryContextImporter,
    dependencies: [],
  },
  {
    key: 'thg-gallery',
    name: 'THG Galleries',
    description: 'Import thematic galleries as collections',
    importerClass: ThgGalleryImporter,
    dependencies: ['thg-gallery-context'],
  },
  {
    key: 'thg-gallery-translation',
    name: 'THG Gallery Translations',
    description: 'Import thematic gallery translations',
    importerClass: ThgGalleryTranslationImporter,
    dependencies: ['thg-gallery', 'language'],
  },
  {
    key: 'thg-theme',
    name: 'THG Themes',
    description: 'Import thematic gallery themes',
    importerClass: ThgThemeImporter,
    dependencies: ['thg-gallery'],
  },
  {
    key: 'thg-theme-translation',
    name: 'THG Theme Translations',
    description: 'Import thematic gallery theme translations',
    importerClass: ThgThemeTranslationImporter,
    dependencies: ['thg-theme', 'thg-gallery-context', 'language'],
  },
  {
    key: 'thg-theme-item',
    name: 'THG Theme Items',
    description: 'Attach items to thematic gallery collections (all legacy DBs)',
    importerClass: ThgThemeItemImporter,
    dependencies: [
      'thg-gallery',
      'object',
      'monument',
      'monument-detail',
      'sh-object',
      'sh-monument',
      'sh-monument-detail',
    ],
  },
  {
    key: 'thg-theme-item-translation',
    name: 'THG Theme Item Translations',
    description: 'Import contextual item descriptions for thematic galleries',
    importerClass: ThgThemeItemTranslationImporter,
    dependencies: ['thg-theme-item', 'thg-gallery-context', 'language'],
  },
  {
    key: 'thg-item-related',
    name: 'THG Item Relations',
    description: 'Import item-to-item links within thematic galleries',
    importerClass: ThgItemRelatedImporter,
    dependencies: ['thg-theme-item', 'thg-gallery-context'],
  },
  {
    key: 'thg-item-related-translation',
    name: 'THG Item Relation Translations',
    description: 'Import translations for item-to-item links',
    importerClass: ThgItemRelatedTranslationImporter,
    dependencies: ['thg-item-related', 'language'],
  },
  // Phase 10: Gallery-Item Link Importers (direct links from thg_gallery to items)
  {
    key: 'thg-gallery-mwnf3-object',
    name: 'THG Gallery MWNF3 Objects',
    description: 'Link mwnf3 objects to THG gallery collections',
    importerClass: ThgGalleryMwnf3ObjectImporter,
    dependencies: ['thg-gallery', 'object'],
  },
  {
    key: 'thg-gallery-mwnf3-monument',
    name: 'THG Gallery MWNF3 Monuments',
    description: 'Link mwnf3 monuments to THG gallery collections',
    importerClass: ThgGalleryMwnf3MonumentImporter,
    dependencies: ['thg-gallery', 'monument'],
  },
  {
    key: 'thg-gallery-sh-object',
    name: 'THG Gallery SH Objects',
    description: 'Link Sharing History objects to THG gallery collections',
    importerClass: ThgGalleryShObjectImporter,
    dependencies: ['thg-gallery', 'sh-object'],
  },
  {
    key: 'thg-gallery-sh-monument',
    name: 'THG Gallery SH Monuments',
    description: 'Link Sharing History monuments to THG gallery collections',
    importerClass: ThgGalleryShMonumentImporter,
    dependencies: ['thg-gallery', 'sh-monument'],
  },
  {
    key: 'thg-gallery-travel-monument',
    name: 'THG Gallery Travel Monuments',
    description: 'Link travel monuments to THG gallery collections',
    importerClass: ThgGalleryTravelMonumentImporter,
    dependencies: ['thg-gallery', 'travels-monument'],
  },
];

/**
 * Simple Legacy Database wrapper
 */
class LegacyDatabase implements ILegacyDatabase {
  private connection: mysql.Connection | null = null;
  private config: mysql.ConnectionOptions;

  constructor() {
    this.config = {
      host: process.env['LEGACY_DB_HOST'] || 'localhost',
      port: parseInt(process.env['LEGACY_DB_PORT'] || '3306', 10),
      user: process.env['LEGACY_DB_USER'] || 'root',
      password: process.env['LEGACY_DB_PASSWORD'] || '',
      database: process.env['LEGACY_DB_DATABASE'] || 'mwnf3',
      multipleStatements: false, // Disabled for security - use single queries
    };
  }

  async connect(): Promise<void> {
    this.connection = await mysql.createConnection(this.config);
  }

  async disconnect(): Promise<void> {
    if (this.connection) {
      await this.connection.end();
      this.connection = null;
    }
  }

  async query<T>(sql: string, params?: unknown[]): Promise<T[]> {
    if (!this.connection) {
      throw new Error('Database not connected');
    }
    const [rows] = params
      ? await this.connection.execute(sql, params)
      : await this.connection.execute(sql);
    return rows as T[];
  }

  async execute(sql: string, params?: unknown[]): Promise<void> {
    if (!this.connection) {
      throw new Error('Database not connected');
    }
    if (params) {
      await this.connection.execute(sql, params);
    } else {
      await this.connection.execute(sql);
    }
  }
}

/**
 * Resilient database connection wrapper with automatic reconnection
 */
class ResilientConnection {
  private connection: mysql.Connection | null = null;
  private config: mysql.ConnectionOptions;
  private reconnecting = false;

  constructor(config: mysql.ConnectionOptions) {
    this.config = config;
  }

  async connect(): Promise<void> {
    this.connection = await mysql.createConnection(this.config);

    // Handle connection errors
    this.connection.on('error', (err: Error & { code?: string }) => {
      console.error('[ResilientConnection] Connection error:', err.message);
      if (err.code === 'PROTOCOL_CONNECTION_LOST' || err.code === 'ECONNRESET') {
        this.reconnect().catch((reconnectErr) =>
          console.error('[ResilientConnection] Reconnect failed:', reconnectErr)
        );
      }
    });
  }

  private async reconnect(): Promise<void> {
    if (this.reconnecting) return;

    this.reconnecting = true;
    console.log('[ResilientConnection] Connection lost, attempting to reconnect...');

    try {
      if (this.connection) {
        try {
          await this.connection.end();
        } catch {
          // Ignore errors when closing dead connection
        }
      }

      await this.connect();
      console.log('[ResilientConnection] Reconnected successfully');
    } finally {
      this.reconnecting = false;
    }
  }

  async execute<
    T extends
      | mysql.RowDataPacket[]
      | mysql.RowDataPacket[][]
      | mysql.OkPacket
      | mysql.OkPacket[]
      | mysql.ResultSetHeader,
  >(sql: string, values?: unknown): Promise<[T, mysql.FieldPacket[]]> {
    const maxRetries = 5;
    let lastError: Error | null = null;

    for (let attempt = 1; attempt <= maxRetries; attempt++) {
      try {
        if (!this.connection) {
          await this.connect();
        }

        if (!this.connection) {
          throw new Error('Failed to establish connection');
        }

        return await this.connection.execute<T>(sql, values);
      } catch (err) {
        const error = err as Error & { code?: string };
        lastError = error;
        const isConnectionError =
          error.code === 'PROTOCOL_CONNECTION_LOST' ||
          error.code === 'ECONNRESET' ||
          error.message?.includes('connection is in closed state');

        if (isConnectionError && attempt < maxRetries) {
          console.log(
            `[ResilientConnection] Connection error on attempt ${attempt}/${maxRetries}, retrying in ${attempt * 2}s...`
          );
          await new Promise((resolve) => setTimeout(resolve, attempt * 2000));
          await this.reconnect();
        } else if (!isConnectionError) {
          // Not a connection error, throw immediately
          throw err;
        }
      }
    }

    throw new Error(`Failed after ${maxRetries} attempts: ${lastError?.message}`);
  }

  async end(): Promise<void> {
    if (this.connection) {
      await this.connection.end();
      this.connection = null;
    }
  }

  getConnection(): mysql.Connection {
    if (!this.connection) {
      throw new Error('Connection not established');
    }
    return this.connection;
  }
}

/**
 * Create connection to new database
 */
async function createNewDbConnection(): Promise<ResilientConnection> {
  const resilientConn = new ResilientConnection({
    host: process.env['DB_HOST'] || 'localhost',
    port: parseInt(process.env['DB_PORT'] || '3306', 10),
    user: process.env['DB_USERNAME'] || 'root',
    password: process.env['DB_PASSWORD'] || '',
    database: process.env['DB_DATABASE'] || 'inventory',
  });

  await resilientConn.connect();
  return resilientConn;
}

/**
 * Determine if an importer should run
 */
function shouldRunImporter(
  config: ImporterConfig,
  only: string | undefined,
  startAt: string | undefined,
  stopAt: string | undefined
): boolean {
  if (only) {
    return config.key === only;
  }

  const importerIndex = ALL_IMPORTERS.findIndex((i) => i.key === config.key);

  if (startAt) {
    const startIndex = ALL_IMPORTERS.findIndex((i) => i.key === startAt);
    if (startIndex === -1) {
      throw new Error(`Unknown importer: ${startAt}`);
    }
    if (importerIndex < startIndex) {
      return false;
    }
  }

  if (stopAt) {
    const stopIndex = ALL_IMPORTERS.findIndex((i) => i.key === stopAt);
    if (stopIndex === -1) {
      throw new Error(`Unknown importer: ${stopAt}`);
    }
    if (importerIndex > stopIndex) {
      return false;
    }
  }

  return true;
}

// CLI
const program = new Command();

program
  .name('importer')
  .description('Unified Legacy Import Tool - Imports data from legacy database')
  .version('1.0.0');

program
  .command('import')
  .description('Run import process')
  .option('--dry-run', 'Simulate import without writing data', false)
  .option('--start-at <importer>', 'Start from specific importer')
  .option('--stop-at <importer>', 'Stop at specific importer')
  .option('--only <importer>', 'Run only the specified importer')
  .option('--list-importers', 'List all available importers')
  .action(async (options) => {
    // Initialize file logger
    const logger = new FileLogger('ImportCLI', 'logs');

    try {
      const dryRun = options.dryRun === true;
      const startAt = options.startAt;
      const stopAt = options.stopAt;
      const only = options.only;
      const listImporters = options.listImporters === true;

      // Handle --list-importers
      if (listImporters) {
        console.log(chalk.bold('\nAvailable Importers:\n'));
        ALL_IMPORTERS.forEach((imp, idx) => {
          console.log(
            `  ${(idx + 1).toString().padStart(2)}. ${imp.key.padEnd(22)} - ${imp.description}`
          );
        });
        console.log('\nUsage examples:');
        console.log('  npm run import                          # Run all importers');
        console.log('  npm run import -- --start-at project    # Start from project onwards');
        console.log('  npm run import -- --stop-at partner     # Run up to and including partner');
        console.log('  npm run import -- --only partner        # Run only partner');
        console.log('');
        process.exit(0);
      }

      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.bold.cyan('UNIFIED LEGACY IMPORT'));
      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.gray(`Start time: ${new Date().toISOString()}`));
      console.log(chalk.gray(`Log file: ${logger.getLogFilePath()}`));
      console.log(chalk.gray(`Dry-run: ${dryRun ? 'YES' : 'NO'}`));
      if (startAt) console.log(chalk.gray(`Start at: ${startAt}`));
      if (stopAt) console.log(chalk.gray(`Stop at: ${stopAt}`));
      if (only) console.log(chalk.gray(`Only: ${only}`));
      console.log('');

      logger.info(
        `Import started with options: dryRun=${dryRun}, startAt=${startAt || 'none'}, stopAt=${stopAt || 'none'}, only=${only || 'none'}`
      );

      // Connect to databases
      console.log(chalk.cyan('Connecting to databases...'));
      const legacyDb = new LegacyDatabase();
      await legacyDb.connect();
      console.log(chalk.green('✓ Legacy database connected'));
      logger.info('Legacy database connected');

      const newDb = await createNewDbConnection();
      console.log(chalk.green('✓ New database connected'));
      logger.info('New database connected');

      // Initialize tracker and strategy
      const tracker = new UnifiedTracker();
      const strategy = new SqlWriteStrategy(newDb, tracker);

      // Create import context
      const importContext: ImportContext = {
        legacyDb,
        strategy,
        tracker,
        dryRun,
      };

      // Track results and phases
      const results = new Map<string, ImportResult>();
      const phases: PhaseSummary[] = [];
      let currentPhase = 'Phase 0: Reference Data';
      let phaseStart = Date.now();
      let phaseImported = 0;
      let phaseSkipped = 0;
      let phaseErrors = 0;

      // Helper to determine phase
      const getPhase = (key: string): string => {
        if (
          [
            'default-context',
            'language',
            'language-translation',
            'country',
            'country-translation',
          ].includes(key)
        ) {
          return 'Phase 0: Reference Data';
        } else if (['project', 'partner'].includes(key)) {
          return 'Phase 1: Projects and Partners';
        } else if (['object', 'monument', 'monument-detail'].includes(key)) {
          return 'Phase 2: Items';
        } else if (
          [
            'object-picture',
            'monument-picture',
            'monument-detail-picture',
            'partner-picture',
          ].includes(key)
        ) {
          return 'Phase 3: Images';
        } else if (['glossary', 'glossary-translation', 'glossary-spelling'].includes(key)) {
          return 'Phase 4: Glossary';
        } else if (
          [
            'explore-context',
            'explore-root-collections',
            'explore-thematiccycle',
            'explore-country',
            'explore-location',
            'explore-monument',
            'explore-itinerary',
          ].includes(key)
        ) {
          return 'Phase 6: Explore';
        } else if (
          [
            'thg-gallery-context',
            'thg-gallery',
            'thg-gallery-translation',
            'thg-theme',
            'thg-theme-translation',
            'thg-theme-item',
            'thg-theme-item-translation',
            'thg-item-related',
            'thg-item-related-translation',
            'thg-gallery-mwnf3-object',
            'thg-gallery-mwnf3-monument',
            'thg-gallery-sh-object',
            'thg-gallery-sh-monument',
          ].includes(key)
        ) {
          return 'Phase 10: Thematic Galleries';
        } else {
          return 'Phase Unknown';
        }
      };

      // Execute importers
      for (const config of ALL_IMPORTERS) {
        const shouldRun = shouldRunImporter(config, only, startAt, stopAt);

        // Check if phase changed
        const newPhase = getPhase(config.key);
        if (newPhase !== currentPhase && shouldRun) {
          // Save current phase results
          if (phaseImported > 0 || phaseSkipped > 0 || phaseErrors > 0) {
            phases.push({
              phase: currentPhase,
              duration: Date.now() - phaseStart,
              imported: phaseImported,
              skipped: phaseSkipped,
              errors: phaseErrors,
            });
          }
          // Start new phase
          currentPhase = newPhase;
          phaseStart = Date.now();
          phaseImported = 0;
          phaseSkipped = 0;
          phaseErrors = 0;
          logger.logPhaseStart(currentPhase);
        }

        if (!shouldRun) {
          console.log(chalk.gray(`⏭  Skipping ${config.name}`));
          logger.info(`Skipping ${config.name}`);
          continue;
        }

        logger.logImporterStart(config.name);
        const importerStart = Date.now();

        try {
          const importer = new config.importerClass(importContext, logger);
          const result = await importer.import();
          results.set(config.key, result);

          const importerDuration = Date.now() - importerStart;
          phaseImported += result.imported;
          phaseSkipped += result.skipped;
          phaseErrors += result.errors.length;

          logger.logImporterComplete(
            config.name,
            result.imported,
            result.skipped,
            result.errors.length,
            importerDuration
          );

          if (result.errors.length > 0) {
            // Log errors to file
            result.errors.forEach((err) => {
              logger.logImporterError(config.name, err);
            });
          }

          // Report warnings
          if (result.warnings && result.warnings.length > 0) {
            console.log(chalk.yellow(`   ⚠  ${result.warnings.length} warnings`));
            result.warnings.forEach((warn) => {
              logger.warning(`${config.name}: ${warn}`);
            });
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const importerDuration = Date.now() - importerStart;
          phaseErrors++;
          logger.logImporterComplete(config.name, 0, 0, 1, importerDuration);
          logger.logImporterError(config.name, message);
          results.set(config.key, {
            success: false,
            imported: 0,
            skipped: 0,
            errors: [message],
          });
        }
      }

      // Save final phase results
      if (phaseImported > 0 || phaseSkipped > 0 || phaseErrors > 0) {
        phases.push({
          phase: currentPhase,
          duration: Date.now() - phaseStart,
          imported: phaseImported,
          skipped: phaseSkipped,
          errors: phaseErrors,
        });
      }

      // Calculate totals
      const totals = Array.from(results.values()).reduce(
        (acc, r) => ({
          imported: acc.imported + r.imported,
          skipped: acc.skipped + r.skipped,
          errors: acc.errors + r.errors.length,
          warnings: acc.warnings + (r.warnings?.length || 0),
        }),
        { imported: 0, skipped: 0, errors: 0, warnings: 0 }
      );

      // Log final summary (handles both console and file output)
      logger.logFinalSummary(phases);

      // Cleanup
      logger.info('Disconnecting from databases...');
      await legacyDb.disconnect();
      await newDb.end();
      logger.info('Import process ended');

      if (totals.errors > 0) {
        process.exit(1);
      }
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      logger.error('Fatal error', error);
      console.error(chalk.red(`\nFatal error: ${message}`));
      if (error instanceof Error && error.stack) {
        console.error(chalk.gray(error.stack));
      }
      process.exit(1);
    }
  });

program
  .command('validate')
  .description('Validate database connections')
  .action(async () => {
    console.log(chalk.cyan('Validating connections...\n'));

    let hasErrors = false;

    // Legacy database
    try {
      console.log('Testing legacy database connection...');
      const legacyDb = new LegacyDatabase();
      await legacyDb.connect();
      console.log(chalk.green('✓ Legacy database connection successful'));
      await legacyDb.disconnect();
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      console.log(chalk.red(`❌ Legacy database connection failed: ${message}`));
    }

    // New database
    try {
      console.log('Testing new database connection...');
      const newDb = await createNewDbConnection();
      console.log(chalk.green('✓ New database connection successful'));
      await newDb.end();
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      console.log(chalk.red(`❌ New database connection failed: ${message}`));
    }

    if (hasErrors) {
      console.log(chalk.red('\n❌ Validation failed. Fix errors above before importing.'));
      process.exit(1);
    } else {
      console.log(chalk.green('\n✓ All connections validated successfully.'));
    }
  });

program
  .command('image-sync')
  .description(
    'Synchronize legacy images to new storage (ItemImages and PartnerImages with size=1)'
  )
  .option('--symlink', 'Create symbolic links instead of copying files', false)
  .option('--dry-run', 'Simulate synchronization without making changes', false)
  .action(async (options) => {
    const logger = new FileLogger('ImageSync', 'logs');

    try {
      const useSymlink = options.symlink === true;
      const dryRun = options.dryRun === true;

      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.bold.cyan('IMAGE SYNCHRONIZATION'));
      console.log(chalk.bold('='.repeat(80)));
      console.log(chalk.gray(`Start time: ${new Date().toISOString()}`));
      console.log(chalk.gray(`Log file: ${logger.getLogFilePath()}`));
      console.log(chalk.gray(`Mode: ${useSymlink ? 'SYMLINK' : 'COPY'}`));
      console.log(chalk.gray(`Dry-run: ${dryRun ? 'YES' : 'NO'}`));
      console.log('');

      logger.info(`Image sync started with options: symlink=${useSymlink}, dryRun=${dryRun}`);

      // Get configuration
      const legacyImagesRoot =
        process.env['LEGACY_IMAGES_ROOT'] || 'C:\\mwnf-server\\pictures\\images';

      // Get new images root from Laravel artisan command
      console.log(chalk.cyan('Getting image storage path from Laravel...'));
      const { exec } = await import('child_process');
      const { promisify } = await import('util');
      const execAsync = promisify(exec);

      const laravelRoot = resolve(process.cwd(), '../..');
      const { stdout } = await execAsync('php artisan storage:image-path pictures', {
        cwd: laravelRoot,
      });
      const newImagesRoot = stdout.trim();

      console.log(chalk.green(`✓ Image storage path: ${newImagesRoot}`));
      logger.info(`Image storage path: ${newImagesRoot}`);

      // Connect to database
      console.log(chalk.cyan('Connecting to database...'));
      const newDb = await createNewDbConnection();
      console.log(chalk.green('✓ Database connected'));
      logger.info('Database connected');

      // Create and run image sync tool
      const tool = new ImageSyncTool(
        newDb.getConnection(),
        {
          useSymlink,
          legacyImagesRoot,
          newImagesRoot,
          dryRun,
        },
        logger
      );

      const result = await tool.run();

      // Cleanup
      await newDb.end();
      console.log(chalk.green('\n✓ Database disconnected'));

      // Final summary
      console.log('');
      console.log(chalk.bold('='.repeat(80)));
      if (result.success) {
        console.log(chalk.bold.green('IMAGE SYNC COMPLETED SUCCESSFULLY'));
        console.log(chalk.gray(`End time: ${new Date().toISOString()}`));
        console.log(chalk.green(`✓ ${result.imported} images synchronized`));
        console.log(chalk.yellow(`⊘ ${result.skipped} images skipped`));
      } else {
        console.log(chalk.bold.red('IMAGE SYNC COMPLETED WITH ERRORS'));
        console.log(chalk.gray(`End time: ${new Date().toISOString()}`));
        console.log(chalk.green(`✓ ${result.imported} images synchronized`));
        console.log(chalk.yellow(`⊘ ${result.skipped} images skipped`));
        console.log(chalk.red(`✗ ${result.errors.length} errors`));
        console.log('');
        console.log(chalk.red('Errors:'));
        result.errors.slice(0, 10).forEach((err) => console.log(chalk.red(`  - ${err}`)));
        if (result.errors.length > 10) {
          console.log(chalk.red(`  ... and ${result.errors.length - 10} more errors`));
        }
      }
      console.log(chalk.bold('='.repeat(80)));

      process.exit(result.success ? 0 : 1);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      logger.error('ImageSync', error);
      console.log(chalk.red(`\n❌ Image sync failed: ${message}`));
      console.log(chalk.red(error instanceof Error ? error.stack : ''));
      process.exit(1);
    }
  });

program.parse();
