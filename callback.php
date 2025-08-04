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
 * Callback handler.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * @todo Log disconnection (editor close) for respective user. note, editor open (connection) is logged in view.php
 */
// phpcs:ignore moodle.Files.RequireLogin.Missing
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
// phpcs:enable

defined('AJAX_SCRIPT') || define('AJAX_SCRIPT', true);

$doc = required_param('doc', PARAM_TEXT);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex');
header('Content-Encoding: UTF-8');
header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . 'GMT');
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");

$response = [];
$response['status'] = 'success';
$response['error'] = 1;

if (empty($doc)) {
    $response['error'] = 'Bad request';
    die(json_encode($response));
}

$crypt = new \mod_onlyofficeeditor\hasher();
[$hash, $error] = $crypt->read_hash($doc);

if ($error || $hash == null) {
    die(json_encode($response));
}

$request = file_get_contents('php://input');
if ($request === false) {
    die(json_encode($response));
}

$data = json_decode($request, true);

if ($data === null) {
    die(json_encode($response));
}

$modconfig = get_config('onlyofficeeditor');
if (!empty($modconfig->documentserversecret)) {
    if (!empty($data['token'])) {
        try {
            $payload = \mod_onlyofficeeditor\jwt_wrapper::decode($data['token'], $modconfig->documentserversecret);
        } catch (\UnexpectedValueException $e) {
            $response['status'] = 'error';
            $response['error'] = '403 Access denied';
            die(json_encode($response));
        }
    } else {
        $jwtheader = !empty($modconfig->jwtheader) ? $modconfig->jwtheader : 'Authorization';
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $token = substr($headers[strtolower($jwtheader)], strlen('Bearer '));
        try {
            $decodedheader = \mod_onlyofficeeditor\jwt_wrapper::decode($token, $modconfig->documentserversecret);

            $payload = $decodedheader->payload;
        } catch (\UnexpectedValueException $e) {
            $response['status'] = 'error';
            $response['error'] = '403 Access denied';
            die(json_encode($response));
        }
    }

    $data['users'] = isset($payload->users) ? $payload->users : null;
    $data['url'] = isset($payload->url) ? $payload->url : null;
    $data['status'] = $payload->status;
    $data['key'] = $payload->key;
}

if (isset($data['status'])) {
    $status = (int) $data['status'];
    switch ($status) {
        case mod_onlyofficeeditor\util::STATUS_NOTFOUND:
            $response['error'] = 1;
            break;

        case mod_onlyofficeeditor\util::STATUS_MUSTSAVE:
        case mod_onlyofficeeditor\util::STATUS_FORCESAVE:
            $isforcesave = $status === mod_onlyofficeeditor\util::STATUS_FORCESAVE;
            // Save to Moodle.
            if (mod_onlyofficeeditor\util::save_document_to_moodle($data, $hash, $isforcesave)) {
                $response['error'] = 0;
            } else {
                $response['error'] = 1;
            }
            break;

        case mod_onlyofficeeditor\util::STATUS_ERRORSAVING:
        case mod_onlyofficeeditor\util::STATUS_ERRORFORCESAVE:
            $response['error'] = 1;
            break;

        case mod_onlyofficeeditor\util::STATUS_EDITING:
        case mod_onlyofficeeditor\util::STATUS_CLOSEDNOCHANGES:
            $response['error'] = 0;
            break;

        default:
            $response['error'] = 1;
    }
}
die(json_encode($response));
