# Managing Python dependencies for `docs/` with pip-tools

This repository uses a compiled `docs/requirements.txt` for CI installs. To manage these dependencies with `pip-tools` (recommended workflow for reproducible, locked installs), follow the steps below.

Local workflow (developer):

1. Install pip-tools (only needed when you edit dependencies):

```bash
python -m pip install --upgrade pip setuptools wheel
python -m pip install pip-tools
```

2. Edit `docs/requirements.in` to add/remove top-level dependencies.

3. Rebuild the locked requirements file:

```bash
cd docs
pip-compile --output-file=requirements.txt requirements.in
# or to include hashes for pip's --require-hashes mode
pip-compile --generate-hashes --output-file=requirements.txt requirements.in
```

4. Commit both `docs/requirements.in` and the generated `docs/requirements.txt`.

CI behavior:

- The GitHub Actions job installs the compiled `docs/requirements.txt` prior to running docs-related Python scripts. No CI changes are required when you regenerate `requirements.txt` (only commit the updated file).

Notes:

- `pip-tools` is a development-time tool; it is not required to run CI. Commit the generated `requirements.txt` so CI installs deterministic versions.
- If you prefer `poetry` instead, we can migrate; `poetry.lock` behaves similarly to `package-lock.json`/`composer.lock`.
