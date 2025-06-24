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
 * Check document server connection ws function.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\external;

use context_system;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_api;
use mod_onlyofficeeditor\local\docs\docs_settings_validator;

/**
 * Check document server connection ws function.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_documentserver_connection extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'docserverurl' => new external_value(PARAM_URL, 'Document server URL'),
            'secret' => new external_value(PARAM_TEXT, 'Document server secret', VALUE_DEFAULT, ''),
            'jwtheader' => new external_value(PARAM_TEXT, 'JWT header', VALUE_DEFAULT, ''),
            'internalurl' => new external_value(PARAM_URL, 'Internal URL for the document server', VALUE_DEFAULT, ''),
            'storageurl' => new external_value(PARAM_URL, 'Storage URL for the document server', VALUE_DEFAULT, ''),
            'disableverifyssl' => new external_value(PARAM_BOOL, 'Disable SSL verification', VALUE_DEFAULT, false),
        ]);
    }

    /**
     * Execute the function to check the connection with the Document Server
     *
     * @param string $docserverurl Document Server public url
     * @param string $secret JWT secret
     * @param string $jwtheader JWT Header
     * @param string $internalurl Document Server internal url
     * @param string $storageurl Document Storage url
     * @param bool $disableverifyssl Flag for disabling ssl verification
     * @return array
     */
    public static function execute($docserverurl, $secret, $jwtheader, $internalurl, $storageurl, $disableverifyssl) {
        global $CFG;
        require_once("$CFG->dirroot/lib/filelib.php");

        $contextsystem = context_system::instance();
        self::validate_context($contextsystem);
        require_capability('moodle/site:config', $contextsystem);

        [
            'docserverurl' => $docserverurl,
            'secret' => $secret,
            'jwtheader' => $jwtheader,
            'internalurl' => $internalurl,
            'storageurl' => $storageurl,
            'disableverifyssl' => $disableverifyssl,
        ] = self::validate_parameters(self::execute_parameters(), [
            'docserverurl' => $docserverurl,
            'secret' => $secret,
            'jwtheader' => $jwtheader,
            'internalurl' => $internalurl,
            'storageurl' => $storageurl,
            'disableverifyssl' => $disableverifyssl,
        ]);

        $validator = new docs_settings_validator();
        $validationerrors = $validator->validate([
            'docserverurl' => $docserverurl,
            'secret' => $secret,
            'jwtheader' => $jwtheader,
            'internalurl' => $internalurl,
            'storageurl' => $storageurl,
            'disableverifyssl' => $disableverifyssl,
        ]);

        return self::return_errors(
            empty($validationerrors) ? 'success' : 'error',
            array_map(function($key, $message) {
                return [
                    'field' => $key,
                    'message' => $message,
                ];
            }, array_keys($validationerrors), $validationerrors)
        );
    }

    /**
     * Describe the return structure of the external service.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status: success or error'),
            'errors' => new external_multiple_structure(
                new external_single_structure([
                    'field' => new external_value(PARAM_TEXT, 'Error field name'),
                    'message' => new external_value(PARAM_TEXT, 'Error message'),
                ])
            ),
        ]);
    }

    /**
     * Handle return error.
     *
     * @param string $status Validation status
     * @param array $errors Errors list
     * @return array
     */
    protected static function return_errors($status, $errors): array {
        return [
            'status' => $status,
            'errors' => $errors,
        ];
    }
}
