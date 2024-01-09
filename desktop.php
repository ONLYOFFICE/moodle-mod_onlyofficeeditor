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
 * @copyright   2023 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_onlyofficeeditor\util;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $CFG, $USER;

if (!util::desktop_detect()) {
    redirect($CFG->wwwroot);
}

$domain = "'" . $CFG->wwwroot . "'";
$displayname = "'" . \fullname($USER) . "'";
$provider = "'Moodle'";
$redirecturl = "'" . $CFG->wwwroot . "'";

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