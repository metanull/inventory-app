import mysql from 'mysql2/promise';
import * as fs from 'fs';
import { resolve } from 'path';

/**
 * Create connection to new database (main app database)
 */
export async function createNewDbConnection(): Promise<mysql.Connection> {
  // Read new database config from main project .env
  const mainEnvPath = resolve(process.cwd(), '../../.env');
  const mainEnvContent = fs.readFileSync(mainEnvPath, 'utf-8');
  const mainEnv: Record<string, string> = {};

  mainEnvContent.split('\n').forEach((line) => {
    const match = line.match(/^([^=]+)=(.*)$/);
    if (match && match[1] && match[2]) {
      mainEnv[match[1].trim()] = match[2].trim();
    }
  });

  return await mysql.createConnection({
    host: mainEnv['DB_HOST'] || 'localhost',
    port: parseInt(mainEnv['DB_PORT'] || '3306', 10),
    user: mainEnv['DB_USERNAME'] || 'root',
    password: mainEnv['DB_PASSWORD'] || '',
    database: mainEnv['DB_DATABASE'] || 'inventory_staging',
  });
}
