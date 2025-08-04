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
 * Define restore_onlyofficeeditor_activity_structure_step class
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps.
 */
class restore_onlyofficeeditor_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define structure.
     */
    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('onlyofficeeditor', '/activity/onlyofficeeditor');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Define restore process.
     *
     * @param array $data restore data array.
     */
    protected function process_onlyofficeeditor($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('onlyofficeeditor', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * After restore.
     */
    protected function after_restore() {
        $this->add_related_files('mod_onlyofficeeditor', 'intro', null);
        $this->add_related_files('mod_onlyofficeeditor', 'content', null);
    }
}
