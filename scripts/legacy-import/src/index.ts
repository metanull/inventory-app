import { Command } from 'commander';
import dotenv from 'dotenv';
import { resolve } from 'path';
import { quickLogin } from './utils/LoginHelper.js';
import { createApiClient } from './api/InventoryApiClient.js';
import { createLegacyDatabase } from './database/LegacyDatabase.js';
import { Logger } from './utils/Logger.js';

// Load environment variables
dotenv.config({ path: resolve(process.cwd(), '.env') });

// Importer registry - single source of truth
interface ImporterConfig {
  key: string;
  name: string;
  description: string;
  importerModule: string;
  dependencies?: string[]; // Keys of importers that must be loaded first
}

const ALL_IMPORTERS: ImporterConfig[] = [
  // === Phase 0: Reference Data (Foundation) ===
  {
    key: 'language',
    name: 'Languages',
    description: 'Import language reference data',
    importerModule: './importers/phase-00/LanguageImporter.js',
    dependencies: [], // No dependencies - foundation data
  },
  {
    key: 'language-translation',
    name: 'Language Translations',
    description: 'Import language name translations',
    importerModule: './importers/phase-00/LanguageTranslationImporter.js',
    dependencies: ['language'], // Needs languages
  },
  {
    key: 'country',
    name: 'Countries',
    description: 'Import country reference data',
    importerModule: './importers/phase-00/CountryImporter.js',
    dependencies: [], // No dependencies - foundation data
  },
  {
    key: 'country-translation',
    name: 'Country Translations',
    description: 'Import country name translations',
    importerModule: './importers/phase-00/CountryTranslationImporter.js',
    dependencies: ['language', 'country'], // Needs languages and countries
  },
  {
    key: 'default-context',
    name: 'Default Context',
    description: 'Create default context',
    importerModule: './importers/phase-00/DefaultContextImporter.js',
    dependencies: [], // No dependencies
  },
  // === Phase 1: Core Data ===
  {
    key: 'project',
    name: 'Projects',
    description: 'Import projects and collections',
    importerModule: './importers/phase-01/ProjectImporter.js',
    dependencies: ['language'], // Uses language_id for projects and collections
  },
  {
    key: 'partner',
    name: 'Partners',
    description: 'Import museums and institutions',
    importerModule: './importers/phase-01/PartnerImporter.js',
    dependencies: ['project', 'language', 'country', 'default-context'], // Uses project_id, language_id for translations, country_id for location, default context for institutions
  },
  {
    key: 'object',
    name: 'Objects',
    description: 'Import object items',
    importerModule: './importers/phase-01/ObjectImporter.js',
    dependencies: ['project', 'partner', 'language'], // Uses context/collection from projects, partner_id, language_id for translations
  },
  {
    key: 'monument',
    name: 'Monuments',
    description: 'Import monument items',
    importerModule: './importers/phase-01/MonumentImporter.js',
    dependencies: ['project', 'partner', 'language'], // Uses context/collection from projects, partner_id, language_id for translations
  },
];

// Helper to determine if an importer should run
function shouldRunImporter(
  config: ImporterConfig,
  only: string | undefined,
  startAt: string | undefined,
  stopAt: string | undefined
): boolean {
  // If --only is specified, run only that importer
  if (only) {
    return config.key === only;
  }

  const importerIndex = ALL_IMPORTERS.findIndex((i) => i.key === config.key);

  // Check --start-at
  if (startAt) {
    const startIndex = ALL_IMPORTERS.findIndex((i) => i.key === startAt);
    if (startIndex === -1) {
      throw new Error(`Unknown importer: ${startAt}`);
    }
    if (importerIndex < startIndex) {
      return false; // Skip importers before start-at
    }
  }

  // Check --stop-at
  if (stopAt) {
    const stopIndex = ALL_IMPORTERS.findIndex((i) => i.key === stopAt);
    if (stopIndex === -1) {
      throw new Error(`Unknown importer: ${stopAt}`);
    }
    if (importerIndex > stopIndex) {
      return false; // Skip importers after stop-at
    }
  }

  return true;
}

