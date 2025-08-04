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
 * Define restore_onlyofficeeditor_activity_task class
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/onlyofficeeditor/backup/moodle2/restore_onlyofficeeditor_stepslib.php');

/**
 * Restore task that provides all the settings and steps to perform one complete restore of the activity
 */
class restore_onlyofficeeditor_activity_task extends restore_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new restore_onlyofficeeditor_activity_structure_step('onlyofficeeditor_structure', 'onlyofficeeditor.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('onlyofficeeditor', ['intro'], 'onlyofficeeditor');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule(
            'ONLYOFFICEEDITORVIEWBYID',
            '/mod/onlyofficeeditor/view.php?id=$1',
            'course_module'
        );
        $rules[] = new restore_decode_rule(
            'ONLYOFFICEEDITORINDEX',
            '/mod/onlyofficeeditor/index.php?id=$1',
            'course'
        );

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule(
            'onlyofficeeditor',
            'add',
            'view.php?id={course_module}',
            '{onlyofficeeditor}'
        );
        $rules[] = new restore_log_rule(
            'onlyofficeeditor',
            'update',
            'view.php?id={course_module}',
            '{onlyofficeeditor}'
        );
        $rules[] = new restore_log_rule(
            'onlyofficeeditor',
            'view',
            'view.php?id={course_module}',
            '{onlyofficeeditor}'
        );
        $rules[] = new restore_log_rule(
            'onlyofficeeditor',
            'preview',
            'view.php?id={course_module}',
            '{onlyofficeeditor}'
        );

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        $rules[] = new restore_log_rule('onlyofficeeditor', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
