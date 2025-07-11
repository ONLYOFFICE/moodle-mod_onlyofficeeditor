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
 * @module     mod_onlyofficeeditor/check_docserver_button
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
/* eslint-disable no-console */
define(
    [
        'core/notification',
        'core/str',
        'mod_onlyofficeeditor/repository',
        'mod_onlyofficeeditor/url_validator',
        'mod_onlyofficeeditor/docs_integration_api',
    ],
    function(Notification, Str, Repository, UrlValidator, DocsIntegrationAPI) {
        let checkButton = null;
        const fields = {};
        const validationElements = [];

        const checkDocumentServerConnection = async(event) => {
            event.preventDefault();
            checkButton.disabled = true;
            checkButton.classList.add('loading');

            const data = {
                docsurl: fields.docsurl.value.trim(),
                secret: fields.secret.value.trim(),
                jwtheader: fields.jwtheader.value.trim(),
                internalurl: fields.internalurl.value.trim(),
                storageurl: fields.storageurl.value.trim(),
                disableverifyssl: fields.disableverifyssl.checked,
            };

            const errors = await validateFields(data);
            clearValidationOutput();

            if (errors.length > 0) {
                await displayErrors(errors);
            } else {
                Notification.addNotification({
                    message: await Str.get_string("connectionsuccess", "onlyofficeeditor"),
                    type: 'success',
                });
            }

            checkButton.disabled = false;
            checkButton.classList.remove('loading');
        };

        const highlightErrorField = (error) => {
            const fieldForm = document.getElementById("admin-" + error.field);
            const fieldSetting = fieldForm ? fieldForm.querySelector(".form-setting") : null;
            if (fieldSetting) {
                fieldSetting.querySelectorAll(".error").forEach((el) => {
                    el.remove();
                });
                const errorElement = document.createElement("div");
                errorElement.innerHTML = '<span class="error">' + error.message + "</span>";
                fieldSetting.prepend(errorElement);
                validationElements.push(errorElement);
            }
        };

        const clearValidationOutput = () => {
            for (const element of validationElements) {
                element.remove();
            }
            validationElements.length = 0;
            const notifications = document.getElementById("user-notifications");
            notifications.innerHTML = "";
        };

        const displayErrors = async(errors) => {
            for (const error of errors) {
                if (error.field === 'general') {
                    Notification.addNotification({
                    message: error.message,
                    type: 'error',
                });
                } else {
                    highlightErrorField(error);
                }
            }

            Notification.addNotification({
                message: await Str.get_string("connectionerror", "onlyofficeeditor"),
                type: 'error',
            });
        };

        const validateFields = async(data) => {
            const errors = await preValidateFields(data);

            if (errors.length > 0) {
                return errors;
            }

            await Repository.checkDocumentServerConnection(
                data.secret,
                data.jwtheader,
                data.internalurl.length > 0 ? data.internalurl : data.docsurl,
                data.storageurl,
                data.disableverifyssl,
            )
            .then(async(response) => {
                if (response.status === 'error') {
                    for (const error of response.errors) {
                        errors.push({
                            field: error.field,
                            message: error.message
                        });
                    }
                }
                return;
            }).catch(async(error) => {
                console.error('Error checking Document Server connection:', error);
                errors.push({
                    field: 'general',
                    message: await Str.get_string("connectionerror:unexpected", "onlyofficeeditor"),
                });
            });

            return errors;
        };

        const preValidateFields = async(data) => {
            const errors = [];

            try {
                await validateDocumentServerUrl(data.docsurl);
            } catch (e) {
                errors.push({
                    field: 'documentserverurl',
                    message: e.message
                });
            }

            if (data.internalurl && !UrlValidator.isValidUrl(data.internalurl)) {
                errors.push({
                    field: 'documentserverinternal',
                    message: await Str.get_string("validationerror:invalidurl", "onlyofficeeditor")
                });
            }

            if (data.storageurl && !UrlValidator.isValidUrl(data.storageurl)) {
                errors.push({
                    field: 'storageurl',
                    message: await Str.get_string("validationerror:invalidurl", "onlyofficeeditor")
                });
            }

            return errors;
        };

        const validateDocumentServerUrl = async(url) => {
            if (!url) {
                throw new Error(await Str.get_string("validationerror:emptyurl", "onlyofficeeditor"));
            }

            if (!UrlValidator.isValidUrl(url)) {
                throw new Error(await Str.get_string("validationerror:docsinvalidurl", "onlyofficeeditor"));
            }

            if (UrlValidator.isMixedContent(url)) {
                throw new Error(await Str.get_string("validationerror:mixedcontent", "onlyofficeeditor"));
            }

            await DocsIntegrationAPI.loadDocsApi(url)
                .catch((e) => {
                    console.log(e);
                });

            // eslint-disable-next-line no-undef
            if (typeof DocsAPI === "undefined" || DocsAPI === null) {
                throw new Error(await Str.get_string('validationerror:apijsunavailable', 'onlyofficeeditor'));
            } else {
                DocsIntegrationAPI.removeDocsApi();
            }
        };

        return {
            init: function() {
                fields.docsurl = document.getElementById('id_s_onlyofficeeditor_documentserverurl');
                fields.secret = document.getElementById('id_s_onlyofficeeditor_documentserversecret');
                fields.jwtheader = document.getElementById('id_s_onlyofficeeditor_jwtheader');
                fields.internalurl = document.getElementById('id_s_onlyofficeeditor_documentserverinternal');
                fields.storageurl = document.getElementById('id_s_onlyofficeeditor_storageurl');
                fields.disableverifyssl = document.getElementById('id_s_onlyofficeeditor_disable_verify_ssl');

                if (
                    !fields.docsurl || !fields.secret || !fields.jwtheader
                    || !fields.internalurl || !fields.storageurl || !fields.disableverifyssl) {
                    console.error('One or more required fields are missing in the form.');
                    return;
                }

                checkButton = document.querySelector("button[data-action='check-documentserver-connection']");
                if (checkButton) {
                    checkButton.addEventListener('click', checkDocumentServerConnection);
                }
            }
        };
    }
);
/* eslint-enable no-console */
