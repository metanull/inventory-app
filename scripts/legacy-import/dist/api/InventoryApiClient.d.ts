import { DefaultApi } from '@metanull/inventory-app-api-client';
/**
 * API Client wrapper for Inventory Management System
 * Uses the published npm package: @metanull/inventory-app-api-client
 */
export interface ApiConfig {
    baseUrl: string;
    token: string;
}
export declare class InventoryApiClient {
    private config;
    private api;
    constructor(config: ApiConfig);
    testConnection(): Promise<boolean>;
    getApi(): DefaultApi;
}
export declare function createApiClient(): InventoryApiClient;
//# sourceMappingURL=InventoryApiClient.d.ts.map