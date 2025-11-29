#!/usr/bin/env tsx
/**
 * Generic Data Dump Tool
 *
 * Dumps data from legacy database and new database for comparison
 */

import dotenv from 'dotenv';
import { resolve } from 'path';
import * as fs from 'fs';
import { createLegacyDatabase } from './database/LegacyDatabase.js';
import { createNewDbConnection } from './database/NewDatabase.js';

dotenv.config({ path: resolve(process.cwd(), '.env') });

interface DumpConfig {
  name: string;
  description: string;

  // Legacy source (either SQL query or JSON file path)
  legacySource: {
    type: 'sql' | 'json';
    query?: string; // SQL query for legacy DB
    jsonPath?: string; // Path to JSON file (relative to project root)
  };

  // New database queries (can be multiple tables)
  newDbQueries: Array<{
    name: string; // Output filename prefix (e.g., 'items', 'translations')
    query: string;
  }>;
}

const DUMP_CONFIGS: Record<string, DumpConfig> = {
  languages: {
    name: 'Languages',
    description: 'Languages reference data (seeded from JSON)',
    legacySource: {
      type: 'json',
      jsonPath: 'database/seeders/data/languages.json',
    },
    newDbQueries: [
      {
        name: 'languages',
        query: `
          SELECT id, internal_name, backward_compatibility, is_default, created_at, updated_at
          FROM languages
          ORDER BY id
        `,
      },
      {
        name: 'language-translations',
        query: `
          SELECT lt.*, l1.internal_name as language_name, l2.internal_name as display_language_name
          FROM language_translations lt
          JOIN languages l1 ON lt.language_id = l1.id
          JOIN languages l2 ON lt.display_language_id = l2.id
          ORDER BY lt.language_id, lt.display_language_id
        `,
      },
    ],
  },

  countries: {
    name: 'Countries',
    description: 'Countries reference data (seeded from JSON)',
    legacySource: {
      type: 'json',
      jsonPath: 'database/seeders/data/countries.json',
    },
    newDbQueries: [
      {
        name: 'countries',
        query: `
          SELECT id, internal_name, backward_compatibility, created_at, updated_at
          FROM countries
          ORDER BY id
        `,
      },
      {
        name: 'country-translations',
        query: `
          SELECT ct.*, c.internal_name as country_name
          FROM country_translations ct
          JOIN countries c ON ct.country_id = c.id
          ORDER BY ct.country_id, ct.language_id
        `,
      },
    ],
  },

  projects: {
    name: 'Projects',
    description: 'Projects → Contexts + Collections',
    legacySource: {
      type: 'sql',
      query: 'SELECT * FROM mwnf3.projects ORDER BY project_id',
    },
    newDbQueries: [
      {
        name: 'contexts',
        query: `
          SELECT id, internal_name, backward_compatibility, is_default, created_at, updated_at
          FROM contexts
          WHERE backward_compatibility LIKE 'mwnf3:projects:%'
          ORDER BY backward_compatibility
        `,
      },
      {
        name: 'collections',
        query: `
          SELECT id, internal_name, type, parent_id, backward_compatibility, created_at, updated_at
          FROM collections
          WHERE backward_compatibility LIKE 'mwnf3:projects:%'
          ORDER BY backward_compatibility
        `,
      },
      {
        name: 'collection-translations',
        query: `
          SELECT ct.*, c.internal_name as collection_name, c.backward_compatibility
          FROM collection_translations ct
          JOIN collections c ON ct.collection_id = c.id
          WHERE c.backward_compatibility LIKE 'mwnf3:projects:%'
          ORDER BY c.backward_compatibility, ct.language_id, ct.context_id
        `,
      },
    ],
  },

  contexts: {
    name: 'Contexts (All)',
    description: 'All Contexts in the system',
    legacySource: {
      type: 'sql',
      query: 'SELECT * FROM mwnf3.projects ORDER BY project_id',
    },
    newDbQueries: [
      {
        name: 'contexts',
        query: `
          SELECT id, internal_name, backward_compatibility, is_default, created_at, updated_at
          FROM contexts
          ORDER BY backward_compatibility
        `,
      },
    ],
  },

  'partners-museums': {
    name: 'Partners (Museums)',
    description: 'Museums → Partners',
    legacySource: {
      type: 'sql',
      query: `SELECT * FROM mwnf3.museums`,
    },
    newDbQueries: [
      {
        name: 'partners',
        query: `
          SELECT id, internal_name, type, country_id, backward_compatibility, created_at, updated_at
          FROM partners
          WHERE backward_compatibility LIKE 'mwnf3:museums:%'
          ORDER BY backward_compatibility
        `,
      },
    ],
  },

  'partners-institutions': {
    name: 'Partners (Institutions)',
    description: 'Institutions → Partners',
    legacySource: {
      type: 'sql',
      query: `SELECT * FROM mwnf3.institutions`,
    },
    newDbQueries: [
      {
        name: 'partners',
        query: `
          SELECT id, internal_name, type, country_id, backward_compatibility, created_at, updated_at
          FROM partners
          WHERE backward_compatibility LIKE 'mwnf3:institutions:%'
          ORDER BY backward_compatibility
        `,
      },
    ],
  },

  partners: {
    name: 'Partners (All)',
    description: 'All Partners with translations (museums and institutions combined)',
    legacySource: {
      type: 'sql',
      query: `
        (SELECT * FROM mwnf3.museums)
        UNION ALL
        (SELECT * FROM mwnf3.institutions)
      `,
    },
    newDbQueries: [
      {
        name: 'partners',
        query: `
          SELECT id, internal_name, type, country_id, project_id, latitude, longitude, 
                 map_zoom, visible, backward_compatibility, created_at, updated_at
          FROM partners
          ORDER BY backward_compatibility
        `,
      },
      {
        name: 'partner-translations',
        query: `
          SELECT pt.*, p.internal_name as partner_name, p.type as partner_type, p.backward_compatibility
          FROM partner_translations pt
          JOIN partners p ON pt.partner_id = p.id
          ORDER BY p.backward_compatibility, pt.language_id, pt.context_id
        `,
      },
    ],
  },

  'partners-with-translations': {
    name: 'Partners With Translations',
    description: 'Complete partner data with all translation fields for detailed analysis',
    legacySource: {
      type: 'sql',
      query: `
        SELECT m.museum_id as id, m.country, 'museum' as type, m.name, m.project_id,
               mn.lang, mn.name as translation_name, mn.description, mn.city as translation_city,
               mn.how_to_reach, mn.opening_hours,
               m.address, m.postal_address, m.phone, m.email, m.url
        FROM mwnf3.museums m
        LEFT JOIN mwnf3.museumnames mn ON m.museum_id = mn.museum_id AND m.country = mn.country
        UNION ALL
        SELECT i.institution_id as id, i.country, 'institution' as type, i.name, NULL as project_id,
               inames.lang, inames.name as translation_name, inames.description, NULL as translation_city,
               NULL as how_to_reach, NULL as opening_hours,
               i.address, NULL as postal_address, i.phone, i.email, i.url
        FROM mwnf3.institutions i
        LEFT JOIN mwnf3.institutionnames inames ON i.institution_id = inames.institution_id AND i.country = inames.country
        ORDER BY type, id, country, lang
      `,
    },
    newDbQueries: [
      {
        name: 'partners-with-translations',
        query: `
          SELECT p.id, p.internal_name, p.type, p.country_id, p.project_id, p.visible,
                 p.backward_compatibility, p.created_at, p.updated_at,
                 pt.language_id, pt.context_id, pt.name as translation_name,
                 pt.description, pt.city_display, pt.address_line_1, pt.address_line_2,
                 pt.postal_code, pt.address_notes, pt.contact_name, pt.contact_email_general,
                 pt.contact_email_press, pt.contact_phone, pt.contact_website, pt.contact_notes,
                 pt.extra as translation_extra
          FROM partners p
          LEFT JOIN partner_translations pt ON p.id = pt.partner_id
          ORDER BY p.backward_compatibility, pt.language_id
        `,
      },
    ],
  },

  authors: {
    name: 'Authors',
    description: 'All Authors (should be extracted from legacy artist/preparedby fields)',
    legacySource: {
      type: 'sql',
      query: `
        SELECT DISTINCT artist as name FROM mwnf3.objects WHERE artist IS NOT NULL AND artist != '' AND artist != ' '
        ORDER BY name
      `,
    },
    newDbQueries: [
      {
        name: 'authors',
        query: `
          SELECT id, internal_name, backward_compatibility, created_at, updated_at
          FROM authors
          ORDER BY internal_name
        `,
      },
    ],
  },

  tags: {
    name: 'Tags',
    description: 'All Tags (extracted from keywords, materials, dynasty, and artists)',
    legacySource: {
      type: 'sql',
      query: `
        SELECT 'keywords' as source, keywords as value FROM mwnf3.objects WHERE keywords IS NOT NULL AND keywords != ''
        UNION ALL
        SELECT 'materials' as source, materials as value FROM mwnf3.objects WHERE materials IS NOT NULL AND materials != ''
        UNION ALL
        SELECT 'dynasty' as source, dynasty as value FROM mwnf3.objects WHERE dynasty IS NOT NULL AND dynasty != ''
        UNION ALL
        SELECT 'artist' as source, artist as value FROM mwnf3.objects WHERE artist IS NOT NULL AND artist != ''
        UNION ALL
        SELECT 'keywords' as source, keywords as value FROM mwnf3.monuments WHERE keywords IS NOT NULL AND keywords != ''
        UNION ALL
        SELECT 'dynasty' as source, dynasty as value FROM mwnf3.monuments WHERE dynasty IS NOT NULL AND dynasty != ''
        ORDER BY source, value
      `,
    },
    newDbQueries: [
      {
        name: 'tags',
        query: `
          SELECT id, internal_name, backward_compatibility, created_at, updated_at
          FROM tags
          ORDER BY backward_compatibility
        `,
      },
      {
        name: 'item-tags',
        query: `
          SELECT it.item_id, it.tag_id, it.created_at, it.updated_at,
                 i.type as item_type, i.backward_compatibility as item_backward_compatibility, 
                 t.internal_name as tag_name, t.backward_compatibility as tag_backward_compatibility
          FROM item_tag it
          JOIN items i ON it.item_id = i.id
          JOIN tags t ON it.tag_id = t.id
          ORDER BY i.backward_compatibility, t.internal_name
        `,
      },
    ],
  },

  objects: {
    name: 'Objects',
    description: 'mwnf3.objects → Items (type=object) + ItemTranslations',
    legacySource: {
      type: 'sql',
      query: 'SELECT * FROM mwnf3.objects ORDER BY project_id, country, museum_id, number, lang',
    },
    newDbQueries: [
      {
        name: 'items',
        query: `
          SELECT id, internal_name, type, partner_id, parent_id, country_id, project_id, collection_id, 
                 owner_reference, mwnf_reference, backward_compatibility, created_at, updated_at
          FROM items
          WHERE type = 'object'
          ORDER BY backward_compatibility
        `,
      },
      {
        name: 'item-translations',
        query: `
          SELECT it.*, i.backward_compatibility as item_backward_compatibility
          FROM item_translations it
          JOIN items i ON it.item_id = i.id
          WHERE i.type = 'object'
          ORDER BY i.backward_compatibility, it.language_id, it.context_id
        `,
      },
      {
        name: 'item-images',
        query: `
          SELECT ii.*, i.backward_compatibility as item_backward_compatibility
          FROM item_images ii
          JOIN items i ON ii.item_id = i.id
          WHERE i.type = 'object'
          ORDER BY i.backward_compatibility, ii.display_order
        `,
      },
    ],
  },

  monuments: {
    name: 'Monuments',
    description: 'mwnf3.monuments → Items (type=monument) + ItemTranslations',
    legacySource: {
      type: 'sql',
      query: 'SELECT * FROM mwnf3.monuments ORDER BY project_id, country, number, lang',
    },
    newDbQueries: [
      {
        name: 'items',
        query: `
          SELECT id, internal_name, type, partner_id, parent_id, country_id, project_id, collection_id, 
                 mwnf_reference, backward_compatibility, created_at, updated_at
          FROM items
          WHERE type = 'monument'
          ORDER BY backward_compatibility
        `,
      },
      {
        name: 'item-translations',
        query: `
          SELECT it.*, i.backward_compatibility as item_backward_compatibility
          FROM item_translations it
          JOIN items i ON it.item_id = i.id
          WHERE i.type = 'monument'
          ORDER BY i.backward_compatibility, it.language_id, it.context_id
        `,
      },
      {
        name: 'item-images',
        query: `
          SELECT ii.*, i.backward_compatibility as item_backward_compatibility
          FROM item_images ii
          JOIN items i ON ii.item_id = i.id
          WHERE i.type = 'monument'
          ORDER BY i.backward_compatibility, ii.display_order
        `,
      },
    ],
  },
};

