import {
  Configuration,
  MobileAppAuthenticationApi,
  LanguageApi,
} from '@metanull/inventory-app-api-client';
import { createInterface } from 'readline/promises';
import { stdin as input, stdout as output } from 'process';
import { writeFileSync, readFileSync, existsSync } from 'fs';
import { resolve } from 'path';

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

export class LoginHelper {
  private baseUrl: string;

  constructor(baseUrl?: string) {
    this.baseUrl = baseUrl || process.env['API_BASE_URL'] || 'http://localhost:8000/api';
  }

  /**
   * Prompt user for credentials interactively
   */
  async promptCredentials(): Promise<LoginCredentials> {
    const rl = createInterface({ input, output });

    try {
      const email = await rl.question('Email: ');
      const password = await rl.question('Password: ', { signal: AbortSignal.timeout(60000) });

      return { email, password, deviceName: 'legacy-import-cli' };
    } finally {
      rl.close();
    }
  }

  /**
   * Authenticate with API and get access token
   */
  async login(credentials: LoginCredentials): Promise<string> {
    const config = new Configuration({
      basePath: this.baseUrl,
    });
    const authApi = new MobileAppAuthenticationApi(config);

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
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      console.error('✗ Authentication failed:', message);
      throw new Error(`Login failed: ${message}`);
    }
  }

  /**
   * Save token to .env file
   */
  saveToken(token: string): void {
    const envPath = resolve(process.cwd(), '.env');
    let envContent = '';

    // Read existing .env if it exists
    if (existsSync(envPath)) {
      envContent = readFileSync(envPath, 'utf-8');
    } else {
      // Copy from .env.example if .env doesn't exist
      const examplePath = resolve(process.cwd(), '.env.example');
      if (existsSync(examplePath)) {
        envContent = readFileSync(examplePath, 'utf-8');
      }
    }

    // Update or add API_TOKEN
    const tokenRegex = /^API_TOKEN=.*$/m;
    if (tokenRegex.test(envContent)) {
      envContent = envContent.replace(tokenRegex, `API_TOKEN=${token}`);
    } else {
      // Add API_TOKEN after API_BASE_URL if it exists
      const baseUrlRegex = /^API_BASE_URL=.*$/m;
      if (baseUrlRegex.test(envContent)) {
        envContent = envContent.replace(baseUrlRegex, (match) => `${match}\nAPI_TOKEN=${token}`);
      } else {
        envContent += `\nAPI_TOKEN=${token}\n`;
      }
    }

    writeFileSync(envPath, envContent, 'utf-8');
    console.log(`✓ Token saved to ${envPath}`);
  }

  /**
   * Test the token by making an API call
   */
  async testToken(token: string): Promise<boolean> {
    const config = new Configuration({
      basePath: this.baseUrl,
      accessToken: token,
    });
    const languageApi = new LanguageApi(config);

    try {
      console.log('Testing token...');
      await languageApi.languageIndex();
      console.log('✓ Token is valid');
      return true;
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      console.error('✗ Token test failed:', message);
      return false;
    }
  }

  /**
   * Complete login flow: prompt, authenticate, save, test
   */
  async loginFlow(): Promise<string> {
    console.log(`\nLogin to Inventory Management API`);
    console.log(`API URL: ${this.baseUrl}\n`);

    const credentials = await this.promptCredentials();
    const token = await this.login(credentials);
    this.saveToken(token);
    await this.testToken(token);

    return token;
  }
}

/**
 * Quick login helper for CLI usage
 */
export async function quickLogin(baseUrl?: string): Promise<string> {
  const helper = new LoginHelper(baseUrl);
  return await helper.loginFlow();
}
