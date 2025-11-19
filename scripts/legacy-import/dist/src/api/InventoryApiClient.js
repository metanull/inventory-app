"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.InventoryApiClient = void 0;
exports.createApiClient = createApiClient;
const inventory_app_api_client_1 = require("@metanull/inventory-app-api-client");
class InventoryApiClient {
    config;
    configuration;
    availableImage;
    collection;
    collectionImage;
    collectionTranslation;
    context;
    country;
    imageUpload;
    info;
    item;
    itemImage;
    itemTranslation;
    language;
    location;
    locationTranslation;
    markdown;
    auth;
    partner;
    partnerImage;
    partnerTranslation;
    partnerTranslationImage;
    project;
    province;
    provinceTranslation;
    tag;
    theme;
    themeTranslation;
    constructor(config) {
        this.config = config;
        this.configuration = new inventory_app_api_client_1.Configuration({
            basePath: config.baseUrl,
            accessToken: config.token,
        });
        // Initialize all API instances
        this.availableImage = new inventory_app_api_client_1.AvailableImageApi(this.configuration);
        this.collection = new inventory_app_api_client_1.CollectionApi(this.configuration);
        this.collectionImage = new inventory_app_api_client_1.CollectionImageApi(this.configuration);
        this.collectionTranslation = new inventory_app_api_client_1.CollectionTranslationApi(this.configuration);
        this.context = new inventory_app_api_client_1.ContextApi(this.configuration);
        this.country = new inventory_app_api_client_1.CountryApi(this.configuration);
        this.imageUpload = new inventory_app_api_client_1.ImageUploadApi(this.configuration);
        this.info = new inventory_app_api_client_1.InfoApi(this.configuration);
        this.item = new inventory_app_api_client_1.ItemApi(this.configuration);
        this.itemImage = new inventory_app_api_client_1.ItemImageApi(this.configuration);
        this.itemTranslation = new inventory_app_api_client_1.ItemTranslationApi(this.configuration);
        this.language = new inventory_app_api_client_1.LanguageApi(this.configuration);
        this.location = new inventory_app_api_client_1.LocationApi(this.configuration);
        this.locationTranslation = new inventory_app_api_client_1.LocationTranslationApi(this.configuration);
        this.markdown = new inventory_app_api_client_1.MarkdownApi(this.configuration);
        this.auth = new inventory_app_api_client_1.MobileAppAuthenticationApi(this.configuration);
        this.partner = new inventory_app_api_client_1.PartnerApi(this.configuration);
        this.partnerImage = new inventory_app_api_client_1.PartnerImageApi(this.configuration);
        this.partnerTranslation = new inventory_app_api_client_1.PartnerTranslationApi(this.configuration);
        this.partnerTranslationImage = new inventory_app_api_client_1.PartnerTranslationImageApi(this.configuration);
        this.project = new inventory_app_api_client_1.ProjectApi(this.configuration);
        this.province = new inventory_app_api_client_1.ProvinceApi(this.configuration);
        this.provinceTranslation = new inventory_app_api_client_1.ProvinceTranslationApi(this.configuration);
        this.tag = new inventory_app_api_client_1.TagApi(this.configuration);
        this.theme = new inventory_app_api_client_1.ThemeApi(this.configuration);
        this.themeTranslation = new inventory_app_api_client_1.ThemeTranslationApi(this.configuration);
    }
    async testConnection() {
        try {
            // Test connection by fetching languages (reference data)
            await this.language.languageIndex();
            console.log('✓ API connection successful:', this.config.baseUrl);
            return true;
        }
        catch (error) {
            console.error('✗ API connection failed:', error);
            return false;
        }
    }
}
exports.InventoryApiClient = InventoryApiClient;
function createApiClient() {
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