# Development stand

Moodle + ONLYOFFICE Docs in Docker, with this repository mounted into Moodle as `mod/onlyofficeeditor`.
Needs Docker and bash (on Windows: Git Bash with Docker Desktop).

```bash
dev/stand.sh            # clone Moodle, start, install/upgrade, configure the plugin
```

Moodle is at http://localhost:8000 (`admin` / `admin`), Docs at http://localhost:8080.
Plugin edits are live; the Moodle checkout lives in a Docker volume, not on disk.

If [moodle-assignsubmission_onlyoffice](https://github.com/ONLYOFFICE/moodle-assignsubmission_onlyoffice) is cloned
next to this repository, the stand mounts it as `mod/assign/submission/onlyoffice` and installs it too:

```
Docs/
├── moodle-mod_onlyofficeeditor/        # this repository, run dev/stand.sh here
└── moodle-assignsubmission_onlyoffice/
```

It needs no settings of its own: it reads the Docs connection from this plugin. Clone it after the stand is
already running and just run `dev/stand.sh` again.

| Command | |
|---|---|
| `dev/stand.sh up` | start (default); also runs the upgrade after a `version.php` bump |
| `dev/stand.sh down` | stop, keep data |
| `dev/stand.sh destroy` | stop and delete Moodle, database and files |
| `dev/stand.sh grunt` | rebuild `amd/build` from `amd/src` |
| `dev/stand.sh grunt assign` | the same for assignsubmission |
| `dev/stand.sh php admin/cli/purge_caches.php` | run a Moodle CLI script |
| `dev/stand.sh shell` / `logs [service]` | shell in the webserver / follow logs |

Settings go in `dev/.env` (git-ignored); defaults shown:

```
MOODLE_BRANCH=MOODLE_503_STABLE   # changing it needs `destroy` first
PHP_VERSION=8.3
MOODLE_PORT=8000
DS_PORT=8080
DS_VERSION=latest
DS_JWT_SECRET=secret
ADMIN_USER=admin
ADMIN_PASS=admin
ASSIGNSUBMISSION_DIR=../../moodle-assignsubmission_onlyoffice   # relative to dev/; empty = leave it out
```

Moodle 5.x needs PostgreSQL 17, which the stand uses. The stand is tested on `MOODLE_503_STABLE`; `MOODLE_405_STABLE` (no `public/` directory) is handled but untested.

## How the addresses fit together

The browser uses `localhost:8000` and `localhost:8080`. Inside Docker, Docs reaches Moodle as `http://webserver`
(`storageurl`) and Moodle reaches Docs as `http://documentserver` (`documentserverinternal`). `config.php`
switches `wwwroot` by host so Moodle does not redirect Docs to `localhost`, and the `ds-proxy` container makes
`localhost:8080` resolve to Docs from PHP too, because `view.php` checks the public URL server-side.
