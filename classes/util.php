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
 * Utils for editor.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;
use context_user;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->dirroot/course/modlib.php");

/**
 * Utils class.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {

    /** No doc with the specified key can be found. */
    const STATUS_NOTFOUND = 0;

    /** User has entered/exited editor. */
    const STATUS_EDITING = 1;

    /** Document updated, changing content. */
    const STATUS_MUSTSAVE = 2;

    /** Saving the document has failed. */
    const STATUS_ERRORSAVING = 3;

    /** No document updates. */
    const STATUS_CLOSEDNOCHANGES = 4;

    /** Document updated, force saving content. */
    const STATUS_FORCESAVE = 6;

    /** Force saving the document has failed. */
    const STATUS_ERRORFORCESAVE = 7;

    /** Path locales to create file from ONLYOFFICE template. */
    const PATH_LOCALE = [
        "en_us" => "en",
        "en" => "en-GB",
        "pt_br" => "pt-BR",
        "sr_lt" => "sr",
        "zh_cn" => "zh",
    ];

    /** Desktop user agent string */
    const DESKTOP_USER_AGENT = 'AscDesktopEditor';

    /**
     * File name maximum length
     */
    const FILENAME_MAXIMUM_LENGTH = 255;

    /**
     * Get plugin key.
     *
     * @return string plugin key from the plugin configuration.
     * @throws \dml_exception
     */
    public static function get_appkey() {
        $key = get_config('onlyofficeeditor', 'appkey');
        if (empty($key)) {
            $key = number_format(round(microtime(true) * 1000), 0, ".", "");
            set_config('appkey', $key, 'onlyofficeeditor');
        }
        return $key;
    }

    /**
     * Add permissions for document.
     *
     * @param \stdClass $data form data for new onlyoffice module.
     */
    public static function save_document_permissions($data) {
        $permissions = [];
        if (!empty($data->download)) {
            $permissions['download'] = 1;
        }
        if (!empty($data->print)) {
            $permissions['print'] = 1;
        }
        if (!empty($data->protect)) {
            $permissions['protect'] = 1;
        }
        $data->permissions = serialize($permissions);
    }

    /**
     * Save file.
     *
     * @param \stdClass $data form data for new onlyoffice module.
     */
    public static function save_file($data) {
        global $USER;

        $cmid = $data->coursemodule;
        $draftitemid = $data->file;

        $context = \context_module::instance($cmid);
        if ($draftitemid) {
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');

            foreach ($draftfiles as $file) {
                if (!$file->is_directory()) {
                    $extension = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
                    $newfilename = self::generate_filename($data->name, $extension);
                    if ($newfilename !== $file->get_filename()) {
                        $file->rename($file->get_filepath(), $newfilename);
                    }
                }
            }

            $options = ['subdirs' => false];
            file_save_draft_area_files($draftitemid, $context->id, 'mod_onlyofficeeditor', 'content', 0, $options);
        }
    }

    /**
     * Get connections info.
     *
     * @param string $url url.
     * @return mixed connection info.
     */
    public static function get_connection_info($url) {
        $ch = new \curl();

        $disableverifyssl = get_config('onlyofficeeditor', 'disable_verify_ssl') == 1;

        if ($disableverifyssl) {
            $ch->setopt(['CURLOPT_SSL_VERIFYPEER' => 0]);
            $ch->setopt(['CURLOPT_SSL_VERIFYHOST' => 0]);
        }

        $ch->get($url);
        $info = $ch->get_info();
        return $info;
    }

    /**
     * Save new or changed file.
     *
     * @param array $data callback json.
     * @param object $hash encoded object.
     * @param bool $isforcesave forcesave is enabled or not.
     * @return bool saved or error.
     *
     * @throws \Exception
     */
    public static function save_document_to_moodle($data, $hash, $isforcesave) {
        $downloadurl = \mod_onlyofficeeditor\configuration_manager::replace_document_server_url_to_internal($data['url']);
        $fs = get_file_storage();
        if ($file = $fs->get_file_by_hash($hash->pathnamehash)) {
            $fr = [
                'contextid' => $file->get_contextid(),
                'component' => $file->get_component(),
                'filearea' => 'draft',
                'itemid' => $file->get_itemid(),
                'filename' => $file->get_filename() . '_temp',
                'filepath' => '/',
                'userid' => $file->get_userid(),
                'timecreated' => $file->get_timecreated()];
            try {
                $disableverifyssl = get_config('onlyofficeeditor', 'disable_verify_ssl');
                $options['skipcertverify'] = $disableverifyssl == 1;
                $newfile = $fs->create_file_from_url($fr, $downloadurl, $options);
                $file->replace_file_with($newfile);
                $file->set_timemodified(time());
                $newfile->delete();
                if (!$isforcesave) {
                    \mod_onlyofficeeditor\document::set_key($hash->cm);
                }
                return true;
            } catch (\moodle_exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Create new empty file for ONLYOFFICE activity.
     *
     * @param string $fileformat new file format.
     * @param object $user user.
     * @param int $contextid context id.
     * @param int $fileid new file id.
     * @param string $name name of the new file.
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public static function create_from_onlyoffice_template($fileformat, $user, $contextid, $fileid, $name) {
        switch ($fileformat) {
            case 'Document': {
                $fileformat = 'docx';
                break;
            }
            case 'Spreadsheet': {
                $fileformat = 'xlsx';
                break;
            }
            case 'Presentation': {
                $fileformat = 'pptx';
                break;
            }
            case 'PDF form': {
                $fileformat = 'pdf';
                break;
            }
        }

        $pathname = self::get_template_path($fileformat, $user);

        $fileinfo = [
            'author' => fullname($user),
            'contextid' => $contextid,
            'component' => 'mod_onlyofficeeditor',
            'filearea' => 'content',
            'userid' => $user->id,
            'itemid' => $fileid,
            'filepath' => '/',
            'filename' => $name . '.' . $fileformat];

        $fs = get_file_storage();
        $file = $fs->create_file_from_pathname($fileinfo, $pathname);
    }

    /**
     * Generate new module for converted file;
     *
     * @param string $ext Template extension.
     * @param object $user user
     * @return string
     */
    public static function get_template_path($ext, $user = null) {
        global $USER;
        global $CFG;

        if ($user === null) {
            $user = $USER;
        }

        $langcode = $user->lang;

        $pathlocale = self::PATH_LOCALE[$langcode];
        if (isset($pathlocale)) {
            $langcode = $pathlocale;
        }

        if (!file_exists($CFG->dirroot . '/mod/onlyofficeeditor/newdocs/' . $langcode . '/new.' . $ext)) {
            $langcode = "default";
        }

        return $CFG->dirroot . '/mod/onlyofficeeditor/newdocs/' . $langcode . '/new.' . $ext;
    }

    /**
     * Generate new module for converted file;
     * @param object $moduleinfo Onlyoffice module info.
     * @param object $course Course.
     * @param object $cm Course module.
     * @param null|int $section Section.
     * @return object|\stdClass
     * @throws \moodle_exception
     */
    public static function generate_new_module_info($moduleinfo, $course, $cm, $section) {
        $newmoduleinfo = $moduleinfo;
        $newtime = time();
        $permissions = unserialize($moduleinfo->permissions);

        $newmoduleinfo->download = $permissions['download'];
        $newmoduleinfo->print = $permissions['print'];
        $newmoduleinfo->instance = 0;
        $newmoduleinfo->coursemodule = 0;
        $newmoduleinfo->section = $section;
        $newmoduleinfo->course = $course->id;
        $newmoduleinfo->add = 'onlyofficeeditor';
        $newmoduleinfo->cmidnumber = '';
        $newmoduleinfo->completionunlocked = 1;
        $newmoduleinfo->completion = $cm->completion;
        $newmoduleinfo->completionexpected = $cm->completionexpected;
        $newmoduleinfo->showdescription = $cm->showdescription;
        $newmoduleinfo->visible = $cm->visible;
        $newmoduleinfo->visibleoncoursepage = $cm->visibleoncoursepage;
        $newmoduleinfo->tags = [];
        $newmoduleinfo->update = 0;
        $newmoduleinfo->return = 0;
        $newmoduleinfo->sr = 0;
        $newmoduleinfo->competencies = [];
        $newmoduleinfo->competency_rule = 0;
        $newmoduleinfo->timecreated = $newtime;
        $newmoduleinfo->timemodified = $newtime;
        $newmoduleinfo->documentkey = null;
        $newmoduleinfo->availabilityconditionsjson = '{"op":"&","c":[],"showc":[]}';

        $newmoduleinfo = add_moduleinfo($newmoduleinfo, $course);
        return $newmoduleinfo;
    }

    /**
     * Create new activity module on save as... action from editor.
     * @param string $url Url of document.
     * @param string $title Title of document.
     * @param \context_module $context Context.
     * @param int $cmid Course module id.
     * @param int $courseid Course id.
     * @param int $section Section.
     * @throws \Exception
     */
    public static function save_as_document($url, $title, $context, $cmid, $courseid, $section) {
        $documentserverurl = get_config('onlyofficeeditor', 'documentserverurl');
        $connectioninfo = self::get_connection_info($documentserverurl);
        $httpcode = $connectioninfo['http_code'] ?? null;
        if (
            !isset($documentserverurl) ||
            empty($documentserverurl) ||
            $httpcode != 200
        ) {
            throw new \Exception(get_string('docserverunreachable', 'onlyofficeeditor'));
        }

        if (parse_url($url, PHP_URL_HOST) !== parse_url($documentserverurl, PHP_URL_HOST)) {
            throw new \Exception('The domain in the file url does not match the domain of the Document server');
        }

        $url = \mod_onlyofficeeditor\configuration_manager::replace_document_server_url_to_internal($url);

        global $DB;
        $fs = get_file_storage();
        $permission = has_capability('mod/onlyofficeeditor:addinstance', $context);

        $files = $fs->get_area_files($context->id, 'mod_onlyofficeeditor', 'content', 0, 'sortorder DESC, id ASC', false, 0, 0, 1);
        $file = null;
        if (count($files) >= 1) {
            $file = reset($files);
        }
        if (!$permission || $file === null) {
            throw new \Exception();
        }

        try {
            $cm = get_fast_modinfo($courseid)->get_cm($cmid)->get_course_module_record();
            $moduleinfo = (object)$DB->get_record('onlyofficeeditor', ['id' => $cm->instance]);
            $course = get_course($courseid);
            $modulename = (object) ['modulename' => 'onlyofficeeditor'];
            list($module, $cntxt, $cw) = can_add_moduleinfo($course, $modulename->modulename, $section);

            $moduleinfo->module = $module->id;
            $moduleinfo->modulename = $modulename->modulename;
            $moduleinfo = self::generate_new_module_info($moduleinfo, $course, $cm, $section);

            $fileinfo = [
                'author' => $file->get_author(),
                'contextid' => \context_module::instance($moduleinfo->coursemodule)->id,
                'component' => 'mod_onlyofficeeditor',
                'filearea' => 'content',
                'userid' => $file->get_userid(),
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $title,
            ];

            $disableverifyssl = get_config('onlyofficeeditor', 'disable_verify_ssl');
            $options['skipcertverify'] = $disableverifyssl == 1;
            $fs->create_file_from_url($fileinfo, $url, $options);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns all users who can be mentioned in the comments.
     *
     * @param \context $context Module context.
     * @return array Users array for mentioning.
     * @throws \coding_exception
     */
    public static function get_users_to_mention_in_comments($context) {
        global $USER;
        $users = get_users_by_capability($context, 'mod/onlyofficeeditor:view');
        $userstomention = [];
        foreach ($users as $user) {
            if ($user->id !== $USER->id) {
                array_push($userstomention, [
                    'email' => $user->email,
                    'name' => $user->firstname . ' ' . $user->lastname,
                ]);
            }
        }
        return $userstomention;
    }

    /**
     * Send notification to users about mentioning in the comment.
     *
     * @param string $actionlink Link to the comment.
     * @param string $comment Comment text.
     * @param array $emails Emails of mentioned users.
     * @param \context $context Module context.
     * @return array Array of mentioned users.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function mention_user_in_comment($actionlink, $comment, $emails, $context) {
        global $DB, $USER;
        $mentionedusers = [];

        $messagedata = new \stdClass();
        $messagedata->notifier = $USER->firstname . ' ' . $USER->lastname;
        $messagedata->course = $context->get_course_context()->get_context_name(false);

        foreach ($emails as $email) {
            $user = $DB->get_record('user', ['email' => $email]);
            $permission = has_capability('mod/onlyofficeeditor:editdocument', $context, $user) ? 'Full Access' : 'Read only';
            $mentioneduser = ['permissions' => $permission, 'user' => $user->firstname . ' ' . $user->lastname];
            $mentionedusers[] =& $mentioneduser;

            $message = new \core\message\message();
            $message->component = 'mod_onlyofficeeditor';
            $message->name = 'mentionnotifier';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->subject = get_string('mentionnotifier:notification', 'onlyofficeeditor', $messagedata);
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = '<p>' . $comment . '</p>';
            $message->notification = 1;
            $message->contexturl = $actionlink;
            $message->contexturlname = get_string('mentioncontexturlname', 'onlyofficeeditor');

            $messageid = message_send($message);
        }
        return $mentionedusers;
    }

    /**
     * Detect desktop user agent.
     *
     * @return bool - user agent.
     */
    public static function desktop_detect() {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/' . self::DESKTOP_USER_AGENT . '/', $useragent)) {
            return true;
        }

        return false;
    }

    /**
     * Generate valid file name
     *
     * @param string $name
     * @param string $ext
     * @return string
     */
    public static function generate_filename($name, $ext) {
        $filename = "$name.$ext";

        if (strlen($filename) > static::FILENAME_MAXIMUM_LENGTH) {
            $filename = substr($name, 0, static::FILENAME_MAXIMUM_LENGTH - strlen(".$ext")) . ".$ext";
        }

        return $filename;
    }
}
