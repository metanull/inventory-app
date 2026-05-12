# OVH Server Local Config Template

Copy this file to `.copilot/local/server-ovh.md` and fill values for your access path. Do not commit the copied file.

## Connection

- host: `<OVH_HOST_OR_ALIAS>`
- deploy_user: `<DEPLOY_USER>`
- deploy_ssh_key: `<LOCAL_PATH_TO_DEPLOY_KEY>`
- admin_user: `<ADMIN_USER>`
- admin_ssh_key: `<LOCAL_PATH_TO_ADMIN_KEY>`

## Application Paths

- app_root: `<OVH_APP_ROOT>`
- current_symlink: `<OVH_CURRENT_SYMLINK>`
- releases_root: `<OVH_RELEASES_ROOT>`
- shared_root: `<OVH_SHARED_ROOT>`
- shared_env: `<OVH_SHARED_ENV>`
- shared_storage: `<OVH_SHARED_STORAGE>`
- shared_pictures_dir: `<OVH_SHARED_PICTURES_DIR>`
- laravel_log: `<OVH_LARAVEL_LOG>`
- queue_worker_log: `<OVH_QUEUE_WORKER_LOG>`
- backups_root: `<OVH_BACKUPS_ROOT>`

## Runtime

- domain: `<OVH_DOMAIN>`
- nginx_vhost: `<OVH_NGINX_VHOST>`
- queue_service: `<OVH_QUEUE_SERVICE>`
- redis_db: `<REDIS_DB>`
- redis_cache_db: `<REDIS_CACHE_DB>`
- db_credentials_path: `<OVH_DB_CREDENTIALS_PATH>`

## Notes

Keep secrets and private key contents out of this file.
