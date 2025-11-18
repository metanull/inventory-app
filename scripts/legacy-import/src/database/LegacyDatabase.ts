import mysql from 'mysql2/promise';

export interface LegacyDbConfig {
  host: string;
  port: number;
  user: string;
  password: string;
}

export class LegacyDatabase {
  private connection: mysql.Connection | null = null;

  constructor(private config: LegacyDbConfig) {}

  async connect(): Promise<void> {
    this.connection = await mysql.createConnection({
      host: this.config.host,
      port: this.config.port,
      user: this.config.user,
      password: this.config.password,
      multipleStatements: false,
    });
  }

  async disconnect(): Promise<void> {
    if (this.connection) {
      await this.connection.end();
      this.connection = null;
    }
  }

  async query<T = unknown>(sql: string, params?: unknown[]): Promise<T[]> {
    if (!this.connection) {
      throw new Error('Database not connected');
    }
    const [rows] = await this.connection.execute(sql, params);
    return rows as T[];
  }

  async queryOne<T = unknown>(sql: string, params?: unknown[]): Promise<T | null> {
    const rows = await this.query<T>(sql, params);
    return rows[0] ?? null;
  }

  isConnected(): boolean {
    return this.connection !== null;
  }
}

export function createLegacyDatabase(): LegacyDatabase {
  const config: LegacyDbConfig = {
    host: process.env['LEGACY_DB_HOST'] || 'localhost',
    port: parseInt(process.env['LEGACY_DB_PORT'] || '3306', 10),
    user: process.env['LEGACY_DB_USER'] || 'root',
    password: process.env['LEGACY_DB_PASSWORD'] || '',
  };

  return new LegacyDatabase(config);
}
