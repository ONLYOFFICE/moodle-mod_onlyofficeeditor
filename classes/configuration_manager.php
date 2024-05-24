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
 * Onlyoffice configuration manager.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

/**
 * Onlyoffice configuration manager class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class configuration_manager {

    /**
     * Get the document service address available from Moodle from the application configuration
     * @return string document server url
     */
    public static function get_document_server_internal_url() {
        $url = get_config("onlyofficeeditor", "documentserverinternal");
        if (empty($url)) {
            return get_config("onlyofficeeditor", "documentserverurl");
        }
        return $url;
    }

    /**
     * Get the Moodle address available from document server from the application configuration
     *
     * @return string document storage url
     */
    public static function get_storage_url() {
        global $CFG;
        $url = get_config("onlyofficeeditor", "storageurl");
        if (empty($url)) {
            return $CFG->wwwroot;
        }
        return $url;
    }

    /**
     * Replace domain in document server url with internal address from configuration
     *
     * @param string $url - document server url
     *
     * @return string
     */
    public static function replace_document_server_url_to_internal($url) {
        $documentserverurl = self::get_document_server_internal_url();
        if (!empty($documentserverurl)) {
            $from = get_config("onlyofficeeditor", "documentserverurl");

            if ($from !== $documentserverurl) {
                $url = str_replace($from, $documentserverurl, $url);
            }
        }

        return $url;
    }
}
