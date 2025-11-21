import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { createLegacyDatabase } from '../../src/database/LegacyDatabase.js';
import { createApiClient } from '../../src/api/InventoryApiClient.js';
import { PartnerImporter } from '../../src/importers/phase-01/PartnerImporter.js';
import { ProjectImporter } from '../../src/importers/phase-01/ProjectImporter.js';
import { BackwardCompatibilityTracker } from '../../src/utils/BackwardCompatibilityTracker.js';

/**
 * E2E Test: Phase 1 Import with REAL database and API
 *
 * ⚠️ DESTRUCTIVE TEST - Creates real data in the database!
 *
 * Run explicitly with:
 *   npm test tests/e2e/phase-01-real-import.test.ts
 *
 * Prerequisites:
 * 1. Legacy database accessible with credentials in .env
 * 2. API running and accessible (use `npx tsx src/index.ts login` first)
 * 3. Database should be in clean state or able to handle duplicates
 *
 * This test:
 * - Connects to REAL legacy MySQL database
 * - Connects to REAL API
 * - Imports ACTUAL data (limited to 2 records per entity)
 * - Verifies data was created via API
 */
describe.skip('E2E: Phase 1 Real Import', () => {
  const legacyDb = createLegacyDatabase();
  const apiClient = createApiClient();
  let tracker: BackwardCompatibilityTracker;

  beforeAll(async () => {
    // Connect to legacy database
    await legacyDb.connect();

    // Verify API is accessible
    const isConnected = await apiClient.testConnection();
    if (!isConnected) {
      throw new Error('API connection failed. Run "npx tsx src/index.ts login" and ensure API is running.');
    }

    tracker = new BackwardCompatibilityTracker();
  });

  afterAll(async () => {
    await legacyDb.disconnect();
  });

  it('should import projects from legacy database', async () => {
    const context = {
      legacyDb,
      apiClient,
      tracker,
      dryRun: false,
      limit: 2, // Import only 2 projects
    };

    const importer = new ProjectImporter(context);
    const result = await importer.import();

    // Verify import succeeded
    expect(result.success).toBe(true);
    expect(result.imported).toBeGreaterThan(0);
    expect(result.imported).toBeLessThanOrEqual(2);

    // Verify contexts were created via API
    const contextsResponse = await apiClient.context.contextIndex();
    expect(contextsResponse.data.data.length).toBeGreaterThan(0);

    // Verify collections were created
    const collectionsResponse = await apiClient.collection.collectionIndex();
    expect(collectionsResponse.data.data.length).toBeGreaterThan(0);

    console.log('\n✓ Projects imported successfully:');
    console.log(`  - Contexts created: ${result.imported}`);
    console.log(`  - Collections created: ${result.imported}`);
    console.log(`  - Tracker entries: ${tracker.getAll().length}`);
  }, 30000); // 30 second timeout for database operations

  it('should import partners (museums + institutions) from legacy database', async () => {
    const context = {
      legacyDb,
      apiClient,
      tracker,
      dryRun: false,
      limit: 2, // Import only 2 partners total
    };

    const importer = new PartnerImporter(context);
    const result = await importer.import();

    // Verify import succeeded
    expect(result.success).toBe(true);
    expect(result.imported).toBeGreaterThan(0);
    expect(result.imported).toBeLessThanOrEqual(4); // 2 museums + 2 institutions max

    // Verify partners were created via API
    const partnersResponse = await apiClient.partner.partnerIndex();
    expect(partnersResponse.data.data.length).toBeGreaterThan(0);

    // Find at least one museum and one institution
    const partners = partnersResponse.data.data;
    const hasMuseum = partners.some((p) => p.type === 'museum');
    const hasInstitution = partners.some((p) => p.type === 'institution');

    console.log('\n✓ Partners imported successfully:');
    console.log(`  - Total partners: ${result.imported}`);
    console.log(`  - Has museum: ${hasMuseum}`);
    console.log(`  - Has institution: ${hasInstitution}`);
    console.log(`  - Tracker entries: ${tracker.getAll().length}`);

    // At least one type should exist
    expect(hasMuseum || hasInstitution).toBe(true);
  }, 30000);

  it('should handle deduplication on repeated imports', async () => {
    const context = {
      legacyDb,
      apiClient,
      tracker,
      dryRun: false,
      limit: 1,
    };

    // First import
    const importer1 = new PartnerImporter(context);
    const result1 = await importer1.import();

    // Second import with same tracker (should skip)
    const importer2 = new PartnerImporter(context);
    const result2 = await importer2.import();

    // Second import should skip everything
    expect(result2.skipped).toBeGreaterThanOrEqual(result1.imported);
    expect(result2.imported).toBe(0);

    console.log('\n✓ Deduplication works:');
    console.log(`  - First import: ${result1.imported} imported`);
    console.log(`  - Second import: ${result2.skipped} skipped, ${result2.imported} imported`);
  }, 30000);
});

/**
 * To enable this test, remove .skip and run:
 *
 *   npm test tests/e2e/phase-01-real-import.test.ts
 *
 * Or run in watch mode:
 *
 *   npm run test:watch tests/e2e/phase-01-real-import.test.ts
 */
