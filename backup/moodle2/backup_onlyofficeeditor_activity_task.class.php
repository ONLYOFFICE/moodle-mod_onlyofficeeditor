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
 * Defines backup_onlyofficeeditor_activity_task class
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/onlyofficeeditor/backup/moodle2/backup_onlyofficeeditor_stepslib.php');
require_once($CFG->dirroot . '/mod/onlyofficeeditor/backup/moodle2/backup_onlyofficeeditor_settingslib.php');

/**
 * Provides the steps to perform one complete backup of the onlyofficeeditor instance
 */
class backup_onlyofficeeditor_activity_task extends backup_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the onlyofficeeditor.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_onlyofficeeditor_activity_structure_step('onlyofficeeditor_structure', 'onlyofficeeditor.xml'));
    }

    /**
     * Encode URLs
     *
     * @param string $content html text with URLs to the activity instance scripts
     * @return string content with encoded URLs
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to instances list.
        $search = "/(" . $base . "\/mod\/onlyofficeeditor\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ONLYOFFICEEDITORINDEX*$2@$', $content);

        // Link to the editor page.
        $search = "/(" . $base . "\/mod\/onlyofficeeditor\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ONLYOFFICEEDITORVIEWBYID*$2@$', $content);

        return $content;
    }
}
