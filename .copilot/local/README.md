# Contributor-Local Copilot Config

Files in this directory let shared Copilot agents and instructions stay valid for every contributor while each contributor keeps local paths, host aliases, SSH key paths, and bootstrap user lists on their own machine.

- `*.template.md` files are committed and safe to share.
- Copy a template to the same name without `.template`, then fill in your local values.
- Non-template files in this directory are ignored by Git.
- Do not store secrets, passwords, private key contents, tokens, or production `.env` values here.

Shared agents and instructions must read the relevant local file first. If it is missing or incomplete, they must ask the user for the missing values before executing commands.
