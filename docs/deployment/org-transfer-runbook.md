---
layout: default
title: Organization Transfer Runbook
parent: Deployment Guide
nav_order: 10
---

# Organization Transfer Runbook

{: .no_toc }

The step-by-step procedure for transferring the thirteen-repository estate from
the personal account `metanull` to the org `museumwithnofrontiers`.

## Table of Contents
{: .no_toc .text-delta }

1. TOC
{:toc}

## Why the order matters

A GitHub organization transfer is not one action across the whole estate — it
is thirteen separate, one-at-a-time repository transfers, each of which some
other part of the platform depends on. Two facts about what a transfer does
and does not carry with it are the reason this runbook has phases at all
rather than a single checklist done in any order.

- **GitHub does not redirect Actions `uses:` references.** A transferred
  repository's own web and clone URLs redirect automatically — `git clone`,
  `git fetch`, and a browser hitting the old address all land in the new
  place. A reusable-workflow `uses:` reference does not get this treatment:
  Actions resolves it as a literal `owner/repo` lookup at run time, with no
  fallback to a prior location. Every one of the 48
  `uses: metanull/viewer-workflows/.github/workflows/*.yml@v1.7.0` lines
  spread across 11 repos breaks the moment `viewer-workflows` moves, on the
  very next push, in all 11 repos at once. This is unavoidable — there is no
  transfer setting that prevents it — so the whole point of the sequence
  below is to make that window as short as possible, and to make sure
  nothing is running inside it. Confirmed against
  [Reusing workflow configurations](https://docs.github.com/en/actions/reference/workflows-and-actions/reusing-workflow-configurations)
  and GitHub's [renaming a repository](https://docs.github.com/en/repositories/creating-and-managing-repositories/renaming-a-repository)
  guidance, which states plainly that GitHub will not redirect calls to an
  action hosted by a renamed (or transferred) repository, and that such
  workflow runs fail with a "repository not found" error.
- **GitHub Pages does not redirect either.** A repository's Pages
  *configuration* (source branch, build type) survives a transfer intact, but
  `https://metanull.github.io/<repo>/` does not forward visitors to
  `https://museumwithnofrontiers.github.io/<repo>/` — GitHub simply serves
  nothing at the old address afterwards. Eight repos serve Pages today (the
  seven public websites plus `inventory-app`'s docs site), and each one needs
  a manual post-transfer check that the new URL is actually live, not just
  that Pages is "enabled." GitHub's own
  [transfer documentation](https://docs.github.com/en/repositories/creating-and-managing-repositories/transferring-a-repository)
  says this directly: *"If the transferred repository contains a GitHub
  Pages site, then links to the Git repository on the Web and through Git
  activity are redirected. However, we don't redirect GitHub Pages
  associated with the repository."*

A third fact, found only after the scouting pass and specific to
repositories with meaningful Actions usage, is documented in Phase 1 below,
where it is most relevant.

Everything else in this document follows from containing the blast radius of
those facts: move the pieces that other things depend on first, stage the
fix before the breakage rather than after it, and verify the things GitHub's
own documentation does not promise.

## Phase 0 — prerequisites, no repo moves yet, all reversible

Nothing in this phase transfers a repository. Everything here can be undone
or simply left in place if the transfer is postponed.

### Org Actions policy

This cannot be checked from here — the credentials available during scouting
lack `admin:org`, and `gh api orgs/museumwithnofrontiers/actions/permissions`
403s under them:

```bash
gh api orgs/museumwithnofrontiers/actions/permissions
```

```powershell
gh api orgs/museumwithnofrontiers/actions/permissions
```

That 403 means the check could not be performed with the tokens available
during scouting, not that the policy is known to be wrong. **An org admin
must open the org's Settings → Actions page directly**
(`https://github.com/organizations/museumwithnofrontiers/settings/actions`)
and confirm, before any repo moves:

- Allowed actions and reusable workflows permits third-party actions and
  reusable workflows (every one of the 13 repos today has
  `allowed_actions: all` at the repo level; the org-level setting must not be
  more restrictive than that, or it silently overrides the repo setting).
- Default workflow permissions are at least read (every repo today has
  `default_workflow_permissions: read`; `inventory-app` additionally has
  `can_approve_pull_request_reviews: true`, which the org policy must also
  not narrow).

Treat this as unverified until an admin has looked at that page. Do not
assume the org's defaults match the personal account's.

### npmjs trusted publishing

Only three packages have a GitHub-bound trusted publisher on npmjs:
`@museumwnf/viewer-core`, `@museumwnf/viewer-layout`, and
`@museumwnf/viewer-i18n`. Each is bound to `metanull/<repo>` plus the
workflow filename `release.yml` — the *caller's* `release.yml` in each
package repo, not the reusable `package-release.yml` in `viewer-workflows`,
because npm authorizes the workflow that actually requested the OIDC token.
Neither `release.yml` sets an `environment:` for the publish job, so the
binding carries no GitHub Environment name — only the owner, the repo, and
the workflow filename. The seven `@museumwnf/<site>-data` packages are
published manually from an operator's own npm login and have no GitHub-bound
trusted publisher at all to break; there is nothing to do for them here.

The procedure, made possible by two facts about npmjs's trusted-publisher
settings: a package can hold up to ten trusted-publisher connections at once,
and npm does not validate a connection when it is saved — only when a publish
is actually attempted against it. That means a connection for a
not-yet-existing `owner/repo` pair can be created ahead of time.

On npmjs.com, for each of the three packages:

1. Open the package's Settings → Trusted publishing page.
2. Add a **second** trusted-publisher connection: owner/org
   `museumwithnofrontiers`, repository `<repo>`, workflow filename
   `release.yml`. Leave the existing `metanull/<repo>` connection in place —
   do not delete it yet.
3. Transfer the repo (Phase 2, for `viewer-core`/`viewer-layout`/`viewer-i18n`).
4. Confirm a release publishes successfully from the new location before
   relying on it (Phase 2 gives the exact check).
5. Only then, delete the stale `metanull/<repo>` connection as cleanup — this
   step is not urgent.

**Do not put any credential, token, or npm login detail in this document, or
anywhere else in this repository.** The steps above are npmjs.com UI actions
only; nothing here needs a secret to be written down.

npm's own documentation does not state whether a trusted-publisher connection
is matched against the GitHub OIDC token's owner/repo *name* claims or its
immutable numeric *id* claims. GitHub's OIDC token carries both, but npm's
configuration surface only exposes name fields, and its docs never say which
it validates. Treat the binding as **name-based** until npm's documentation
says otherwise — under that assumption, a transfer genuinely breaks the old
connection (the owner-name claim changes), which is exactly why the
two-connections approach above exists. **No release should be cut in
`viewer-core`, `viewer-layout`, or `viewer-i18n` between the transfer and
confirming the new connection works** — a release cut in that window would
fail the `npm publish --provenance` step with no existing binding to fall
back to.

### `@metanull/inventory-app-api-client` — no longer a Phase 4 gate

This package and the `/spa` app it served were removed from `inventory-app`
(see the companion PR that deletes the SPA and API client code and the
workflows that generated/published it). The rename decision this section
used to describe — what the package's new npm scope should be once
`inventory-app` moves to `museumwithnofrontiers` — no longer applies, because
there is no package left to rename or publish. Phase 4 is not gated on it.

One consequence of the removal is outside this runbook's scope but worth
flagging for whoever owns it: `metanull/inventory-management-ui` — a
repository that is **not** part of this transfer — depended on
`@metanull/inventory-app-api-client` via GitHub Packages. Deleting the
package's source does not by itself unpublish already-published versions
from GitHub Packages, but there will be no new versions after the removal.
Confirm with that repository's maintainer whether it needs to pin to the
last published version or migrate off the client.

### Merge the propagation-tool owner fix

`tools/propagate.mjs` in `viewer-workflows` discovers websites by calling
`gh api user` — the *operator's own* personal account — rather than an
explicit target owner. Once the estate is under the org, running the tool as
the personal `metanull` account will discover zero sites and exit
successfully having propagated nothing. A fix for this is being raised
separately as a pull request against `metanull/viewer-workflows`. Confirm it
is merged before starting Phase 1:

```bash
gh pr list -R metanull/viewer-workflows --search "propagate discoverSites owner"
```

```powershell
gh pr list -R metanull/viewer-workflows --search "propagate discoverSites owner"
```

Fill in the PR number here once it exists. Do not begin Phase 1 until this
fix has merged — otherwise the very first propagation run made against the
org's repositories will silently do nothing.

## Phase 1 — `viewer-workflows`, the hinge

`viewer-workflows` is the one repository every other repository's CI depends
on through `uses:` references, so it moves first, and it moves inside a
sequence designed to make the window from "the references stop resolving" to
"the references resolve again" as short as the tooling allows:

1. Prepare and **push one branch per consumer repository**, repointing every
   `uses:` line from `metanull/viewer-workflows/...@v1.7.0` to
   `museumwithnofrontiers/viewer-workflows/...@v1.7.0`. **Do not open the
   pull requests yet.** Every consumer workflow triggers on `pull_request`
   only, with no `push:` trigger — confirmed by reading
   `islamicart/.github/workflows/ci.yml` and
   `viewer-core/.github/workflows/ci.yml` on their default branches — so
   pushing the branch runs nothing. This step is free, reversible, and can
   be done unhurried, well before the transfer.
2. Freeze pushes to the estate. There is no single command for this — it is
   a coordination step: no PR gets merged and no branch gets pushed directly
   to `main` in any of the 13 repositories for the duration of the freeze,
   and Dependabot automerge (`merge-dependabot-pr.yml` in `inventory-app`,
   `automerge.yml` in every package and site repo) should not be allowed to
   merge anything mid-window either.
3. Transfer `viewer-workflows` from `metanull` to `museumwithnofrontiers`.
4. **Open the pull requests** from the branches pushed in step 1. This is
   the first time their checks run at all, so they resolve
   `museumwithnofrontiers/viewer-workflows/...@v1.7.0` and go green on the
   first attempt. Open one PR first and confirm it goes green before opening
   the remaining ten — if something unexpected is wrong, this finds it on
   one repository instead of eleven.
5. Merge them, then lift the freeze.

This ordering — branches pushed early, pull requests opened only after the
transfer — is deliberate, not incidental: opening the PRs before the
transfer would run their checks against a path that does not exist yet,
producing failed runs that then have to be coaxed into re-running inside the
window. Opening them afterwards means every check runs exactly once, against
the correct path. The slow part — writing the edits themselves — is fully
done in step 1 either way, so there is nothing to gain, and a failure mode
to avoid, by opening the PRs early.

The `@v1.7.0` tag itself needs no change — tags travel with the repository on
transfer, so the same pinned version resolves correctly at the new path once
the owner in the `uses:` line is corrected.

The affected repositories and exactly which files carry the reference, per
the pre-transfer audit:

| Repositories | `uses:` lines per repo | Files |
|---|---|---|
| `amulets`, `baroqueart`, `carpets`, `islamicart`, `sharinghistory`, `the-use-of-colours-in-art`, `water-in-islam`, `website-template` | 5 | `audit.yml`, `automerge.yml`, `ci.yml` (2 lines), `deploy.yml` |
| `viewer-core`, `viewer-layout` | 2 | `ci.yml`, `release.yml` |
| `viewer-i18n` | 4 | `automerge.yml`, `ci.yml` (2 lines), `release.yml` |

That is 48 lines across 11 repositories. Every one of them needs its branch
from step 1, pushed before the transfer in step 3.

### Repository name retirement

GitHub's transfer documentation describes a permanent side effect for
repositories with meaningful Marketplace or Actions usage. This was not
surfaced by the scouting pass, and it cuts both ways for this phase, so it
is recorded here in full:

> "If the transferred repository contains an action listed on GitHub
> Marketplace, or had more than 100 clones or more than 100 uses of GitHub
> Actions in the week prior to the transfer, GitHub permanently retires the
> owner name and repository name combination (`OWNER/REPOSITORY-NAME`) when
> you transfer the repository."
>
> "If you try to create a repository using a retired owner name and
> repository name combination, you will see the error: 'The repository
> `REPOSITORY_NAME` has been retired and cannot be reused.'"

— [Transferring a repository](https://docs.github.com/en/repositories/creating-and-managing-repositories/transferring-a-repository)

`viewer-workflows` is called by eleven other repositories across their
`ci`/`deploy`/`audit`/`automerge`/`release` workflows, so this will almost
certainly apply to it, and may apply to others in the estate — neither this
runbook nor the scouting pass measured weekly clone or Actions-usage counts
per repository, so this is not asserted as certain for any specific
repository beyond `viewer-workflows`.

This cuts both ways:

- **It closes a security hole.** Once `metanull/viewer-workflows` is
  retired, that exact owner/name combination can never be registered again
  by anyone — nobody can stand up a repository at the old address to serve
  a poisoned reusable workflow to a `uses:` line that was missed during the
  move. It also resolves the concern raised during scouting about the
  `.gitmodules` submodule URLs (Phase 4) relying on GitHub's ordinary
  clone-URL redirect: that redirect was flagged as fragile specifically
  because someone could, in principle, reclaim the old name later and break
  it. A retired name cannot be reclaimed, so that particular risk does not
  apply to `viewer-workflows` — or to any other repository in the estate
  that crosses the same threshold.
- **It forecloses one tempting mitigation.** Do not leave, or plan to leave,
  a stub repository at `metanull/viewer-workflows` to keep old `uses:`
  references resolving during the window. It would not have worked anyway —
  Actions has no forwarding mechanism for `uses:` references, which is the
  entire premise of the sequence above — and once the real transfer
  completes, it will not even be possible to create that stub: GitHub
  rejects the attempt with the retired-name error quoted above. Say this
  plainly so nobody spends time on it mid-window.

### Keep the window branches to the owner swap only

The branches from step 1 touch each consumer's `ci.yml`, among other files,
and `viewer-core/.github/workflows/ci.yml` already carries a stale comment
there: it justifies a `packages: read` permission grant by saying it
installs "the private `@museumwnf/*-data` packages," which have been public
on npmjs since M1. Do not fix that here. Keep every step-1 branch to a
one-line change per `uses:` reference and nothing else — sweep the unused
`packages:` grants and their inaccurate comments separately, afterwards
(Phase 5). Mixing a cleanup into the change that has to land inside the
freeze is how a simple merge turns into a debugging session at the worst
possible moment.

## Phase 2 — `viewer-core`, `viewer-layout`, `viewer-i18n`, `website-template`

None of these four repositories serve GitHub Pages, so the Pages concern from
"Why the order matters" does not apply here — only the `uses:` concern does,
and Phase 1 already prepared and merged the fix for their
`ci.yml`/`release.yml` references. Transfer all four now.

After each transfer, verify two things GitHub's own documentation does not
confirm either way:

- `website-template` still reports `is_template: true`.
- Each of the seven site repositories still reports a `template_repository`
  link back to `website-template`. This matters beyond curiosity: dynamic
  site discovery (`package-ci.yml`'s `dependents` job, and — once the Phase 0
  fix lands — `propagate.mjs`) depends on this field resolving correctly.
  GitHub's docs describe `template_repository` at creation time only and are
  silent on whether the link survives a transfer of either side of it, so
  this has to be spot-checked rather than assumed:

```bash
gh api repos/museumwithnofrontiers/islamicart --jq .template_repository
```

```powershell
gh api repos/museumwithnofrontiers/islamicart --jq .template_repository
```

Repeat for each of the seven site repositories (they have not transferred
yet at this point in the sequence, so this specific check happens again,
per-site, during Phase 3 — do it here only for whichever of the four Phase 2
repos you can already confirm, i.e. re-run it against `website-template`
itself to confirm `is_template` survived).

One more reference is deliberately **not** fixed in this phase:
`viewer-workflows/.github/workflows/locale-validate.yml` hardcodes
`repository: metanull/viewer-i18n` on an `actions/checkout` step, and every
site's `ci.yml` calls this reusable workflow. This keeps working through
GitHub's ordinary clone-URL redirect (`actions/checkout` is a plain git
clone, not a `uses:` resolution), so it is not part of the breakage this
runbook exists to contain. Fixing it now would mean cutting a
`viewer-workflows` release and running a full propagation cycle *during* the
transfer window, which adds surface area exactly when the goal is to keep
that window short. It is safer to defer this fix to the final cleanup
release (Phase 5), where it can be tested and rolled out on its own schedule
instead of being raced against the rest of the transfer.

## Phase 3 — the seven sites

`islamicart`, `baroqueart`, `sharinghistory`, `carpets`, `amulets`,
`the-use-of-colours-in-art`, `water-in-islam`.

Their `ci.yml`/`deploy.yml`/`audit.yml`/`automerge.yml` references were
already repointed and merged in Phase 1. Transfer each of the seven now.

After **each** transfer, verify Pages is both enabled and actually serving at
the new URL — this project's standing rule from prior site work is that a
green deploy run does not by itself prove Pages is live, and that rule
applies here just as much as it did to a from-scratch site:

```bash
gh api repos/museumwithnofrontiers/<site>/pages --jq '.html_url,.status'
curl -sI https://museumwithnofrontiers.github.io/<site>/ | head -n 1
```

```powershell
gh api repos/museumwithnofrontiers/<site>/pages --jq '.html_url,.status'
(Invoke-WebRequest -Uri "https://museumwithnofrontiers.github.io/<site>/" -Method Head -UseBasicParsing).StatusCode
```

Then open one pull request per site updating `src/dataset.config.js`'s
`site.origin` field from `https://metanull.github.io/<site>` to
`https://museumwithnofrontiers.github.io/<site>`. This single value drives
canonical links, permalinks, OpenGraph tags, and the sitemap — it is not
cosmetic.

The Vite `base` setting was checked and needs no change: every site's
`vite.config.js` sets `base` from an environment variable supplied by the
deploy workflow at build time, and that value encodes only the repository
name (e.g. `/carpets/`), never the owner. Nothing there references
`metanull`.

## Phase 4 — `inventory-app`

Transfer `inventory-app`. After the transfer:

- Verify the docs-site Pages deployment the same way as Phase 3 (Pages
  configuration survives, the URL does not):

```bash
gh api repos/museumwithnofrontiers/inventory-app/pages --jq '.html_url,.status'
curl -sI https://museumwithnofrontiers.github.io/inventory-app/ | head -n 1
```

```powershell
gh api repos/museumwithnofrontiers/inventory-app/pages --jq '.html_url,.status'
(Invoke-WebRequest -Uri "https://museumwithnofrontiers.github.io/inventory-app/" -Method Head -UseBasicParsing).StatusCode
```

- Rewrite the twelve `.new-architecture/` submodule URLs in `.gitmodules`
  from `https://github.com/metanull/<repo>` to
  `https://github.com/museumwithnofrontiers/<repo>` (one entry each for
  `viewer-core`, `viewer-layout`, `viewer-workflows`, `website-template`,
  `islamicart`, `baroqueart`, `sharinghistory`, `carpets`, `water-in-islam`,
  `amulets`, `the-use-of-colours-in-art`, `viewer-i18n`). Existing clones
  then need to re-read `.gitmodules` into their local submodule remote
  config before the next update:

```bash
git submodule sync --recursive
git submodule update --init --recursive
```

```powershell
git submodule sync --recursive
git submodule update --init --recursive
```

- Update `docs/_config.yml`: `repository`, `gh_edit_repository`, `url`,
  `nav_external_links`, and `aux_links`.
- Update the four `metanull`-referencing links in
  `resources/views/components/app-nav.blade.php` (the "Source Code" and
  "Project Docs" links, each appearing once in the desktop nav and once in
  the mobile nav).
- Update `.github/CODEOWNERS` so every path still resolves to an
  account or team with write access at the new location.

Two things are confirmed to need **no** change:

- Composer is entirely unaffected. `composer.json` has no `metanull`
  reference and no VCS `repositories:` block — every dependency resolves
  through ordinary Packagist packages.
- The OVH deploy path never talks to GitHub at all. `deploy-ovh.yml` ships a
  pre-built release artifact over SCP/SSH to the VPS; `deploy.sh` on the
  server side extracts a tarball and only ever references the
  `inventory.metanull.eu` domain (a DNS name, unrelated to the GitHub
  account). Nothing on the VPS changes as a result of this transfer.

## Phase 5 — the sweep

What is left after Phases 1 through 4 is prose and cleanup, not anything that
breaks CI or a live URL:

- READMEs and prose documentation across all thirteen repositories.
- `website-template`'s own README specifically — it documents the
  `https://metanull.github.io/<dataset>/` Pages URL pattern that every
  newly created site's admin copies by hand into that site's own
  `dataset.config.js`. Leaving this stale means every future site inherits
  the wrong pattern, not just this document.
- The `repository` field in each of the three shared packages'
  `package.json` (`viewer-core`, `viewer-layout`, `viewer-i18n`). Already
  published npm versions are immutable — correcting this only affects
  versions published after the fix, not anything already on the registry.
- The deferred `viewer-workflows/.github/workflows/locale-validate.yml` fix
  from Phase 2 (the hardcoded `repository: metanull/viewer-i18n` checkout).
  This needs its own `viewer-workflows` release and a full propagation cycle
  to every site — it is a normal platform change at this point, not a
  transfer-day emergency.
- The unused `packages: read` grant and its stale comment in
  `viewer-core/.github/workflows/ci.yml`, deferred from Phase 1: the comment
  justifies the grant by citing "the private `@museumwnf/*-data` packages,"
  which have been public on npmjs since M1, so the grant is no longer doing
  anything the workflow needs.

Also worth recording explicitly: **`dependents.json` does not exist anywhere
in the estate any more.** Site discovery is dynamic, via `template_repository`
lookups, not a hand-maintained file. If a stale project issue still asks for
`dependents.json` to be updated as part of this transfer, that issue is
describing a mechanism that has already been removed — close it or correct
it rather than acting on it.

## A "what survives a transfer" reference table

The distinction that matters throughout this document is between what
GitHub's documentation explicitly promises survives a transfer, and what it
simply does not mention — the second category is not a guarantee of safety,
only an absence of a claim either way, and every row in it needs a
post-transfer check rather than an assumption.

GitHub's own [transfer documentation](https://docs.github.com/en/repositories/creating-and-managing-repositories/transferring-a-repository)
states the two extremes directly, quoted here verbatim rather than
paraphrased:

> "If the transferred repository contains webhooks, services, secrets, or
> deploy keys, they will remain associated after the transfer is complete."

> "If the transferred repository contains a GitHub Pages site, then links to
> the Git repository on the Web and through Git activity are redirected.
> However, we don't redirect GitHub Pages associated with the repository."

The table below expands this into every category encountered in this
estate, including the ones GitHub's documentation does not address either
way.

| Thing | Survives a transfer? | Status |
|---|---|---|
| Actions secrets & variables | Yes | Documented — GitHub's transfer docs state secrets, webhooks, services, and deploy keys "remain associated" after a transfer. |
| Webhooks | Yes | Documented, same statement. (Moot for this estate: none of the 13 repos currently has any webhook configured.) |
| Deploy keys | Yes | Documented, same statement. Not individually re-checked against the live repos in this pass. |
| Issues, pull requests, wiki, stars, watchers | Yes | Documented explicitly. |
| Forks | Yes, remain associated | Documented explicitly. |
| Git/web URL redirects (clone URLs, repo web pages) | Yes | Documented explicitly — this is the redirect that does **not** extend to Actions `uses:` resolution or npm registry trust decisions (see "Why the order matters" and Phase 0). |
| GitHub Pages configuration (source branch, build type) | Yes | Documented explicitly. |
| GitHub Pages URL | **No** — does not redirect | Documented explicitly ("we don't redirect GitHub Pages associated with the repository"). Every Pages-enabled repo needs the manual check in Phases 3 and 4. |
| Dependabot secrets | Not stated either way | Verify — GitHub's docs only make the general "secrets... remain associated" statement; nothing Dependabot-specific was found. `inventory-app`'s `DEPENDABOT_GITHUB_PACKAGES_TOKEN` should be confirmed present post-transfer, not assumed. |
| Branch protection rules / rulesets | Not stated either way | Verify — all 13 repos carry protection rules or rulesets today; re-check each one after its transfer rather than assuming it carried over unchanged. |
| `is_template` flag (`website-template`) | Not stated either way | Verify — see the Phase 2 check. |
| `template_repository` link (the 7 sites) | Not stated either way | Verify — see the Phase 2 check; this one matters functionally, not just cosmetically, because site discovery depends on it. |
| npmjs trusted-publisher bindings | **No**, under the name-based assumption | Not GitHub's to document at all — this is npmjs.com state. See Phase 0; treated as broken by a transfer unless the two-connection approach is used first. |

## Standing rules

- **No credential, token, or secret value goes in this document or any
  commit that touches it.** Where a secret must exist at the new location,
  this runbook names it and says "confirm it is present" — nothing more.
- **No release is cut in `viewer-core`, `viewer-layout`, or `viewer-i18n`**
  between that repository's transfer and confirming the new npmjs
  trusted-publisher connection actually works.
- **Pushes to the whole estate are frozen for the duration of Phase 1**, from
  before the `viewer-workflows` transfer until its staged PRs are merged.
- **A green CI or deploy run never proves GitHub Pages is serving.** Check
  the URL itself, every time, for every one of the eight Pages-enabled
  repositories as it transfers.
- **Where GitHub's documentation is silent — branch protection, rulesets,
  `is_template`, `template_repository` continuity, Dependabot secrets —
  treat it as "must verify post-transfer," never as "probably fine."**
