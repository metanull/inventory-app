import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';
export class InventoryApiClient {
  config;
  api;
  constructor(config) {
    this.config = config;
    const configuration = new Configuration({
      basePath: config.baseUrl,
      accessToken: config.token,
    });
    this.api = new DefaultApi(configuration);
  }
  async testConnection() {
    try {
      // Test connection by fetching languages (reference data)
      await this.api.languageIndex();
      console.log('✓ API connection successful:', this.config.baseUrl);
      return true;
    } catch (error) {
      console.error('✗ API connection failed:', error);
      return false;
    }
  }
  getApi() {
    return this.api;
  }
}
export function createApiClient() {
  const config = {
    baseUrl: process.env['API_BASE_URL'] || 'http://localhost:8000/api',
    token: process.env['API_TOKEN'] || '',
  };
  if (!config.token) {
    console.warn('WARNING: API_TOKEN not set. API calls will fail.');
  }
  return new InventoryApiClient(config);
}
//# sourceMappingURL=InventoryApiClient.js.map
