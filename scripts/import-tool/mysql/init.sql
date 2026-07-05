-- Only runs on first initialization of a fresh local-mysql volume (Docker's
-- official mysql image only executes /docker-entrypoint-initdb.d/* when the
-- data directory is empty). If local-mysql-data already has data, this file
-- is not re-run — an existing volume that predates this file needs the same
-- ALTER USER run manually once.
--
-- Why: MySQL 8's default caching_sha2_password auth plugin isn't supported
-- by the Alpine-based MariaDB client used elsewhere in this tool's own image
-- ("Plugin caching_sha2_password could not be loaded" — the plugin's .so
-- isn't packaged, not an SSL/config issue). local-mysql is a disposable,
-- local-only, non-production instance with hardcoded dev credentials, so
-- switching its auth plugin to the older, universally-supported
-- mysql_native_password is a safe, narrowly-scoped fix — it only affects this
-- container's own throwaway user, never OVH's real database or credentials.
ALTER USER 'inventory'@'%' IDENTIFIED WITH mysql_native_password BY 'secret';
FLUSH PRIVILEGES;
