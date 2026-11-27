# Runbook: how the platform runs live

A **runbook** tells any squad member how to deploy, check and roll back the platform without asking anyone.
Write variable NAMES only. Never write a secret value (keys, passwords, tokens) in this repository.

## Live addresses
| Part | Live URL | Host | Deploys when | Owner |
|---|---|---|---|---|
| Orders API | TODO | TODO | TODO (for example "after CI passes on main") | TODO |
| Web shop | TODO | TODO | TODO | TODO |
| Rep app (APK) | TODO (GitHub Release link) | GitHub Releases | TODO | TODO |
| Database | (no public URL) | TODO | | TODO |

## Settings (environment variables)
| Part | Name | What it is | Where it is set |
|---|---|---|---|
| Orders API | `APP_KEY` | Laravel's encryption key | TODO |
| Orders API | `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | TODO | TODO |
| Web shop | `VITE_API_URL` | TODO | TODO |
| Rep app | `API_URL` (given with `--dart-define`) | TODO | TODO |

## Check that it works (smoke test, 5 minutes)
1. `node contract/check-contract.mjs <live API URL>`: TODO (the result you expect)
2. TODO
3. TODO

## Roll back (when a deploy breaks something)
TODO: the exact clicks or commands for each part.

## Known limits
TODO: for example "the free API sleeps after 15 minutes without visits; the next request waits about a minute".
