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
 * The ONLYOFFICE editor api.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
defined('AJAX_SCRIPT') || define('AJAX_SCRIPT', true);

$cmid = required_param('cmid', PARAM_INT);
$apitype = required_param('apiType', PARAM_TEXT);

$context = CONTEXT_MODULE::instance($cmid);
switch ($apitype) {
    case 'mention':
        require_capability('mod/onlyofficeeditor:editdocument', $context);
        try {
            $courseid = required_param('courseid', PARAM_INT);
            require_login($courseid);
            $actionlink = required_param('link', PARAM_URL);
            $emails = required_param('emails', PARAM_TEXT);
            $comment = required_param('comment', PARAM_TEXT);
            \mod_onlyofficeeditor\util::mention_user_in_comment($actionlink, $comment, $emails, $context);
            echo json_encode($comment);
        } catch (moodle_exception $e) {
            throw new \Exception();
        }
        break;
    case 'sections':
        try {
            $courseid = required_param('courseid', PARAM_INT);
            require_login($courseid);
            $moduleinfo = get_fast_modinfo($courseid);
            $sections = course_get_format($courseid)->get_sections();
            $data = new stdClass();
            $data->sections = [];
            foreach ($sections as $key => $sectioninfo) {
                $sectionobject = new stdClass();
                $sectionobject->sectionid = $sectioninfo->section;
                $sectionobject->sectionname = get_section_name($courseid, $sectioninfo);
                $data->sections[] = $sectionobject;
            }
            echo json_encode($data);
        } catch (\Exception $e) {
            throw new \Exception();
        }
        break;
    case 'saveas':
        try {
            $courseid = required_param('courseid', PARAM_INT);
            require_login($courseid);
            $url = required_param('url', PARAM_URL);
            $title = required_param('title', PARAM_TEXT);
            $section = required_param('section', PARAM_INT);
            \mod_onlyofficeeditor\util::save_as_document($url, $title, $context, $cmid, $courseid, $section);
            echo json_encode($title);
        } catch (\Exception $e) {
            throw new \Exception();
        }
        break;
    default:
        break;
}

die();
