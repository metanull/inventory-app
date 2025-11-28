#!/usr/bin/env node
import dotenv from 'dotenv';
import { resolve } from 'path';
import { createNewDbConnection } from './database/NewDatabase.js';
import { createLegacyDatabase } from './database/LegacyDatabase.js';
import { v4 as uuidv4 } from 'uuid';
import { convertHtmlToMarkdown } from './utils/HtmlToMarkdownConverter.js';
import { mapLanguageCode } from './utils/CodeMappings.js';
import type { Connection, RowDataPacket } from 'mysql2/promise';

dotenv.config({ path: resolve(process.cwd(), '.env') });

interface LegacyObject {
  project_id: string;
  country: string;
  museum_id: string;
  number: string;
  lang: string;
  working_number?: string;
  inventory_id?: string;
  name?: string;
  name2?: string;
  typeof?: string;
  holding_museum?: string;
  location?: string;
  province?: string;
  date_description?: string;
  current_owner?: string;
  original_owner?: string;
  dimensions?: string;
  production_place?: string;
  datationmethod?: string;
  provenancemethod?: string;
  obtentionmethod?: string;
  description?: string;
  bibliography?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
  artist?: string;
  birthplace?: string;
  deathplace?: string;
  birthdate?: string;
  deathdate?: string;
  period_activity?: string;
  materials?: string;
  dynasty?: string;
  keywords?: string;
  workshop?: string;
  description2?: string;
  copyright?: string;
  binding_desc?: string;
}

interface LegacyMonument {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  lang: string;
  working_number?: string;
  name?: string;
  name2?: string;
  typeof?: string;
  location?: string;
  province?: string;
  address?: string;
  date_description?: string;
  datationmethod?: string;
  description?: string;
  bibliography?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
  dynasty?: string;
  keywords?: string;
  phone?: string;
  fax?: string;
  email?: string;
  institution?: string;
  patrons?: string;
  architects?: string;
  history?: string;
  external_sources?: string;
  description2?: string;
}

const tracker = new Map<string, string>();
const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

async function main() {
  console.log('Starting SQL-based import...\n');

  const legacyDb = createLegacyDatabase();
  await legacyDb.connect();

  const newDb = await createNewDbConnection();

  try {
    // Note: Languages, Countries, Projects, Partners should already be imported via API
    // This importer focuses on high-volume data: Objects and Monuments

    // Import Objects
    console.log('Importing objects...');
    const objects = await legacyDb.query<LegacyObject>(
      'SELECT * FROM mwnf3.objects ORDER BY project_id, country, museum_id, number, lang'
    );

    const objectGroups = groupByItem(objects);
    console.log(`Found ${objectGroups.length} unique objects (${objects.length} translations)`);

    let importedObjects = 0;
    for (const group of objectGroups) {
      try {
        await importObject(newDb, group);
        importedObjects++;
        if (importedObjects % 100 === 0)
          process.stdout.write(`\r  Imported: ${importedObjects}/${objectGroups.length}`);
      } catch (error) {
        console.error(`\nError importing ${group.key}:`, error);
      }
    }
    console.log(`\n✅ Imported ${importedObjects} objects\n`);

    // Import Monuments
    console.log('Importing monuments...');
    const monuments = await legacyDb.query<LegacyMonument>(
      'SELECT * FROM mwnf3.monuments ORDER BY project_id, country, institution_id, number, lang'
    );

    const monumentGroups = groupByMonument(monuments);
    console.log(
      `Found ${monumentGroups.length} unique monuments (${monuments.length} translations)`
    );

    let importedMonuments = 0;
    for (const group of monumentGroups) {
      try {
        await importMonument(newDb, group);
        importedMonuments++;
        if (importedMonuments % 100 === 0)
          process.stdout.write(`\r  Imported: ${importedMonuments}/${monumentGroups.length}`);
      } catch (error) {
        console.error(`\nError importing ${group.key}:`, error);
      }
    }
    console.log(`\n✅ Imported ${importedMonuments} monuments\n`);
  } finally {
    await legacyDb.disconnect();
    await newDb.end();
  }
}

