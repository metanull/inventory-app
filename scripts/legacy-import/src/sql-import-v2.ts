#!/usr/bin/env node
/**
 * SQL-Based Legacy Importer
 *
 * Imports data directly from legacy database to new database using SQL queries.
 * This is MUCH faster than the API-based importer but must implement the same logic.
 *
 * Import order matches API-based importer (index.ts):
 * Phase 0: Languages, Countries, Default Context
 * Phase 1: Projects (creates Contexts + Collections), Partners (Museums, Institutions)
 * Phase 2: Items (Objects, Monuments) with Authors, Artists, Tags
 *
 * Options:
 * --collect-samples: Collect sample data for test fixtures
 * --sample-size <number>: Number of success samples per category (default: 20)
 * --sample-db <path>: Path to sample database file (default: ./test-fixtures/samples.sqlite)
 */
import dotenv from 'dotenv';
import { resolve } from 'path';
import chalk from 'chalk';
import { createNewDbConnection } from './database/NewDatabase.js';
import { createLegacyDatabase } from './database/LegacyDatabase.js';
import { LanguageSqlImporter } from './sql-importers/phase-00/LanguageSqlImporter.js';
import { LanguageTranslationSqlImporter } from './sql-importers/phase-00/LanguageTranslationSqlImporter.js';
import { CountrySqlImporter } from './sql-importers/phase-00/CountrySqlImporter.js';
import { CountryTranslationSqlImporter } from './sql-importers/phase-00/CountryTranslationSqlImporter.js';
import { ProjectSqlImporter } from './sql-importers/phase-01/ProjectSqlImporter.js';
import { MuseumSqlImporter } from './sql-importers/phase-01/MuseumSqlImporter.js';
import { InstitutionSqlImporter } from './sql-importers/phase-01/InstitutionSqlImporter.js';
import { ObjectSqlImporter } from './sql-importers/phase-01/ObjectSqlImporter.js';
import { MonumentSqlImporter } from './sql-importers/phase-01/MonumentSqlImporter.js';
import type { ImportResult } from './sql-importers/base/BaseSqlImporter.js';
import { LogWriter } from './sql-importers/utils/LogWriter.js';
import { SampleCollector } from './utils/SampleCollector.js';
import * as fs from 'fs';

dotenv.config({ path: resolve(process.cwd(), '.env') });

// Parse command line arguments
const args = process.argv.slice(2);
const collectSamples = args.includes('--collect-samples');
const sampleSizeIdx = args.indexOf('--sample-size');
const sampleSize = sampleSizeIdx >= 0 ? parseInt(args[sampleSizeIdx + 1] || '20', 10) : 20;
const sampleDbIdx = args.indexOf('--sample-db');
const sampleDbPath =
  sampleDbIdx >= 0 && args[sampleDbIdx + 1]
    ? args[sampleDbIdx + 1]
    : './test-fixtures/samples.sqlite';
const sampleDb = resolve(__dirname, '..', sampleDbPath!);

const tracker = new Map<string, string>();

interface PhaseResult {
  phase: string;
  importers: Array<{
    name: string;
    result: ImportResult;
    duration: number;
  }>;
  totalDuration: number;
}

