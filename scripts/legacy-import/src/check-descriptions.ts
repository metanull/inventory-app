import 'dotenv/config';
import { LegacyDatabase } from './database/LegacyDatabase.js';

async function checkDescriptions() {
  const config = {
    host: process.env.LEGACY_DB_HOST || '127.0.0.1',
    port: parseInt(process.env.LEGACY_DB_PORT || '3306'),
    user: process.env.LEGACY_DB_USER || 'root',
    password: process.env.LEGACY_DB_PASSWORD || 'root',
  };
  const db = new LegacyDatabase(config);
  await db.connect();

  // Check objects
  const objects = await db.query<{ description?: string; description2?: string }>(
    'SELECT description, description2 FROM mwnf3.objects'
  );

  const objBoth = objects.filter(
    (o) => o.description && o.description.trim() && o.description2 && o.description2.trim()
  ).length;
  const objOnlyDesc = objects.filter(
    (o) => o.description && o.description.trim() && (!o.description2 || !o.description2.trim())
  ).length;
  const objOnlyDesc2 = objects.filter(
    (o) => (!o.description || !o.description.trim()) && o.description2 && o.description2.trim()
  ).length;
  const objNeither = objects.filter(
    (o) => (!o.description || !o.description.trim()) && (!o.description2 || !o.description2.trim())
  ).length;

  console.log('OBJECTS:');
  console.log('  Both description & description2:', objBoth);
  console.log('  Only description:', objOnlyDesc);
  console.log('  Only description2:', objOnlyDesc2);
  console.log('  Neither:', objNeither);
  console.log('  Total:', objects.length);
  console.log('');

  // Check monuments
  const monuments = await db.query<{ description?: string; description2?: string }>(
    'SELECT description, description2 FROM mwnf3.monuments'
  );

  const monBoth = monuments.filter(
    (m) => m.description && m.description.trim() && m.description2 && m.description2.trim()
  ).length;
  const monOnlyDesc = monuments.filter(
    (m) => m.description && m.description.trim() && (!m.description2 || !m.description2.trim())
  ).length;
  const monOnlyDesc2 = monuments.filter(
    (m) => (!m.description || !m.description.trim()) && m.description2 && m.description2.trim()
  ).length;
  const monNeither = monuments.filter(
    (m) => (!m.description || !m.description.trim()) && (!m.description2 || !m.description2.trim())
  ).length;

  console.log('MONUMENTS:');
  console.log('  Both description & description2:', monBoth);
  console.log('  Only description:', monOnlyDesc);
  console.log('  Only description2:', monOnlyDesc2);
  console.log('  Neither:', monNeither);
  console.log('  Total:', monuments.length);
  console.log('');

  // Check objects by project_id
  const objectsByProject = await db.query<{
    project_id: string;
    description?: string;
    description2?: string;
  }>('SELECT project_id, description, description2 FROM mwnf3.objects ORDER BY project_id');

  const projectStats = new Map<
    string,
    { both: number; onlyDesc: number; onlyDesc2: number; neither: number; total: number }
  >();

  for (const obj of objectsByProject) {
    if (!projectStats.has(obj.project_id)) {
      projectStats.set(obj.project_id, { both: 0, onlyDesc: 0, onlyDesc2: 0, neither: 0, total: 0 });
    }
    const stats = projectStats.get(obj.project_id)!;
    stats.total++;

    const hasDesc = obj.description && obj.description.trim();
    const hasDesc2 = obj.description2 && obj.description2.trim();

    if (hasDesc && hasDesc2) {
      stats.both++;
    } else if (hasDesc) {
      stats.onlyDesc++;
    } else if (hasDesc2) {
      stats.onlyDesc2++;
    } else {
      stats.neither++;
    }
  }

  console.log('OBJECTS BY PROJECT:');
  console.log('Project | Both | Only Desc | Only Desc2 | Neither | Total');
  console.log('--------|------|-----------|------------|---------|------');
  for (const [projectId, stats] of Array.from(projectStats.entries()).sort((a, b) =>
    a[0].localeCompare(b[0])
  )) {
    const pct2 = ((stats.onlyDesc2 / stats.total) * 100).toFixed(1);
    console.log(
      `${projectId.padEnd(7)} | ${stats.both.toString().padStart(4)} | ${stats.onlyDesc.toString().padStart(9)} | ${stats.onlyDesc2.toString().padStart(10)} (${pct2}%) | ${stats.neither.toString().padStart(7)} | ${stats.total}`
    );
  }
  console.log('');

  // Check monuments by project_id
  const monumentsByProject = await db.query<{
    project_id: string;
    description?: string;
    description2?: string;
  }>('SELECT project_id, description, description2 FROM mwnf3.monuments ORDER BY project_id');

  const monProjectStats = new Map<
    string,
    { both: number; onlyDesc: number; onlyDesc2: number; neither: number; total: number }
  >();

  for (const mon of monumentsByProject) {
    if (!monProjectStats.has(mon.project_id)) {
      monProjectStats.set(mon.project_id, { both: 0, onlyDesc: 0, onlyDesc2: 0, neither: 0, total: 0 });
    }
    const stats = monProjectStats.get(mon.project_id)!;
    stats.total++;

    const hasDesc = mon.description && mon.description.trim();
    const hasDesc2 = mon.description2 && mon.description2.trim();

    if (hasDesc && hasDesc2) {
      stats.both++;
    } else if (hasDesc) {
      stats.onlyDesc++;
    } else if (hasDesc2) {
      stats.onlyDesc2++;
    } else {
      stats.neither++;
    }
  }

  console.log('MONUMENTS BY PROJECT:');
  console.log('Project | Both | Only Desc | Only Desc2 | Neither | Total');
  console.log('--------|------|-----------|------------|---------|------');
  for (const [projectId, stats] of Array.from(monProjectStats.entries()).sort((a, b) =>
    a[0].localeCompare(b[0])
  )) {
    const pct2 = ((stats.onlyDesc2 / stats.total) * 100).toFixed(1);
    console.log(
      `${projectId.padEnd(7)} | ${stats.both.toString().padStart(4)} | ${stats.onlyDesc.toString().padStart(9)} | ${stats.onlyDesc2.toString().padStart(10)} (${pct2}%) | ${stats.neither.toString().padStart(7)} | ${stats.total}`
    );
  }

  await db.disconnect();
}

checkDescriptions().catch(console.error);