function groupByItem(objects: LegacyObject[]) {
  const map = new Map<string, LegacyObject[]>();
  for (const obj of objects) {
    const key = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;
    if (!map.has(key)) map.set(key, []);
    map.get(key)!.push(obj);
  }
  return Array.from(map.entries()).map(([key, translations]) => ({ key, translations }));
}

async function importObject(db: Connection, group: { key: string; translations: LegacyObject[] }) {
  const first = group.translations[0];
  if (!first) return; // Skip if no translations

  const backwardCompat = `mwnf3:objects:${group.key}`;

  // Check if already imported
  const [existing] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM items WHERE backward_compatibility = ?',
    [backwardCompat]
  );
  if (existing.length > 0) return;

  // Resolve project → context
  const projectBackwardCompat = `mwnf3:projects:${first.project_id}`;
  const [contexts] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM contexts WHERE backward_compatibility = ?',
    [projectBackwardCompat]
  );
  const contextId = contexts[0]?.id as string | undefined;
  if (!contextId) return; // Skip if project not found

  // Resolve context → collection
  const collectionBackwardCompat = `${projectBackwardCompat}:collection`;
  const [collections] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM collections WHERE backward_compatibility = ?',
    [collectionBackwardCompat]
  );
  const collectionId = collections[0]?.id as string | undefined;
  if (!collectionId) return;

  // Resolve museum → partner
  const partnerBackwardCompat = `mwnf3:museums:${first.museum_id}:${first.country}`;
  const [partners] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM partners WHERE backward_compatibility = ?',
    [partnerBackwardCompat]
  );
  const partnerId = partners[0]?.id as string | undefined;
  if (!partnerId) return;

  // Create Item
  const itemId = uuidv4();
  await db.execute(
    `INSERT INTO items (id, partner_id, collection_id, internal_name, type, owner_reference, mwnf_reference, backward_compatibility, created_at, updated_at)
     VALUES (?, ?, ?, ?, 'object', ?, ?, ?, ?, ?)`,
    [
      itemId,
      partnerId,
      collectionId,
      first.inventory_id || first.working_number || first.number,
      first.inventory_id,
      first.working_number,
      backwardCompat,
      now,
      now,
    ]
  );

  // Create translations
  for (const obj of group.translations) {
    await importTranslation(db, itemId, contextId, obj);
  }

  // Create tags
  await createTags(db, itemId, first);

  // Create artists
  if (first.artist) {
    await createArtists(db, itemId, first);
  }
}

