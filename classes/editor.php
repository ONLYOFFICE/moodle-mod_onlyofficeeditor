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
 * Construct editor config.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;


use mod_onlyofficeeditor\onlyoffice_file_utility;
use mod_onlyofficeeditor\document;
use mod_onlyofficeeditor\jwt_wrapper;
use mod_onlyofficeeditor\configuration_manager;

/**
 * Editor config class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editor {

    /**
     * @var int the course id.
     */
    private $courseid;

    /**
     * @var context_module context instance.
     */
    private $context;

    /**
     * @var cm_info information about that course-module.
     */
    private $cm;

    /**
     * @var mixed config of mod.
     */
    private $modconfig;

    /**
     * @var mixed document file.
     */
    private $file;

    /**
     * Editor constructor.
     * @param int $courseid the course id.
     * @param context_module $context context instance.
     * @param cm_info $cm information about that course-module.
     * @param mixed $modconfig config of mod.
     */
    public function __construct($courseid, $context, $cm, $modconfig) {
        $this->courseid = $courseid;
        $this->context = $context;
        $this->cm = $cm;
        $this->modconfig = $modconfig;

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_onlyofficeeditor', 'content', 0,
            'sortorder DESC, id ASC', false, 0, 0, 1);

        if (count($files) >= 1) {
            $this->file = reset($files);
        }
    }

    /**
     * @todo Warn if document is in format needing conversion.
     * @todo Send to ONLYOFFICE conversion service for conversion and overwrite current version before opening in editor
     */

    /**
     * Return editor config for document.
     * @return array|null editor config.
     */
    public function config() {
        /*
         * Note: It is important to preserv the case (camelCase) of the $config
         * array keys, as they are used in the config passed to JS
         *
         * Note: Error "too many parameters passed to js_init_call()" occurs in DEBUG_DEVELOPER. See MDL-57614, MDL-62468
         */

        global $CFG, $OUTPUT, $USER;

        if (!isset($this->file) || empty($this->file)) {
            return null;
        }

        $file = $this->file;

        // Top level config object.
        $config = [];
        $crypt = new \mod_onlyofficeeditor\hasher();
        $storageurl = configuration_manager::get_storage_url();

        // Document.
        $document = [];
        $filename = $file->get_filename();
        $path = '/' . $this->context->id . '/mod_onlyofficeeditor/content/'
                    . urlencode(substr($file->get_filepath(), 1) . $filename);
        $contenthash = $crypt->get_hash(['userid' => $USER->id, 'contenthash' => $file->get_contenthash()]);
        $documenturl = $storageurl . '/pluginfile.php' . $path . '?doc=' . $contenthash;

        $document['url'] = $documenturl;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $document['fileType'] = $ext;
        $document['title'] = $filename;
        $document['key'] = document::get_key($this->cm);
        $document['permissions'] = document::get_permissions($this->context, $this->cm, $filename);

        // Editorconfig.
        $editorconfig = [];
        $pathnamehash = $crypt->get_hash(['userid' => $USER->id, 'pathnamehash' => $file->get_pathnamehash(), 'cm' => $this->cm]);
        $editorconfig['actionLink'] = null;
        $editorconfig['callbackUrl'] = $storageurl . '/mod/onlyofficeeditor/callback.php?doc=' . $pathnamehash;
        if (property_exists($USER, 'lang')) {
            $lang = stristr($USER->lang, '_', true) !== false ? stristr($USER->lang, '_', true) : $USER->lang;
        } else {
            $lang = $CFG->lang;
        }
        $editorconfig['lang'] = $lang;

        // User.
        $user = [];
        $user['id'] = hash('md5', $USER->id);
        $user['name'] = \fullname($USER);
        $editorconfig['user'] = $user;

        // Customization.
        $customization = [];
        $customization['goback']['blank'] = false;
        $customization['goback']['text'] = get_string('returntodocument', 'onlyofficeeditor');
        $customization['goback']['url'] = $CFG->wwwroot . '/course/view.php?id=' . $this->courseid;
        $customization['forcesave'] = $this->modconfig->forcesave == 1;
        $customization['chat'] = $this->modconfig->editor_view_chat == 1 && !is_guest($this->context);
        $customization['help'] = $this->modconfig->editor_view_help == 1;
        $customization['compactHeader'] = $this->modconfig->editor_view_header == 1;
        $customization['feedback'] = $this->modconfig->editor_view_feedback == 1;
        $customization['toolbarNoTabs'] = $this->modconfig->editor_view_toolbar == 1;
        $customization['commentAuthorOnly'] = true;
        $customization['plugins'] = $this->modconfig->editor_security_plugin == 1;
        $customization['macros'] = $this->modconfig->editor_security_macros == 1;
        $customization['integrationMode'] = 'embed';
        $editorconfig['customization'] = $customization;

        // Device type.
        $devicetype = \core_useragent::get_device_type();
        if ($devicetype == 'tablet' || $devicetype == 'mobile') {
            $devicetype = 'mobile';
        } else {
            $devicetype = 'desktop';
        }

        // Package config object from parts.
        $config['type'] = $devicetype;
        $config['document'] = $document;
        $config['editorConfig'] = $editorconfig;
        $config['documentType'] = onlyoffice_file_utility::get_document_type('.' . $ext);

        // Add token.
        if (!empty($this->modconfig->documentserversecret)) {
            $token = jwt_wrapper::encode($config, $this->modconfig->documentserversecret);
            $config['token'] = $token;
        }
        return $config;
    }

}
