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
      process.exit(0);
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
  .action(async (options) => {
    try {
      const phase = options.phase === 'all' ? 'all' : parseInt(options.phase, 10);
      const dryRun = options.dryRun === true;

      console.log('🚀 Starting import...');
      console.log(`   Phase: ${phase}`);
      console.log(`   Dry-run: ${dryRun ? 'YES' : 'NO'}`);
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

      // Initialize result variables
      let languageResult = { success: true, imported: 0, skipped: 0, errors: [] as string[] };
      let countryResult = { success: true, imported: 0, skipped: 0, errors: [] as string[] };
      let defaultContextResult = { success: true, imported: 0, skipped: 0, errors: [] as string[] };
      let projectResult = { imported: 0, skipped: 0, errors: [] as string[] };
      let partnerResult = { imported: 0, skipped: 0, errors: [] as string[] };
      let objectResult = { imported: 0, skipped: 0, errors: [] as string[] };
      let monumentResult = { imported: 0, skipped: 0, errors: [] as string[] };
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

      // Setup log file
      const { writeFileSync, mkdirSync, appendFileSync } = await import('fs');
      const logPath = `./logs/import-phase${phase}-${timestamp}.log`;

      // Helper to write to log directly (file only - console output is handled by importers)
      const writeLog = (line: string) => {
        try {
          appendFileSync(logPath, line + '\n', 'utf-8');
        } catch {
          // Ignore append errors
        }
      };

      // Create log file and write header
      try {
        mkdirSync('./logs', { recursive: true });
        const header = [
          '='.repeat(60),
          'IMPORT DETAILED LOG',
          '='.repeat(60),
          `Timestamp: ${new Date().toISOString()}`,
          `Phase: ${phase}`,
          `Dry-run: ${dryRun}`,
          '',
        ].join('\n');
        writeFileSync(logPath, header + '\n', 'utf-8');
        console.log(`📄 Writing log to: ${logPath}\n`);
      } catch (err) {
        console.error('Failed to create log file:', err);
      }

      // Setup import execution context (tracker, API client, database connection, etc.)
      const { BackwardCompatibilityTracker } = await import(
        './utils/BackwardCompatibilityTracker.js'
      );
      const tracker = new BackwardCompatibilityTracker();
      const importContext = {
        legacyDb,
        apiClient,
        tracker,
        dryRun,
        logPath,
      };

      // Import based on phase

      // Phase 0: Reference Data (always runs)
      console.log('📚 Phase 0: Reference data (Languages, Countries, Default Context)\n');
      writeLog('Phase 0: Reference data (Languages, Countries, Default Context)');
      writeLog('-'.repeat(60));

      const { LanguageImporter } = await import('./importers/phase-00/LanguageImporter.js');
      const { CountryImporter } = await import('./importers/phase-00/CountryImporter.js');
      const { DefaultContextImporter } = await import(
        './importers/phase-00/DefaultContextImporter.js'
      );

      // Languages
      console.log('Languages: ');
      writeLog('\nLanguages:');
      const languageImporter = new LanguageImporter(importContext);
      languageResult = await languageImporter.import();
      writeLog(`  Imported: ${languageResult.imported}`);
      writeLog(`  Skipped: ${languageResult.skipped}`);

      // CRITICAL: Stop if Language import failed
      if (!languageResult.success) {
        console.error('\n❌ CRITICAL ERROR: Language import failed. Cannot proceed.\n');
        writeLog('\n❌ CRITICAL ERROR: Language import failed. Cannot proceed.');
        if (languageResult.errors.length > 0) {
          languageResult.errors.forEach((err) => writeLog(`    - ${err}`));
        }
        process.exit(1);
      }

      // Countries
      console.log('Countries: ');
      writeLog('\nCountries:');
      const countryImporter = new CountryImporter(importContext);
      countryResult = await countryImporter.import();
      writeLog(`  Imported: ${countryResult.imported}`);
      writeLog(`  Skipped: ${countryResult.skipped}`);

      // CRITICAL: Stop if Country import failed
      if (!countryResult.success) {
        console.error('\n❌ CRITICAL ERROR: Country import failed. Cannot proceed.\n');
        writeLog('\n❌ CRITICAL ERROR: Country import failed. Cannot proceed.');
        if (countryResult.errors.length > 0) {
          countryResult.errors.forEach((err) => writeLog(`    - ${err}`));
        }
        process.exit(1);
      }

      // Default Context
      console.log('Default Context: ');
      writeLog('\nDefault Context:');
      const defaultContextImporter = new DefaultContextImporter(importContext);
      defaultContextResult = await defaultContextImporter.import();
      writeLog(`  Imported: ${defaultContextResult.imported}`);
      writeLog(`  Skipped: ${defaultContextResult.skipped}`);

      // CRITICAL: Stop if Default Context import failed
      if (!defaultContextResult.success) {
        console.error('\n❌ CRITICAL ERROR: Default Context import failed. Cannot proceed.\n');
        writeLog('\n❌ CRITICAL ERROR: Default Context import failed. Cannot proceed.');
        if (defaultContextResult.errors.length > 0) {
          defaultContextResult.errors.forEach((err) => writeLog(`    - ${err}`));
        }
        process.exit(1);
      }

      if (phase === 1 || phase === 'all') {
        console.log('📦 Phase 1: Core data (Projects, Partners)\n');
        writeLog('\nPhase 1: Core data (Projects, Partners)');
        writeLog('-'.repeat(60));

        const { PartnerImporter } = await import('./importers/phase-01/PartnerImporter.js');
        const { ProjectImporter } = await import('./importers/phase-01/ProjectImporter.js');

        // Import projects first
        console.log('Projects: ');
        writeLog('\nProjects Import:');
        const projectImporter = new ProjectImporter(importContext);
        projectResult = await projectImporter.import();
        writeLog(`  Imported: ${projectResult.imported}`);
        writeLog(`  Skipped: ${projectResult.skipped}`);
        writeLog(`  Errors: ${projectResult.errors.length}`);
        if (projectResult.errors.length > 0) {
          writeLog('  Error details:');
          projectResult.errors.forEach((err) => writeLog(`    - ${err}`));
        }

        // CRITICAL: Stop if Project import had errors (partners depend on projects)
        if (projectResult.errors.length > 0) {
          console.error('\n❌ CRITICAL ERROR: Project import failed. Cannot proceed with Partners.\n');
          writeLog('\n❌ CRITICAL ERROR: Project import failed. Cannot proceed.');
          process.exit(1);
        }

        // Import partners (museums + institutions)
        console.log('Partners: ');
        writeLog('\nPartners Import (Museums + Institutions):');
        const partnerImporter = new PartnerImporter(importContext);
        partnerResult = await partnerImporter.import();
        writeLog(`  Imported: ${partnerResult.imported}`);
        writeLog(`  Skipped: ${partnerResult.skipped}`);
        writeLog(`  Errors: ${partnerResult.errors.length}`);
        if (partnerResult.errors.length > 0) {
          writeLog('  Error details:');
          partnerResult.errors.forEach((err) => writeLog(`    - ${err}`));
        }

        // CRITICAL: Stop if Partner import had errors (items depend on partners)
        if (partnerResult.errors.length > 0) {
          console.error('\n❌ CRITICAL ERROR: Partner import failed. Cannot proceed with Items.\n');
          writeLog('\n❌ CRITICAL ERROR: Partner import failed. Cannot proceed.');
          process.exit(1);
        }

        // Import items (objects, monuments)
        const { ObjectImporter } = await import('./importers/phase-01/ObjectImporter.js');
        const { MonumentImporter } = await import('./importers/phase-01/MonumentImporter.js');

        // Import objects
        console.log('Objects: ');
        writeLog('\nObjects Import:');
        const objectImporter = new ObjectImporter(importContext);
        objectResult = await objectImporter.import();
        writeLog(`  Imported: ${objectResult.imported}`);
        writeLog(`  Skipped: ${objectResult.skipped}`);
        writeLog(`  Errors: ${objectResult.errors.length}`);
        if (objectResult.errors.length > 0) {
          writeLog('  Error details (first 10):');
          objectResult.errors.slice(0, 10).forEach((err) => writeLog(`    - ${err}`));
        }

        // Import monuments
        console.log('Monuments: ');
        writeLog('\nMonuments Import:');
        const monumentImporter = new MonumentImporter(importContext);
        monumentResult = await monumentImporter.import();
        writeLog(`  Imported: ${monumentResult.imported}`);
        writeLog(`  Skipped: ${monumentResult.skipped}`);
        writeLog(`  Errors: ${monumentResult.errors.length}`);
        if (monumentResult.errors.length > 0) {
          writeLog('  Error details (first 10):');
          monumentResult.errors.slice(0, 10).forEach((err) => writeLog(`    - ${err}`));
        }
      }

      if (phase !== 1 && phase !== 'all') {
        console.log(`\n⚠️  Phase ${phase} not yet implemented`);
      }

      await legacyDb.disconnect();

      // Generate console summary
      const totalImported =
        (languageResult?.imported || 0) +
        (countryResult?.imported || 0) +
        (defaultContextResult?.imported || 0) +
        (projectResult?.imported || 0) +
        (partnerResult?.imported || 0) +
        (objectResult?.imported || 0) +
        (monumentResult?.imported || 0);
      const totalSkipped =
        (languageResult?.skipped || 0) +
        (countryResult?.skipped || 0) +
        (defaultContextResult?.skipped || 0) +
        (projectResult?.skipped || 0) +
        (partnerResult?.skipped || 0) +
        (objectResult?.skipped || 0) +
        (monumentResult?.skipped || 0);
      const totalErrors =
        (languageResult?.errors.length || 0) +
        (countryResult?.errors.length || 0) +
        (defaultContextResult?.errors.length || 0) +
        (projectResult?.errors.length || 0) +
        (partnerResult?.errors.length || 0) +
        (objectResult?.errors.length || 0) +
        (monumentResult?.errors.length || 0);

      console.log('='.repeat(60));
      console.log('IMPORT SUMMARY');
      console.log('='.repeat(60));
      console.log(`  Total Imported: ${totalImported}`);
      console.log(`  Total Skipped: ${totalSkipped}`);
      console.log(`  Total Errors: ${totalErrors}`);
      console.log(
        `  Status: ${totalErrors === 0 ? '\x1b[32m✓ SUCCESS\x1b[0m' : '\x1b[31m✗ FAILED\x1b[0m'}`
      );
      console.log('='.repeat(60) + '\n');

      // Complete detailed log
      writeLog('');
      writeLog('='.repeat(60));
      writeLog('FINAL SUMMARY');
      writeLog('='.repeat(60));
      writeLog(`Total Imported: ${totalImported}`);
      writeLog(`Total Skipped: ${totalSkipped}`);
      writeLog(`Total Errors: ${totalErrors}`);
      writeLog(`Status: ${totalErrors === 0 ? 'SUCCESS' : 'FAILED'}`);
      writeLog('='.repeat(60));

      console.log(`📄 Log saved to: ${logPath}`);

      if (totalErrors > 0) {
        process.exit(1);
      }
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      const stack = error instanceof Error ? error.stack : '';
      console.error('\n✗ Import failed:', message);
      if (stack) {
        console.error('\nStack trace:');
        console.error(stack);
      }

      // Write error log
      try {
        const { writeFileSync, mkdirSync } = await import('fs');
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const logPath = `./logs/import-error-${timestamp}.log`;
        mkdirSync('./logs', { recursive: true });
        writeFileSync(logPath, `Error: ${message}\n\nStack:\n${stack}`, 'utf-8');
        console.error(`\n📄 Error log written to: ${logPath}`);
      } catch (err) {
        console.error('Failed to write error log:', err);
      }

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
