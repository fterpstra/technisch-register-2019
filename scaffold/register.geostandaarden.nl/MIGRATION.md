# Migration from technisch-register-2019

Path from the webhook-based mechanism (`releasecreated.php`) to this repository.
The two mechanisms can run in parallel during the whole migration; nothing breaks
if a step takes a while.

## 1. Create the repository

1. Create `Geonovum/register.geostandaarden.nl` and copy the contents of this
   scaffold into it (`main` branch).
2. Seed it with the currently published content: copy the artefact directories from
   the production server into the repository root and commit — one commit per model
   keeps the history readable, a single `Seed with production content <date>` commit
   is also fine:

   ```bash
   # on the webserver, or via scp/rsync from it
   cd /var/www/geostandaarden/v2/production
   rsync -a --exclude 'resources' --exclude 'index.php' \
     --exclude 'listDescriptions.php' --exclude 'autodeploy' \
     ./ /path/to/register.geostandaarden.nl/
   ```

   This puts the published state under version control for the first time.
3. Create the `develop` branch from `main`.

## 2. Configure deployment

1. Install the GitHub App used by the docs.geostandaarden.nl flow on this repository
   (for `publish-artefacts.yml`), or create an equivalent one.
2. Add the secrets `SSH_DEPLOY_KEY`, `SSH_HOST`, `SSH_USER`, `SSH_TARGET_DIR_PROD`,
   `SSH_TARGET_DIR_DEV` (same convention as docs.geostandaarden.nl). Point
   `SSH_TARGET_DIR_DEV` at a new test DocumentRoot and configure a
   `test.register.geostandaarden.nl` vhost for it.
3. Enable "Allow GitHub Actions to create and approve pull requests" in the repo
   settings (needed by `sync-releases.yml`).
4. Push to `develop` and verify the rsync deploy and the test site: all five page
   types must render with the same URLs as production.

## 3. Verify content ingestion

1. Run `sync-releases.yml` manually for one model (`workflow_dispatch`, e.g.
   `repo_id: imgeo`) and check that the resulting PR reproduces the production
   content (the diff against the seeded content should be empty or explainable).
2. Add the caller workflow (`examples/source-repo-publish.yml`) to one Geonovum-owned
   source repo, cut a test **pre-release**, and verify the artefacts appear on the
   test site. Then cut a test release and verify the PR to `main`.

## 4. Cut over production

1. Point `SSH_TARGET_DIR_PROD` / the production vhost DocumentRoot at the
   rsync-managed directory (or rsync into the existing DocumentRoot after removing
   the `autodeploy` directory from it).
2. Disable the old webhook: remove the `releasecreated.php` webhook configuration
   from the source repos (or simply delete `autodeploy/` from the server).
3. Roll the caller workflow out to the remaining source repos. Repos where the
   GitHub App cannot be installed (outside the Geonovum organisation) are covered
   by the daily `sync-releases.yml` run.
4. Archive `Geonovum/technisch-register-2019` with a pointer to this repository.

## 5. Afterwards (optional)

* Extract the deploy job of `deploy.yml` and the PR-creation logic of
  `publish-artefacts.yml` into reusable workflows shared with
  docs.geostandaarden.nl, so both sites are maintained in one place.
* The `documentatie/` guides of technisch-register-2019 need a rewrite of the
  register-maintainer guide (webhook chapter no longer applies); the guide for
  model owners only changes in how publishing is triggered (workflow instead of
  webhook) — the repository structure requirements are identical.
