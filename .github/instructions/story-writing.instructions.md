---
description: "Use when writing implementation stories, GitHub issue-ready stories, epics, milestones, task breakdowns, or change specifications for this project."
---
# Story Writing Guidelines

- Write stories as Markdown documents that are ready to copy or convert into GitHub issues.
- Write each story for exactly one change. If the requested change contains multiple independent changes, split it into multiple stories.
- Keep stories brief but exhaustive: include all context and constraints needed for an implementer to complete the work without asking follow-up questions.
- Make every story unambiguous. Do not include choices, open questions, optional approaches, or alternative implementations.
- Align every story with the project's active rules, milestone constraints, architecture, file-specific instructions, and testing conventions that apply to the requested change.
- Verify whether the requested behavior or related implementation already exists before writing implementation instructions.
- When user asks to create epic/stories in GitHub, proceed in sequence to avoid race conditions. Github doesn't support "epic"/"stories" as first-class entities, so you must create the epic issue first, then create child stories that link to the epic. Do not create any child story until the epic issue is created and its URL is available to include in the child stories.
- Stories must be self-contained and not rely on implicit references, conversations, or decisions. If the story requires information from those sources, include it in the story itself.
- Stories must be actionable and not require further analysis or interpretation. They must not contain questions or open choices. Clarify with the user before writing the story if the request is ambiguous or contains multiple options.
- Stories must not contain comments or statement related to the story writing process itself. They must be focused on the implementation task and not include meta-level discussion.

## Required Story Structure

Every story must include these sections:

- `Title`: name the single change in imperative or outcome-focused language.
- `Context`: explain the relevant project state, user-facing workflow, milestone, architectural boundary, and existing code pattern.
- `Problem`: describe the exact gap, defect, or missing behavior the story addresses.
- `Solution`: state the required end state in direct terms.
- `Implementation Instructions`: provide precise, ordered instructions that follow the established project patterns and name the expected files, components, routes, tests, or services when known.
- `Definition of Done`: list all acceptance criteria, including required validation.

## Definition of Done Requirements

Every story's `Definition of Done` must include:

- The requested behavior is implemented and follows the applicable project conventions.
- Relevant automated tests are added or updated for the business logic and user-visible behavior.
- Lint checks pass without warnings or errors.
- Relevant test suites pass without warnings or errors.
- No unrelated behavior, migrations, dependencies, or documentation are changed unless the story explicitly requires them.
- Code is linted
- All tests are passing
- No warnings or errors in lint or tests

## Epics And Milestones

- Create an epic instead of a story when the change is too large to implement safely as one autonomous story.
- An epic must include context, the larger problem description, the high-level solution, cross-cutting constraints, and child stories.
- Create a milestone when multiple epics are required, when stories span different domains, or when the work has meaningful dependencies between domains.
- A milestone must include high-level context, scope boundaries, epics, child stories, dependency order, and completion criteria.
- Child stories under an epic or milestone must still follow the required story structure and describe exactly one change each.