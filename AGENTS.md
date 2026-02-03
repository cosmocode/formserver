# Formserver Agent Handbook

This document orients fully autonomous agents (Cursor, Copilot, Claude, etc.) who contribute to `/Users/aniuta/dev/formserver`. It captures the repo’s tooling, workflow expectations, and coding conventions so you can execute tasks confidently without human nudges.

---

## 1. Quick Orientation
1. PHP 8.2 Slim app with PHP-DI for wiring and Bulma-based frontend.
2. Forms are modeled in YAML (`data/<formId>/config.yaml`) and rendered dynamically.
3. Browser UI is built from custom elements registered in `js_src/components`. Esbuild bundles into `public/js/bundle.js`.
4. E2E coverage comes from Cypress specs under `cypress/e2e`. They depend on fixture YAML under `cypress/yaml`.
5. No Cursor rule files (`.cursor/rules/`, `.cursorrules`) and no `.github/copilot-instructions.md`; this handbook is the authoritative policy.

---

## 2. Environment & Setup Checklist
1. **Base requirements:** PHP ≥8.2, Composer, Node 23 per CI, npm, zip, and a mail-capable environment if you exercise outbound email code.
2. **Install dependencies:**
   - `composer install`
   - `npm install`
3. **Environment variables:** Copy `.env.example` to `.env` and set:
   - `DATA_DIR` (`data` locally, `cypress/yaml` in CI)
   - `CYPRESS_BASE_URL` (e.g., `http://localhost:8181`)
4. **Local server:** `composer start` launches `php -S localhost:8181 -t public` (matches CI URL).
5. **Data directories:** Real forms live in `data/`; Cypress fixtures mirror the same structure inside `cypress/yaml/`.
6. **Logs:** Slim writes to `logs/`. Keep this directory writable; do not commit log output.
7. **Release assets:** `build.sh` zips everything except git metadata, Docker files, script itself, and `output.log`.

---

## 3. Command Reference (single-test friendly)
| Purpose | Command |
| --- | --- |
| PHP dev server | `composer start` |
| PHP coding standards check | `composer check` (PHPCS PSR-12 on `src/`) |
| PHP auto-fix | `composer fix` (PHPCBF PSR-12 on `src/`) |
| JS bundle | `npm run build` (esbuild bundle + minify) |
| JS watch | `npm run watch` |
| Cypress GUI | `npm run cy:open` |
| Cypress headless suite | `npm run cy:run` |
| Cypress single spec | `npm run cy:spec -- cypress/e2e/<file>.cy.js` |
| Composer + npm clean install (CI parity) | `composer install && npm ci` |
| Zip release | `./build.sh` |

### 3.1 PHPUnit
* PHPUnit is available via `vendor/bin/phpunit --filter <TestName>` but no suite currently exists. When you add tests, place configs under `phpunit.xml` (missing now) and follow PSR-4 `tests/` namespace.

### 3.2 Cypress Single-Test Workflow
1. Ensure `CYPRESS_BASE_URL` points to a running `composer start` server.
2. Run `npm run cy:spec -- cypress/e2e/foo.cy.js` to execute just that spec.
3. Specs often clean fixture state with `cy.exec('rm -f ./cypress/yaml/<form>/values.yaml')`; replicate when adding new tests.

---

## 4. Backend Code Style & Conventions
1. **PSR-12 everywhere.** Use strict types (`declare(strict_types=1);`) and typed properties/method signatures.
2. **Dependency injection:** New services belong in `app/dependencies.php` using PHP-DI factories; avoid `new` inside actions when containerable.
3. **Actions:** Extend `CosmoCode\Formserver\Actions\AbstractAction`; fetch request args via `$this->resolveArg()` and respond with Slim PSR-7 responses.
4. **Configuration:** Pull runtime settings through `YamlHelper::parseYaml()` and environment overrides in `app/settings.php`. Respect the merge order: defaults → local overrides → environment.
5. **Persistence:** `Form::persist()` writes submitted `values.yaml`. Never assume file presence—wrap with try/catch as in `Form::__construct`.
6. **Error handling:** `public/index.php` installs a minimal custom error handler that renders HTML from `conf/settings*.yaml` paths. When you throw domain exceptions (`FormException`, `MailException`, `YamlException`), ensure they map to user-friendly language strings via `LangManager`.
7. **Translations:** Use `LangManager::getString()` for human-facing text and keep new strings in `conf/language.*.yaml`. Initialize languages through meta `language` fields.
8. **File exports:** When enabling `export` metadata, rely on `FileExporter` service; it calculates destination directories specified in `settings.fileExporter`.
9. **Logging:** Use Slim’s logger (configured via container) or `Monolog` if you extend functionality; do not write directly to STDOUT in production paths.
10. **Namespaces:** Follow PSR-4: `CosmoCode\Formserver\...` mapping to `src/`. Never place production PHP outside `src/`.