// Helper to derive importer class name from module path
function getImporterClassName(modulePath: string): string {
  // Extract filename without extension: './importers/phase-01/ProjectImporter.js' -> 'ProjectImporter'
  const filename = modulePath.split('/').pop()!;
  return filename.replace('.js', '');
}

const program = new Command();

program
  .name('legacy-import')
  .description('Import legacy museum database into new Inventory Management System')
  .version('1.0.0');

program
  .command('login')
  .description('Authenticate with API and save access token')
  .option('--url <url>', 'API base URL (overrides .env)')
  .action(async (options) => {
    const logger = new Logger();
    try {
      const baseUrl = options.url || process.env['API_BASE_URL'];
      await quickLogin(baseUrl);
      logger.console('');
      logger.info('Login complete. You can now run import commands.', '✓');
      process.exit(0);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      logger.console('');
      logger.error(`Login failed: ${message}`, true);
      process.exit(1);
    }
  });

program
  .command('validate')
  .description('Validate legacy database connection and API availability')
  .action(async () => {
    const logger = new Logger();
    logger.console('Validating connections...\n');

    let hasErrors = false;

    // Validate API connection
    try {
      logger.console('Testing API connection...');
      const apiClient = createApiClient();
      const isConnected = await apiClient.testConnection();
      if (!isConnected) {
        hasErrors = true;
        logger.error('API connection failed', true);
        logger.console('Run "npx tsx src/index.ts login" to authenticate\n');
      }
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      logger.error(`API connection error: ${message}`, true);
      logger.console('Run "npx tsx src/index.ts login" to authenticate\n');
    }

    // Validate legacy database connection
    try {
      logger.console('Testing legacy database connection...');
      const legacyDb = createLegacyDatabase();
      await legacyDb.connect();
      logger.info('Legacy database connection successful', '✓');
      await legacyDb.disconnect();
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      logger.error(`Legacy database connection failed: ${message}`, true);
      logger.console('Check LEGACY_DB_* settings in .env\n');
    }

    if (hasErrors) {
      logger.console('\n❌ Validation failed. Fix errors above before importing.');
      process.exit(1);
    } else {
      logger.console('\n✅ All connections validated successfully.');
    }
  });

