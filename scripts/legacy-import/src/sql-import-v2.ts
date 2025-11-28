#!/usr/bin/env node
import dotenv from 'dotenv';
import { resolve } from 'path';
import chalk from 'chalk';
import { createNewDbConnection } from './database/NewDatabase.js';
import { createLegacyDatabase } from './database/LegacyDatabase.js';
import { LanguageSqlImporter } from './sql-importers/phase-00/LanguageSqlImporter.js';
import { CountrySqlImporter } from './sql-importers/phase-00/CountrySqlImporter.js';
import { ProjectSqlImporter } from './sql-importers/phase-01/ProjectSqlImporter.js';
import { MuseumSqlImporter } from './sql-importers/phase-01/MuseumSqlImporter.js';
import { InstitutionSqlImporter } from './sql-importers/phase-01/InstitutionSqlImporter.js';
import { ObjectSqlImporter } from './sql-importers/phase-01/ObjectSqlImporter.js';
import { MonumentSqlImporter } from './sql-importers/phase-01/MonumentSqlImporter.js';
import type { ImportResult } from './sql-importers/base/BaseSqlImporter.js';
import fs from 'fs';

dotenv.config({ path: resolve(process.cwd(), '.env') });

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
  const startTime = Date.now();
  const logFile = resolve(
    process.cwd(),
    'logs',
    `sql-import-${new Date().toISOString().replace(/[:.]/g, '-')}.log`
  );

  console.log(chalk.bold('='.repeat(80)));
  console.log(chalk.bold.cyan('SQL-BASED LEGACY IMPORT'));
  console.log(chalk.bold('='.repeat(80)));
  console.log(chalk.gray(`Start time: ${new Date().toISOString()}`));
  console.log(chalk.gray(`Log file: ${logFile}`));
  console.log('');

  const legacyDb = createLegacyDatabase();
  await legacyDb.connect();

  const newDb = await createNewDbConnection();

  const phases: PhaseResult[] = [];

  try {
    // Phase 0: Reference Data
    console.log(chalk.bold.yellow('\n📦 PHASE 0: Reference Data\n'));
    const phase0Start = Date.now();
    const phase0Results: PhaseResult['importers'] = [];

    // Languages
    const langImporter = new LanguageSqlImporter(newDb, tracker);
    const langStart = Date.now();
    const langResult = await langImporter.import();
    phase0Results.push({
      name: 'Languages',
      result: langResult,
      duration: Date.now() - langStart,
    });

    // Countries
    const countryImporter = new CountrySqlImporter(newDb, tracker);
    const countryStart = Date.now();
    const countryResult = await countryImporter.import();
    phase0Results.push({
      name: 'Countries',
      result: countryResult,
      duration: Date.now() - countryStart,
    });

    phases.push({
      phase: 'Phase 0: Reference Data',
      importers: phase0Results,
      totalDuration: Date.now() - phase0Start,
    });

    // Phase 1: Projects and Partners
    console.log(chalk.bold.yellow('\n📦 PHASE 1: Projects and Partners\n'));
    const phase1Start = Date.now();
    const phase1Results: PhaseResult['importers'] = [];

    // Projects (creates Contexts + Collections)
    const projectImporter = new ProjectSqlImporter(newDb, tracker, legacyDb);
    const projectStart = Date.now();
    const projectResult = await projectImporter.import();
    phase1Results.push({
      name: 'Projects',
      result: projectResult,
      duration: Date.now() - projectStart,
    });

    // Museums
    const museumImporter = new MuseumSqlImporter(newDb, tracker, legacyDb);
    const museumStart = Date.now();
    const museumResult = await museumImporter.import();
    phase1Results.push({
      name: 'Museums',
      result: museumResult,
      duration: Date.now() - museumStart,
    });

    // Institutions
    const institutionImporter = new InstitutionSqlImporter(newDb, tracker, legacyDb);
    const institutionStart = Date.now();
    const institutionResult = await institutionImporter.import();
    phase1Results.push({
      name: 'Institutions',
      result: institutionResult,
      duration: Date.now() - institutionStart,
    });

    phases.push({
      phase: 'Phase 1: Projects and Partners',
      importers: phase1Results,
      totalDuration: Date.now() - phase1Start,
    });

    // Phase 2: Items (Objects + Monuments)
    console.log(chalk.bold.yellow('\n📦 PHASE 2: Items (Objects + Monuments)\n'));
    const phase2Start = Date.now();
    const phase2Results: PhaseResult['importers'] = [];

    // Objects (creates Authors, Artists, Tags)
    const objectImporter = new ObjectSqlImporter(newDb, tracker, legacyDb);
    const objectStart = Date.now();
    const objectResult = await objectImporter.import();
    phase2Results.push({
      name: 'Objects',
      result: objectResult,
      duration: Date.now() - objectStart,
    });

    // Monuments (creates Authors, Tags)
    const monumentImporter = new MonumentSqlImporter(newDb, tracker, legacyDb);
    const monumentStart = Date.now();
    const monumentResult = await monumentImporter.import();
    phase2Results.push({
      name: 'Monuments',
      result: monumentResult,
      duration: Date.now() - monumentStart,
    });

    phases.push({
      phase: 'Phase 2: Items',
      importers: phase2Results,
      totalDuration: Date.now() - phase2Start,
    });

    // Summary
    console.log(chalk.bold.yellow('\n📊 IMPORT SUMMARY\n'));
    const totalDuration = Date.now() - startTime;

    for (const phase of phases) {
      console.log(chalk.bold(phase.phase));
      for (const imp of phase.importers) {
        const status = imp.result.success ? chalk.green('✅') : chalk.red('❌');
        console.log(
          `  ${status} ${imp.name}: ${imp.result.imported} imported, ${imp.result.skipped} skipped, ${imp.result.errors.length} errors (${(imp.duration / 1000).toFixed(2)}s)`
        );
        if (imp.result.errors.length > 0) {
          console.log(chalk.red(`     Errors: ${imp.result.errors.slice(0, 5).join(', ')}`));
          if (imp.result.errors.length > 5) {
            console.log(chalk.red(`     ... and ${imp.result.errors.length - 5} more`));
          }
        }
      }
      console.log(chalk.gray(`  Phase duration: ${(phase.totalDuration / 1000).toFixed(2)}s`));
      console.log('');
    }

    console.log(
      chalk.bold.green(`✅ Total import duration: ${(totalDuration / 1000).toFixed(2)}s`)
    );
    console.log(chalk.gray(`End time: ${new Date().toISOString()}`));

    // Write summary to log file
    const logContent = phases
      .map(
        (phase) =>
          `${phase.phase}\n` +
          phase.importers
            .map(
              (imp) =>
                `  ${imp.name}: ${imp.result.imported} imported, ${imp.result.skipped} skipped, ${imp.result.errors.length} errors`
            )
            .join('\n')
      )
      .join('\n\n');

    fs.mkdirSync(resolve(process.cwd(), 'logs'), { recursive: true });
    fs.writeFileSync(logFile, logContent);
  } finally {
    await legacyDb.disconnect();
    await newDb.end();
  }
}

main().catch((error) => {
  console.error(chalk.red('Fatal error:'), error);
  process.exit(1);
});
