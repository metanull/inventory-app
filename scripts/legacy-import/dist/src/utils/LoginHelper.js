"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.LoginHelper = void 0;
exports.quickLogin = quickLogin;
const inventory_app_api_client_1 = require("@metanull/inventory-app-api-client");
const promises_1 = require("readline/promises");
const process_1 = require("process");
const fs_1 = require("fs");
const path_1 = require("path");
class LoginHelper {
    baseUrl;
    constructor(baseUrl) {
        this.baseUrl = baseUrl || process.env['API_BASE_URL'] || 'http://localhost:8000/api';
    }
    /**
     * Prompt user for credentials interactively
     */
    async promptCredentials() {
        const rl = (0, promises_1.createInterface)({ input: process_1.stdin, output: process_1.stdout });
        try {
            const email = await rl.question('Email: ');
            const password = await rl.question('Password: ', { signal: AbortSignal.timeout(60000) });
            return { email, password, deviceName: 'legacy-import-cli' };
        }
        finally {
            rl.close();
        }
    }
    /**
     * Authenticate with API and get access token
     */
    async login(credentials) {
        const config = new inventory_app_api_client_1.Configuration({
            basePath: this.baseUrl,
        });
        const authApi = new inventory_app_api_client_1.MobileAppAuthenticationApi(config);
        try {
            console.log('Authenticating with API...');
            const response = await authApi.tokenAcquire({
                email: credentials.email,
                password: credentials.password,
                device_name: credentials.deviceName || 'legacy-import-cli',
            });
            if (!response.data.token) {
                throw new Error('No token returned from API');
            }
            console.log('✓ Authentication successful');
            return response.data.token;
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            console.error('✗ Authentication failed:', message);
            throw new Error(`Login failed: ${message}`);
        }
    }
    /**
     * Save token to .env file
     */
    saveToken(token) {
        const envPath = (0, path_1.resolve)(process.cwd(), '.env');
        let envContent = '';
        // Read existing .env if it exists
        if ((0, fs_1.existsSync)(envPath)) {
            envContent = (0, fs_1.readFileSync)(envPath, 'utf-8');
        }
        else {
            // Copy from .env.example if .env doesn't exist
            const examplePath = (0, path_1.resolve)(process.cwd(), '.env.example');
            if ((0, fs_1.existsSync)(examplePath)) {
                envContent = (0, fs_1.readFileSync)(examplePath, 'utf-8');
            }
        }
        // Update or add API_TOKEN
        const tokenRegex = /^API_TOKEN=.*$/m;
        if (tokenRegex.test(envContent)) {
            envContent = envContent.replace(tokenRegex, `API_TOKEN=${token}`);
        }
        else {
            // Add API_TOKEN after API_BASE_URL if it exists
            const baseUrlRegex = /^API_BASE_URL=.*$/m;
            if (baseUrlRegex.test(envContent)) {
                envContent = envContent.replace(baseUrlRegex, (match) => `${match}\nAPI_TOKEN=${token}`);
            }
            else {
                envContent += `\nAPI_TOKEN=${token}\n`;
            }
        }
        (0, fs_1.writeFileSync)(envPath, envContent, 'utf-8');
        console.log(`✓ Token saved to ${envPath}`);
    }
    /**
     * Test the token by making an API call
     */
    async testToken(token) {
        const config = new inventory_app_api_client_1.Configuration({
            basePath: this.baseUrl,
            accessToken: token,
        });
        const languageApi = new inventory_app_api_client_1.LanguageApi(config);
        try {
            console.log('Testing token...');
            await languageApi.languageIndex();
            console.log('✓ Token is valid');
            return true;
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            console.error('✗ Token test failed:', message);
            return false;
        }
    }
    /**
     * Complete login flow: prompt, authenticate, save, test
     */
    async loginFlow() {
        console.log(`\nLogin to Inventory Management API`);
        console.log(`API URL: ${this.baseUrl}\n`);
        const credentials = await this.promptCredentials();
        const token = await this.login(credentials);
        this.saveToken(token);
        await this.testToken(token);
        return token;
    }
}
exports.LoginHelper = LoginHelper;
/**
 * Quick login helper for CLI usage
 */
async function quickLogin(baseUrl) {
    const helper = new LoginHelper(baseUrl);
    return await helper.loginFlow();
}
//# sourceMappingURL=LoginHelper.js.map