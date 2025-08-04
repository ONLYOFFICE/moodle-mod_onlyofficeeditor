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
define(['jquery', 'core/modal', 'core/templates', 'core/str'],
    function($, Modal, Templates, Str) {

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

        const SaveAsDialog = Object;

        SaveAsDialog.modal = null;

        SaveAsDialog.build = async(cmid, courseid, saveAsData) => {
            var self = this;

            var stringkeys = [
                {
                    key: 'saveastitle',
                    component: 'mod_onlyofficeeditor'
                }
            ];
            // eslint-disable-next-line promise/catch-or-return
            Str.get_strings(stringkeys).then(async([title]) => {
                this.modal = await Modal.create({
                    title: title,
                    show: true
                });

                $.ajax(M.cfg.wwwroot + `/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=sections&cmid=${cmid}`, {
                    type: 'GET',
                    dataType: 'json',
                    data: {courseid: courseid}
                }).done((response) => {
                    var sections = response.sections;

                    var body = Templates.render('mod_onlyofficeeditor/modal_saveas_sections_list', {sections: sections});
                    var footer = Templates.render('mod_onlyofficeeditor/modal_saveas_footer');

                    self.modal.setBody(body);
                    self.modal.setFooter(footer);

                    self.modal.getRoot().on('click', SELECTORS.SECTION, (e) => {
                        var selected = self.modal.selectedSection;
                        if (selected !== undefined) {
                            var previousSection = self.modal.getBody().find('#saveas-section-' + selected)[0];
                            previousSection.classList.remove('onlyofficeeditor-saveas-section-selected');
                        }
                        var button = self.modal.getFooter().find(SELECTORS.BUTTON)[0];
                        button.removeAttribute('disabled');
                        e.target.classList.add('onlyofficeeditor-saveas-section-selected');
                        self.modal.selectedSection = parseInt(e.target.id.split('-')[2]);
                    });

                    self.modal.getRoot().on('click', SELECTORS.BUTTON, () => {
                        saveAsData.section = self.modal.selectedSection;
                        $.ajax(M.cfg.wwwroot +
                            `/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=saveas&cmid=${cmid}`, {
                            type: 'POST',
                            dataType: 'json',
                            data: saveAsData,
                        }).done(() => {
                            displayNotification('saveassuccess', 'success');
                        }).fail(() => {
                            displayNotification('saveaserror', 'error');
                        }).always(() => {
                            self.modal.hide();
                        });
                    });

                }).fail(() => {
                    displayNotification('saveaserror', 'error');
                });
                return;
            });
        };

        var SELECTORS = {
            SECTION: '.onlyofficeeditor-saveas-section',
            BUTTON: '#onlyofficeeditor-saveas-button'
        };

        return SaveAsDialog;
    });