program
  .command('import')
  .description('Run import process')
  .option('--dry-run', 'Simulate import without writing data', false)
  .option('--start-at <importer>', 'Start from specific importer (skip earlier ones)')
  .option('--stop-at <importer>', 'Stop at specific importer (skip later ones)')
  .option('--only <importer>', 'Run only the specified importer (skip all others)')
  .option('--list-importers', 'List all available importers and exit')
  .option('--collect-samples', 'Collect sample data for test fixtures', false)
  .option('--sample-size <number>', 'Number of success samples per category', '20')
  .option(
    '--sample-db <path>',
    'Path to sample database file (relative to script directory)',
    './test-fixtures/samples.sqlite'
  )
  .action(async (options) => {
    try {
      const dryRun = options.dryRun === true;
      const startAt = options.startAt;
      const stopAt = options.stopAt;
      const only = options.only;
      const listImporters = options.listImporters === true;
      const collectSamples = options.collectSamples === true;
      const sampleSize = parseInt(options.sampleSize, 10);
      // Resolve sample DB path relative to script directory
      // options.sampleDb is relative to script dir (e.g., './test-fixtures/samples.sqlite')
      const sampleDb = resolve(__dirname, '..', options.sampleDb);

      // Handle --list-importers
      if (listImporters) {
        const logger = new Logger();
        logger.console('');
        logger.info('Available Importers', '📋');
        logger.console('');
        ALL_IMPORTERS.forEach((imp, idx) => {
          logger.console(
            `  ${(idx + 1).toString().padStart(2)}. ${imp.key.padEnd(20)} - ${imp.description}`
          );
        });
        logger.console('');
        logger.console('Usage examples:');
        logger.console(
          '  npx tsx src/index.ts import                          # Run all importers'
        );
        logger.console(
          '  npx tsx src/index.ts import --start-at project       # Start from project onwards'
        );
        logger.console(
          '  npx tsx src/index.ts import --stop-at partner        # Run up to and including partner'
        );
        logger.console('  npx tsx src/index.ts import --start-at project --stop-at object');
        logger.console('  npx tsx src/index.ts import --only partner           # Run only partner');
        logger.console('');
        process.exit(0);
      }

      // Initialize logger
      const logger = new Logger();
      const logPath = logger.initFile({ dryRun, startAt, stopAt, only });

      logger.info('Starting import...', '🚀');
      logger.console(`   Dry-run: ${dryRun ? 'YES' : 'NO'}`);
      if (startAt) logger.console(`   Start at: ${startAt}`);
      if (stopAt) logger.console(`   Stop at: ${stopAt}`);
      if (only) logger.console(`   Only: ${only}`);
      logger.console('');

      // Validate connections
      logger.console('Validating connections...');

      const apiClient = createApiClient();
      const legacyDb = createLegacyDatabase();

      // Always connect to legacy DB (needed even in sample collection mode)
      await legacyDb.connect();
      logger.info('Legacy database connected', '✓');

      // Only validate API connection in normal mode
      if (!collectSamples && !dryRun) {
        // Normal mode: validate API connection
        if (!(await apiClient.testConnection())) {
          throw new Error('API connection failed. Run "npx tsx src/index.ts login" first.');
        }
        logger.info('API connection validated', '✓');
      } else {
        // Sample-only mode: skip API validation
        logger.console('⏭️  Dry-run or Sample collection mode - skipping API validation');
      }
      logger.info(`Log: ${logPath}`, '📄');
      logger.console('');

      // Initialize results
      const results = new Map<
        string,
        {
          success?: boolean;
          imported: number;
          skipped: number;
          errors: string[];
          warnings: string[];
        }
      >();
      ALL_IMPORTERS.forEach((imp) =>
        results.set(imp.key, {
          success: true,
          imported: 0,
          skipped: 0,
          errors: [],
          warnings: [],
        })
      );

      // Setup import execution context (tracker, API client, database connection, etc.)
      const { BackwardCompatibilityTracker } =
        await import('./utils/BackwardCompatibilityTracker.js');
      const tracker = new BackwardCompatibilityTracker();

      // Initialize sample collector if requested
      let sampleCollector;
      const sampleOnlyMode = collectSamples; // When collecting samples, skip API calls

      if (collectSamples) {
        // Auto-delete existing samples database to start fresh
        const fs = await import('fs');
        if (fs.existsSync(sampleDb)) {
          fs.unlinkSync(sampleDb);
          logger.console(`🗑️  Deleted existing sample database`);
        }

        const { SampleCollector } = await import('./utils/SampleCollector.js');
        sampleCollector = new SampleCollector({
          enabled: true,
          dbPath: sampleDb,
          sampleSize: sampleSize,
          collectAllWarnings: true,
          collectAllEdgeCases: true,
          collectAllFoundation: true, // Collect ALL languages and countries
        });
        logger.info(`Sample collection mode: ${sampleDb}`, '🧪');
        logger.console(`   Sample size per category: ${sampleSize}`);
        logger.console(`   Mode: Sample-only (skipping API calls for speed)`);
      }

      const importContext = {
        legacyDb,
        apiClient,
        tracker,
        dryRun,
        logPath,
        sampleCollector,
        sampleOnlyMode,
      };

      // Import DependencyLoader
      const { DependencyLoader } = await import('./utils/DependencyLoader.js');
      const dependencyLoader = new DependencyLoader(apiClient, tracker);

      // Track which dependencies have been loaded to avoid reloading
      const loadedDependencies = new Set<string>();

      // Execute importers in sequence
      for (const config of ALL_IMPORTERS) {
        const shouldRun = shouldRunImporter(config, only, startAt, stopAt);
        const result = results.get(config.key)!;

        if (!shouldRun) {
          logger.skipped(config.name);
          continue;
        }

        // Load dependencies before running this importer
        if (config.dependencies && config.dependencies.length > 0) {
          logger.console('');
          logger.info('Loading dependencies...', '🔗');
          for (const depKey of config.dependencies) {
            // Skip if already loaded in this session
            if (loadedDependencies.has(depKey)) {
              continue;
            }

            // Load dependency data into tracker
            await dependencyLoader.loadDependency(depKey);
            loadedDependencies.add(depKey);
          }
          logger.console('');
        }

        logger.started(config.name);

        try {
          const module = await import(config.importerModule);
          const ImporterClass = module[getImporterClassName(config.importerModule)];
          const importResult = await new ImporterClass(importContext).import();

          result.success = importResult.success;
          result.imported = importResult.imported;
          result.skipped = importResult.skipped;
          result.errors = importResult.errors || [];
          result.warnings = importResult.warnings || [];

          logger.completed(result.imported, result.skipped, result.errors.length, result.errors);

          // Report warnings if any
          if (result.warnings.length > 0) {
            logger.console('');
            logger.warning(
              `⚠️  ${config.name} had ${result.warnings.length} data quality warnings:`
            );
            result.warnings.slice(0, 10).forEach((w) => logger.console(`  - ${w}`));
            if (result.warnings.length > 10) {
              logger.console(`  ... and ${result.warnings.length - 10} more (see log file)`);
            }
          }

          if (result.errors.length > 0) {
            logger.error(`${config.name} import failed. Cannot proceed.`);
            await legacyDb.disconnect();
            process.exit(1);
          }
        } catch (error) {
          result.errors.push(error instanceof Error ? error.message : String(error));
          result.success = false;
          logger.error(`${config.name} import failed. Cannot proceed.`);
          await legacyDb.disconnect();
          process.exit(1);
        }
      }

      await legacyDb.disconnect();

      // Calculate totals and display summary
      const totals = Array.from(results.values()).reduce(
        (acc, r) => ({
          imported: acc.imported + r.imported,
          skipped: acc.skipped + r.skipped,
          errors: acc.errors + r.errors.length,
          warnings: acc.warnings + r.warnings.length,
        }),
        { imported: 0, skipped: 0, errors: 0, warnings: 0 }
      );

      logger.summary(totals.imported, totals.skipped, totals.errors);

      if (totals.warnings > 0) {
        logger.console('');
        logger.warning(`⚠️  Total data quality warnings: ${totals.warnings}`);
        logger.console('Review log file for details on how to address these issues.');
      }

      // Show sample collection statistics
      if (sampleCollector) {
        const stats = sampleCollector.getStats();
        const totalSamples = Object.values(stats).reduce((sum, count) => sum + count, 0);

        logger.console('');
        logger.info(`🧪 Collected ${totalSamples} samples for test fixtures`, '');
        logger.console(`   Database: ${sampleDb}`);

        // Show breakdown by category
        const categories = Object.entries(stats).sort(([, a], [, b]) => b - a);
        if (categories.length > 0 && categories.length <= 20) {
          logger.console('   Breakdown:');
          categories.forEach(([category, count]) => {
            logger.console(`     - ${category}: ${count}`);
          });
        }

        sampleCollector.close();
      }

      logger.info(`Log saved: ${logPath}`, '📄');

      if (totals.errors > 0) process.exit(1);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      const stack = error instanceof Error ? error.stack : '';

      const logger = new Logger();
      logger.error(`Import failed: ${message}`, true);
      if (stack) {
        logger.console(stack);
      }

      process.exit(1);
    }
  });

program
  .command('status')
  .description('Show import progress and statistics')
  .action(async () => {
    const logger = new Logger();
    logger.console('Import status...');
    // TODO: Implement status reporting
  });

program.parse();
