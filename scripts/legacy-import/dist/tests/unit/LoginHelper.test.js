"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || (function () {
    var ownKeys = function(o) {
        ownKeys = Object.getOwnPropertyNames || function (o) {
            var ar = [];
            for (var k in o) if (Object.prototype.hasOwnProperty.call(o, k)) ar[ar.length] = k;
            return ar;
        };
        return ownKeys(o);
    };
    return function (mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k = ownKeys(mod), i = 0; i < k.length; i++) if (k[i] !== "default") __createBinding(result, mod, k[i]);
        __setModuleDefault(result, mod);
        return result;
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
const vitest_1 = require("vitest");
const LoginHelper_js_1 = require("../../src/utils/LoginHelper.js");
const fs_1 = require("fs");
// Mock fs module
vitest_1.vi.mock('fs', () => ({
    existsSync: vitest_1.vi.fn(),
    readFileSync: vitest_1.vi.fn(),
    writeFileSync: vitest_1.vi.fn(),
}));
// Mock API client
vitest_1.vi.mock('@metanull/inventory-app-api-client', () => {
    const mockTokenAcquire = vitest_1.vi.fn();
    const mockLanguageIndex = vitest_1.vi.fn();
    return {
        Configuration: vitest_1.vi.fn(),
        MobileAppAuthenticationApi: vitest_1.vi.fn(() => ({
            tokenAcquire: mockTokenAcquire,
        })),
        LanguageApi: vitest_1.vi.fn(() => ({
            languageIndex: mockLanguageIndex,
        })),
        __mockTokenAcquire: mockTokenAcquire,
        __mockLanguageIndex: mockLanguageIndex,
    };
});
(0, vitest_1.describe)('LoginHelper', () => {
    let helper;
    let mockTokenAcquire;
    let mockLanguageIndex;
    (0, vitest_1.beforeEach)(async () => {
        vitest_1.vi.clearAllMocks();
        // Get mocked functions
        const apiModule = await Promise.resolve().then(() => __importStar(require('@metanull/inventory-app-api-client')));
        mockTokenAcquire = apiModule.__mockTokenAcquire;
        mockLanguageIndex = apiModule.__mockLanguageIndex;
        helper = new LoginHelper_js_1.LoginHelper('http://test-api.local/api');
    });
    (0, vitest_1.describe)('login', () => {
        vitest_1.it.skip('should authenticate and return token', async () => {
            const mockToken = 'test-token-123';
            mockTokenAcquire.mockResolvedValue({
                data: { token: mockToken },
            });
            const token = await helper.login({
                email: 'test@example.com',
                password: 'testpass',
            });
            (0, vitest_1.expect)(token).toBe(mockToken);
            (0, vitest_1.expect)(mockTokenAcquire).toHaveBeenCalledWith(vitest_1.expect.objectContaining({
                email: 'test@example.com',
                password: 'testpass',
            }));
        });
        vitest_1.it.skip('should throw error if no token returned', async () => {
            mockTokenAcquire.mockResolvedValue({
                data: {},
            });
            await (0, vitest_1.expect)(helper.login({
                email: 'test@example.com',
                password: 'testpass',
            })).rejects.toThrow('No token returned from API');
        });
        vitest_1.it.skip('should throw error on authentication failure', async () => {
            mockTokenAcquire.mockRejectedValue(new Error('Invalid credentials'));
            await (0, vitest_1.expect)(helper.login({
                email: 'test@example.com',
                password: 'wrongpass',
            })).rejects.toThrow('Login failed: Invalid credentials');
        });
    });
    (0, vitest_1.describe)('saveToken', () => {
        (0, vitest_1.it)('should update existing API_TOKEN in .env', () => {
            const existingEnv = `API_BASE_URL=http://localhost:8000/api
API_TOKEN=old-token
DRY_RUN=true`;
            vitest_1.vi.mocked(fs_1.existsSync).mockReturnValue(true);
            vitest_1.vi.mocked(fs_1.readFileSync).mockReturnValue(existingEnv);
            helper.saveToken('new-token-456');
            (0, vitest_1.expect)(fs_1.writeFileSync).toHaveBeenCalledWith(vitest_1.expect.stringContaining('.env'), vitest_1.expect.stringContaining('API_TOKEN=new-token-456'), 'utf-8');
        });
        (0, vitest_1.it)('should add API_TOKEN if not present', () => {
            const existingEnv = `API_BASE_URL=http://localhost:8000/api
DRY_RUN=true`;
            vitest_1.vi.mocked(fs_1.existsSync).mockReturnValue(true);
            vitest_1.vi.mocked(fs_1.readFileSync).mockReturnValue(existingEnv);
            helper.saveToken('new-token-789');
            (0, vitest_1.expect)(fs_1.writeFileSync).toHaveBeenCalledWith(vitest_1.expect.stringContaining('.env'), vitest_1.expect.stringContaining('API_TOKEN=new-token-789'), 'utf-8');
        });
        (0, vitest_1.it)('should create .env from .env.example if not exists', () => {
            const exampleEnv = `API_BASE_URL=http://localhost:8000/api
API_TOKEN=`;
            vitest_1.vi.mocked(fs_1.existsSync).mockImplementation((path) => {
                return String(path).includes('.env.example');
            });
            vitest_1.vi.mocked(fs_1.readFileSync).mockReturnValue(exampleEnv);
            helper.saveToken('token-from-example');
            (0, vitest_1.expect)(fs_1.writeFileSync).toHaveBeenCalledWith(vitest_1.expect.stringContaining('.env'), vitest_1.expect.stringContaining('API_TOKEN=token-from-example'), 'utf-8');
        });
    });
    (0, vitest_1.describe)('testToken', () => {
        vitest_1.it.skip('should return true for valid token', async () => {
            mockLanguageIndex.mockResolvedValue({ data: [] });
            const isValid = await helper.testToken('valid-token');
            (0, vitest_1.expect)(isValid).toBe(true);
            (0, vitest_1.expect)(mockLanguageIndex).toHaveBeenCalled();
        });
        vitest_1.it.skip('should return false for invalid token', async () => {
            mockLanguageIndex.mockRejectedValue(new Error('Unauthorized'));
            const isValid = await helper.testToken('invalid-token');
            (0, vitest_1.expect)(isValid).toBe(false);
        });
    });
});
//# sourceMappingURL=LoginHelper.test.js.map