# PHP Test Runner Local Config Template

Copy this file to `.copilot/local/php-test-runner.md` and fill values for your machine. Do not commit the copied file.

## Workspace

- host_workspace_root: `<ABSOLUTE_HOST_WORKSPACE_ROOT>`
- container_workspace_root: `/workspaces/inventory-app`
- devcontainer_image: `inventory-app-dev`

## Docker Volumes

- php_vendor_volume: `inv-app-php-vendor`
- root_node_modules_volume: `inv-app-node-modules`
- spa_node_modules_volume: `inv-app-spa-node-modules`
- importer_node_modules_volume: `inv-app-importer-node-modules`

## Notes

Only needed when running the manual Docker commands outside the VS Code Dev Container.
