# Hotfix — ppadevs/ddd: DoctrineSession for ORM 3 (wrapInTransaction)

> Companion hotfixes: `C:\WWW\ppadevs\paginator\DOCS\hotfix\orm3ApiCompat\` (same
> runtime concern) and `C:\WWW\ppadevs\ddd\DOCS\hotfix\doctrineOrmBump\` (the
> constraint bump — ships on the same release train).

## Problem

Doctrine ORM 3 **removed** `EntityManager::transactional()` — only
`wrapInTransaction(callable $func): mixed` remains (verified against ORM 3.7.2,
EntityManager.php:182). `ppadevs\Ddd\Infrastructure\Application\Service\DoctrineSession::executeAtomically()`
still calls `$this->entityManager->transactional($operation)` — under ORM 3 the first
executed transaction **fatals with "Call to undefined method"** on the write path.
ORM 2 consumers are unaffected.

## Current behavior

Verified 2026-09-30 against the testing line install
(`C:\WWW\ppadevs\_orm3-bump-verify` — kept as evidence):

```powershell
& 'C:\PHP85\php.exe' -r "require 'C:\WWW\ppadevs\_orm3-bump-verify\vendor\autoload.php'; var_dump(method_exists('Doctrine\ORM\EntityManager', 'transactional'), method_exists('Doctrine\ORM\EntityManager', 'wrapInTransaction'));"
```

→ `bool(false)`, `bool(true)`.

- `vendor/nimdev/ddd/src/ppadevs/Ddd/Infrastructure/Application/Service/DoctrineSession.php:29`
  (testing line v26.41.1) still calls the removed method.
- Install itself works fine: `nimdev/ddd v26.41.1` + `doctrine/orm 3.7.2` resolve and
  autoload (constraint already relaxed by the companion `doctrineOrmBump` hotfix).

Repo facts (checked 2026-09-30): production release `2.3.4`/master (`839ba98`) calls
the removed method the same way.

Checked and OK under ORM 3: `EntityRepository::getEntityManager()` (protected, still
present) used by the event-store classes. Note: ORM 3's `flush()` takes no arguments,
so the event-store classes' `flush($entity)` degrades to a full flush (ignored
argument — not fatal); those classes are not used by the new stack — out of scope
here, owner may address later.

## Expected behavior

`DoctrineSession::executeAtomically()` prefers ORM 3's `wrapInTransaction` and falls
back to ORM 2's `transactional`:

```php
if (method_exists($this->entityManager, 'wrapInTransaction')) {
    return $this->entityManager->wrapInTransaction($operation);
}

return $this->entityManager->transactional($operation);
```

Transactions then execute without fatal errors on both ORM 2 and ORM 3 (BC kept).

## Reproduction steps

1. Install the testing line into a test project (companion `doctrineOrmBump`
   reproduction; evidence at `C:\WWW\ppadevs\_orm3-bump-verify`).
2. Run the `method_exists` check above → `transactional` false on ORM 3.
3. Read the installed `DoctrineSession.php:29` — the removed method is still called.

## Fix constraints

- Minimal code change: the `method_exists` guard in `DoctrineSession` only; keep the
  ORM 2 fallback (no BC break).
- Do the change on the hotfix branch, not directly on master.
- No commits/pushes without the owner's explicit request; pushing to GitHub uses the
  owner's credentials/permissions.
- Release rides the same `v26.41.x` train as the companion `doctrineOrmBump` hotfix
  (one production tag carrying constraint + this fix).

## Scope

- **In**: the `DoctrineSession` fix; release on the shared train; verification.
- **Out**: event-store/flush refinements (noted above, not used by the new stack);
  other package code; the constraint bump itself (companion spec); any specific
  consumer projects.
