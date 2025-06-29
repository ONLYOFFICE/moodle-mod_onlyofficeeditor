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
 * Repository to perform WS calls for mod_onlyofficeeditor.
 *
 * @module     mod_onlyofficeeditor/repository
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

/**
 * Check connection to the Document Server.
 *
 * @param {string} documentserversecret - The secret key for the Document Server.
 * @param {string} jwtheader - The JWT header for authentication.
 * @param {string} documentserverinternal - The internal URL for the Document Server.
 * @param {string} storageurl - The storage URL for the Document Server.
 * @param {boolean} disable_verify_ssl - Whether to disable SSL verification.
 * @returns {Promise}
 */
export const checkDocumentServerConnection = (
    documentserversecret,
    jwtheader,
    documentserverinternal,
    storageurl,
    disable_verify_ssl, // eslint-disable-line camelcase
) => {
    const args = {
        documentserversecret,
        jwtheader,
        documentserverinternal,
        storageurl,
        disable_verify_ssl, // eslint-disable-line camelcase
    };

    return fetchMany([{methodname: 'mod_onlyofficeeditor_check_documentserver_connection', args}])[0];
};
