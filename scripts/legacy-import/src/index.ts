import { Command } from 'commander';
import dotenv from 'dotenv';
import { resolve } from 'path';
import { quickLogin } from './utils/LoginHelper.js';
import { createApiClient } from './api/InventoryApiClient.js';
import { createLegacyDatabase } from './database/LegacyDatabase.js';

// Load environment variables
dotenv.config({ path: resolve(process.cwd(), '.env') });

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
    try {
      const baseUrl = options.url || process.env['API_BASE_URL'];
      await quickLogin(baseUrl);
      console.log('\n✓ Login complete. You can now run import commands.');
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      console.error('\n✗ Login failed:', message);
      process.exit(1);
    }
  });

program
  .command('validate')
  .description('Validate legacy database connection and API availability')
  .action(async () => {
    console.log('Validating connections...\n');

    let hasErrors = false;

    // Validate API connection
    try {
      console.log('Testing API connection...');
      const apiClient = createApiClient();
      const isConnected = await apiClient.testConnection();
      if (!isConnected) {
        hasErrors = true;
        console.log('✗ API connection failed\n');
        console.log('Run "npm start -- login" to authenticate\n');
      }
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      console.error('✗ API connection error:', message);
      console.log('Run "npm start -- login" to authenticate\n');
    }

    // Validate legacy database connection
    try {
      console.log('Testing legacy database connection...');
      const legacyDb = createLegacyDatabase();
      await legacyDb.connect();
      console.log('✓ Legacy database connection successful');
      await legacyDb.disconnect();
    } catch (error) {
      hasErrors = true;
      const message = error instanceof Error ? error.message : String(error);
      console.error('✗ Legacy database connection failed:', message);
      console.log('Check LEGACY_DB_* settings in .env\n');
    }

    if (hasErrors) {
      console.log('\n❌ Validation failed. Fix errors above before importing.');
      process.exit(1);
    } else {
      console.log('\n✅ All connections validated successfully.');
    }
  });

program
  .command('import')
  .description('Run import process')
  .option('-p, --phase <number>', 'Run specific phase (1-17)', 'all')
  .option('--dry-run', 'Simulate import without writing data', false)
  .option('--limit <number>', 'Limit number of records per entity', '0')
  .action(async (options) => {
    try {
      const phase = options.phase === 'all' ? 'all' : parseInt(options.phase, 10);
      const dryRun = options.dryRun === true;
      const limit = parseInt(options.limit, 10);

      console.log('🚀 Starting import...');
      console.log(`   Phase: ${phase}`);
      console.log(`   Dry-run: ${dryRun ? 'YES' : 'NO'}`);
      console.log(`   Limit: ${limit > 0 ? limit : 'unlimited'}`);
      console.log('');

      // Validate connections first
      console.log('Validating connections...');
      const apiClient = createApiClient();
      const isApiConnected = await apiClient.testConnection();
      if (!isApiConnected) {
        throw new Error('API connection failed. Run "npm start -- login" first.');
      }

      const legacyDb = createLegacyDatabase();
      await legacyDb.connect();
      console.log('✓ Connections validated\n');

      // Import based on phase
      if (phase === 1 || phase === 'all') {
        console.log('📦 Phase 1: Core data (Projects, Partners)');
        const { PartnerImporter } = await import('./importers/phase-01/PartnerImporter.js');
        const { ProjectImporter } = await import('./importers/phase-01/ProjectImporter.js');
        const { BackwardCompatibilityTracker } = await import(
          './utils/BackwardCompatibilityTracker.js'
        );

        const tracker = new BackwardCompatibilityTracker();
        const context = {
          legacyDb,
          apiClient,
          tracker,
          dryRun,
          limit,
        };

        // Import projects first
        const projectImporter = new ProjectImporter(context);
        const projectResult = await projectImporter.import();
        console.log(
          `✓ Projects: ${projectResult.imported} imported, ${projectResult.skipped} skipped, ${projectResult.errors.length} errors`
        );
        if (projectResult.errors.length > 0) {
          console.error('Errors:', projectResult.errors.slice(0, 5));
        }

        // Import partners (museums + institutions)
        const partnerImporter = new PartnerImporter(context);
        const partnerResult = await partnerImporter.import();
        console.log(
          `✓ Partners: ${partnerResult.imported} imported, ${partnerResult.skipped} skipped, ${partnerResult.errors.length} errors`
        );
        if (partnerResult.errors.length > 0) {
          console.error('Errors:', partnerResult.errors.slice(0, 5));
        }
      }

      if (phase !== 1 && phase !== 'all') {
        console.log(`\n⚠️  Phase ${phase} not yet implemented`);
      }

      await legacyDb.disconnect();
      console.log('\n✅ Import completed');
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      console.error('\n✗ Import failed:', message);
      process.exit(1);
    }
  });

program
  .command('status')
  .description('Show import progress and statistics')
  .action(async () => {
    console.log('Import status...');
    // TODO: Implement status reporting
  });

program.parse();
