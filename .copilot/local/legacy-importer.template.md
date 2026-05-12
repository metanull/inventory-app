# Legacy Importer Local Config Template

Copy this file to `.copilot/local/legacy-importer.md` and fill values for your machine. Do not commit the copied file.

## Workspace

- workspace_root: `<ABSOLUTE_WORKSPACE_ROOT>`
- importer_dir: `<ABSOLUTE_WORKSPACE_ROOT>/scripts/importer`
- local_laravel_root: `<ABSOLUTE_WORKSPACE_ROOT>`

## Local Target Profile

- legacy_db_host: `<LEGACY_DB_HOST>`
- legacy_db_port: `<LEGACY_DB_PORT>`
- legacy_db_database: `<LEGACY_DB_DATABASE>`
- local_target_db_host: `<LOCAL_TARGET_DB_HOST>`
- local_target_db_port: `<LOCAL_TARGET_DB_PORT>`
- local_target_db_database: `<LOCAL_TARGET_DB_DATABASE>`
- legacy_images_root: `<LEGACY_IMAGES_ROOT>`
- local_target_images_root: `<LOCAL_TARGET_IMAGES_ROOT>`

## Production Windows Target Profile

- pssession_computer_name: `<WINDOWS_SERVER_HOST_OR_ALIAS>`
- production_app_root: `<WINDOWS_PRODUCTION_APP_ROOT>`
- temp_app_root: `<WINDOWS_TEMP_APP_ROOT>`
- temp_importer_dir: `<WINDOWS_TEMP_IMPORTER_DIR>`
- legacy_images_root: `<WINDOWS_LEGACY_IMAGES_ROOT>`

## OVH Target Profile

- ovh_host: `<OVH_HOST_OR_ALIAS>`
- ovh_deploy_user: `<OVH_DEPLOY_USER>`
- ovh_deploy_ssh_key: `<LOCAL_PATH_TO_DEPLOY_KEY>`
- ovh_app_root: `<OVH_APP_ROOT>`
- ovh_shared_pictures_dir: `<OVH_SHARED_PICTURES_DIR>`
- ovh_db_tunnel_local_host: `127.0.0.1`
- ovh_db_tunnel_local_port: `<LOCAL_TUNNEL_PORT>`
- ovh_db_tunnel_remote_host: `127.0.0.1`
- ovh_db_tunnel_remote_port: `3306`
- ovh_local_image_temp_dir: `<LOCAL_TEMP_IMAGE_DIR>`
- ovh_db_credentials_path: `<OVH_DB_CREDENTIALS_PATH>`

## Users To Recreate After Full Reset

List only non-secret user bootstrap data you are allowed to use in this environment.

| email | password_or_policy | role | verify_email |
|-------|--------------------|------|--------------|
| `<EMAIL>` | `<PASSWORD_OR_POLICY>` | `<ROLE>` | `<true_or_false>` |

## Secrets

Do not store passwords, private keys, or tokens here. Read secrets from the environment-specific `.env`, GitHub Environment secrets, or the server-side credentials file named above.
