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
 * Behat custom steps for mod_onlyofficeeditor.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Custom Behat steps for the ONLYOFFICE Document Editor activity.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_onlyofficeeditor extends behat_base {

    /**
     * Configure the Document Server connection from environment variables.
     *
     * @Given /^the ONLYOFFICE Document Server connection is configured from the environment$/
     */
    public function the_docs_connection_is_configured_from_env(): void {
        $url      = getenv('BEHAT_ONLYOFFICEEDITOR_DOCS_URL') ?: 'http://localhost:8080';
        $secret   = getenv('BEHAT_ONLYOFFICEEDITOR_DOCS_SECRET') ?: 'secret';
        $header   = getenv('BEHAT_ONLYOFFICEEDITOR_JWT_HEADER') ?: 'Authorization';
        $internal = getenv('BEHAT_ONLYOFFICEEDITOR_DOCS_INTERNAL_URL') ?: '';
        $storage  = getenv('BEHAT_ONLYOFFICEEDITOR_DOCS_STORAGE_URL') ?: '';

        set_config('documentserverurl', $url, 'onlyofficeeditor');
        set_config('documentserversecret', $secret, 'onlyofficeeditor');
        set_config('jwtheader', $header, 'onlyofficeeditor');
        set_config('documentserverinternal', $internal, 'onlyofficeeditor');
        set_config('storageurl', $storage, 'onlyofficeeditor');
    }
}
