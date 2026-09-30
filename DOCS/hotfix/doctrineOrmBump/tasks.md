# Tasks — hotfix-doctrineOrmBump (ppadevs/ddd)

Branch: `hotfix-doctrineOrmBump` created from `master` in this repo
(`C:\WWW\ppadevs\ddd`) by the implementing agent.
Conventions: no staging/committing/pushing without the owner's explicit request —
present results and ask per task.

- [ ] 1. Relax the doctrine/orm constraint
  - `composer.json`: `"doctrine/orm" : "^2.11"` → `"doctrine/orm" : "^2.11 || ^3.0"`
    (keep the tab-indent + spaced-keys style; single-line diff)
  - Verify: `composer validate` (`composer85.bat validate`);
    PHP 8.5 lint pass over `src/` (`C:\PHP85\php.exe -l` — reference run 2026-09-30:
    24 files, 0 failures)
  - Observable: composer.json parses, validator passes, no other diff hunks

- [ ] 2. Release `v26.41.0`
  - Commit on `hotfix-doctrineOrmBump` (owner approval), merge to `master`, tag
    `v26.41.0`, push `master` + tag to `https://github.com/ppadevs/ddd.git`
  - **Do not also tag a 2.x line** (e.g. 2.3.5): the major jump is intentional —
    `^2.x` consumers must never auto-pick the new release
  - Observable: `https://repo.packagist.org/p2/ppadevs/ddd.json` lists `v26.41.0` with
    `doctrine/orm ^2.11 || ^3.0` (Packagist webhook or owner-triggered update)

- [ ] 3. Verification with a fresh ORM 3 consumer project (shared final gate — only
      after BOTH this release and the companion paginator hotfix `v26.41.0` are
      published)
  - Create a throwaway test project (temp folder):
    `{"require": {"php": ">=8.2", "doctrine/orm": "^3.0", "ppadevs/ddd": "^26.0",
    "ppadevs/paginator": "^26.0"}}` and run
    `& composer85.bat install --no-interaction`
  - Observable: resolution succeeds — `ppadevs/ddd v26.41.0` +
    `ppadevs/paginator v26.41.0` install alongside `doctrine/orm ^3.0`; and with the
    vendor autoload loaded, `class_exists('ppadevs\Ddd\Domain\Exception\DomainException')`
    and `class_exists('Paginator\Paginator')` are true (quick `php -r` sanity)
  - BC check: a second test project requiring `doctrine/orm ^2.11` + `ppadevs/ddd ^2.0`
    still resolves 2.3.4 (constraint-only change, no break; 26.x stays invisible to it)
  - Report installed versions back to the owner

## Notes

- Constraint-only change; no BC break — `^2.11` consumers still resolve.
- Companion spec (same fix, paginator repo):
  `C:\WWW\ppadevs\paginator\DOCS\hotfix\doctrineOrmBump\`.
- Packagist update may need the owner to trigger it if the webhook does not fire
  within minutes of the push.
- Clean up the throwaway test project folders afterwards.
