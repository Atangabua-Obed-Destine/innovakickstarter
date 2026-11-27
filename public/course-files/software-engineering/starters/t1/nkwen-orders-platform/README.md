# Nkwen Orders Platform (squad repository)

Starter for Chapter 10, **Squad Sprint: The Nkwen Orders Platform**. One repository for the whole squad:

| Folder or file | What it is | Used in |
|---|---|---|
| `api/` | The Orders API (Laravel). Has a Dockerfile already. | Backend Build, Deployment |
| `web/` | The web shop and the manager page (React + TypeScript). | Frontend Build |
| `mobile/` | The sales-rep app (Flutter). | Mobile Build |
| `contract/` | The API contract, its examples, the contract tests, the platform's checks and the stand-in API. | System Design, Integration & Testing |
| `docs/` | Team, definition of done, sprint log, architecture, schema, wireframes, test plan, runbook. | Every activity |
| `checks/` | Self-checks for the planning and design documents. | Sprint Planning, System Design |
| `.github/` | Issue forms, the pull request template, CODEOWNERS and the CI workflow. | Sprint Planning, Team Git Workflow & CI |
| `docker-compose.yml`, `.env.example` | Run the API and MySQL together with one command. | Deployment |

## Commands (from the repository root, Node 22 or newer)
```
node --test "checks/planning.test.mjs"     # Sprint Planning self-check
node --test "checks/design.test.mjs"       # System Design self-check
node --test "contract/*.test.mjs"          # contract tests, against the stand-in API
node contract/stand-in-api.mjs             # the stand-in API on http://127.0.0.1:8787
node contract/check-contract.mjs http://127.0.0.1:8000   # the platform's 15 checks, against any API
```
On Windows PowerShell, keep the double quotes around the patterns with `*`.

## If your squad has no ...
Mentors build squads with every pathway when they can. When that is not possible:

- **No backend member, but a full-stack member:** the full-stack member builds the API (and does both Build activities).
- **No backend and no full-stack member:** the **stand-in API** (`contract/stand-in-api.mjs`) is your API. It keeps the
  contract, takes product data from the Nkwen practice API, and needs no database. Deploy it as a Node service, and leave
  out of scope what needs a real database: orders survive only until the service restarts. Write this in `docs/team.md`.
- **No frontend member and no full-stack member:** leave the web shop out of scope. The rep app is your client.
  The API team adds a very small "order status" page only if there is time.
- **No mobile member:** leave the rep app out of scope. Keep the `Idempotency-Key` rule: the web shop uses it too.
- **Only two members, or someone leaves:** tell your mentor at once. They may merge squads, join as a reviewer, or cut the scope.

The CI workflow skips the job of a part that does not exist yet, so a missing part never blocks your pull requests.

## Rules
Read `CONTRIBUTING.md` before your first pull request.
