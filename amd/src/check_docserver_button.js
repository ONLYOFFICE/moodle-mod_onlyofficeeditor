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
    ['core/notification', 'core/str', 'mod_onlyofficeeditor/repository', 'mod_onlyofficeeditor/url_validator'],
    function(Notification, Str, Repository, UrlValidator) {
    const fields = {
        'docserverurl': 'documentserverurl',
        'secret': 'documentserversecret',
        'jwtheader': 'jwtheader',
        'internalurl': 'documentserverinternal',
        'storageurl': 'storageurl',
        'disableverifyssl': 'disable_verify_ssl'
    };

    const validationElements = [];

    const checkDocumentServerConnection = async(event) => {
        event.preventDefault();

        const docsurl = document.getElementById('id_s_onlyofficeeditor_documentserverurl').value.trim();
        const connectionerrormessage = await Str.get_string("connectionerror", "onlyofficeeditor");

        const errors = await validateDocumentServerUrl(docsurl);

        if (errors.length > 0) {
            clearValidationOutput();
            Notification.addNotification({
                message: connectionerrormessage,
                type: 'error'
            });
            for (const error of errors) {
                highlightErrorField(error);
            }
            return;
        }

        await Repository.checkDocumentServerConnection(
            docsurl,
            document.getElementById('id_s_onlyofficeeditor_documentserversecret').value,
            document.getElementById('id_s_onlyofficeeditor_jwtheader').value,
            document.getElementById('id_s_onlyofficeeditor_documentserverinternal').value,
            document.getElementById('id_s_onlyofficeeditor_storageurl').value,
            document.getElementById('id_s_onlyofficeeditor_disable_verify_ssl').checked
        )
        .then(async (response) => {
            clearValidationOutput();
            if (response.status === 'success') {
                Notification.addNotification({message: "Connection is stable.", type: 'success'});
            } else if (response.status === 'error') {
                Notification.addNotification({
                    message: connectionerrormessage,
                    type: 'error'
                });
                for (const error of response.errors) {
                    if (error.field === "general") {
                        Notification.addNotification({message: error.message, type: 'error'});
                    } else {
                        highlightErrorField(error);
                    }
                }
            }
            return;
        }).catch(async (error) => {
            console.error('Error checking Document Server connection:', error);
            Notification.addNotification({
                message: await Str.get_string("connectionerror:unexpected", "onlyofficeeditor"),
                type: 'error'
            });
        });
    };

    const highlightErrorField = (error) => {
        const fieldForm = document.getElementById("admin-" + fields[error.field]);
        const fieldSetting = fieldForm ? fieldForm.querySelector(".form-setting") : null;
        if (fieldSetting) {
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

    const validateDocumentServerUrl = async (url) => {
        let error = [];

        if (!url) {
            error.push({
                field: 'docserverurl',
                message: await Str.get_string("validationerror:emptyurl", "onlyofficeeditor")
            });
        } else if (!UrlValidator.isValidUrl(url)) {
            error.push({
                field: 'docserverurl',
                message: await Str.get_string("validationerror:invalidurl", "onlyofficeeditor")
            });
        } else if (UrlValidator.isMixedContent(url)) {
            error.push({
                field: 'docserverurl',
                message: await Str.get_string("validationerror:mixedcontent", "onlyofficeeditor")
            });
        }

        return error;
    };

    return {
        init: function() {
            const checkButton = document.querySelector("button[data-action='check-documentserver-connection']");
            if (checkButton) {
                checkButton.addEventListener('click', checkDocumentServerConnection);
            }
        }
    };
});
/* eslint-enable no-console */