async function main() {
  // Initialize logging
  const logger = new LogWriter('logs');

  console.log(chalk.bold('='.repeat(80)));
  console.log(chalk.bold.cyan('SQL-BASED LEGACY IMPORT'));
  console.log(chalk.bold('='.repeat(80)));
  console.log(chalk.gray(`Start time: ${new Date().toISOString()}`));
  console.log(chalk.gray(`Log file: ${logger.getLogFilePath()}`));
  if (collectSamples) {
    console.log(chalk.cyan(`🧪 Sample collection enabled: ${sampleDb}`));
    console.log(chalk.cyan(`   Sample size: ${sampleSize} per category`));
  }
  console.log('');

  // Initialize sample collector if requested
  let sampleCollector: SampleCollector | undefined;
  if (collectSamples) {
    // Auto-delete existing samples database to start fresh
    if (fs.existsSync(sampleDb)) {
      fs.unlinkSync(sampleDb);
      logger.log('🗑️  Deleted existing sample database');
    }

    sampleCollector = new SampleCollector({
      enabled: true,
      dbPath: sampleDb,
      sampleSize: sampleSize,
      collectAllWarnings: true,
      collectAllEdgeCases: true,
      collectAllFoundation: true, // Collect ALL languages and countries
    });
    logger.log(`Sample collection initialized: ${sampleDb}`);
  }

  const legacyDb = createLegacyDatabase();
  await legacyDb.connect();
  logger.log('Connected to legacy database');

  const newDb = await createNewDbConnection();
  logger.log('Connected to new database');

  const phases: PhaseResult[] = [];

  try {
    // Phase 0: Reference Data
    logger.logPhaseStart('PHASE 0: Reference Data');
    const phase0Start = Date.now();
    const phase0Results: PhaseResult['importers'] = [];

    // Languages
    logger.logImporterStart('LanguageSqlImporter');
    const langImporter = new LanguageSqlImporter(newDb, tracker, sampleCollector);
    const langStart = Date.now();
    const langResult = await langImporter.import();
    const langDuration = Date.now() - langStart;
    logger.logImporterComplete(
      'LanguageSqlImporter',
      langResult.imported,
      langResult.skipped,
      langResult.errors.length,
      langDuration
    );
    if (langResult.errors.length > 0) {
      langResult.errors.forEach((err) => logger.logError('LanguageSqlImporter', err));
    }
    phase0Results.push({
      name: 'Languages',
      result: langResult,
      duration: langDuration,
    });

    // Language Translations
    logger.logImporterStart('LanguageTranslationSqlImporter');
    const langTransImporter = new LanguageTranslationSqlImporter(
      newDb,
      tracker,
      legacyDb,
      sampleCollector
    );
    const langTransStart = Date.now();
    const langTransResult = await langTransImporter.import();
    const langTransDuration = Date.now() - langTransStart;
    logger.logImporterComplete(
      'LanguageTranslationSqlImporter',
      langTransResult.imported,
      langTransResult.skipped,
      langTransResult.errors.length,
      langTransDuration
    );
    if (langTransResult.errors.length > 0) {
      langTransResult.errors.forEach((err) =>
        logger.logError('LanguageTranslationSqlImporter', err)
      );
    }
    phase0Results.push({
      name: 'Language Translations',
      result: langTransResult,
      duration: langTransDuration,
    });

    // Countries
    logger.logImporterStart('CountrySqlImporter');
    const countryImporter = new CountrySqlImporter(newDb, tracker, sampleCollector);
    const countryStart = Date.now();
    const countryResult = await countryImporter.import();
    const countryDuration = Date.now() - countryStart;
    logger.logImporterComplete(
      'CountrySqlImporter',
      countryResult.imported,
      countryResult.skipped,
      countryResult.errors.length,
      countryDuration
    );
    if (countryResult.errors.length > 0) {
      countryResult.errors.forEach((err) => logger.logError('CountrySqlImporter', err));
    }
    phase0Results.push({
      name: 'Countries',
      result: countryResult,
      duration: countryDuration,
    });

    // Country Translations
    logger.logImporterStart('CountryTranslationSqlImporter');
    const countryTransImporter = new CountryTranslationSqlImporter(
      newDb,
      tracker,
      legacyDb,
      sampleCollector
    );
    const countryTransStart = Date.now();
    const countryTransResult = await countryTransImporter.import();
    const countryTransDuration = Date.now() - countryTransStart;
    logger.logImporterComplete(
      'CountryTranslationSqlImporter',
      countryTransResult.imported,
      countryTransResult.skipped,
      countryTransResult.errors.length,
      countryTransDuration
    );
    if (countryTransResult.errors.length > 0) {
      countryTransResult.errors.forEach((err) =>
        logger.logError('CountryTranslationSqlImporter', err)
      );
    }
    phase0Results.push({
      name: 'Country Translations',
      result: countryTransResult,
      duration: countryTransDuration,
    });

    const phase0Duration = Date.now() - phase0Start;
    phases.push({
      phase: 'Phase 0: Reference Data',
      importers: phase0Results,
      totalDuration: phase0Duration,
    });
    logger.log(`Phase 0 completed in ${(phase0Duration / 1000).toFixed(2)}s`);

    // Phase 1: Projects and Partners
    logger.logPhaseStart('PHASE 1: Projects and Partners');
    const phase1Start = Date.now();
    const phase1Results: PhaseResult['importers'] = [];

    // Projects (creates Contexts + Collections)
    logger.logImporterStart('ProjectSqlImporter');
    const projectImporter = new ProjectSqlImporter(newDb, tracker, legacyDb, sampleCollector);
    const projectStart = Date.now();
    const projectResult = await projectImporter.import();
    const projectDuration = Date.now() - projectStart;
    logger.logImporterComplete(
      'ProjectSqlImporter',
      projectResult.imported,
      projectResult.skipped,
      projectResult.errors.length,
      projectDuration
    );
    if (projectResult.errors.length > 0) {
      projectResult.errors.forEach((err) => logger.logError('ProjectSqlImporter', err));
    }
    phase1Results.push({
      name: 'Projects',
      result: projectResult,
      duration: projectDuration,
    });

    // Museums
    logger.logImporterStart('MuseumSqlImporter');
    const museumImporter = new MuseumSqlImporter(newDb, tracker, legacyDb, sampleCollector);
    const museumStart = Date.now();
    const museumResult = await museumImporter.import();
    const museumDuration = Date.now() - museumStart;
    logger.logImporterComplete(
      'MuseumSqlImporter',
      museumResult.imported,
      museumResult.skipped,
      museumResult.errors.length,
      museumDuration
    );
    if (museumResult.errors.length > 0) {
      museumResult.errors.forEach((err) => logger.logError('MuseumSqlImporter', err));
    }
    phase1Results.push({
      name: 'Museums',
      result: museumResult,
      duration: museumDuration,
    });

    // Institutions
    logger.logImporterStart('InstitutionSqlImporter');
    const institutionImporter = new InstitutionSqlImporter(
      newDb,
      tracker,
      legacyDb,
      sampleCollector
    );
    const institutionStart = Date.now();
    const institutionResult = await institutionImporter.import();
    const institutionDuration = Date.now() - institutionStart;
    logger.logImporterComplete(
      'InstitutionSqlImporter',
      institutionResult.imported,
      institutionResult.skipped,
      institutionResult.errors.length,
      institutionDuration
    );
    if (institutionResult.errors.length > 0) {
      institutionResult.errors.forEach((err) => logger.logError('InstitutionSqlImporter', err));
    }
    phase1Results.push({
      name: 'Institutions',
      result: institutionResult,
      duration: institutionDuration,
    });

    const phase1Duration = Date.now() - phase1Start;
    phases.push({
      phase: 'Phase 1: Projects and Partners',
      importers: phase1Results,
      totalDuration: phase1Duration,
    });
    logger.log(`Phase 1 completed in ${(phase1Duration / 1000).toFixed(2)}s`);

    // Phase 2: Items (Objects + Monuments)
    logger.logPhaseStart('PHASE 2: Items (Objects + Monuments)');
    const phase2Start = Date.now();
    const phase2Results: PhaseResult['importers'] = [];

    // Objects (creates Authors, Artists, Tags)
    logger.logImporterStart('ObjectSqlImporter');
    const objectImporter = new ObjectSqlImporter(newDb, tracker, legacyDb, sampleCollector);
    const objectStart = Date.now();
    const objectResult = await objectImporter.import();
    const objectDuration = Date.now() - objectStart;
    logger.logImporterComplete(
      'ObjectSqlImporter',
      objectResult.imported,
      objectResult.skipped,
      objectResult.errors.length,
      objectDuration
    );
    if (objectResult.errors.length > 0) {
      objectResult.errors.slice(0, 10).forEach((err) => logger.logError('ObjectSqlImporter', err));
      if (objectResult.errors.length > 10) {
        logger.logError(
          'ObjectSqlImporter',
          `... and ${objectResult.errors.length - 10} more errors`
        );
      }
    }
    phase2Results.push({
      name: 'Objects',
      result: objectResult,
      duration: objectDuration,
    });

    // Monuments (creates Authors, Tags)
    logger.logImporterStart('MonumentSqlImporter');
    const monumentImporter = new MonumentSqlImporter(newDb, tracker, legacyDb, sampleCollector);
    const monumentStart = Date.now();
    const monumentResult = await monumentImporter.import();
    const monumentDuration = Date.now() - monumentStart;
    logger.logImporterComplete(
      'MonumentSqlImporter',
      monumentResult.imported,
      monumentResult.skipped,
      monumentResult.errors.length,
      monumentDuration
    );
    if (monumentResult.errors.length > 0) {
      monumentResult.errors
        .slice(0, 10)
        .forEach((err) => logger.logError('MonumentSqlImporter', err));
      if (monumentResult.errors.length > 10) {
        logger.logError(
          'MonumentSqlImporter',
          `... and ${monumentResult.errors.length - 10} more errors`
        );
      }
    }
    phase2Results.push({
      name: 'Monuments',
      result: monumentResult,
      duration: monumentDuration,
    });

    const phase2Duration = Date.now() - phase2Start;
    phases.push({
      phase: 'Phase 2: Items',
      importers: phase2Results,
      totalDuration: phase2Duration,
    });
    logger.log(`Phase 2 completed in ${(phase2Duration / 1000).toFixed(2)}s`);

    // Summary
    const phaseSummary = phases.map((phase) => ({
      phase: phase.phase,
      duration: phase.totalDuration,
      imported: phase.importers.reduce((sum, imp) => sum + imp.result.imported, 0),
      skipped: phase.importers.reduce((sum, imp) => sum + imp.result.skipped, 0),
      errors: phase.importers.reduce((sum, imp) => sum + imp.result.errors.length, 0),
    }));

    logger.logSummary(phaseSummary);

    // Console error details
    for (const phase of phases) {
      for (const imp of phase.importers) {
        if (imp.result.errors.length > 0) {
          console.log(chalk.red(`\n${imp.name} errors (showing first 5):`));
          imp.result.errors.slice(0, 5).forEach((err) => console.log(chalk.red(`  - ${err}`)));
          if (imp.result.errors.length > 5) {
            console.log(chalk.red(`  ... and ${imp.result.errors.length - 5} more (see log file)`));
          }
        }
      }
    }

    // Show sample collection statistics
    if (sampleCollector) {
      const stats = sampleCollector.getStats();
      const totalSamples = Object.values(stats).reduce((sum, count) => sum + count, 0);

      console.log('');
      console.log(chalk.cyan('🧪 Sample Collection Summary'));
      console.log(chalk.cyan(`   Total samples: ${totalSamples}`));
      console.log(chalk.cyan(`   Database: ${sampleDb}`));

      // Show breakdown by category
      const categories = Object.entries(stats).sort(([, a], [, b]) => b - a);
      if (categories.length > 0 && categories.length <= 20) {
        console.log(chalk.cyan('   Breakdown:'));
        categories.forEach(([category, count]) => {
          console.log(chalk.cyan(`     - ${category}: ${count}`));
        });
      }

      sampleCollector.close();
      logger.log(`Sample collection complete: ${totalSamples} samples collected`);
    }
  } catch (error) {
    const errorMessage = error instanceof Error ? error.message : String(error);
    logger.log(`FATAL ERROR: ${errorMessage}`);
    if (error instanceof Error && error.stack) {
      logger.log(`Stack trace: ${error.stack}`);
    }
    throw error;
  } finally {
    logger.log('Disconnecting from databases...');
    await legacyDb.disconnect();
    await newDb.end();
    logger.log('Import process ended');
  }
}

main().catch((error) => {
  console.error(chalk.red('Fatal error:'), error);
  process.exit(1);
});
