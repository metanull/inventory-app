/**
 * Login helper to authenticate with the API and retrieve access token
 *
 * This utility:
 * 1. Prompts for username/password
 * 2. Authenticates via API
 * 3. Saves token to .env file
 * 4. Returns configured API client
 */
export interface LoginCredentials {
    email: string;
    password: string;
    deviceName?: string;
}
export declare class LoginHelper {
    private baseUrl;
    constructor(baseUrl?: string);
    /**
     * Prompt user for credentials interactively
     */
    promptCredentials(): Promise<LoginCredentials>;
    /**
     * Authenticate with API and get access token
     */
    login(credentials: LoginCredentials): Promise<string>;
    /**
     * Save token to .env file
     */
    saveToken(token: string): void;
    /**
     * Test the token by making an API call
     */
    testToken(token: string): Promise<boolean>;
    /**
     * Complete login flow: prompt, authenticate, save, test
     */
    loginFlow(): Promise<string>;
}
/**
 * Quick login helper for CLI usage
 */
export declare function quickLogin(baseUrl?: string): Promise<string>;
//# sourceMappingURL=LoginHelper.d.ts.map