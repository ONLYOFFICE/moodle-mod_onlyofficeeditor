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
 * Key and permissions for document.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

use curl;
use mod_onlyofficeeditor\configuration_manager;
use mod_onlyofficeeditor\local\exceptions\command_service_exception;
use mod_onlyofficeeditor\local\exceptions\conversion_service_exception;

/**
 * Document class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2024 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class document_service {

    /**
     * Get document conversion url.
     * @param string $documenturi original file source.
     * @param string $from original file format.
     * @param string $to format to which to convert.
     * @param string $key document key.
     * @param array $pdf pdf conversion param
     * @return string source url for converted document
     */
    public static function get_conversion_url($documenturi, $from, $to, $key, $pdf = []) {
        $modconfig = get_config('onlyofficeeditor');

        $curl = new curl();
        $curl->setHeader(['Content-type: application/json']);
        $curl->setHeader(['Accept: application/json']);

        $conversionbody = [
            "async" => false,
            "url" => $documenturi,
            "outputtype" => $to,
            "filetype" => $from,
            "title" => $key . '.' . $from,
            "key" => $key,
        ];

        if ($pdf) {
            $conversionbody['pdf']['form'] = $pdf['form'];
        }

        if (!empty($modconfig->documentserversecret)) {
            $params = [
                'payload' => $conversionbody,
            ];
            $token = \mod_onlyofficeeditor\jwt_wrapper::encode($params, $modconfig->documentserversecret);
            $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
            $curl->setHeader([$jwtheader . ': Bearer ' . $token]);

            $token = \mod_onlyofficeeditor\jwt_wrapper::encode($conversionbody, $modconfig->documentserversecret);
            $conversionbody['token'] = $token;
        }

        $conversionbody = json_encode($conversionbody);
        $documentserverurl = configuration_manager::get_document_server_internal_url();
        $conversionurl = rtrim($documentserverurl, "/") . '/ConvertService.ashx';

        $disableverifyssl = get_config('onlyofficeeditor', 'disable_verify_ssl') == 1;

        if ($disableverifyssl) {
            $curl->setopt(['CURLOPT_SSL_VERIFYPEER' => 0]);
            $curl->setopt(['CURLOPT_SSL_VERIFYHOST' => 0]);
        }

        $response = $curl->post($conversionurl, $conversionbody);

        $conversionjson = json_decode($response);
        if (isset($conversionjson->error)) {
            throw new conversion_service_exception(abs($conversionjson->error));
        }

        if (isset($conversionjson->endConvert) && $conversionjson->endConvert) {
            return $conversionjson->fileUrl;
        }

        return '';
    }

    /**
     * Command request.
     * @param string $method command method.
     * @return array command result
     */
    public static function command($method) {
        $modconfig = get_config('onlyofficeeditor');

        $curl = new curl();
        $curl->setHeader(['Content-type: application/json']);
        $curl->setHeader(['Accept: application/json']);

        $commandbody = [
            'c' => $method,
        ];

        if (!empty($modconfig->documentserversecret)) {
            $params = [
                'payload' => $commandbody,
            ];
            $token = \mod_onlyofficeeditor\jwt_wrapper::encode($params, $modconfig->documentserversecret);
            $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
            $curl->setHeader([$jwtheader . ': Bearer ' . $token]);

            $token = \mod_onlyofficeeditor\jwt_wrapper::encode($commandbody, $modconfig->documentserversecret);
            $commandbody['token'] = $token;
        }

        $commandbody = json_encode($commandbody);
        $documentserverurl = configuration_manager::get_document_server_internal_url();
        $commandurl = rtrim($documentserverurl, "/") . '/coauthoring/CommandService.ashx';

        $disableverifyssl = get_config('onlyofficeeditor', 'disable_verify_ssl') == 1;

        if ($disableverifyssl) {
            $curl->setopt(['CURLOPT_SSL_VERIFYPEER' => 0]);
            $curl->setopt(['CURLOPT_SSL_VERIFYHOST' => 0]);
        }
        $response = $curl->post($commandurl, $commandbody);

        $commandjson = json_decode($response);

        if (isset($commandjson->error) && $commandjson->error > 0) {
            throw new command_service_exception($commandjson->error);
        }

        return $commandjson;
    }

    /**
     * Get document server version
     * @return string document server version
     */
    public static function get_version() {
        $result = self::command('version');

        return $result->version;
    }
}
