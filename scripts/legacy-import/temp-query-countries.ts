import { createLegacyDatabase } from './src/database/LegacyDatabase.js';

async function main() {
  const db = createLegacyDatabase();
  await db.connect();

  const countries = await db.query<{ country: string; name: string }>(
    'SELECT DISTINCT country, MIN(name) as name FROM mwnf3.countrynames GROUP BY country ORDER BY country'
  );

  console.log('\nLegacy country codes found in database:');
  console.log('========================================');
  countries.forEach((c) => {
    console.log(`${c.country}: ${c.name}`);
  });
  console.log(`\nTotal: ${countries.length} country codes`);

  await db.disconnect();
}

main().catch(console.error);
