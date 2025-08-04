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
 * Jwt wrapper.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

/**
 * Jwt wrapper class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class jwt_wrapper {
    /**
     * Encrypting payload.
     * @param array $payload payload to crypt.
     * @param string $secret secret key.
     */
    public static function encode($payload, $secret) {
        \Firebase\JWT\JWT::$leeway = 60;
        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Decrypting payload.
     * @param string $token jwt string.
     * @param string $secret secret key.
     */
    public static function decode($token, $secret) {
        \Firebase\JWT\JWT::$leeway = 60;
        return \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
    }
}
