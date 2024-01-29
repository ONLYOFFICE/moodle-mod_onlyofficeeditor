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
 * Upgrade code for the onlyoffice.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2024 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute ONLYOFFICE upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_onlyofficeeditor_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.
    $coretypes = core_filetypes::get_types();

    if (!array_key_exists("docxf", $coretypes)) {
        core_filetypes::add_type("docxf", "application/vnd.openxmlformats-officedocument.wordprocessingml.document.docxf",
            "document", array(), '', 'ONLYOFFICE docxf');
    }
    if (!array_key_exists("oform", $coretypes)) {
        core_filetypes::add_type("oform", "application/vnd.openxmlformats-officedocument.wordprocessingml.document.oform",
            "document", array(), '', 'ONLYOFFICE oform');
    }

    return true;
}