### 4.1 Imports & Ordering
1. Group `use` statements alphabetically, standard library first, then third-party, then project namespaces.
2. Avoid unused imports—PHPCS will flag them.
3. When referencing global classes (e.g., `\Exception`), either import explicitly or prefix with backslash consistently as in current codebase.

### 4.2 Types & Error Semantics
1. Prefer `array` shape annotations in docblocks when PHP lacks generics (see `Mailer::sendForm`).
2. Convert arrays to value objects only when the structure becomes non-trivial; existing code leverages associative arrays for YAML-driven data.
3. Wrap risky IO (YAML parsing, filesystem ops, mail transport) in targeted exceptions so Actions can return meaningful HTTP codes.

---

## 5. Frontend Structure & Style
1. **Modules:** Every file in `js_src/` is ES module syntax; imports are relative and bundled via esbuild. Do not use CommonJS in new code.
2. **Custom elements:** Components extend `HTMLElement` via `BaseComponent`. Always call `super()` and rely on `initialize(state, config)` before attaching to DOM.
3. **Private state:** Use class fields and private members (e.g., `#isInitialized`, `#visibilityExpression`) to mirror existing encapsulation.
4. **Rendering:** `render()` uses `morphdom` (`childrenOnly: true`). If you override, maintain the conditional visibility + wrapper semantics.
5. **State management:** `State` proxies values and persists to OPFS. Use `ComponentState` helpers for binding; keep updates atomic via `ComponentState.value = ...`.
6. **Validation:** Throw `ValidatorError` with translated messages; base class already enforces required fields when `visible`.
7. **Styling:** Base Bulma plus overrides in `public/css/style.css`. Frontend components should output semantic Bulma class names so wrappers (columns) apply automatically.
8. **Visibility logic:** `visible` config compiles via `U.getParsedExpression`. When adding new dependencies, ensure expressions remain pure and reference form fields with dot notation.
9. **Imports:** Keep them grouped near top; avoid default exports—every component registers itself via side effects on import.
10. **Tooling:** esbuild config is implicit (`npm run build`). Keep browser-compatible syntax (ES2021) or configure esbuild target if you introduce newer features.

### 5.1 CSS Expectations
1. Use SCSS-like nesting sparingly; the current `style.css` uses nested selectors processed via Bulma tooling. Maintain readability and match indentation.
2. Respect Bulma variables for colors (`--bulma-danger-*`, `--bulma-input-*`).
3. Keep custom elements `display: contents` if they wrap Bulma columns.

---

## 6. Cypress & Testing Guidelines
1. Specs reside in `cypress/e2e/*.cy.js`. Name files after the component or scenario they cover.
2. Use `beforeEach` to `cy.visit('/<fixture>')` and `afterEach` to remove generated `values.yaml` via `cy.exec`. This prevents fixture bleed.
3. Prefer data-role selectors (component tags, `[name="..."]`) matching actual form field names to minimize brittleness.
4. Base URL is injected from `.env`; avoid hardcoding hostnames.
5. When submitting forms, assert both DOM outcomes (notifications, classes) and YAML persistence if relevant by checking filesystem state via `cy.readFile`.
6. Keep assertions deterministic; if you rely on asynchronous UI (upload dropzone, OPFS), wait on explicit UI state rather than arbitrary timeouts.

