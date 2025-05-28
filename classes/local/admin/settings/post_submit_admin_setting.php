<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Post-submit admin setting.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\admin\settings;

use admin_setting;
use mod_onlyofficeeditor\local\docs\docs_settings_validator;
use core\notification;

/**
 * Post-submit admin setting.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_submit_admin_setting extends admin_setting {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct(
            'onlyofficeeditor_post_submit_admin_setting',
            '',
            '',
            ''
        );
    }

    /**
     * Get setting.
     *
     * @return bool True
     */
    public function get_setting() {
        return true;
    }

    /**
     * Write setting.
     *
     * @param string $data Data to write
     * @return string Empty string
     */
    public function write_setting($data) {
        // Validate settings after every form submission.
        $docssettingsvalidator = new docs_settings_validator();
        $docssettingsvalidator->validate();
        if ($docssettingsvalidator->has_errors()) {
            foreach ($docssettingsvalidator->get_errors() as $error) {
                notification::error($error);
            }
        }

        return '';
    }

    /**
     * Output HTML.
     *
     * @param string $data Data to output
     * @param string $query Query string
     * @return string HTML output
     */
    public function output_html($data, $query='') {
        return '<div><input type="hidden" name="' . $this->get_full_name() . '" value="1"/></div>';
    }
}
