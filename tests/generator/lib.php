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
 * mod_onlyofficeeditor data generator.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mod_onlyofficeeditor data generator class.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_onlyofficeeditor_generator extends testing_module_generator {

    /**
     * Create a new mod_onlyofficeeditor activity.
     *
     * @param array|stdClass|null $record Custom values to merge over the defaults.
     * @param array|null $options Standard module-generator options.
     * @return stdClass The created instance.
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (array) $record + [
            'name'        => 'OnlyOffice activity',
            'intro'       => '',
            'introformat' => FORMAT_MOODLE,
            'display'     => 0,
            'download'    => 1,
            'print'       => 1,
            'protect'     => 1,
        ];

        return parent::create_instance($record, (array) $options);
    }
}
