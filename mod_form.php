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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main ONLYOFFICE configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use mod_onlyofficeeditor\onlyoffice_file_utility;
use mod_onlyofficeeditor\util;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * New ONLYOFFICE module form.
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_onlyofficeeditor_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $config = get_config('onlyofficeeditor');

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('onlyofficename', 'onlyofficeeditor'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);
        $filemanageroptions = [];

        // Limit to types supported by ONLYOFFICE -- docx, xlsx, pptx, odt, csv, txt, etc. ($config->allowedformats).
        $filemanageroptions['accepted_types'] = '*';
        $filemanageroptions['maxbytes'] = -1;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['subdirs'] = 0;

        $mform->addElement('filemanager', 'file', null, null, $filemanageroptions);
        if (!$this->_instance) {
            $attr = ['class' => 'onlyofficeeditor-create-button'];
            $createbuttons = [];
            $createbuttons[] =& $mform->createElement('radio', 'onlyofficetemplateformat', '',
                get_string('docxformname', 'onlyofficeeditor'), 'Document', $attr);
            $createbuttons[] =& $mform->createElement('radio', 'onlyofficetemplateformat', '',
                get_string('xlsxformname', 'onlyofficeeditor'), 'Spreadsheet', $attr);
            $createbuttons[] =& $mform->createElement('radio', 'onlyofficetemplateformat', '',
                get_string('pptxformname', 'onlyofficeeditor'), 'Presentation', $attr);
            $createbuttons[] =& $mform->createElement('radio', 'onlyofficetemplateformat', '',
                get_string('pdfformname', 'onlyofficeeditor'), 'PDF form', $attr);
            $createbuttons[] =& $mform->createElement('radio', 'onlyofficetemplateformat', '',
                get_string('uploadformname', 'onlyofficeeditor'), 'Upload file', $attr);

            $mform->addGroup($createbuttons, 'create_buttons',
                get_string('selectfile', 'onlyofficeeditor'), [' '], false);
            $mform->addRule('create_buttons', get_string('required'), 'required',
                null, 'client');

            $mform->disabledIf('file', 'onlyofficetemplateformat', 'notchecked', 'Upload file');
            $mform->hideIf('file', 'onlyofficetemplateformat', 'notchecked', 'Upload file');
        }

        $mform->addElement('header', 'documentpermissions', get_string('documentpermissions', 'onlyofficeeditor'));
        $mform->addElement('checkbox', 'download', get_string('download', 'onlyofficeeditor'));
        $mform->setDefault('download', 1);
        $mform->addHelpButton('download', 'download', 'onlyofficeeditor');

        $mform->addElement('checkbox', 'print', get_string('print', 'onlyofficeeditor'));
        $mform->setDefault('print', 1);
        $mform->addHelpButton('print', 'print', 'onlyofficeeditor');

        $mform->addElement('checkbox', 'protect', get_string('protect', 'onlyofficeeditor'));
        $mform->setDefault('protect', 1);
        $mform->addHelpButton('protect', 'protect', 'onlyofficeeditor');

        // Add standard grading elements.
        // Add grading capability. need use case for grading.
        // $this->standard_grading_coursemodule_elements();
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Form verification.
     *
     * @param array $data form data.
     * @param array $files uploaded file.
     * @return mixed of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        $usercontext = \context_user::instance($USER->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['file'], 'sortorder, id', false);
        if (!$files) {
            $fileformat = $data['onlyofficetemplateformat'];
            if ($fileformat != null && $fileformat != 'Upload file') {
                util::create_from_onlyoffice_template($fileformat, $USER, $this->context->id,
                    $data['file'], $data['name']);
            } else {
                $errors['file'] = get_string('required');
            }
        } else {
            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));

                if (!onlyoffice_file_utility::is_format_supported($extension)) {
                    $errors['file'] = get_string('unsupportedfileformat', 'onlyofficeeditor');
                    break;
                }
            }
        }
        return $errors;
    }

    /**
     * Modify data returned by get_moduleinfo_data() or prepare_new_moduleinfo_data() before calling set_data()
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param array $defaultvalues passed by reference
     */
    public function data_preprocessing(&$defaultvalues) {
        $draftitemid = file_get_submitted_draft_itemid('file');
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_onlyofficeeditor', 'content', 0, ['subdirs' => false]);
        $defaultvalues['file'] = $draftitemid;
        if (!empty($defaultvalues['permissions'])) {
            $permissions = unserialize($defaultvalues['permissions']);
            if (isset($permissions['download'])) {
                $defaultvalues['download'] = $permissions['download'];
            } else {
                $defaultvalues['download'] = 0;
            }
            if (isset($permissions['print'])) {
                $defaultvalues['print'] = $permissions['print'];
            } else {
                $defaultvalues['print'] = 0;
            }
            if (isset($permissions['protect'])) {
                $defaultvalues['protect'] = $permissions['protect'];
            } else {
                $defaultvalues['protect'] = 0;
            }
        }
    }

}
