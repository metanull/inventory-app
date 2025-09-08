---
applyTo: "**/*.ps1,**/*.psm1"
---
# Project PowerShell scripting guidelines

- **CRITICAL: Strictly comply with PowerShell coding standard.**
- **CRITICAL: Strictly comply with PowerShell best practices and guidelines.**
- **CRITICAL: Comply with PowerShell approved verbs.**
- Organize scripts in a logical and consistent directory structure.
- Keep scripts simple and focused on a single behavior.
- Keep scripts isolated and independent from each other.
- Use meaningful script names that describe their purpose and functionality.
- Include comments in scripts to explain complex logic and provide context.
- Use consistent naming conventions for variables and functions.
- Avoid using hard-coded values; use `psd1` configuration files instead.
- **CRITICAL: Scripts must pass `Invoke-ScriptAnalyser` controls.**