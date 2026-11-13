# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`mod_onlyofficeeditor` — a Moodle **activity module** that embeds ONLYOFFICE Docs (Document Server) so users can view, edit and co-author office documents inside a course. Each activity instance owns exactly one file. The plugin is its own git repository, checked out at `mod/onlyofficeeditor/` inside a Moodle tree.

Frozen facts live in [version.php](version.php): component `mod_onlyofficeeditor`, `$plugin->requires` pins the minimum Moodle. Release notes go in [CHANGELOG.md](CHANGELOG.md); the release workflows take the package version from its top heading, so that heading must be the release being built.

`newdocs/` is a **git submodule** ([ONLYOFFICE/document-templates](https://github.com/ONLYOFFICE/document-templates)) holding blank `new.docx/xlsx/pptx/pdf` per locale. Don't edit its contents here; `git submodule update --init --recursive` is required before packaging. Don't commit a changed submodule pointer unless the task is a template update.

**The assign subplugin depends on this plugin.** `assignsubmission_onlyoffice` (separate repo, installed at `mod/assign/submission/onlyoffice/`) uses this plugin's classes, constants, lang strings and AMD modules. Treat all of them as public API: before renaming one or changing its behaviour, grep that plugin for `mod_onlyofficeeditor` and `'onlyofficeeditor'` and update it in step.

## Commands

All checks run through [moodle-plugin-ci](https://moodlehq.github.io/moodle-plugin-ci/) against a Moodle checkout. Read [.github/workflows/ci.yml](.github/workflows/ci.yml) for the commands, their flags, which checks are advisory, and the matrix of PHP versions, Moodle branches and databases; run the same commands locally.

`phpcs`/`phpdoc` are zero-warning gates, so every new class/function needs the full Moodle docblock (`@package`, `@copyright`, `@license`, `@param`, `@return`) and the GPL file header. Deviations must be annotated inline with `phpcs:ignore` + `phpcs:enable`, as [callback.php](callback.php) and [desktop.php](desktop.php) do for the missing `require_login`.

Most features need a live ONLYOFFICE Docs, so verify behaviour changes on a Moodle site connected to one.

**AMD JavaScript.** Sources are in [amd/src/](amd/src/); the minified `amd/build/*.min.js` and `.map` files are committed and must be regenerated with Moodle's `grunt` whenever a source file changes. Run it from the Moodle root, not from this directory: `npx grunt amd --root=mod/onlyofficeeditor` (prefix the path with `public/` on Moodle versions with the `public/` layout). Commit the rebuilt files with the source change: release packages are built from the git tree. Legacy modules are AMD (`define([...], function(){})`); write new modules as ES modules (`import`/`export`, transpiled to AMD by `grunt`, e.g. [amd/src/fullscreen.js](amd/src/fullscreen.js)) and migrate legacy ones to ES where practical.

## Architecture

### The editing round trip

The central thing to understand is that **two independent parties talk to Moodle**: the browser, and Document Server itself (server-to-server, with no Moodle session).

1. [view.php](view.php) renders the activity page, health-checks the configured Document Server, injects its `api.js` and kicks off the AMD module `mod_onlyofficeeditor/editor`.
2. [amd/src/editor.js](amd/src/editor.js) fetches the editor config from [dsconfig.php](dsconfig.php) (session-authenticated AJAX), wires up event handlers, and constructs `DocsAPI.DocEditor`.
3. [dsconfig.php](dsconfig.php) builds the config via [classes/editor.php](classes/editor.php): the document URL (a `pluginfile.php` URL), the `callbackUrl`, permissions, customization flags and a JWT `token` over the whole config.
4. Document Server downloads the file from `pluginfile.php` → [`onlyofficeeditor_pluginfile()`](lib.php) in lib.php.
5. On save, Document Server POSTs to [callback.php](callback.php), which writes the new content back into the Moodle file storage via `util::save_document_to_moodle()`.

Steps 4 and 5 arrive **without a Moodle session**, so they are authorized by two separate mechanisms that are easy to confuse:

- **`?doc=` hash** ([classes/hasher.php](classes/hasher.php)) — a base64 `sha256(payload + appkey)?payload` envelope carrying `userid` plus either `contenthash` (pluginfile) or `pathnamehash` + `cm` (callback). This is what identifies *which* file and *on whose behalf*; `pluginfile` re-checks enrolment/capabilities for that `userid`.
- **JWT** ([classes/jwt_wrapper.php](classes/jwt_wrapper.php), thin wrapper over `Firebase\JWT`; the algorithm and leeway are set there) — proves the request really came from Document Server. Present either as a `token` field in the callback body or as a `Bearer` token in the header named by the `jwtheader` setting. **When the callback carries a JWT, the decoded payload overrides the request body** — never trust the raw body when a secret is configured.

`callback.php` dispatches on the numeric `status` field; the `STATUS_*` constants live on [`util`](classes/util.php).

### Document key invalidation

`documentkey` on the `onlyofficeeditor` row is Document Server's cache key for a document version. [`document::set_key()`](classes/document.php) rotates it to a fresh random string and **must** be called whenever the stored file changes outside an active editing session, such as a final save or an instance update that replaces the file, but *not* after a forcesave, which is an intermediate autosave. Forgetting this makes Document Server serve stale cached content.

### URL configuration — three distinct addresses

[classes/configuration_manager.php](classes/configuration_manager.php) / [configuration_constants.php](classes/configuration_constants.php) separate:

- `documentserverurl` — **public**, what the browser loads `api.js` from.
- `documentserverinternal` — what *Moodle* uses to reach Document Server (falls back to the public URL). `replace_document_server_url_to_internal()` rewrites the download URLs Document Server hands back in callbacks, so the file fetch goes to the internal address. Every server-side request to Document Server must use the internal address.
- `storageurl` — what *Document Server* uses to reach Moodle (falls back to `$CFG->wwwroot`). All `callbackUrl` and document URLs handed to Document Server are built from this.

Always go through `configuration_manager` rather than raw `get_config()` when touching these. `disable_verify_ssl` must be honoured on every outbound curl call (`CURLOPT_SSL_VERIFYPEER`/`VERIFYHOST`, or `skipcertverify` for `create_file_from_url`).

### Database schema

One table, `onlyofficeeditor`, defined in [db/install.xml](db/install.xml) — one row per activity instance. Read that file for the columns and indexes. Beyond the standard activity columns, the plugin-specific ones are:

- **`permissions`** — the per-activity permission checkboxes from `mod_form`, stored as a **PHP-serialized array**, written with `serialize()` and read back with `unserialize()`. Absent keys mean "not granted", so readers must handle a missing key, not just a falsy one. It is not JSON and not a Moodle config blob — don't "fix" it to one without a migration.
- **`documentkey`** — Document Server's version cache key; see *Document key invalidation* above. Deliberately `text` rather than a fixed char so a future version can hold multiple documents/keys.

There is **no user-level table**: nothing per-user is persisted, so backup has no user data to include and the privacy provider exports nothing (see *Privacy API* below).

Actual file bytes live in Moodle's file storage, never in this table — see *File storage* below.

**Changing the schema** means all four of: edit `db/install.xml` (via Moodle's XMLDB editor, which keeps the element ordering and attributes it expects), add a matching block to [db/upgrade.php](db/upgrade.php), bump `$plugin->version` in [version.php](version.php), and use that same new version number in the `upgrade_mod_savepoint()` call — `moodle-plugin-ci savepoints` fails if they disagree. Add new columns to the backup element list in [backup/moodle2/backup_onlyofficeeditor_stepslib.php](backup/moodle2/backup_onlyofficeeditor_stepslib.php) too, or they are silently dropped on course backup/restore.

Note that retired plugin **settings** are also cleaned up in `db/upgrade.php`, with `unset_config()` — they live in Moodle's `config_plugins`, not in this table, so dropping a setting is an upgrade step like any schema change. Moodle drops the table itself on uninstall.

### File storage

One file per activity, always in component `mod_onlyofficeeditor`, filearea `content`, `itemid = 0`, `filepath = '/'`. When reading it, copy the existing lookup in [lib.php](lib.php) so every reader picks the same file. `mod_form` submits a draft area, and the saved file is renamed to match the activity name.

Creating an activity from a blank template takes an unusual path: the template is written during form validation (a validation hook with a side effect) and moved to the module context when the instance is added. Read `mod_form::validation()` and `onlyofficeeditor_add_instance()` before changing either.

### Permissions

Two layers, both needed:

- Moodle capabilities, defined in [db/access.php](db/access.php).
- Per-activity editor permissions, from the serialized `permissions` column. [`document::get_permissions()`](classes/document.php) merges them with capabilities and the file extension into the permission block sent to the editor; `edit`/`review` also require the extension to be in `onlyoffice_file_utility::get_editable_extensions()`.

[classes/onlyoffice_file_utility.php](classes/onlyoffice_file_utility.php) is the single source of truth for format lists: accepted formats per document type, the `word|cell|slide|pdf` mapping, and the much smaller editable set.

### Extra entry points

- [onlyofficeeditorapi.php](onlyofficeeditorapi.php) — session-authenticated AJAX for editor features such as mentions and "Save as" (which creates a *new activity*), dispatched on `apiType`. Any URL it fetches must first be checked against Document Server's host, as "Save as" does.
- Web services — declared in [db/services.php](db/services.php). The settings page's "check connection" button calls one that delegates to [docs_settings_validator](classes/local/docs/docs_settings_validator.php), which probes the Document Server services and maps each failure back to the *specific settings field* the admin form highlights.
- [desktop.php](desktop.php) — reached from a `user_loggedin` observer ([db/events.php](db/events.php) → `onlyofficeeditor_login_handler()`) when the `AscDesktopEditor` user agent is detected; hands credentials to the ONLYOFFICE desktop app via `portal:login`.
- [index.php](index.php) lists instances; [backup/moodle2/](backup/moodle2/) implements backup/restore.

### Privacy API

[classes/privacy/provider.php](classes/privacy/provider.php) is **metadata-only**, and deliberately so: the plugin's own table stores nothing per-user, so there is nothing to export or delete. It is *not* a null provider, though: it declares the data sent to Document Server as an external location, and links `core_files` for the edited documents, which the files subsystem exports and deletes itself.

The practical rule: **the external-location declaration must stay in sync with whatever `editor::config()` and `onlyofficeeditorapi.php` actually transmit**, including any claim its `privacy:metadata:*` strings make about how the data is sent. Anything new added to the editor config or to the mention flow is personal data crossing that boundary and belongs in `get_metadata()` with a matching `privacy:metadata:*` lang string. Don't assume the declared surface is complete; check the code path before answering questions about it.

### Admin settings

[settings.php](settings.php) uses the plugin's own setting classes from [classes/local/admin/settings/](classes/local/admin/settings/), which extend the core ones with input validation; use them for new text and URL settings. `check_document_server_button` is not a setting at all — it renders the connection-check button. Validation is **input-validity only**; live connectivity checking is that explicit button, not a save-time side effect.

Adding a setting means: a constant in `configuration_constants` (if code reads it), the `$settings->add(...)` call, and lang strings in [lang/en/onlyofficeeditor.php](lang/en/onlyofficeeditor.php).

### Language strings and locales

**Three independent locale systems.** Don't conflate them:

1. **Moodle UI strings** — [lang/](lang/), `$string['key'] = '...'` in `lang/<code>/onlyofficeeditor.php`, read with `get_string('key', 'onlyofficeeditor')`. A handful of translated locales ship alongside `en`. `lang/en/` is the source, and every lang file holds exactly the same keys: add, rename or remove a key in **every** `lang/<code>/` file in the same change, translating new strings yourself (a human reviews them). A missing translation is easy to miss because Moodle silently falls back to English per key. Folder names must be Moodle language codes (`zh_cn`, not `zh`). Keys are kept in **alphabetical (byte) order** throughout — insert new ones in place rather than appending, since Moodle's coding style expects sorted lang files. `moodle-plugin-ci validate` separately requires `pluginname` to exist.
2. **Blank-document templates** — `newdocs/<locale>/new.{docx,xlsx,pptx,pdf}`, selected by `util::get_template_path()` from `$USER->lang`. The submodule carries far more locales than `lang/` does, under its own naming scheme, so `util::PATH_LOCALE` maps the Moodle codes that differ (e.g. `pt_br`→`pt-BR`) and anything unmatched falls back to `newdocs/default/`. Adding a `lang/` locale does **not** add a template, or vice versa.
3. **Editor UI language** — [classes/editor.php](classes/editor.php) derives `editorConfig.lang` from `$USER->lang` and hands it to Document Server, which resolves it against its own locale list.

**Key naming.** Two suffix conventions coexist and both are load-bearing; match the neighbouring keys rather than picking one:

- `_help` — required suffix for `$mform->addHelpButton()` targets (`download_help`, `print_help`, `protect_help`). The name is dictated by Moodle, not by taste.
- `_desc` vs `:description` — settings descriptions use both (`documentserversecret_desc`, but `disable_verify_ssl:description`). Namespaced `:` prefixes also group error families: `connectionerror:*`, `validationerror:*` (consumed by [docs_settings_validator](classes/local/docs/docs_settings_validator.php) and the settings-page JS).

Other keys whose names are fixed by Moodle APIs: `pluginname`, `modulename`, `modulenameplural`, `modulename_help`, `privacy:metadata*`, and `messageprovider:mentionnotifier` (which must match the handler declared in [db/messages.php](db/messages.php)).

Strings are consumed from PHP via `get_string()`, from AMD via `core/str`'s `get_string(key, 'onlyofficeeditor')` (see the `displayNotification` helper in [amd/src/editor.js](amd/src/editor.js)), and from Mustache via the template's own string helper — a key removed from `lang/en/` fails loudly in PHP but can fail quietly in JS, so grep all three when renaming. Placeholders use Moodle's `{$a}` / `{$a->field}` form (e.g. `mentionnotifier:notification`), never `sprintf`.

## Licensing and third-party code

The plugin is **GPL v3 or later** ([LICENSE](LICENSE)), matching Moodle. Every PHP and JS file carries the standard Moodle GPL header comment *and* a `@license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later` docblock tag — `phpcs`/`phpdoc` fail without them, so copy the header from a neighbouring file when adding one. Copyright lines are `Ascensio System SIA <integration@onlyoffice.com>`, often alongside a `based on work by ... Olumuyiwa Taiwo` line on files inherited from the original module; keep that attribution when editing such a file. [AUTHORS.md](AUTHORS.md) is the canonical author list.

Third-party attributions live in **[3rd-Party.license](3rd-Party.license)**, with full licence texts in [licenses/](licenses/). Note the file is duplicated — the root copy and `licenses/3rd-Party.license` are byte-identical, so **edit both or they drift**.

What is and isn't bundled matters here, because the declared list is short for a reason:

- The plugin vendors **no PHP dependencies** — there is no `composer.json` and no `vendor/`. Libraries used from **Moodle core**, such as `Firebase\JWT` (in [classes/jwt_wrapper.php](classes/jwt_wrapper.php)) and jQuery, are not bundled dependencies of this plugin. Don't add a composer dependency to pull in something core already ships.
- `newdocs/` is a submodule under a **different licence** — Apache 2.0, not GPL — and it *is* redistributed, since [.github/workflows/artifact.yml](.github/workflows/artifact.yml) initialises submodules and packages the directory into the release zip. Its own `LICENSE` travels with it; leave it in place.

Anything genuinely new and bundled needs three things together: the entry in both `3rd-Party.license` copies, its full licence text in `licenses/`, and a compatible licence — GPLv3 is the constraint. The CHANGELOG records past rounds of "approval blockers" and "market code prechecks", so treat this as a release gate rather than housekeeping.

## Branches and releases

- PRs target `develop`. `master` holds released code; merging `develop` into it tags and releases.
- Branch names: `<type>/<short-kebab-description>` (`feat/`, `fix/`, `ci/`, `chore/`, `test/`, `docs/`).
- Commit messages follow Conventional Commits: `<type>(<optional scope>): <description>`, lowercase type (`feat`, `fix`, `refactor`, `test`, `docs`, `ci`, `build`, `chore`).
- Add user-visible changes to the top section of [CHANGELOG.md](CHANGELOG.md), under `### Added` or `### Changed`.
- A release also needs `$plugin->version` raised and `$plugin->release` set to `'v<version>'` in [version.php](version.php), matching the tag.

## Coding practices

- Before writing new code, check whether Moodle core already provides it through a public API (file storage, `core\http_client`/`curl`, external functions, output renderers and Mustache, `core/ajax`, `core/str`, `core/modal`, `core/toast`). Use it and keep the change small.
- Write PHP for the lowest PHP version supported by the oldest Moodle in `$plugin->requires`; the CI matrix in [.github/workflows/ci.yml](.github/workflows/ci.yml) lists it.
- Every Moodle API you use must exist in the oldest supported Moodle and still exist in the newest one in the CI matrix. Check Moodle's `UPGRADING.md`/`upgrade.txt` notes and the developer docs for anything recently added or deprecated.
- Code must work in both Moodle directory layouts (with and without `public/`). Build file paths from `$CFG->dirroot` and URLs from `moodle_url`/`$CFG->wwwroot` or `configuration_manager`, never from a hardcoded layout.
- No superglobals (`$_GET`, `$_POST`, `$_SERVER`…): use `required_param()`/`optional_param()` and Moodle's request APIs. Superglobals and global names outside the plugin's prefix were Marketplace approval blockers.
- New PHP classes go under `classes/` in the `mod_onlyofficeeditor\…` namespace (autoloaded). Global functions belong only in `lib.php` (plus Moodle's `db/` hooks) and carry the plugin prefix.
- A new web service needs: the class under `classes/external/` (`validate_parameters()`, `validate_context()`, `require_capability()`), its entry in [db/services.php](db/services.php), a raised `$plugin->version`, and an AMD repository call through `core/ajax`.
- Before relying on an ONLYOFFICE Docs API option, event or command, check in the Docs API documentation which Docs version added it, against the minimum Docs version stated in `README.md`.
- Every user-visible string goes through `get_string()` (PHP), `core/str` (JS) or `{{#str}}` (Mustache), never hardcoded.
- When a secret is set, requests to Document Server carry the JWT both as a body `token` and as `Bearer` in the configured `jwtheader`, as `document_service::command()` does.
- Document Server error codes are thrown as `command_service_exception` / `conversion_service_exception` (subclasses of `local\exceptions\document_server_exception`), not generic exceptions.

## Conventions

- Editor config array keys are **camelCase and case-sensitive** (`editorConfig`, `callbackUrl`, `documentType`, `fillForms`) — they go straight to the ONLYOFFICE API. PHP variables otherwise follow Moodle's lowercase-no-underscore style.
- Templates are Mustache in [templates/](templates/), rendered from PHP or from JS through `core/templates`, each with Moodle's `@template` doc comment and example context; they are linted in CI.
- Write new JavaScript as ES modules without jQuery, even next to modules that use `define()` or jQuery. Pass data from PHP with `$PAGE->requires->js_call_amd()`.
- Every new PHP, JS and Mustache file gets the Moodle GPL header. PHP files also carry `@package`/`@copyright`/`@license`, JS files `@module`/`@copyright`/`@license`, Mustache templates `@template`. Copy them from a neighbouring file of the same type.
