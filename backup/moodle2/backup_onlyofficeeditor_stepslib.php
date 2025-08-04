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
 * Define all the backup steps.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_onlyofficeeditor_activity_task
 */

/**
 * Define the complete choice structure for backup, with file and id annotations
 */
class backup_onlyofficeeditor_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define structure.
     *
     * @return backup_nested_element
     * @throws base_element_struct_exception
     * @throws base_step_exception
     */
    protected function define_structure() {
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $onlyofficeeditor = new backup_nested_element('onlyofficeeditor', ['id'], [
            'name', 'intro', 'introformat', 'timecreated', 'timemodified',
            'display', 'displayoptions', 'permissions', 'documentkey',
        ]);

        // Define sources.
        $onlyofficeeditor->set_source_table('onlyofficeeditor', ['id' => backup::VAR_ACTIVITYID]);

        // Define file annotations.
        $onlyofficeeditor->annotate_files('mod_onlyofficeeditor', 'intro', null);
        $onlyofficeeditor->annotate_files('mod_onlyofficeeditor', 'content', null);

        // Return the root element (instance), wrapped into standard activity structure.
        return $this->prepare_activity_structure($onlyofficeeditor);
    }
}
