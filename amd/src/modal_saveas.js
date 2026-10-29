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
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
define(['jquery', 'core/modal', 'core/templates', 'core/str', 'core/toast'],
    function($, Modal, Templates, Str, Toast) {

        const SaveAsDialog = {};

        var showMessage = function(key, type) {
            Toast.add(Str.getString(key, 'onlyofficeeditor'), {type: type});
        };

        SaveAsDialog.build = async(cmid, courseid, saveAsData) => {
            var stringkeys = [
                {
                    key: 'saveastitle',
                    component: 'mod_onlyofficeeditor'
                }
            ];
            // eslint-disable-next-line promise/catch-or-return
            Str.get_strings(stringkeys).then(async([title]) => {
                const modal = await Modal.create({
                    title: title,
                    show: true,
                    removeOnClose: true
                });
                var selectedSection;

                $.ajax(M.cfg.wwwroot + `/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=sections&cmid=${cmid}`, {
                    type: 'GET',
                    dataType: 'json',
                    data: {courseid: courseid}
                }).done((response) => {
                    var sections = response.sections;

                    var body = Templates.render('mod_onlyofficeeditor/modal_saveas_sections_list', {sections: sections});
                    var footer = Templates.render('mod_onlyofficeeditor/modal_saveas_footer');

                    modal.setBody(body);
                    modal.setFooter(footer);

                    modal.getRoot().on('click', SELECTORS.SECTION, (e) => {
                        if (selectedSection !== undefined) {
                            var previousSection = modal.getBody().find('#saveas-section-' + selectedSection)[0];
                            previousSection.classList.remove('onlyofficeeditor-saveas-section-selected');
                        }
                        var button = modal.getFooter().find(SELECTORS.BUTTON)[0];
                        button.removeAttribute('disabled');
                        e.target.classList.add('onlyofficeeditor-saveas-section-selected');
                        selectedSection = parseInt(e.target.id.split('-')[2]);
                    });

                    modal.getRoot().on('click', SELECTORS.BUTTON, (e) => {
                        e.currentTarget.setAttribute('disabled', '');
                        saveAsData.section = selectedSection;
                        $.ajax(M.cfg.wwwroot +
                            `/mod/onlyofficeeditor/onlyofficeeditorapi.php?apiType=saveas&cmid=${cmid}`, {
                            type: 'POST',
                            dataType: 'json',
                            data: saveAsData,
                        }).done(() => {
                            showMessage('saveassuccess', 'success');
                        }).fail(() => {
                            showMessage('saveaserror', 'danger');
                        }).always(() => {
                            modal.destroy();
                        });
                    });

                }).fail(() => {
                    modal.destroy();
                    showMessage('saveaserror', 'danger');
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
