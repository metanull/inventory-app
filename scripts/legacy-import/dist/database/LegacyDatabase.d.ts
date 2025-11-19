export interface LegacyDbConfig {
    host: string;
    port: number;
    user: string;
    password: string;
}
export declare class LegacyDatabase {
    private config;
    private connection;
    constructor(config: LegacyDbConfig);
    connect(): Promise<void>;
    disconnect(): Promise<void>;
    query<T = unknown>(sql: string, params?: unknown[]): Promise<T[]>;
    queryOne<T = unknown>(sql: string, params?: unknown[]): Promise<T | null>;
    isConnected(): boolean;
}
export declare function createLegacyDatabase(): LegacyDatabase;
//# sourceMappingURL=LegacyDatabase.d.ts.map