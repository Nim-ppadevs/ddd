# Tasks — hotfix-orm3ApiCompat (ppadevs/ddd)

Branch: `hotfix-orm3ApiCompat` created from `master` in this repo
(`C:\WWW\ppadevs\ddd`) by the implementing agent (may ride the
`hotfix-doctrineOrmBump` branch if both hotfixes are done together — owner decides).
Conventions: no staging/committing/pushing without the owner's explicit request —
present results and ask per task.

- [x] 1. Fix `DoctrineSession` for ORM 3
  - `src/ppadevs/Ddd/Infrastructure/Application/Service/DoctrineSession.php`:
    replace the `transactional()` call in `executeAtomically()` with the
    `method_exists` guard for `wrapInTransaction` (ORM 3) and the `transactional`
    fallback (ORM 2) — exact snippet in bugfix.md *Expected behavior*
  - Verify: PHP 8.5 lint (`C:\PHP85\php.exe -l`); single-hunk diff
  - Observable: no removed-API call remains outside the fallback branch; ORM 2
    fallback kept
  - _Evidence: ORM 3.7.2 `EntityManager` has only `wrapInTransaction(callable): mixed`
    (transactional removed); reproduced against the testing line install at
    `C:\WWW\ppadevs\_orm3-bump-verify`_

- [ ] 2. Release (shared train with the `doctrineOrmBump` companion)
  - Commit on the hotfix branch (owner approval); the production tag carries
    constraint bump (companion spec) + this fix in one release — proposed next number
    on top of the testing-line head (v26.41.1); owner decides
  - Push to the testing line first (`nimdev/ddd` on Packagist) and re-verify; then to
    the production repo `https://github.com/ppadevs/ddd.git`
  - **Do not also tag a 2.x line** (e.g. 2.3.5): the major jump is intentional —
    `^2.x` consumers must never auto-pick the new release
  - Observable: `https://repo.packagist.org/p2/ppadevs/ddd.json` lists the new tag
    with `doctrine/orm ^2.11 || ^3.0`

- [ ] 3. Verification (shared final gate with the companion paginator hotfix)
  - Test project (temp folder; interim against the testing line, final against
    `ppadevs/ddd ^26.0`): `{"require": {"php": ">=8.2", "doctrine/orm": "^3.0",
    "<vendor>/ddd": "^26.0", "<vendor>/paginator": "^26.0"}}` — resolves, installs,
    autoloads
  - Runtime spot check for this fix: with the vendor autoload loaded, confirm the
    installed `DoctrineSession` no longer calls `transactional()` unconditionally
    (inspect the installed file) and
    `method_exists('Doctrine\ORM\EntityManager', 'wrapInTransaction')` is true
  - BC check: a second test project requiring `doctrine/orm ^2.11` + `ppadevs/ddd ^2.0`
    still resolves 2.3.4 and its `DoctrineSession` keeps the `transactional` path
  - Report installed versions back to the owner

## Notes

- Minimal diff; keep the file's existing style.
- Companion specs: paginator runtime fix
  (`C:\WWW\ppadevs\paginator\DOCS\hotfix\orm3ApiCompat\`) and the constraint bump
  (`C:\WWW\ppadevs\ddd\DOCS\hotfix\doctrineOrmBump\`).
- Full transactional behavior (begin/commit/rollback) is exercised by the consuming
  project's phase-2 write path later; this hotfix's verification stops at the
  API-correctness level described above.
- Clean up the throwaway test project folder after the production releases land.
