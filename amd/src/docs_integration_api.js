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
 * @module mod_onlyofficeeditor/docs_integration_api
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
/* eslint-disable no-console, no-undef */
define([], function() {
    const DOCS_API_PATH = 'web-apps/apps/api/documents/api.js';
    const SCRIPT_ID = 'onlyofficeeditor-docsapi';

    /**
     * Load the ONLYOFFICE Document Server API script.
     *
     * @param {String} url The URL to the document server
     * @return {Promise} A promise that resolves when the API is loaded
     */
    const loadDocsApi = function(url) {
        // eslint-disable-next-line consistent-return
        return new Promise(function(resolve, reject) {
            try {
                // If DocsAPI is already loaded, resolve immediately
                if (typeof DocsAPI !== "undefined" && DocsAPI !== null) {
                    return resolve(null);
                }

                if (!url || url.trim() === '') {
                    return reject('Document server URL is not provided');
                }

                const existingScript = document.getElementById(SCRIPT_ID);

                if (existingScript) {
                    // If the script element is found, wait for it to load.
                    let intervalHandler = setInterval(() => {
                        const loading = existingScript.getAttribute("loading");
                        if (loading) {
                            // If the download is not completed, continue to wait.
                            return;
                        } else {
                            // If the download is completed, stop the wait.
                            clearInterval(intervalHandler);

                            // If DocsAPI is defined, after loading return resolve.
                            if (DocsAPI) {
                                resolve(null);
                            }

                            // If DocsAPI is not defined, delete the existing script and create a new one.
                            const script = _createScriptTag(SCRIPT_ID, url, resolve, reject);
                            existingScript.remove();
                            document.body.appendChild(script);
                        }
                    }, 500);
                } else {
                    // If the script element is not found, create it.
                    const script = _createScriptTag(SCRIPT_ID, url, resolve, reject);
                    document.body.appendChild(script);
                }
            } catch (error) {
                console.error('Error loading Document Server API:', error);
                return reject(error);
            }
        });
    };

    const _createScriptTag = (id, url, resolve, reject) => {
        const script = document.createElement("script");

        script.id = id;
        script.type = "text/javascript";
        script.src = url.endsWith("/") ? url + DOCS_API_PATH : url + "/" + DOCS_API_PATH;
        script.async = true;

        script.onload = () => {
            // Remove attribute loading after loading is complete.
            script.removeAttribute("loading");

            resolve(null);
        };
        script.onerror = (error) => {
            // Remove attribute loading after loading is complete.
            script.removeAttribute("loading");
            reject(error);
        };

        script.setAttribute("loading", "");

        return script;
    };

    const removeDocsApi = function() {
        return new Promise(function(resolve, reject) {
            try {
                const script = document.getElementById(SCRIPT_ID);
                // Remove the script element from the document.
                if (script) {
                    script.remove();
                }

                // If DocsAPI is not loaded, resolve immediately.
                if (typeof DocsAPI === "undefined" || DocsAPI === null) {
                    return resolve(null);
                }

                // Set DocsAPI to null to indicate it has been removed.
                DocsAPI = null;
                return resolve(null);
            } catch (error) {
                console.error('Error removing Document Server API:', error);
                return reject(error);
            }
        });
    };

    return {
        loadDocsApi,
        removeDocsApi
    };
});
/* eslint-enable no-console, no-undef */