<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Desktop handler.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_onlyofficeeditor\util;

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
// phpcs:enable

global $CFG, $USER, $SESSION;

$wantsurl = !empty($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;

$domain = "'" . $CFG->wwwroot . "'";
$displayname = "'" . \fullname($USER) . "'";
$provider = "'Moodle'";
$redirecturl = "'" . $wantsurl . "'";

if (!util::desktop_detect()) {
    redirect($wantsurl);
}

$js = <<< JAVASCRIPT
    if (!window['AscDesktopEditor']) {
        location.href = $redirecturl;
    }

    var data = {
        displayName: $displayname,
        domain: $domain,
        provider: $provider,
    };

    window.AscDesktopEditor.execCommand('portal:login', JSON.stringify(data));

    location.href = $redirecturl;
JAVASCRIPT;

echo html_writer::script($js);
