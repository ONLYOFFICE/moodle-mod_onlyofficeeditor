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
 * Document settings validator.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\docs;

use mod_onlyofficeeditor\configuration_manager;
use mod_onlyofficeeditor\document_service;
use mod_onlyofficeeditor\local\exceptions\command_service_exception;
use mod_onlyofficeeditor\local\exceptions\document_server_exception;
use mod_onlyofficeeditor\local\exceptions\conversion_service_exception;

/**
 * Document settings validator.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class docs_settings_validator {
    /**
     * List of errors.
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Validate document settings.
     *
     * @return void
     */
    public function validate() {
        $this->check_for_mixed_content();
        $this->check_document_server();
        $this->check_command_service();
        $this->check_conversion_service();
    }

    /**
     * Check if there are any errors.
     *
     * @return bool True if there are errors, false otherwise
     */
    public function has_errors() {
        return count($this->errors) > 0;
    }

    /**
     * Get list of errors.
     *
     * @return array List of errors
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Check for mixed content issues between Moodle and Document Server URLs.
     *
     * @return void
     */
    private function check_for_mixed_content() {
        global $CFG;

        $moodleurl = $CFG->wwwroot;
        $docserverurl = configuration_manager::get_document_server_public_url();

        if (strpos($moodleurl, 'https://') === 0 && strpos($docserverurl, 'https://') !== 0) {
            debugging('Mixed content issue: Moodle uses HTTPS but Document Server uses HTTP', DEBUG_DEVELOPER);
            $this->errors[] = get_string('mixedcontenterror', 'onlyofficeeditor');
        }
    }

    /**
     * Check document server health.
     *
     * @return void
     */
    private function check_document_server() {
        try {
            document_service::health_check();
        } catch (document_server_exception $e) {
            debugging('Document server error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $this->errors[] = get_string('documentservererror', 'onlyofficeeditor');
            return;
        }
    }

    /**
     * Check command service.
     *
     * @return void
     */
    private function check_command_service() {
        try {
            document_service::command('version');
        } catch (command_service_exception $e) {
            debugging('Command service error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            if ($e->getCode() === command_service_exception::ERROR_INVALID_TOKEN) {
                $this->errors[] = get_string('validationerror:incorrectsecret', 'onlyofficeeditor');
                return;
            }
        }
    }

    /**
     * Check conversion service.
     *
     * @return void
     */
    private function check_conversion_service() {
        $temporaryfileurl = $this->get_temp_file_url();

        try {
            document_service::get_conversion_url(
                $temporaryfileurl,
                'docx',
                'docx',
                'key_' . time()
            );
        } catch (conversion_service_exception $e) {
            debugging('Conversion service error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $this->errors[] = get_string('conversionserviceerror', 'onlyofficeeditor');
            return;
        }
    }

    /**
     * Get temporary file URL.
     *
     * @return string Temporary file URL
     */
    private function get_temp_file_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/onlyofficeeditor/newdocs/default/new.docx';
    }
}
