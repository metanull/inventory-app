# Windows Server Local Config Template

Copy this file to `.copilot/local/server-windows.md` and fill values for your access path. Do not commit the copied file.

## Connection

- pssession_computer_name: `<WINDOWS_SERVER_HOST_OR_ALIAS>`
- vpn_required: `<true_or_false>`

## Paths

- server_root: `<WINDOWS_SERVER_ROOT>`
- apps_root: `<WINDOWS_APPS_ROOT>`
- dynapps_root: `<WINDOWS_DYNAPPS_ROOT>`
- configuration_root: `<WINDOWS_CONFIGURATION_ROOT>`
- software_root: `<WINDOWS_SOFTWARE_ROOT>`
- pictures_root: `<WINDOWS_PICTURES_ROOT>`
- github_apps_root: `<WINDOWS_GITHUB_APPS_ROOT>`
- inventory_production_root: `<WINDOWS_INVENTORY_PRODUCTION_ROOT>`
- inventory_temp_root: `<WINDOWS_INVENTORY_TEMP_ROOT>`
- inventory_laravel_log: `<WINDOWS_INVENTORY_LARAVEL_LOG>`

## Public And Private Hosts

- inventory_url: `<INVENTORY_URL>`
- backoffice_host: `<BACKOFFICE_HOST>`

## Notes

Keep secrets and credentials out of this file.
