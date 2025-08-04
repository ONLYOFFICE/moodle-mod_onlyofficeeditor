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
 * Custom config text admin setting.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\admin\settings;

use admin_setting_configtext;

/**
 * Custom config text admin setting.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class onlyoffice_admin_setting_text extends admin_setting_configtext {
    /**
     * Whether the setting is required.
     *
     * @var bool
     */
    protected $required;

    /**
     * Constructor.
     *
     * @param string $name Name of the setting
     * @param string $visiblename Visible name of the setting
     * @param string $description Description of the setting
     * @param bool $required Whether the setting is required
     * @param string $defaultsetting Default value for the setting
     * @param int $paramtype Type of parameter (default is PARAM_TEXT)
     */
    public function __construct(
        $name,
        $visiblename,
        $description,
        $required = false,
        $defaultsetting = '',
        $paramtype = PARAM_TEXT
    ) {
        $this->required = $required;
        parent::__construct($name, $visiblename, $description, $defaultsetting, $paramtype);
    }

    /**
     * Write setting.
     *
     * @param string $data Data to write
     * @return string Empty string
     */
    public function write_setting($data) {
        // Clean up the data before saving.
        $data = trim($data);

        return parent::write_setting($data);
    }

    /**
     * Validate data before storage.
     *
     * @param string $data The string to be validated.
     * @return bool|string true for success or error string if invalid.
     */
    public function validate($data) {
        if ($this->required && empty($data)) {
            return get_string('required', 'admin');
        }

        return parent::validate($data);
    }
}
