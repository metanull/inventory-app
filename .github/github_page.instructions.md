---
applyTo: "docs/"
---
**CRITICAL: The `docs/` directory contains a Ruby application based on Jekyll.**
**CRITICAL: The build process is automated using a CI/CD pipeline.**
**CRITICAL: Do not modify the build scripts directly.**
**CRITICAL: Do not build the docs/ directory manually.**
**CRITICAL: The build process is based on the Jekyll framework. It takes the markdown files in the docs/ directory and generates a static website.**
**CRITICAL: Always use `wsl bash -c 'COMMANDS'` instead of PowerShell to interact with Ruby.**
  - Example: `wsl bash -lc 'cd docs && PATH="$HOME/.local/share/gem/ruby/3.2.0/bin:$PATH" && bundle exec jekyll build'`
