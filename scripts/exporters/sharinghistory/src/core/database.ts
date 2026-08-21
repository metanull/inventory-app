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
   * The user supplies short keys like "awe". The inventory DB stores Sharing
   * History projects as backward_compatibility =
   * "mwnf3_sharing_history:sh_projects:awe" — the SH keyspace is LOWERCASE
   * (formatShBackwardCompatibility convention in the importer), unlike the
   * uppercase mwnf3 keys the islamicart/baroqueart exporters use. This method
   * builds the lookup values and returns the matching project UUIDs.
   */
  async resolveProjectIds(projectKeys: string[]): Promise<string[]> {
    const bcValues = projectKeys.map(k => `mwnf3_sharing_history:sh_projects:${k.toLowerCase()}`)
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

    // Return ids in ARGUMENT order, not DB order: callers pair
    // projectIds[i] with projectKeys[i] (e.g. partner-exporter), and the
    // IN (...) query gives no ordering guarantee.
    const idByBc = new Map(rows.map(r => [r.backward_compatibility, r.id]))
    return bcValues.map(bc => idByBc.get(bc)!)
  }

  /**
   * Resolve context UUIDs for the given project keys.
   *
   * Each SH project (e.g. "awe") has a Context, Collection and Project all
   * sharing the identical backward_compatibility string
   * "mwnf3_sharing_history:sh_projects:awe" (sh-project-transformer). Item
   * translations must be filtered to these context IDs so that translations
   * from other contexts are excluded.
   */
  async resolveContextIds(projectKeys: string[]): Promise<string[]> {
    const bcValues = projectKeys.map(k => `mwnf3_sharing_history:sh_projects:${k.toLowerCase()}`)
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

    // Same argument-order guarantee as resolveProjectIds.
    const idByBc = new Map(rows.map(r => [r.backward_compatibility, r.id]))
    return bcValues.map(bc => idByBc.get(bc)!)
  }
}
