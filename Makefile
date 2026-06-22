.PHONY: help docs docs-serve docs-generate docs-model

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-20s %s\n", $$1, $$2}'

# ── Documentation ─────────────────────────────────────────────────────────────

docs-model: ## Regenerate model docs via artisan  [requires: app container running]
	docker compose exec app php artisan docs:model

docs-generate: ## Regenerate commit + client docs  [starts a one-off docs container]
	docker compose run --rm docs sh -c "cd /workspace && python3 scripts/generate-commit-docs.py && python3 scripts/generate-client-docs.py"

docs-serve: ## Start the Jekyll dev server at http://localhost:4000
	docker compose up docs

docs: docs-generate docs-serve ## Regenerate docs then start the Jekyll dev server
