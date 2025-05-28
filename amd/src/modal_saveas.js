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
 * Contain the logic for the add save as modal.
 *
 * @module    mod_onlyofficeeditor/modal_saveas
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry', 'core/templates'],
    function($, Notification, CustomEvents, Modal, ModalRegistry, Templates) {

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

        var SELECTORS = {
            LOADING_ICON_CONTAINER: '[data-region="overlay-icon-container"]',
            SECTION: '.onlyofficeeditor-saveas-section',
            BUTTON: '#onlyofficeeditor-saveas-button',
            CONTAINER: '.container'
        };

        /**
         * Show the loading spinner.
         *
         * @param  {jquery} body The body element.
         */
        var showLoadingIcon = function(body) {
            body.find(SELECTORS.LOADING_ICON_CONTAINER).removeClass('hidden');
        };

        /**
         * Hide the loading spinner.
         *
         * @param  {jquery} body The body element.
         */
        var hideLoadingIcon = function(body) {
            body.find(SELECTORS.LOADING_ICON_CONTAINER).addClass('hidden');
        };

        /**
         * Constructor for the Modal.
         *
         * @param {object} root The root jQuery element for the modal
         */
        var ModalSaveas = function(root) {
            Modal.call(this, root);
            this.selectedSection = null;
            this.saveAsData = null;
            this.CMID = null;
            this.courseid = null;
        };

        ModalSaveas.TYPE = 'mod_onlyofficeeditor-modal_saveas';
        ModalSaveas.prototype = Object.create(Modal.prototype);
        ModalSaveas.prototype.constructor = ModalSaveas;

        /**
         * Set up all of the event handling for the modal.
         *
         * @method registerEventListeners
         */
        ModalSaveas.prototype.registerEventListeners = function() {
            Modal.prototype.registerEventListeners.call(this);

            var modal = this;
            this.getModal().on('click', SELECTORS.SECTION, function(e) {
                var selected = modal.selectedSection;
                if (selected !== null) {
                    var previousSection = modal.getBody().find('#saveas-section-' + selected)[0];
                    previousSection.classList.remove('onlyofficeeditor-saveas-section-selected');
                }
                var button = modal.getFooter().find(SELECTORS.BUTTON)[0];
                button.removeAttribute('disabled');
                e.target.classList.add('onlyofficeeditor-saveas-section-selected');
                modal.selectedSection = parseInt(e.target.id.split('-')[2]);
            }).bind(this);

            this.getModal().on('click', SELECTORS.BUTTON, function() {
                modal.saveAsData.section = modal.selectedSection;
                $.ajax(M.cfg.wwwroot +
                    `/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=saveas&cmid=${modal.CMID}`, {
                    type: 'POST',
                    dataType: 'json',
                    data: modal.saveAsData,
                }).done(() => {
                    displayNotification('saveassuccess', 'success');
                }).fail(() => {
                    displayNotification('saveaserror', 'error');
                }).always(() => {
                    modal.hide();
                });
            }).bind(this);
        };

        ModalSaveas.prototype.renderSections = function(body, cmid, courseid) {
            var modal = this;
            showLoadingIcon(body);
            $.ajax(M.cfg.wwwroot + `/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=sections&cmid=${cmid}`, {
                type: 'GET',
                dataType: 'json',
                data: {courseid: courseid}
            }).done((response) => {
                var sections = response.sections;
                // eslint-disable-next-line promise/catch-or-return
                Templates.render('mod_onlyofficeeditor/modal_saveas_sections_list', {sections: sections})
                    .then((html, js) => {
                        var container = body.find(SELECTORS.CONTAINER)[0];
                        Templates.replaceNode(container, html, js);
                        return;
                    })
                    .always(() => {
                        hideLoadingIcon(body);
                    });
            }).fail(() => {
                modal.hide();
                displayNotification('saveaserror', 'error');
            });
        };

        /**
         * Override the modal show function to load the form when this modal is first shown.
         *
         * @method show
         */
        ModalSaveas.prototype.show = function() {
            Modal.prototype.show.call(this);

            if (this.selectedSection !== null) {
                this.selectedSection = null;
            }
            var button = this.getFooter().find(SELECTORS.BUTTON)[0];
            button.setAttribute('disabled', '');
        };

        if (!ModalRegistry.get(ModalSaveas.TYPE)) {
            ModalRegistry.register(ModalSaveas.TYPE, ModalSaveas, 'mod_onlyofficeeditor/modal_saveas');
        }

        return ModalSaveas;
    });
