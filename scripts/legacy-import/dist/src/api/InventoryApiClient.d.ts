import { AvailableImageApi, CollectionApi, CollectionImageApi, CollectionTranslationApi, ContextApi, CountryApi, ImageUploadApi, InfoApi, ItemApi, ItemImageApi, ItemTranslationApi, LanguageApi, LocationApi, LocationTranslationApi, MarkdownApi, MobileAppAuthenticationApi, PartnerApi, PartnerImageApi, PartnerTranslationApi, PartnerTranslationImageApi, ProjectApi, ProvinceApi, ProvinceTranslationApi, TagApi, ThemeApi, ThemeTranslationApi } from '@metanull/inventory-app-api-client';
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
    private configuration;
    availableImage: AvailableImageApi;
    collection: CollectionApi;
    collectionImage: CollectionImageApi;
    collectionTranslation: CollectionTranslationApi;
    context: ContextApi;
    country: CountryApi;
    imageUpload: ImageUploadApi;
    info: InfoApi;
    item: ItemApi;
    itemImage: ItemImageApi;
    itemTranslation: ItemTranslationApi;
    language: LanguageApi;
    location: LocationApi;
    locationTranslation: LocationTranslationApi;
    markdown: MarkdownApi;
    auth: MobileAppAuthenticationApi;
    partner: PartnerApi;
    partnerImage: PartnerImageApi;
    partnerTranslation: PartnerTranslationApi;
    partnerTranslationImage: PartnerTranslationImageApi;
    project: ProjectApi;
    province: ProvinceApi;
    provinceTranslation: ProvinceTranslationApi;
    tag: TagApi;
    theme: ThemeApi;
    themeTranslation: ThemeTranslationApi;
    constructor(config: ApiConfig);
    testConnection(): Promise<boolean>;
}
export declare function createApiClient(): InventoryApiClient;
//# sourceMappingURL=InventoryApiClient.d.ts.map