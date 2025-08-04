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
 * Onlyoffice file utility.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

/**
 * Onlyoffice file utility class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class onlyoffice_file_utility {
    /**
     * Get accepted spreadsheets exntensions.
     * @return string[] Accepted extensions of spreadsheet files
     */
    public static function get_accepted_spreadsheet_formats() {
        return ['.xls', '.xlsx', '.xlsm',
            '.xlt', '.xltx', '.xltm',
            '.ods', '.fods', '.ots', '.csv'];
    }

    /**
     * Get accepted document extensions.
     * @return string[] Accepted extensions of document files.
     */
    public static function get_accepted_document_formats() {
        return ['.doc', '.docx', '.docm',
            '.dot', '.dotx', '.dotm',
            '.odt', '.fodt', '.ott', '.rtf', '.txt',
            '.html', '.htm', '.mht', '.xml',
            '.fb2', '.epub'];
    }

    /**
     * Get accepted presentation extensions.
     * @return string[] Accepted extensions of presentation files.
     */
    public static function get_accepted_presentation_formats() {
        return ['.pps', '.ppsx', '.ppsm',
            '.ppt', '.pptx', '.pptm',
            '.pot', '.potx', '.potm',
            '.odp', '.fodp', '.otp'];
    }

    /**
     * Get accepted presentation extensions.
     * @return string[] Accepted extensions of presentation files.
     */
    public static function get_accepted_pdf_formats() {
        return ['.djvu', '.pdf', '.oform', '.docxf', '.xps', '.oxps'];
    }

    /**
     * Return all the accepted formats
     *
     * @return array formats
     */
    public static function get_accepted_formats() {
        $formats = array_unique(array_merge(
            self::get_accepted_spreadsheet_formats(),
            self::get_accepted_presentation_formats(),
            self::get_accepted_document_formats(),
            self::get_accepted_pdf_formats(),
        ));

        return $formats;
    }


    /**
     * Determine if the format is supported by onlyoffice editor
     *
     * @param string $extension
     * @return bool
     */
    public static function is_format_supported(string $extension) {
        $formats = static::get_accepted_formats();

        foreach ($formats as $format) {
            if ($format === ".$extension") {
                return true;
            }
        }

        return false;
    }

    /**
     * Get document type by extension.
     * @param string $ext File extension.
     * @return string|null
     */
    public static function get_document_type($ext) {
        if (in_array($ext, self::get_accepted_document_formats())) {
            return 'word';
        }
        if (in_array($ext, self::get_accepted_spreadsheet_formats())) {
            return 'cell';
        }
        if (in_array($ext, self::get_accepted_presentation_formats())) {
            return 'slide';
        }
        if (in_array($ext, self::get_accepted_pdf_formats())) {
            return 'pdf';
        }
        return 'word';
    }

    /**
     * Editable extensions.
     * @return string[] Editable extensions.
     */
    public static function get_editable_extensions() {
        return ['.docx', '.xlsx', '.pptx', '.docxf', '.oform', '.pdf'];
    }
}
