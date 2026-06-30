import mysql from 'mysql2/promise'

export class Database {
  private connection: mysql.Connection | null = null

  async connect(): Promise<void> {
    this.connection = await mysql.createConnection({
      host: process.env['DB_HOST'] ?? 'localhost',
      port: parseInt(process.env['DB_PORT'] ?? '3306', 10),
      user: process.env['DB_USERNAME'] ?? 'root',
      password: process.env['DB_PASSWORD'] ?? '',
      database: process.env['DB_DATABASE'] ?? 'inventory',
    })
  }

  async disconnect(): Promise<void> {
    if (this.connection) {
      await this.connection.end()
      this.connection = null
    }
  }

  async query<T>(sql: string, params?: (string | number | null)[]): Promise<T[]> {
    if (!this.connection) {
      throw new Error('Database not connected')
    }
    const [rows] = await this.connection.execute(sql, params)
    return rows as T[]
  }

  /**
   * Resolve project UUIDs from the user-supplied legacy project keys.
   *
   * The user supplies short keys like "ISL". The inventory DB stores these as
   * backward_compatibility = "mwnf3:projects:ISL". This method builds the
   * lookup values and returns the matching project UUIDs.
   */
  async resolveProjectIds(projectKeys: string[]): Promise<string[]> {
    const bcValues = projectKeys.map(k => `mwnf3:projects:${k}`)
    const placeholders = bcValues.map(() => '?').join(', ')

    const rows = await this.query<{ id: string; backward_compatibility: string }>(
      `SELECT id, backward_compatibility FROM projects WHERE backward_compatibility IN (${placeholders})`,
      bcValues
    )

    if (rows.length === 0) {
      throw new Error(
        `No projects found. Looked for: ${bcValues.join(', ')}\n` +
          `Run: SELECT backward_compatibility FROM projects; to list available projects.`
      )
    }

    if (rows.length < projectKeys.length) {
      const found = new Set(rows.map(r => r.backward_compatibility))
      const missing = bcValues.filter(v => !found.has(v))
      throw new Error(`Projects not found for: ${missing.join(', ')}`)
    }

    return rows.map(r => r.id)
  }

  /**
   * Resolve context UUIDs for the given project keys.
   *
   * Each legacy project (e.g. "ISL") has a corresponding context row whose
   * backward_compatibility matches the project: "mwnf3:projects:ISL".
   * Item translations must be filtered to these context IDs so that
   * explore-context translations (mwnf3_explore:context) are excluded.
   */
  async resolveContextIds(projectKeys: string[]): Promise<string[]> {
    const bcValues = projectKeys.map(k => `mwnf3:projects:${k}`)
    const placeholders = bcValues.map(() => '?').join(', ')

    const rows = await this.query<{ id: string; backward_compatibility: string }>(
      `SELECT id, backward_compatibility FROM contexts WHERE backward_compatibility IN (${placeholders})`,
      bcValues
    )

    if (rows.length === 0) {
      throw new Error(
        `No contexts found. Looked for: ${bcValues.join(', ')}\n` +
          `Run: SELECT backward_compatibility FROM contexts; to list available contexts.`
      )
    }

    if (rows.length < projectKeys.length) {
      const found = new Set(rows.map(r => r.backward_compatibility))
      const missing = bcValues.filter(v => !found.has(v))
      throw new Error(`Contexts not found for: ${missing.join(', ')}`)
    }

    return rows.map(r => r.id)
  }
}
