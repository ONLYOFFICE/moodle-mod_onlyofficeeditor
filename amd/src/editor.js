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
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define(['jquery'], function($) {
    var displayNotification = function(error, type) {
        require(['core/notification'], function(notification) {
            require(['core/str'], function(str) {
                var errorIsAvailable = str.get_string(error, 'onlyofficeeditor');
                $.when(errorIsAvailable).done(function(localizedStr) {
                    notification.addNotification({
                        message: localizedStr,
                        type: type
                    });
                });
            });
        });
    };

    var createFullScreenButtons = function() {
        require(['core/str'], function(str) {
            var enterFullScreenText = str.get_string('editorenterfullscreen', 'onlyofficeeditor');
            var exitFullScreenText = str.get_string('editorexitfullscreen', 'onlyofficeeditor');
            var navLeftButton = $('.drawertoggle')[1];
            var navRightButton = $('.drawertoggle')[2];
            var editorContainer = $('.onlyofficeeditor-container')[0];

            $.when(enterFullScreenText).done(function(localized) {
                enterFullScreenText = localized;
                var enterButton = document.createElement('button');
                var enterIcon = document.createElement('i');
                enterIcon.className = 'icon fa fa-expand fa-fw';
                enterButton.appendChild(enterIcon);
                enterButton.className = 'onlyofficeeditor-editor-fs-button';
                enterButton.id = 'onlyofficeeditor-enter-fs-button';
                enterButton.innerHTML += enterFullScreenText;

                enterButton.onclick = function() {
                    $('header').hide();
                    $('footer').hide();
                    if (navLeftButton && navLeftButton.getAttribute('data-aria-hidden-tab-index') === null) {
                        $(navLeftButton).click();
                    }
                    if (navRightButton && navRightButton.getAttribute('data-aria-hidden-tab-index') === null) {
                        $(navRightButton).click();
                    }
                    if ($('.editmode-switch-form').length > 0 && $('.editmode-switch-form')[0][0].checked) {
                        $(editorContainer).addClass('onlyofficeeditor-rightindent');
                    }
                    $(editorContainer).addClass('onlyofficeeditor-fullscreen');
                    editorContainer.children[0].style.height = '93vh';
                    $('#onlyofficeeditor-enter-fs-button').hide();
                    $('#onlyofficeeditor-exit-fs-button').show();
                };
                editorContainer.before(enterButton);
            });
            $.when(exitFullScreenText).done(function(localized) {
                exitFullScreenText = localized;
                var exitButton = document.createElement('button');
                var exitIcon = document.createElement('i');
                exitIcon.className = 'icon fa fa-compress fa-fw';
                exitButton.appendChild(exitIcon);
                exitButton.innerHTML += exitFullScreenText;
                exitButton.className = 'onlyofficeeditor-editor-fs-button';
                exitButton.id = 'onlyofficeeditor-exit-fs-button';

                exitButton.onclick = function() {
                    $(editorContainer).removeClass('onlyofficeeditor-fullscreen');
                    $(editorContainer).removeClass('onlyofficeeditor-rightindent');
                    editorContainer.children[0].style.height = '95vh';
                    $('header').show();
                    $('footer').show();
                    $('#onlyofficeeditor-enter-fs-button').show();
                    $('#onlyofficeeditor-exit-fs-button').hide();
                };
                $('#usernavigation')[0].prepend(exitButton);
                $('#onlyofficeeditor-exit-fs-button').hide();
            });
        });
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
            createFullScreenButtons();
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
                    }).fail(() => {
                        displayNotification('onmentionerror', 'error');
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
                }
            });
        }
    };
});