### 6.1 Adding New Specs
1. Create a matching YAML fixture in `cypress/yaml/<id>/config.yaml` with any assets needed.
2. Reference that `<id>` via `cy.visit('/<id>')`.
3. Register the fixture in GitHub Actions by ensuring `DATA_DIR=cypress/yaml` continues to cover it (automatic as long as it sits inside that tree).

---

## 7. Automation & CI Awareness
1. GitHub workflow `release.yml` runs on every push + manual dispatch. It sets `DATA_DIR=cypress/yaml` and `CYPRESS_BASE_URL=http://localhost:8181`.
2. CI steps:
   - Composer install via `php-actions/composer@v6` (uses `composer.json`).
   - Node setup with `actions/setup-node@v4` (Node 23) -> `npm ci` -> `npm run build`.
   - Cypress GitHub Action starts `composer run start` and executes tests headlessly.
   - Tagged `v*` pushes package a release with `./build.sh` and `ncipollo/release-action`.
3. Keep CI deterministic: check in lockfiles (`composer.lock`, `package-lock.json`) after dependency changes.
4. Do not modify workflow secrets; reference them via `${{ secrets.* }}` placeholders.
5. When adding new environment variables, update `.env.example`, CI `env:` block, and `app/settings.php` merge logic.

---

## 8. Troubleshooting & Tips
1. **YAML parsing errors:** `YamlHelper::parseYaml` throws `YamlException`; wrap new calls so the UI can display `LangManager::getString('error_notfound')` or similar friendly text.
2. **Stale OPFS state:** If browsers cache form state, instruct users (or tests) to call `State.clearOPFS()` or clear site data. For Cypress, refresh between tests or stub OPFS via application hooks if needed.
3. **Mail failures:** `Mailer` rethrows as `MailException`; log the original message and show `LangManager::getString('send_failed')`. Provide `email.cc` meta arrays when you need CC behaviors.
4. **File exports:** Ensure directories configured in `settings.fileExporter.dir` exist and are writable before enabling `export` meta fields.
5. **Static assets:** When placing assets for Cypress fixtures, store alongside fixture YAML (`cypress/yaml/<form>/file.ext`) and reference them in `config.yaml`.
6. **Docker image:** Dockerfile expects Debian/Apache base with `composer.phar` available; adjust only if you update base image. Keep `APACHE_DOCUMENT_ROOT` pointing at `public/`.
7. **Logs directory permissions:** On macOS/Linux ensure `logs/` is writable (`chmod 775 logs` if needed). CI already has proper permissions.
8. **Bulma overrides:** If adjusting structural CSS (columns, tabs), confirm corresponding Cypress specs (e.g., `pages.cy.js`) still pass—they validate class-based states like `.has-errors`.

---

## 9. Contribution Etiquette for Agents
1. Never delete user-created data or unrelated changes; respect existing git state.
2. Default to ASCII for file contents unless a file already uses Unicode.
3. Only add explanatory comments when behavior is non-obvious; mirror prevailing tone.
4. Follow repo-relative path references (`src/FormGenerator/Form.php`) when documenting changes in PR descriptions or commit summaries.
5. When asked to commit:
   - Stage only relevant files.
   - Run `composer check` and Cypress spec(s) locally if feasible.
   - Craft commit messages that describe the why, not just the what.
6. Do not amend or force-push without explicit instruction.

---

## 10. Ready-Made Task Patterns
1. **Add backend feature:** Update YAML schema docs (`doc/*.md`) if the change introduces new syntax; add translation keys for all languages, at minimum English + German + French + Italian templates already present.
2. **New frontend component:**
   - Create `js_src/components/FooComponent.js` extending `BaseComponent`.
   - Register `<foo-component>` tag in HTML output (via FormRenderer or YAML `type`).
   - Add styling hooks in `public/css/style.css` if necessary.
   - Cover behaviors with a Cypress spec and fixture YAML.
3. **Configurable email behavior:** Extend `conf/settings.default.yaml` and align `app/settings.php` merge logic; update `.env.example` when new env inputs appear.
4. **CI adjustment:** Mirror any workflow change in this handbook so future agents know about new steps.

---

Maintain this file whenever you alter tooling, workflows, or conventions so downstream agents inherit accurate guidance.
