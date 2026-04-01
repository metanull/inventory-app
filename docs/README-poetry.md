# Managing `docs/` dependencies with Poetry

This project can use Poetry for deterministic Python dependency management. Poetry provides a `pyproject.toml` + `poetry.lock` workflow similar to `package-lock.json` or `composer.lock`.

Quick guide:

- To regenerate the lockfile locally (optional):

```bash
python -m pip install --upgrade pip
python -m pip install poetry
cd docs
poetry lock
```

- CI-friendly install (no virtualenv):

```bash
# inside GitHub Actions or a CI step
python -m pip install --upgrade pip
python -m pip install poetry
cd docs
poetry config virtualenvs.create false --local
poetry install --no-dev --no-interaction
```

Notes:

- We keep `docs/requirements.txt` for immediate compatibility; once you commit a `poetry.lock` you can remove the compiled `requirements.txt` if desired.
- Poetry is optional for local development — CI will install Poetry and run `poetry install` so developers don't need to install Poetry unless they want to regenerate the lockfile.
