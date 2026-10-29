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
 * @module mod_onlyofficeeditor/editor
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define([
    'jquery',
    'core/notification',
    'core/str',
    'core/toast',
    'mod_onlyofficeeditor/fullscreen'
], function($, Notification, Str, Toast, Fullscreen) {
    var displayNotification = function(key, type) {
        Str.get_string(key, 'onlyofficeeditor').then(function(localizedStr) {
            Notification.addNotification({
                message: localizedStr,
                type: type
            });
            return;
        }).catch(Notification.exception);
    };

    var displayToast = function(key, type) {
        Toast.add(Str.getString(key, 'onlyofficeeditor'), {type: type});
    };

    $.urlParam = function(name) {
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results === null) {
            return null;
        }
        return decodeURI(results[1]) || 0;
    };

    var replaceActionLink = function(href, linkParam) {
        var link;
        var actionIndex = href.indexOf("&actionType=");
        if (actionIndex != -1) {
            link = href.substring(0, actionIndex) + "&actionType=" + encodeURIComponent(linkParam.type) +
                "&actionData=" + encodeURIComponent(linkParam.data);
        } else {
            link = href + "&actionType=" + encodeURIComponent(linkParam.type) + "&actionData=" + encodeURIComponent(linkParam.data);
        }
        return link;
    };

    var displaySaveAsModal = function(saveAsData, cmid, courseid) {
        require(['mod_onlyofficeeditor/modal_saveas'],
            function(ModalSaveas) {
                ModalSaveas.build(cmid, courseid, saveAsData);
            });
    };

    return {
        init: function(courseid, cmid) {
            if (typeof DocsAPI === "undefined") {
                displayNotification('docserverunreachable', 'error');
                return;
            }
            var ajaxUrl = M.cfg.wwwroot + '/mod/onlyofficeeditor/dsconfig.php';
            $.getJSON(ajaxUrl, {
                courseid: courseid,
                cmid: cmid,
                actionType: $.urlParam('actionType'),
                actionData: $.urlParam('actionData')
            }).done(function(data) {
                var docEditor = null;
                var config = data.config;

                document.head.innerHTML += '<link type="image/x-icon" rel="icon" href="/mod/onlyofficeeditor/pix/'
                    + config.documentType + '.ico" />';

                var canAddInstance = data.addinstance;

                const innerAlert = (message, inEditor) => {
                    // eslint-disable-next-line no-console
                    if (console && console.log) {
                        // eslint-disable-next-line no-console
                        console.log(message);
                    }
                    if (inEditor && docEditor) {
                        docEditor.showMessage(message);
                    }
                };

                var onMakeActionLink = function(event) {
                    var actionData = event.data.action;
                    docEditor.setActionLink(replaceActionLink(location.href, actionData));
                };

                var onRequestSendNotify = function(event) {
                    var comment = event.data.message;
                    var emails = event.data.emails;
                    var replacedActionLink = replaceActionLink(location.href, event.data.actionLink.action);

                    var mentionData = {
                        comment: comment,
                        emails: emails,
                        link: replacedActionLink,
                        courseid: courseid
                    };

                    $.ajax(M.cfg.wwwroot + '/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=mention&cmid=' + cmid, {
                        type: 'POST',
                        dataType: 'json',
                        data: mentionData
                    }).done(() => {
                        displayToast('onmentionsuccess', 'success');
                    }).fail(() => {
                        displayToast('onmentionerror', 'danger');
                    });
                };

                const onAppReady = () => {
                    innerAlert("Document editor ready");
                };

                const onError = (event) => {
                    if (event) {
                        innerAlert(event.data);
                    }
                };
                config.events = {
                    'onAppReady': onAppReady,
                    'onError': onError,
                    'onMakeActionLink': onMakeActionLink
                };

                var onRequestSaveAs = function(event) {
                    var saveAsData = {
                        title: event.data.title,
                        url: event.data.url,
                        courseid: courseid,
                        section: null
                    };
                    displaySaveAsModal(saveAsData, cmid, courseid);
                };

                if (canAddInstance) {
                    config.events.onRequestSaveAs = onRequestSaveAs;
                }

                var usersToMention = data.userstomention;

                var onRequestUsers = function() {
                    docEditor.setUsers({'users': usersToMention});
                };

                if (usersToMention !== null) {
                    config.events.onRequestUsers = onRequestUsers;
                    config.events.onRequestSendNotify = onRequestSendNotify;
                }

                if ((config.document.fileType === "pdf")
                    // eslint-disable-next-line no-undef
                    && DocsAPI.DocEditor.version().split(".")[0] < 8) {
                    displayNotification('oldversion', 'error');
                } else {
                    // eslint-disable-next-line no-undef
                    docEditor = new DocsAPI.DocEditor("onlyofficeeditor-editor", config);
                    Fullscreen.init(document.querySelector('.onlyofficeeditor-container'), config.documentType);
                }
            });
        }
    };
});
