"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.LegacyDatabase = void 0;
exports.createLegacyDatabase = createLegacyDatabase;
const promise_1 = __importDefault(require("mysql2/promise"));
class LegacyDatabase {
    config;
    connection = null;
    constructor(config) {
        this.config = config;
    }
    async connect() {
        this.connection = await promise_1.default.createConnection({
            host: this.config.host,
            port: this.config.port,
            user: this.config.user,
            password: this.config.password,
            multipleStatements: false,
        });
    }
    async disconnect() {
        if (this.connection) {
            await this.connection.end();
            this.connection = null;
        }
    }
    async query(sql, params) {
        if (!this.connection) {
            throw new Error('Database not connected');
        }
        const [rows] = await this.connection.execute(sql, params);
        return rows;
    }
    async queryOne(sql, params) {
        const rows = await this.query(sql, params);
        return rows[0] ?? null;
    }
    isConnected() {
        return this.connection !== null;
    }
}
exports.LegacyDatabase = LegacyDatabase;
function createLegacyDatabase() {
    const config = {
        host: process.env['LEGACY_DB_HOST'] || 'localhost',
        port: parseInt(process.env['LEGACY_DB_PORT'] || '3306', 10),
        user: process.env['LEGACY_DB_USER'] || 'root',
        password: process.env['LEGACY_DB_PASSWORD'] || '',
    };
    return new LegacyDatabase(config);
}
//# sourceMappingURL=LegacyDatabase.js.map