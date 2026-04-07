# NSCx56WB Copilot Instructions

## Project Overview

This repository is a National Skills Competition project workspace.
Implement and maintain the solution according to the official competition requirements in `docs/`.

Main implementation stack for this project should follow the available tools and packages explicitly allowed by the competition environment.

## Highest Priority Rule (Non-Negotiable)

Always implement according to the competition documents in this order:
1. `docs/infra.md` (available environment and tool constraints)
2. `docs/plan.md` (functional requirements and scoring items)

If there is any conflict between existing code and these documents, update code to match `docs/infra.md` and `docs/plan.md`.

## Implementation Rules

- Do not introduce frameworks/tools that are not available per `docs/infra.md`.
- Keep implementation aligned with all required routes/behaviors/features in `docs/plan.md`.
- Prioritize scoring-related items from the plan first.
- Preserve maintainable project structure and clear code organization.
- Keep security basics in place: input validation, path traversal prevention where applicable, and stable error handling.

## Change Policy

- Before major refactors, verify they still satisfy `docs/plan.md` scoring points.
- Prefer minimal, targeted changes over broad rewrites unless compliance requires larger changes.
- Any new behavior should be justified by explicit requirements from `docs/plan.md`.

## Output Expectation for Copilot

When asked to implement or review:
- First check compliance with `docs/infra.md` and `docs/plan.md`.
- Then propose/apply fixes that maximize competition scoring and compatibility.
- If a requested approach violates infra constraints, reject that approach and provide a compliant alternative.
