import {
  Configuration,
  AvailableImageApi,
  CollectionApi,
  CollectionImageApi,
  CollectionTranslationApi,
  ContextApi,
  CountryApi,
  ImageUploadApi,
  InfoApi,
  ItemApi,
  ItemImageApi,
  ItemTranslationApi,
  LanguageApi,
  LocationApi,
  LocationTranslationApi,
  MarkdownApi,
  MobileAppAuthenticationApi,
  PartnerApi,
  PartnerImageApi,
  PartnerTranslationApi,
  PartnerTranslationImageApi,
  ProjectApi,
  ProvinceApi,
  ProvinceTranslationApi,
  TagApi,
  ThemeApi,
  ThemeTranslationApi,
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
  public availableImage: AvailableImageApi;
  public collection: CollectionApi;
  public collectionImage: CollectionImageApi;
  public collectionTranslation: CollectionTranslationApi;
  public context: ContextApi;
  public country: CountryApi;
  public imageUpload: ImageUploadApi;
  public info: InfoApi;
  public item: ItemApi;
  public itemImage: ItemImageApi;
  public itemTranslation: ItemTranslationApi;
  public language: LanguageApi;
  public location: LocationApi;
  public locationTranslation: LocationTranslationApi;
  public markdown: MarkdownApi;
  public auth: MobileAppAuthenticationApi;
  public partner: PartnerApi;
  public partnerImage: PartnerImageApi;
  public partnerTranslation: PartnerTranslationApi;
  public partnerTranslationImage: PartnerTranslationImageApi;
  public project: ProjectApi;
  public province: ProvinceApi;
  public provinceTranslation: ProvinceTranslationApi;
  public tag: TagApi;
  public theme: ThemeApi;
  public themeTranslation: ThemeTranslationApi;

  constructor(config: ApiConfig) {
    this.configuration = new Configuration({
      basePath: config.baseUrl,
      accessToken: config.token,
    });

    // Initialize all API instances
    this.availableImage = new AvailableImageApi(this.configuration);
    this.collection = new CollectionApi(this.configuration);
    this.collectionImage = new CollectionImageApi(this.configuration);
    this.collectionTranslation = new CollectionTranslationApi(this.configuration);
    this.context = new ContextApi(this.configuration);
    this.country = new CountryApi(this.configuration);
    this.imageUpload = new ImageUploadApi(this.configuration);
    this.info = new InfoApi(this.configuration);
    this.item = new ItemApi(this.configuration);
    this.itemImage = new ItemImageApi(this.configuration);
    this.itemTranslation = new ItemTranslationApi(this.configuration);
    this.language = new LanguageApi(this.configuration);
    this.location = new LocationApi(this.configuration);
    this.locationTranslation = new LocationTranslationApi(this.configuration);
    this.markdown = new MarkdownApi(this.configuration);
    this.auth = new MobileAppAuthenticationApi(this.configuration);
    this.partner = new PartnerApi(this.configuration);
    this.partnerImage = new PartnerImageApi(this.configuration);
    this.partnerTranslation = new PartnerTranslationApi(this.configuration);
    this.partnerTranslationImage = new PartnerTranslationImageApi(this.configuration);
    this.project = new ProjectApi(this.configuration);
    this.province = new ProvinceApi(this.configuration);
    this.provinceTranslation = new ProvinceTranslationApi(this.configuration);
    this.tag = new TagApi(this.configuration);
    this.theme = new ThemeApi(this.configuration);
    this.themeTranslation = new ThemeTranslationApi(this.configuration);
  }

  async testConnection(): Promise<boolean> {
    try {
      // Test connection by fetching languages (reference data)
      await this.language.languageIndex();
      return true;
    } catch {
      return false;
    }
  }
}

export function createApiClient(): InventoryApiClient {
  const config: ApiConfig = {
    baseUrl: process.env['API_BASE_URL'] || 'http://localhost:8000/api',
    token: process.env['API_TOKEN'] || '',
  };

  return new InventoryApiClient(config);
}
