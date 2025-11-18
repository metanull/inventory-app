import {
  Configuration,
  LanguageApi,
  MobileAppAuthenticationApi,
  CollectionApi,
  PartnerApi,
  ItemApi,
  ImageUploadApi,
} from '@metanull/inventory-app-api-client';

/**
 * API Client wrapper for Inventory Management System
 * Uses the published npm package: @metanull/inventory-app-api-client
 */

export interface ApiConfig {
  baseUrl: string;
  token: string;
}

export class InventoryApiClient {
  private configuration: Configuration;
  public language: LanguageApi;
  public auth: MobileAppAuthenticationApi;
  public collection: CollectionApi;
  public partner: PartnerApi;
  public item: ItemApi;
  public image: ImageUploadApi;

  constructor(private config: ApiConfig) {
    this.configuration = new Configuration({
      basePath: config.baseUrl,
      accessToken: config.token,
    });

    // Initialize API instances
    this.language = new LanguageApi(this.configuration);
    this.auth = new MobileAppAuthenticationApi(this.configuration);
    this.collection = new CollectionApi(this.configuration);
    this.partner = new PartnerApi(this.configuration);
    this.item = new ItemApi(this.configuration);
    this.image = new ImageUploadApi(this.configuration);
  }

  async testConnection(): Promise<boolean> {
    try {
      // Test connection by fetching languages (reference data)
      await this.language.languageIndex();
      console.log('✓ API connection successful:', this.config.baseUrl);
      return true;
    } catch (error) {
      console.error('✗ API connection failed:', error);
      return false;
    }
  }
}

export function createApiClient(): InventoryApiClient {
  const config: ApiConfig = {
    baseUrl: process.env['API_BASE_URL'] || 'http://localhost:8000/api',
    token: process.env['API_TOKEN'] || '',
  };

  if (!config.token) {
    console.warn('WARNING: API_TOKEN not set. API calls will fail.');
  }

  return new InventoryApiClient(config);
}