async function dumpEntity(entityKey: string): Promise<void> {
  const config = DUMP_CONFIGS[entityKey];
  if (!config) {
    throw new Error(`Unknown entity: ${entityKey}`);
  }

  console.log('='.repeat(80));
  console.log(`${config.name.toUpperCase()} DATA DUMP`);
  console.log(`${config.description}`);
  console.log('='.repeat(80));
  console.log('');

  const outputDir = resolve(process.cwd(), 'logs', 'data-dumps');
  fs.mkdirSync(outputDir, { recursive: true });

  // 1. Dump legacy source
  console.log(`Dumping legacy source (${config.legacySource.type})...`);
  let legacyData: unknown;

  if (config.legacySource.type === 'json') {
    // Read from JSON file
    const jsonPath = resolve(process.cwd(), '../..', config.legacySource.jsonPath!);
    legacyData = JSON.parse(fs.readFileSync(jsonPath, 'utf-8'));
    console.log(`✅ Read ${Array.isArray(legacyData) ? legacyData.length : 0} rows from JSON`);
  } else {
    // Query legacy database
    const legacyDb = createLegacyDatabase();
    await legacyDb.connect();
    legacyData = await legacyDb.query(config.legacySource.query!);
    await legacyDb.disconnect();
    console.log(
      `✅ Queried ${Array.isArray(legacyData) ? legacyData.length : 0} rows from legacy DB`
    );
  }

  const legacyOutput = resolve(outputDir, `${entityKey}-legacy.json`);
  fs.writeFileSync(legacyOutput, JSON.stringify(legacyData, null, 2));
  console.log(`   → ${legacyOutput}`);
  console.log('');

  // 2. Dump new database tables
  console.log('Dumping new database tables...');
  const newDb = await createNewDbConnection();

  for (const queryConfig of config.newDbQueries) {
    const [rows] = await newDb.execute(queryConfig.query);
    const data = rows as unknown[];

    const outputPath = resolve(outputDir, `${entityKey}-${queryConfig.name}-imported.json`);
    fs.writeFileSync(outputPath, JSON.stringify(data, null, 2));
    console.log(`✅ ${queryConfig.name}: ${data.length} rows`);
    console.log(`   → ${outputPath}`);
  }

  await newDb.end();
  console.log('');
}

async function main() {
  const args = process.argv.slice(2);
  const entityKey = args[0];

  if (!entityKey || entityKey === '--help' || entityKey === '-h') {
    console.log('');
    console.log('Usage: npx tsx src/dump-data.ts <entity>');
    console.log('       npx tsx src/dump-data.ts all');
    console.log('');
    console.log('Available entities:');
    Object.keys(DUMP_CONFIGS).forEach((key) => {
      console.log(`  - ${key}: ${DUMP_CONFIGS[key]?.description || 'N/A'}`);
    });
    console.log('');
    process.exit(0);
  }

  if (entityKey === 'all') {
    // Dump all entities
    for (const key of Object.keys(DUMP_CONFIGS)) {
      try {
        await dumpEntity(key);
      } catch (error) {
        console.error(`❌ Failed to dump ${key}:`, error);
      }
    }
  } else {
    // Dump single entity
    await dumpEntity(entityKey);
  }

  console.log('='.repeat(80));
  console.log('DUMP COMPLETE');
  console.log('='.repeat(80));
  console.log(`Output directory: logs/data-dumps/`);
  console.log('');

  process.exit(0);
}

main().catch((error) => {
  console.error('');
  console.error('💥 Dump failed:');
  console.error(error);
  process.exit(1);
});