async function importTranslation(
  db: Connection,
  itemId: string,
  contextId: string,
  obj: LegacyObject
) {
  const languageId = mapLanguageCode(obj.lang);
  const name = obj.name?.trim();
  if (!name) return; // Skip if no name

  // Create/find authors
  const authorId = obj.preparedby ? await findOrCreateAuthor(db, obj.preparedby) : null;
  const textCopyEditorId = obj.copyeditedby ? await findOrCreateAuthor(db, obj.copyeditedby) : null;
  const translatorId = obj.translationby ? await findOrCreateAuthor(db, obj.translationby) : null;
  const translationCopyEditorId = obj.translationcopyeditedby
    ? await findOrCreateAuthor(db, obj.translationcopyeditedby)
    : null;

  // Build extra field
  const extra: Record<string, string> = {};
  if (obj.workshop) extra.workshop = obj.workshop;
  if (obj.description2) extra.description2 = obj.description2;
  if (obj.copyright) extra.copyright = obj.copyright;
  if (obj.binding_desc) extra.binding_desc = obj.binding_desc;
  const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

  // Convert HTML to Markdown
  const nameMarkdown = convertHtmlToMarkdown(name);
  const alternateNameMarkdown = obj.name2 ? convertHtmlToMarkdown(obj.name2) : null;
  const descriptionMarkdown = obj.description ? convertHtmlToMarkdown(obj.description) : null;
  const bibliographyMarkdown = obj.bibliography ? convertHtmlToMarkdown(obj.bibliography) : null;

  const location = [obj.location, obj.province].filter(Boolean).join(', ') || null;

  const translationId = uuidv4();
  await db.execute(
    `INSERT INTO item_translations (id, item_id, language_id, context_id, name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography, author_id, text_copy_editor_id, translator_id, translation_copy_editor_id, extra, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      translationId,
      itemId,
      languageId,
      contextId,
      nameMarkdown,
      alternateNameMarkdown,
      descriptionMarkdown,
      obj.typeof,
      obj.holding_museum,
      obj.current_owner,
      obj.original_owner,
      obj.date_description,
      location,
      obj.dimensions,
      obj.production_place,
      obj.datationmethod,
      obj.provenancemethod,
      obj.obtentionmethod,
      bibliographyMarkdown,
      authorId,
      textCopyEditorId,
      translatorId,
      translationCopyEditorId,
      extraJson,
      now,
      now,
    ]
  );
}

async function findOrCreateAuthor(db: Connection, name: string): Promise<string> {
  const trimmed = name.trim();
  const backwardCompat = `mwnf3:authors:${trimmed}`;

  if (tracker.has(backwardCompat)) return tracker.get(backwardCompat)!;

  const [existing] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM authors WHERE backward_compatibility = ?',
    [backwardCompat]
  );
  if (existing.length > 0 && existing[0]) {
    const id = existing[0].id as string;
    tracker.set(backwardCompat, id);
    return id;
  }

  const authorId = uuidv4();
  await db.execute(
    'INSERT INTO authors (id, name, internal_name, backward_compatibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
    [authorId, trimmed, trimmed, backwardCompat, now, now]
  );
  tracker.set(backwardCompat, authorId);
  return authorId;
}

async function createTags(db: Connection, itemId: string, obj: LegacyObject) {
  const languageId = mapLanguageCode(obj.lang);
  const tagIds: string[] = [];

  if (obj.materials)
    tagIds.push(...(await findOrCreateTagList(db, obj.materials, 'material', languageId)));
  if (obj.dynasty)
    tagIds.push(...(await findOrCreateTagList(db, obj.dynasty, 'dynasty', languageId)));
  if (obj.keywords)
    tagIds.push(...(await findOrCreateTagList(db, obj.keywords, 'keyword', languageId)));

  for (const tagId of tagIds) {
    await db.execute(
      'INSERT IGNORE INTO item_tag (item_id, tag_id, created_at, updated_at) VALUES (?, ?, ?, ?)',
      [itemId, tagId, now, now]
    );
  }
}

async function findOrCreateTagList(
  db: Connection,
  tagString: string,
  category: string,
  languageId: string
): Promise<string[]> {
  const isStructured = tagString.includes(':');
  const separator = isStructured ? null : tagString.includes(';') ? ';' : ',';
  const tagNames = separator
    ? tagString
        .split(separator)
        .map((t) => t.trim())
        .filter(Boolean)
    : [tagString.trim()];

  const tagIds: string[] = [];
  for (const tagName of tagNames) {
    const tagId = await findOrCreateTag(db, tagName, category, languageId);
    if (tagId) tagIds.push(tagId);
  }
  return tagIds;
}

async function findOrCreateTag(
  db: Connection,
  name: string,
  category: string,
  languageId: string
): Promise<string | null> {
  const normalized = name.toLowerCase();
  const backwardCompat = `mwnf3:tags:${category}:${languageId}:${normalized}`;

  if (tracker.has(backwardCompat)) return tracker.get(backwardCompat)!;

  const [existing] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM tags WHERE backward_compatibility = ?',
    [backwardCompat]
  );
  if (existing.length > 0 && existing[0]) {
    const id = existing[0].id as string;
    tracker.set(backwardCompat, id);
    return id;
  }

  const tagId = uuidv4();
  try {
    await db.execute(
      'INSERT INTO tags (id, internal_name, category, language_id, description, backward_compatibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [tagId, normalized, category, languageId, name, backwardCompat, now, now]
    );
    tracker.set(backwardCompat, tagId);
    return tagId;
  } catch {
    return null; // Duplicate, skip
  }
}

async function createArtists(db: Connection, itemId: string, obj: LegacyObject) {
  if (!obj.artist) return;

  const artistNames = obj.artist
    .split(';')
    .map((n) => n.trim())
    .filter(Boolean);
  for (const artistName of artistNames) {
    const artistId = await findOrCreateArtist(db, artistName, obj);
    if (artistId) {
      await db.execute(
        'INSERT IGNORE INTO artist_item (artist_id, item_id, created_at, updated_at) VALUES (?, ?, ?, ?)',
        [artistId, itemId, now, now]
      );
    }
  }
}

async function findOrCreateArtist(
  db: Connection,
  name: string,
  obj: LegacyObject
): Promise<string | null> {
  const backwardCompat = `mwnf3:artists:${name}`;

  if (tracker.has(backwardCompat)) return tracker.get(backwardCompat)!;

  const [existing] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM artists WHERE backward_compatibility = ?',
    [backwardCompat]
  );
  if (existing.length > 0 && existing[0]) {
    const id = existing[0].id as string;
    tracker.set(backwardCompat, id);
    return id;
  }

  const artistId = uuidv4();
  try {
    await db.execute(
      'INSERT INTO artists (id, name, internal_name, place_of_birth, place_of_death, date_of_birth, date_of_death, period_of_activity, backward_compatibility, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [
        artistId,
        name,
        name,
        obj.birthplace,
        obj.deathplace,
        obj.birthdate,
        obj.deathdate,
        obj.period_activity,
        backwardCompat,
        now,
        now,
      ]
    );
    tracker.set(backwardCompat, artistId);
    return artistId;
  } catch {
    return null; // Duplicate, skip
  }
}

function groupByMonument(monuments: LegacyMonument[]) {
  const map = new Map<string, LegacyMonument[]>();
  for (const monument of monuments) {
    const key = `${monument.project_id}:${monument.country}:${monument.institution_id}:${monument.number}`;
    if (!map.has(key)) map.set(key, []);
    map.get(key)!.push(monument);
  }
  return Array.from(map.entries()).map(([key, translations]) => ({ key, translations }));
}

async function importMonument(
  db: Connection,
  group: { key: string; translations: LegacyMonument[] }
) {
  const first = group.translations[0];
  if (!first) return;

  const backwardCompat = `mwnf3:monuments:${group.key}`;

  // Check if already imported
  const [existing] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM items WHERE backward_compatibility = ?',
    [backwardCompat]
  );
  if (existing.length > 0) return;

  // Resolve project → context
  const projectBackwardCompat = `mwnf3:projects:${first.project_id}`;
  const [contexts] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM contexts WHERE backward_compatibility = ?',
    [projectBackwardCompat]
  );
  const contextId = contexts[0]?.id as string | undefined;
  if (!contextId) return;

  // Resolve context → collection
  const collectionBackwardCompat = `${projectBackwardCompat}:collection`;
  const [collections] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM collections WHERE backward_compatibility = ?',
    [collectionBackwardCompat]
  );
  const collectionId = collections[0]?.id as string | undefined;
  if (!collectionId) return;

  // Resolve institution → partner
  const partnerBackwardCompat = `mwnf3:institutions:${first.institution_id}:${first.country}`;
  const [partners] = await db.execute<RowDataPacket[]>(
    'SELECT id FROM partners WHERE backward_compatibility = ?',
    [partnerBackwardCompat]
  );
  const partnerId = partners[0]?.id as string | undefined;
  if (!partnerId) return;

  // Create Item
  const itemId = uuidv4();
  await db.execute(
    `INSERT INTO items (id, partner_id, collection_id, internal_name, type, mwnf_reference, backward_compatibility, created_at, updated_at)
     VALUES (?, ?, ?, ?, 'monument', ?, ?, ?, ?)`,
    [
      itemId,
      partnerId,
      collectionId,
      first.working_number || first.name || first.number,
      first.working_number,
      backwardCompat,
      now,
      now,
    ]
  );

  // Create translations
  for (const monument of group.translations) {
    await importMonumentTranslation(db, itemId, contextId, monument);
  }

  // Create tags
  await createMonumentTags(db, itemId, first);
}

async function importMonumentTranslation(
  db: Connection,
  itemId: string,
  contextId: string,
  monument: LegacyMonument
) {
  const languageId = mapLanguageCode(monument.lang);
  const name = monument.name?.trim();
  if (!name) return;

  // Create/find authors
  const authorId = monument.preparedby ? await findOrCreateAuthor(db, monument.preparedby) : null;
  const textCopyEditorId = monument.copyeditedby
    ? await findOrCreateAuthor(db, monument.copyeditedby)
    : null;
  const translatorId = monument.translationby
    ? await findOrCreateAuthor(db, monument.translationby)
    : null;
  const translationCopyEditorId = monument.translationcopyeditedby
    ? await findOrCreateAuthor(db, monument.translationcopyeditedby)
    : null;

  // Build extra field for monument-specific fields
  const extra: Record<string, string> = {};
  if (monument.phone) extra.phone = monument.phone;
  if (monument.fax) extra.fax = monument.fax;
  if (monument.email) extra.email = monument.email;
  if (monument.institution) extra.institution = monument.institution;
  if (monument.patrons) extra.patrons = monument.patrons;
  if (monument.architects) extra.architects = monument.architects;
  if (monument.history) extra.history = monument.history;
  if (monument.external_sources) extra.external_sources = monument.external_sources;
  if (monument.description2) extra.description2 = monument.description2;
  const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

  // Convert HTML to Markdown
  const nameMarkdown = convertHtmlToMarkdown(name);
  const alternateNameMarkdown = monument.name2 ? convertHtmlToMarkdown(monument.name2) : null;
  const descriptionMarkdown = monument.description
    ? convertHtmlToMarkdown(monument.description)
    : null;
  const bibliographyMarkdown = monument.bibliography
    ? convertHtmlToMarkdown(monument.bibliography)
    : null;

  const location =
    [monument.location, monument.province, monument.address].filter(Boolean).join(', ') || null;

  const translationId = uuidv4();
  await db.execute(
    `INSERT INTO item_translations (id, item_id, language_id, context_id, name, alternate_name, description, type, dates, location, method_for_datation, bibliography, author_id, text_copy_editor_id, translator_id, translation_copy_editor_id, extra, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      translationId,
      itemId,
      languageId,
      contextId,
      nameMarkdown,
      alternateNameMarkdown,
      descriptionMarkdown,
      monument.typeof,
      monument.date_description,
      location,
      monument.datationmethod,
      bibliographyMarkdown,
      authorId,
      textCopyEditorId,
      translatorId,
      translationCopyEditorId,
      extraJson,
      now,
      now,
    ]
  );
}

async function createMonumentTags(db: Connection, itemId: string, monument: LegacyMonument) {
  const languageId = mapLanguageCode(monument.lang);
  const tagIds: string[] = [];

  if (monument.dynasty)
    tagIds.push(...(await findOrCreateTagList(db, monument.dynasty, 'dynasty', languageId)));
  if (monument.keywords)
    tagIds.push(...(await findOrCreateTagList(db, monument.keywords, 'keyword', languageId)));

  for (const tagId of tagIds) {
    await db.execute(
      'INSERT IGNORE INTO item_tag (item_id, tag_id, created_at, updated_at) VALUES (?, ?, ?, ?)',
      [itemId, tagId, now, now]
    );
  }
}

main().catch(console.error);
