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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Configuration constants for OnlyOffice Editor.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

/**
 * Class containing configuration constants for OnlyOffice Editor.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class configuration_constants {
    /** @var string Plugin name identifier */
    public const PLUGIN_NAME = 'onlyofficeeditor';

    /** @var string Configuration key for SSL verification disable option */
    public const CONFIG_DISABLE_VERIFY_SSL = 'disable_verify_ssl';

    /** @var string Configuration key for JWT header name */
    public const CONFIG_JWT_HEADER = 'jwtheader';

    /** @var string Configuration key for document server public URL */
    public const CONFIG_DOCS_PUBLIC_URL = 'documentserverurl';

    /** @var string Configuration key for document server internal URL */
    public const CONFIG_DOCS_INTERNAL_URL = 'documentserverinternal';

    /** @var string Configuration key for document server secret key */
    public const CONFIG_SECRET = 'documentserversecret';

    /** @var string Configuration key for force save option */
    public const CONFIG_FORCESAVE = 'forcesave';

    /** @var string Configuration key for storage internal URL */
    public const CONFIG_STORAGE_INTERNAL_URL = 'storageurl';
}
