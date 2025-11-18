import { describe, it, expect, beforeEach, vi } from 'vitest';
import { LoginHelper } from '../../src/utils/LoginHelper.js';
import { existsSync, readFileSync, writeFileSync } from 'fs';

// Mock fs module
vi.mock('fs', () => ({
  existsSync: vi.fn(),
  readFileSync: vi.fn(),
  writeFileSync: vi.fn(),
}));

// Mock API client
vi.mock('@metanull/inventory-app-api-client', () => {
  const mockTokenAcquire = vi.fn();
  const mockLanguageIndex = vi.fn();

  return {
    Configuration: vi.fn(),
    MobileAppAuthenticationApi: vi.fn(() => ({
      tokenAcquire: mockTokenAcquire,
    })),
    LanguageApi: vi.fn(() => ({
      languageIndex: mockLanguageIndex,
    })),
    __mockTokenAcquire: mockTokenAcquire,
    __mockLanguageIndex: mockLanguageIndex,
  };
});

describe('LoginHelper', () => {
  let helper: LoginHelper;
  let mockTokenAcquire: ReturnType<typeof vi.fn>;
  let mockLanguageIndex: ReturnType<typeof vi.fn>;

  beforeEach(async () => {
    vi.clearAllMocks();

    // Get mocked functions
    const apiModule = await import('@metanull/inventory-app-api-client');
    mockTokenAcquire = (
      apiModule as typeof apiModule & {
        __mockTokenAcquire: ReturnType<typeof vi.fn>;
        __mockLanguageIndex: ReturnType<typeof vi.fn>;
      }
    ).__mockTokenAcquire;
    mockLanguageIndex = (
      apiModule as typeof apiModule & {
        __mockTokenAcquire: ReturnType<typeof vi.fn>;
        __mockLanguageIndex: ReturnType<typeof vi.fn>;
      }
    ).__mockLanguageIndex;

    helper = new LoginHelper('http://test-api.local/api');
  });

  describe('login', () => {
    it.skip('should authenticate and return token', async () => {
      const mockToken = 'test-token-123';
      mockTokenAcquire.mockResolvedValue({
        data: { token: mockToken },
      });

      const token = await helper.login({
        email: 'test@example.com',
        password: 'testpass',
      });

      expect(token).toBe(mockToken);
      expect(mockTokenAcquire).toHaveBeenCalledWith(
        expect.objectContaining({
          email: 'test@example.com',
          password: 'testpass',
        })
      );
    });

    it.skip('should throw error if no token returned', async () => {
      mockTokenAcquire.mockResolvedValue({
        data: {},
      });

      await expect(
        helper.login({
          email: 'test@example.com',
          password: 'testpass',
        })
      ).rejects.toThrow('No token returned from API');
    });

    it.skip('should throw error on authentication failure', async () => {
      mockTokenAcquire.mockRejectedValue(new Error('Invalid credentials'));

      await expect(
        helper.login({
          email: 'test@example.com',
          password: 'wrongpass',
        })
      ).rejects.toThrow('Login failed: Invalid credentials');
    });
  });

  describe('saveToken', () => {
    it('should update existing API_TOKEN in .env', () => {
      const existingEnv = `API_BASE_URL=http://localhost:8000/api
API_TOKEN=old-token
DRY_RUN=true`;

      vi.mocked(existsSync).mockReturnValue(true);
      vi.mocked(readFileSync).mockReturnValue(existingEnv);

      helper.saveToken('new-token-456');

      expect(writeFileSync).toHaveBeenCalledWith(
        expect.stringContaining('.env'),
        expect.stringContaining('API_TOKEN=new-token-456'),
        'utf-8'
      );
    });

    it('should add API_TOKEN if not present', () => {
      const existingEnv = `API_BASE_URL=http://localhost:8000/api
DRY_RUN=true`;

      vi.mocked(existsSync).mockReturnValue(true);
      vi.mocked(readFileSync).mockReturnValue(existingEnv);

      helper.saveToken('new-token-789');

      expect(writeFileSync).toHaveBeenCalledWith(
        expect.stringContaining('.env'),
        expect.stringContaining('API_TOKEN=new-token-789'),
        'utf-8'
      );
    });

    it('should create .env from .env.example if not exists', () => {
      const exampleEnv = `API_BASE_URL=http://localhost:8000/api
API_TOKEN=`;

      vi.mocked(existsSync).mockImplementation((path: unknown) => {
        return String(path).includes('.env.example');
      });
      vi.mocked(readFileSync).mockReturnValue(exampleEnv);

      helper.saveToken('token-from-example');

      expect(writeFileSync).toHaveBeenCalledWith(
        expect.stringContaining('.env'),
        expect.stringContaining('API_TOKEN=token-from-example'),
        'utf-8'
      );
    });
  });

  describe('testToken', () => {
    it.skip('should return true for valid token', async () => {
      mockLanguageIndex.mockResolvedValue({ data: [] });

      const isValid = await helper.testToken('valid-token');

      expect(isValid).toBe(true);
      expect(mockLanguageIndex).toHaveBeenCalled();
    });

    it.skip('should return false for invalid token', async () => {
      mockLanguageIndex.mockRejectedValue(new Error('Unauthorized'));

      const isValid = await helper.testToken('invalid-token');

      expect(isValid).toBe(false);
    });
  });
});
