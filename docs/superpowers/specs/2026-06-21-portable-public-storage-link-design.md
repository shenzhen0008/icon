# Portable Public Storage Link Design

## Goal

Make `public/storage` portable when the whole project directory is archived or checked out through Git on a Linux server, without changing Laravel's upload directory, database paths, or public URLs.

## Design

Replace the current absolute `public/storage` symbolic-link target with the relative target `../storage/app/public`. Remove `/public/storage` from the repository ignore rules so Git can track the symbolic link (mode `120000`) and recreate it on Linux checkouts.

The Laravel `public` disk remains rooted at `storage/app/public`, and uploaded files continue to use paths such as `recharge-receipts/example.jpg`. No PHP application code, database schema, stored path, or URL-generation behavior changes.

## Deployment Behavior

- Whole-directory archive: an archive format/tool that preserves symbolic links carries the relative link to the new location, where it resolves inside the extracted project.
- GitHub deployment on Linux: Git recreates the tracked relative symbolic link automatically.
- Existing uploaded files: full-directory archives include them when `storage/app/public` is included; Git does not synchronize runtime uploads because those files remain intentionally ignored.

## Compatibility and Risks

- Linux servers are supported. Windows checkouts or archive tools that do not preserve symbolic links are outside this design.
- Running `php artisan storage:link --force` later would replace the portable link with an absolute link. If manual recreation is ever required, use `php artisan storage:link --relative --force`.
- The web-server/PHP user still needs the existing read/write permissions for `storage/app/public`.

## Verification

1. `readlink public/storage` prints `../storage/app/public`.
2. `git ls-files -s public/storage` reports mode `120000`.
3. Existing public-disk files remain accessible through `/storage/...`.
4. `php artisan test` passes.
5. `npm run build` passes.
