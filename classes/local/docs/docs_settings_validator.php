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

use curl;
use Exception;
use mod_onlyofficeeditor\configuration_constants;
use mod_onlyofficeeditor\configuration_manager;
use mod_onlyofficeeditor\jwt_wrapper;
use mod_onlyofficeeditor\local\exceptions\command_service_exception;
use mod_onlyofficeeditor\local\exceptions\conversion_service_exception;
use mod_onlyofficeeditor\local\exceptions\docs_validation_exception;

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
    protected array $errors = [];

    /**
     * Validate document settings.
     *
     * @return array List of errors if any, empty array if validation passed.
     */
    public function validate(array $data): array {
        $secret = $data[configuration_constants::CONFIG_SECRET] ?? '';
        $jwtheader = $data[configuration_constants::CONFIG_JWT_HEADER] ?? '';
        $internalurl = $data[configuration_constants::CONFIG_DOCS_INTERNAL_URL] ?? '';
        $storageurl = $data[configuration_constants::CONFIG_STORAGE_INTERNAL_URL] ?? '';
        $disableverifyssl = $data[configuration_constants::CONFIG_DISABLE_VERIFY_SSL] ?? false;

        try {
            $this->check_document_server($internalurl, $disableverifyssl);
            $this->check_command_service($internalurl, $secret, $disableverifyssl);
            $this->check_conversion_service($internalurl, $jwtheader, $secret, $disableverifyssl);
        } catch (docs_validation_exception $e) {
            // Catch validation exceptions and store the error messages.
            $field = $e->get_field();
            $this->errors[$field] = $e->getMessage();
        } catch (Exception $e) {
            // Catch any other exceptions and log them.
            debugging('Unexpected error: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }

        return $this->errors;
    }

    /**
     * Get errors
     * @return array
     */
    public function get_errors(): array {
        return $this->errors;
    }

    /**
     * Check if the Document Server url is valid.
     * @param string $docserverurl Document Server url.
     * @throws \mod_onlyofficeeditor\local\exceptions\docs_validation_exception
     * @return void
     */
    private function check_docserverurl($docserverurl) {
        if (empty($docserverurl)) {
            throw new docs_validation_exception('docsserverurl', get_string('validationerror:emptyurl', 'onlyofficeeditor'));
        }

        if (!filter_var($docserverurl, FILTER_VALIDATE_URL)) {
            throw new docs_validation_exception('docsserverurl', get_string('validationerror:invalidurl', 'onlyofficeeditor'));
        }
    }

    /**
     * Check for mixed content issues between Moodle and Document Server URLs.
     *
     * @param string $docserverurl Document Server url.
     * @return void
     */
    private function check_for_mixed_content($docserverurl) {
        global $CFG;

        $moodleurl = $CFG->wwwroot;

        if (strpos($moodleurl, 'https://') === 0 && strpos($docserverurl, 'https://') !== 0) {
            throw new docs_validation_exception(
                'docserverurl',
                get_string('validationerror:mixedcontent', 'onlyofficeeditor')
            );
        }
    }

    /**
     * Check document server health.
     *
     * @param string $internalurl Document Server internal url.
     * @param bool $disableverifyssl Flag for disabling ssl verification.
     * @return void
     */
    private function check_document_server($internalurl, $disableverifyssl) {
        $healthcheckurl = "$internalurl/healthcheck";

        $ch = new curl();

        if ($disableverifyssl) {
            $ch->setopt(['CURLOPT_SSL_VERIFYPEER' => 0]);
            $ch->setopt(['CURLOPT_SSL_VERIFYHOST' => 0]);
        }

        try {
            $response = $ch->get($healthcheckurl);
        } catch (Exception $e) {
            throw new docs_validation_exception('general', get_string('connectionerror:unexpected', 'onlyofficeeditor'));
        }

        if ($response !== 'true') {
            throw new docs_validation_exception(
                configuration_constants::CONFIG_DOCS_INTERNAL_URL,
                get_string('validationerror:documentserverunreachable', 'onlyofficeeditor')
            );
        }
    }

    /**
     * Check command service.
     *
     * @param string $docserverurl Document Server url.
     * @param string $secret JWT secret.
     * @param bool $disableverifyssl Flag for disabling ssl verification.
     * @return void
     */
    private function check_command_service($docserverurl, $secret, $disableverifyssl) {
        $curl = new curl();
        $curl->setHeader(['Content-type: application/json']);
        $curl->setHeader(['Accept: application/json']);

        $commandbody = [
            'c' => 'version',
        ];

        $token = jwt_wrapper::encode($commandbody, $secret);
        $commandbody['token'] = $token;
        $commandbody = json_encode($commandbody);

        $commandurl = "$docserverurl/command";

        if ($disableverifyssl) {
            $curl->setopt(['CURLOPT_SSL_VERIFYPEER' => 0]);
            $curl->setopt(['CURLOPT_SSL_VERIFYHOST' => 0]);
        }

        $response = $curl->post($commandurl, $commandbody);
        $commandjson = json_decode($response);

        if (isset($commandjson->error) && abs($commandjson->error) > 0) {
            if ($commandjson->error === command_service_exception::ERROR_INVALID_TOKEN) {
                throw new docs_validation_exception(
                    configuration_constants::CONFIG_SECRET,
                    get_string('validationerror:incorrectsecret', 'onlyofficeeditor')
                );
            } else {
                throw new docs_validation_exception(
                    'general',
                    get_string('connectionerror:command', 'onlyofficeeditor')
                );
            }
        }
    }

    /**
     * Check conversion service.
     *
     * @param string $internalurl Document Server internal url.
     * @param string $jwtheader JWT header.
     * @param string $secret JWT secret.
     * @param bool $disableverifyssl Flag for disabling ssl verification.
     *
     * @return void
     */
    private function check_conversion_service($internalurl, $jwtheader, $secret, $disableverifyssl) {
        $temporaryfileurl = $this->get_temp_file_url();

        $curl = new curl();
        $curl->setHeader(['Content-type: application/json']);
        $curl->setHeader(['Accept: application/json']);

        $key = 'key_' . time();
        $extension = 'docx';

        $conversionbody = [
            "async" => false,
            "url" => $temporaryfileurl,
            "outputtype" => $extension,
            "filetype" => $extension,
            "title" => "$key.$extension",
            "key" => $key,
        ];

        if (!empty($secret)) {
            $params = [
                'payload' => $conversionbody,
            ];
            $token = jwt_wrapper::encode($params, $secret);
            $curl->setHeader([$jwtheader . ': Bearer ' . $token]);
        }

        $conversionbody = json_encode($conversionbody);
        $conversionurl = "$internalurl/converter";

        if ($disableverifyssl) {
            $curl->setopt(['CURLOPT_SSL_VERIFYPEER' => 0]);
            $curl->setopt(['CURLOPT_SSL_VERIFYHOST' => 0]);
        }

        $response = $curl->post($conversionurl, $conversionbody);
        $conversionjson = json_decode($response);

        if (isset($conversionjson->error) && abs($conversionjson->error) > 0) {
            if (abs($conversionjson->error) === conversion_service_exception::ERROR_INVALID_TOKEN) {
                throw new docs_validation_exception(
                    configuration_constants::CONFIG_JWT_HEADER,
                    get_string('validationerror:incorrectjwtheader', 'onlyofficeeditor')
                );
            } else {
                throw new docs_validation_exception(
                    'general',
                    get_string('connectionerror:conversion', 'onlyofficeeditor')
                );
            }
        }

        if (!(isset($conversionjson->endConvert) && $conversionjson->endConvert)) {
            throw new docs_validation_exception(
                'general',
                get_string('connectionerror:conversion', 'onlyofficeeditor')
            );
        }
    }

    /**
     * Get temporary file URL.
     *
     * @return string Temporary file URL
     */
    private function get_temp_file_url() {
        $storageurl = configuration_manager::get_storage_url();
        return $storageurl . '/mod/onlyofficeeditor/newdocs/default/new.docx';
    }
}
