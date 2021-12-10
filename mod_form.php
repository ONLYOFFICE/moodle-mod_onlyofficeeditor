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
 * @package    mod_onlyoffice
 * @copyright  2021 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use mod_onlyoffice\util;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_onlyoffice_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $config = get_config('onlyoffice');

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('onlyofficename', 'onlyoffice'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        }
        else {
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
        $filemanager_options = array();
        /**
         * @todo Limit to types supported by ONLYOFFICE -- docx, xlsx, pptx, odt, csv, txt, etc.
         */
        $acceptedtypes = array_merge(
            \mod_onlyoffice\onlyoffice_file_utility::get_editable_extensions(),
            \mod_onlyoffice\onlyoffice_file_utility::get_convertible_extensions(),
            \mod_onlyoffice\onlyoffice_file_utility::get_viewable_extensions());
        $filemanager_options['accepted_types'] = $acceptedtypes;
        $filemanager_options['maxbytes'] = -1;
        $filemanager_options['maxfiles'] = 1;

        $mform->addElement('filemanager', 'file', get_string('selectfile', 'onlyoffice'), null, $filemanager_options);
        $mform->addRule('file', get_string('required'), 'required', null, 'client');

//-----------------------------------------------------------------------
        $mform->addElement('header', 'documentpermissions', get_string('documentpermissions', 'onlyoffice'));
        $mform->addElement('checkbox', 'download', get_string('download', 'onlyoffice'));
        $mform->setDefault('download', 1);
        $mform->addHelpButton('download', 'download', 'onlyoffice');

        $mform->addElement('checkbox', 'print', get_string('print', 'onlyoffice'));
        $mform->setDefault('print', 1);
        $mform->addHelpButton('print', 'print', 'onlyoffice');

        /**
         * @todo add grading capability. need use case for grading
         */
        // Add standard grading elements.
        // $this->standard_grading_coursemodule_elements();
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        $usercontext = \context_user::instance($USER->id);
        $fs = get_file_storage();
        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['file'], 'sortorder, id', false)) {
            $errors['file'] = get_string('required');
        }
        return $errors;
    }

    /**
     * Modify data returned by get_moduleinfo_data() or prepare_new_moduleinfo_data() before calling set_data()
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param array $default_values passed by reference
     */
    function data_preprocessing(&$default_values) {
        $draftitemid = file_get_submitted_draft_itemid('file');
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_onlyoffice', 'content', 0, array('subdirs' => false));
        $default_values['file'] = $draftitemid;
        if (!empty($default_values['permissions'])) {
            $permissions = unserialize($default_values['permissions']);
            if (isset($permissions['download'])) {
                $default_values['download'] = $permissions['download'];
            }
            else {
                $default_values['download'] = 0;
            }
            if (isset($permissions['print'])) {
                $default_values['print'] = $permissions['print'];
            }
            else {
                $default_values['print'] = 0;
            }
        }
    }

}
