import { Configuration, DefaultApi } from '@metanull/inventory-app-api-client';
import { createInterface } from 'readline/promises';
import { stdin as input, stdout as output } from 'process';
import { writeFileSync, readFileSync, existsSync } from 'fs';
import { resolve } from 'path';
export class LoginHelper {
  baseUrl;
  constructor(baseUrl) {
    this.baseUrl = baseUrl || process.env['API_BASE_URL'] || 'http://localhost:8000/api';
  }
  /**
   * Prompt user for credentials interactively
   */
  async promptCredentials() {
    const rl = createInterface({ input, output });
    try {
      const username = await rl.question('Username: ');
      const password = await rl.question('Password: ', { signal: AbortSignal.timeout(60000) });
      return { username, password };
    } finally {
      rl.close();
    }
  }
  /**
   * Authenticate with API and get access token
   */
  async login(credentials) {
    const api = new DefaultApi(
      new Configuration({
        basePath: this.baseUrl,
      })
    );
    try {
      console.log('Authenticating with API...');
      const response = await api.login({
        username: credentials.username,
        password: credentials.password,
      });
      if (!response.data.token) {
        throw new Error('No token returned from API');
      }
      console.log('✓ Authentication successful');
      return response.data.token;
    } catch (error) {
      console.error('✗ Authentication failed:', error.message);
      throw new Error(`Login failed: ${error.message}`);
    }
  }
  /**
   * Save token to .env file
   */
  saveToken(token) {
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
  async testToken(token) {
    const api = new DefaultApi(
      new Configuration({
        basePath: this.baseUrl,
        accessToken: token,
      })
    );
    try {
      console.log('Testing token...');
      await api.languageIndex();
      console.log('✓ Token is valid');
      return true;
    } catch (error) {
      console.error('✗ Token test failed:', error.message);
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
/**
 * Quick login helper for CLI usage
 */
export async function quickLogin(baseUrl) {
  const helper = new LoginHelper(baseUrl);
  return await helper.loginFlow();
}
//# sourceMappingURL=LoginHelper.js.map
