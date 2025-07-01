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
 * The empty file download handler
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib/filelib.php');
// phpcs:enable

$modconfig = get_config('onlyofficeeditor');

if (!empty($modconfig->documentserversecret)) {
    $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
    $jwtheader = strtolower($jwtheader);
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);

    if (!isset($headers[$jwtheader]) || strpos($headers[$jwtheader], 'Bearer ') !== 0) {
        http_response_code(403);
        die();
    }

    $token = substr($headers[$jwtheader], strlen('Bearer '));
    try {
        $decodedheader = \mod_onlyofficeeditor\jwt_wrapper::decode($token, $modconfig->documentserversecret);
    } catch (\UnexpectedValueException $e) {
        http_response_code(403);
        die();
    }
}

$storageurl = \mod_onlyofficeeditor\configuration_manager::get_storage_url();
$templatename = 'new.docx';
$templatepath = $storageurl . '/mod/onlyofficeeditor/newdocs/default/' . $templatename;
send_file($templatepath, $templatename, 0, 0, false, false, '', false, []);
