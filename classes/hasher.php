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
 * Hash handler.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

use mod_onlyofficeeditor\util;

/**
 * Hash handler class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hasher {
    /**
     * The plugin key from the plugin configuration.
     *
     * @var string
     */
    private $appkey;

    /**
     * crypt constructor.
     */
    public function __construct() {
        $this->appkey = util::get_appkey();
    }

    /**
     * Get hash for object.
     * @param mixed $object object.
     * @return string encoded object.
     */
    public function get_hash($object) {
        $primarykey = json_encode($object);
        $hash = $this->signature_create($primarykey);
        return $hash;
    }

    /**
     * Decode hash.
     * @param string $hash encoded object.
     * @return array decoded object.
     */
    public function read_hash($hash) {
        $result = null;
        $error = null;
        if ($hash === null) {
            return [$result, "hash is empty"];
        }
        try {
            $payload = base64_decode($hash);
            $payloadparts = explode("?", $payload, 2);

            if (count($payloadparts) === 2) {
                $encode = base64_encode(hash("sha256", ($payloadparts[1] . $this->appkey), true));

                if ($payloadparts[0] === $encode) {
                    $result = json_decode($payloadparts[1]);
                } else {
                    $error = "hash not equal";
                }
            } else {
                $error = "incorrect hash";
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        return [$result, $error];
    }

    /**
     * Create hash.
     * @param string $key json encoded string.
     * @return string hash of payload.
     */
    private function signature_create($key) {
        $payload = base64_encode(hash("sha256", ($key . $this->appkey), true)) . "?" . $key;
        $base64str = base64_encode($payload);
        return $base64str;
    }
}
