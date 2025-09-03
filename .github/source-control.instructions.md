**CRITICAL: Never commit directly to the main branch.**
**CRITICAL: Never push the main branch to the remote repository.**
**CRITICAL: On commit always store the commit message in a temporary markdown file (temp\_\*.md); and let git use that file as an input to avoid escaping issues.**
**CRITICAL: On pr creation always store the pr description in a temporary markdown file (temp\_\*.md); and let git use that file as an input to avoid escaping issues.**
**CRITICAL: Always create a new branch for pull-requests (pr).**
**CRITICAL: Always use the `feature/` or `fix/` prefix for the branch name, depending on the type of changes.**
**CRITICAL: Always use `gh pr` to manage pull requests.**
**CRITICAL: With `gh pr create` always escape the `--assignee @me` like this: `--assignee "@me"`.**
**CRITICAL: With `gh pr create` never use `--label`, `--merge`, `--auto`. Instead create the pr first, then make it 'auto-merge' in a second instruction with `gh pr merge`.**
**CRITICAL: With `gh pr merge` always make the pr auto-merge in squash mode with `--squash --auto`.**
- The repository uses Git for version control.
- The repository uses GitHub for hosting the code.
- The repository uses GitHub issues to track bugs and feature requests.
- The repository uses GitHub pull requests to review and merge code changes.
- The repository uses GitHub Actions for continuous integration and deployment.
- The repository uses GitHub Actions to run tests and code quality checks.
- github cli is available as `gh` command in the terminal.
- The default branch is `main`.
- The repository has GitHub rulesets configured for code quality and security:
    - **no-force-push no-delete**: Prevents force pushes and branch deletion
    - **requires-codeQL-scanning**: Mandates CodeQL security analysis
    - **requires-linear-history**: Enforces linear git history (no merge commits)
    - **requires-pull-request**: Requires pull requests for all changes with the following bypass permissions:
        - Repository administrators can bypass review requirements
        - Dependabot can bypass review requirements for dependency updates
        - All other contributors must have their pull requests reviewed before merging
- Before pull requests, update the `CHANGELOG.md` file to reflect the changes made.
