# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`mod_onlyofficeeditor` — a Moodle **activity module** that embeds ONLYOFFICE Docs (Document Server) so users can view, edit and co-author office documents inside a course. Each activity instance owns exactly one file. The plugin is its own git repository, checked out at `mod/onlyofficeeditor/` inside a Moodle tree.

Frozen facts live in [version.php](version.php): component `mod_onlyofficeeditor`, `$plugin->requires` pins the minimum Moodle. Release notes go in [CHANGELOG.md](CHANGELOG.md) — the artifact workflow derives the package version by grepping the first version number out of that file, so the top heading must be the release being built.

`newdocs/` is a **git submodule** ([ONLYOFFICE/document-templates](https://github.com/ONLYOFFICE/document-templates)) holding blank `new.docx/xlsx/pptx/pdf` per locale. Don't edit its contents here; `git submodule update --init --recursive` is required before packaging.

## Commands

All checks run through [moodle-plugin-ci](https://moodlehq.github.io/moodle-plugin-ci/) v4 against a Moodle checkout (see [.github/workflows/ci.yml](.github/workflows/ci.yml) for the exact matrix of PHP versions, Moodle branches and databases):

```bash
moodle-plugin-ci phplint
moodle-plugin-ci phpcs --max-warnings 0     # Moodle Code Checker — must be clean
moodle-plugin-ci phpdoc --max-warnings 0    # PHPDoc Checker — must be clean
moodle-plugin-ci validate
moodle-plugin-ci savepoints                 # db/upgrade.php savepoints
moodle-plugin-ci mustache
moodle-plugin-ci grunt --max-lint-warnings 0
moodle-plugin-ci phpunit --fail-on-warning
moodle-plugin-ci behat --profile chrome
moodle-plugin-ci phpmd                      # advisory only, does not fail CI
```

`phpcs`/`phpdoc` are zero-warning gates, so every new class/function needs the full Moodle docblock (`@package`, `@copyright`, `@license`, `@param`, `@return`) and the GPL file header. Deviations must be annotated inline with `phpcs:ignore` + `phpcs:enable`, as [callback.php](callback.php) and [desktop.php](desktop.php) do for the missing `require_login`.

**AMD JavaScript.** Sources are in [amd/src/](amd/src/); the minified `amd/build/*.min.js` and `.map` files are committed and must be regenerated with Moodle's `grunt` (run from the Moodle root, not from this directory) whenever a source file changes. Modules are AMD (`define([...], function(){})`), not ES modules.

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
- **JWT** ([classes/jwt_wrapper.php](classes/jwt_wrapper.php), thin wrapper over `Firebase\JWT`, HS256, 60s leeway) — proves the request really came from Document Server. Present either as a `token` field in the callback body or as a `Bearer` token in a configurable header (`jwtheader`, default `Authorization`). **When the callback carries a JWT, the decoded payload overrides the request body** (`$data['url']`, `status`, `key`, `users`) — never trust the raw body when a secret is configured.

`callback.php` dispatches on the numeric `status` field; the constants live on [`util`](classes/util.php) (`STATUS_MUSTSAVE = 2`, `STATUS_FORCESAVE = 6`, etc.).

### Document key invalidation

`documentkey` on the `onlyofficeeditor` row is Document Server's cache key for a document version. [`document::set_key()`](classes/document.php) rotates it to a fresh random string and **must** be called whenever the stored file changes outside an active editing session — after a final save (but *not* after a forcesave, which is an intermediate autosave) and after `onlyofficeeditor_update_instance()` replaces the file. Forgetting this makes Document Server serve stale cached content.

### URL configuration — three distinct addresses

[classes/configuration_manager.php](classes/configuration_manager.php) / [configuration_constants.php](classes/configuration_constants.php) separate:

- `documentserverurl` — **public**, what the browser loads `api.js` from.
- `documentserverinternal` — what *Moodle* uses to reach Document Server (falls back to the public URL). `replace_document_server_url_to_internal()` rewrites the download URLs Document Server hands back in callbacks, so the file fetch goes to the internal address. Be aware this is applied inconsistently: the reachability checks in [view.php](view.php) and `util::save_as_document()` read `documentserverurl` straight from config and curl the **public** address server-side, so a deployment where only the internal address is routable from PHP will fail there while saving works.
- `storageurl` — what *Document Server* uses to reach Moodle (falls back to `$CFG->wwwroot`). All `callbackUrl` and document URLs handed to Document Server are built from this.

Always go through `configuration_manager` rather than raw `get_config()` when touching these. `disable_verify_ssl` must be honoured on every outbound curl call (`CURLOPT_SSL_VERIFYPEER`/`VERIFYHOST`, or `skipcertverify` for `create_file_from_url`).

### Database schema

One table, `onlyofficeeditor`, defined in [db/install.xml](db/install.xml) — one row per activity instance. Beyond the standard activity columns (`id`, `course`, `name`, `intro`, `introformat`, `timecreated`, `timemodified`, `display`, `displayoptions`) there are two plugin-specific ones, both `TYPE="text" NOTNULL="false"`:

- **`permissions`** — the `download`/`print`/`protect` checkboxes, stored as a **PHP-serialized array** (`util::save_document_permissions()` writes it, `document::get_permissions()` and `mod_form::data_preprocessing()` read it back with `unserialize()`). Absent keys mean "not granted", so readers must handle a missing key, not just a falsy one. It is not JSON and not a Moodle config blob — don't "fix" it to one without a migration.
- **`documentkey`** — Document Server's version cache key; see *Document key invalidation* above. Deliberately `text` rather than a fixed char so a future version can hold multiple documents/keys.

The only indexes are the `id` primary key and a non-unique index on `course`. No foreign keys, and **no user-level table**: nothing per-user is persisted, which is why the backup step reads `userinfo` but never branches on it, and why the privacy provider exports nothing (see *Privacy API* below).

Actual file bytes live in Moodle's file storage, never in this table — see *File storage* below.

**Changing the schema** means all four of: edit `db/install.xml` (via Moodle's XMLDB editor, which keeps the element ordering and attributes it expects), add a matching block to [db/upgrade.php](db/upgrade.php), bump `$plugin->version` in [version.php](version.php), and use that same new version number in the `upgrade_mod_savepoint()` call — `moodle-plugin-ci savepoints` fails if they disagree. Add new columns to the backup element list in [backup/moodle2/backup_onlyofficeeditor_stepslib.php](backup/moodle2/backup_onlyofficeeditor_stepslib.php) too, or they are silently dropped on course backup/restore.

Note that retired plugin **settings** are also cleaned up in `db/upgrade.php`, with `unset_config()` — they live in Moodle's `config_plugins`, not in this table, so dropping a setting is an upgrade step like any schema change. [db/install.php](db/install.php) and [db/uninstall.php](db/uninstall.php) are empty stubs; Moodle drops the table itself on uninstall.

### File storage

One file per activity, always in component `mod_onlyofficeeditor`, filearea `content`, `itemid = 0`, `filepath = '/'`. Reads consistently use `get_area_files(..., 'sortorder DESC, id ASC', false, 0, 0, 1)` and take `reset()`. `mod_form` submits a draft area; `util::save_file()` renames the draft to match the activity name (`util::generate_filename()`, truncated to `util::FILENAME_MAXIMUM_LENGTH`) before `file_save_draft_area_files()`.

Creating an activity from a blank template takes an unusual path: `mod_form::validation()` calls `util::create_from_onlyoffice_template()` (a validation hook with a side effect), which writes the template into `content` under the *draft* itemid; `onlyofficeeditor_add_instance()` then rewrites those `files` rows to the real module context with `itemid = 0`.

### Permissions

Two layers, both needed:

- Moodle capabilities ([db/access.php](db/access.php)): `:addinstance`, `:view`, `:editdocument` (guests are `CAP_PROHIBIT` on edit).
- Per-activity editor permissions, from the serialized `permissions` column. [`document::get_permissions()`](classes/document.php) merges them with capabilities and the file extension into the permission block sent to the editor; `edit`/`review` also require the extension to be in `onlyoffice_file_utility::get_editable_extensions()`.

[classes/onlyoffice_file_utility.php](classes/onlyoffice_file_utility.php) is the single source of truth for format lists: accepted formats per document type, the `word|cell|slide|pdf` mapping, and the much smaller editable set.

### Extra entry points

- [onlyofficeeditorapi.php](onlyofficeeditorapi.php) — session-authenticated AJAX dispatched on `apiType`: `mention` (notifies mentioned users via Moodle messaging), `sections` (course sections for the Save-As dialog), `saveas` (creates a *new activity* from the editor's "Save as", via `util::save_as_document()` → `util::generate_new_module_info()` → `add_moduleinfo()`; it verifies the URL's host matches Document Server's before fetching).
- [classes/external/check_documentserver_connection.php](classes/external/check_documentserver_connection.php) — the only web service ([db/services.php](db/services.php)), driving the "check connection" button on the settings page. It delegates to [docs_settings_validator](classes/local/docs/docs_settings_validator.php), which probes `/healthcheck`, `/command` and `/converter` and maps failures back to *specific settings fields* so the admin form can highlight them.
- [desktop.php](desktop.php) — reached from a `user_loggedin` observer ([db/events.php](db/events.php) → `onlyofficeeditor_login_handler()`) when the `AscDesktopEditor` user agent is detected; hands credentials to the ONLYOFFICE desktop app via `portal:login`.
- [index.php](index.php) lists instances; [backup/moodle2/](backup/moodle2/) implements backup/restore.

### Privacy API

[classes/privacy/provider.php](classes/privacy/provider.php) is **metadata-only**, and deliberately so. It implements `metadata\provider` + `request\data_provider` and defines just `get_metadata()` — no `get_contexts_for_userid()`, `export_user_data()` or `delete_data_for_*()`, because the plugin's own table stores nothing per-user (`request\data_provider` is a marker interface, so this compiles and satisfies the privacy tests). It is *not* a null provider, though: it makes two declarations.

- `add_external_location_link('onlyofficeeditor', ['userid' => ...], ...)` — data leaves Moodle for Document Server. The `userid` string reads "Actual user ID is not sent", which is a claim about [classes/editor.php](classes/editor.php): the editor config sets `user['id'] = hash('md5', $USER->id)`, a pseudonymised id.
- `add_subsystem_link('core_files', [], ...)` — the edited documents themselves are handled by the files subsystem, which does its own export/delete.

The practical rule: **the external-location declaration must stay in sync with whatever `editor::config()` and `onlyofficeeditorapi.php` actually transmit.** Anything new added to the editor config, or to the mention flow (which sends users' full names and email addresses to the browser and on to Document Server), is personal data crossing that boundary and belongs in `get_metadata()` with a matching `privacy:metadata:*` lang string. Note that [lang/en/onlyofficeeditor.php](lang/en/onlyofficeeditor.php) already carries `privacy:metadata:*` strings describing table columns, with no `add_database_table()` call to consume them — don't assume the declared surface is complete; check the code path before answering questions about it.

### Admin settings

[settings.php](settings.php) uses custom setting classes from [classes/local/admin/settings/](classes/local/admin/settings/) in place of the core ones. `onlyoffice_admin_setting_text` extends `admin_setting_configtext` to add a `$required` flag and trim whitespace on write; `onlyoffice_admin_setting_url` extends *that* to also strip trailing slashes and reject anything `clean_param(…, PARAM_URL)` or `FILTER_VALIDATE_URL` rejects. `check_document_server_button` is not a setting at all — it renders the connection-check button. Validation is **input-validity only**; live connectivity checking is that explicit button, not a save-time side effect.

Adding a setting means: a constant in `configuration_constants` (if code reads it), the `$settings->add(...)` call, and lang strings in [lang/en/onlyofficeeditor.php](lang/en/onlyofficeeditor.php).

### Language strings and locales

**Three independent locale systems.** Don't conflate them:

1. **Moodle UI strings** — [lang/](lang/), `$string['key'] = '...'` in `lang/<code>/onlyofficeeditor.php`, read with `get_string('key', 'onlyofficeeditor')`. A handful of translated locales ship alongside `en`. **Only `lang/en/` is authoritative** — it is the one to edit; the others are translations and go out of sync silently, since Moodle falls back to English per-key with no error. Keys are kept in **alphabetical (byte) order** throughout — insert new ones in place rather than appending, since Moodle's coding style expects sorted lang files. `moodle-plugin-ci validate` separately requires `pluginname` to exist.
2. **Blank-document templates** — `newdocs/<locale>/new.{docx,xlsx,pptx,pdf}`, selected by `util::get_template_path()` from `$USER->lang`. The submodule carries far more locales than `lang/` does, under its own naming scheme, so `util::PATH_LOCALE` maps the Moodle codes that differ (e.g. `pt_br`→`pt-BR`) and anything unmatched falls back to `newdocs/default/`. Adding a `lang/` locale does **not** add a template, or vice versa.
3. **Editor UI language** — [classes/editor.php](classes/editor.php) sets `editorConfig.lang` by truncating `$USER->lang` at the first `_` (`zh_cn` → `zh`), then hands it to Document Server, which resolves it against its own locale list.

**Key naming.** Two suffix conventions coexist and both are load-bearing; match the neighbouring keys rather than picking one:

- `_help` — required suffix for `$mform->addHelpButton()` targets (`download_help`, `print_help`, `protect_help`). The name is dictated by Moodle, not by taste.
- `_desc` vs `:description` — settings descriptions use both (`documentserversecret_desc`, but `disable_verify_ssl:description`). Namespaced `:` prefixes also group error families: `connectionerror:*`, `validationerror:*` (consumed by [docs_settings_validator](classes/local/docs/docs_settings_validator.php) and the settings-page JS).

Other keys whose names are fixed by Moodle APIs: `pluginname`, `modulename`, `modulenameplural`, `modulename_help`, `privacy:metadata*`, and `messageprovider:mentionnotifier` (which must match the handler declared in [db/messages.php](db/messages.php)).

Strings are consumed from PHP via `get_string()`, from AMD via `core/str`'s `get_string(key, 'onlyofficeeditor')` (see the `displayNotification` helper in [amd/src/editor.js](amd/src/editor.js)), and from Mustache via the template's own string helper — a key removed from `lang/en/` fails loudly in PHP but can fail quietly in JS, so grep all three when renaming. Placeholders use Moodle's `{$a}` / `{$a->field}` form (e.g. `mentionnotifier:notification`), never `sprintf`.

## Licensing and third-party code

The plugin is **GPL v3 or later** ([LICENSE](LICENSE)), matching Moodle. Every PHP and JS file carries the standard Moodle GPL header comment *and* a `@license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later` docblock tag — `phpcs`/`phpdoc` fail without them, so copy the header from a neighbouring file when adding one. Copyright lines are `Ascensio System SIA <integration@onlyoffice.com>`, often alongside a `based on work by ... Olumuyiwa Taiwo` line on files inherited from the original module; keep that attribution when editing such a file. [AUTHORS.md](AUTHORS.md) is the canonical author list.

Third-party attributions live in **[3rd-Party.license](3rd-Party.license)**, with full licence texts in [licenses/](licenses/). Note the file is duplicated — the root copy and `licenses/3rd-Party.license` are byte-identical, so **edit both or they drift**.

What is and isn't bundled matters here, because the declared list is short for a reason:

- The plugin vendors **no PHP dependencies** — there is no `composer.json` and no `vendor/`. `Firebase\JWT` (used by [classes/jwt_wrapper.php](classes/jwt_wrapper.php)) and jQuery (the `define(['jquery'], …)` AMD dependency) both come from **Moodle core**, which is why neither is a bundled dependency of this plugin even though `3rd-Party.license` credits them. Don't add a composer dependency to pull in something core already ships.
- `newdocs/` is a submodule under a **different licence** — Apache 2.0, not GPL — and it *is* redistributed, since [.github/workflows/artifact.yml](.github/workflows/artifact.yml) initialises submodules and packages the directory into the release zip. Its own `LICENSE` travels with it; leave it in place.

Anything genuinely new and bundled needs three things together: the entry in both `3rd-Party.license` copies, its full licence text in `licenses/`, and a compatible licence — GPLv3 is the constraint. The CHANGELOG records past rounds of "approval blockers" and "market code prechecks", so treat this as a release gate rather than housekeeping.

## Conventions

- Editor config array keys are **camelCase and case-sensitive** (`editorConfig`, `callbackUrl`, `documentType`, `fillForms`) — they go straight to the ONLYOFFICE API. The rest of the PHP is Moodle's lowercase-no-underscore style.
- Templates are Mustache in [templates/](templates/), rendered via `$OUTPUT->render_from_template('mod_onlyofficeeditor/<name>', ...)`; they are linted in CI.
