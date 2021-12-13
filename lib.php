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
 * Library of interface functions and constants for module ONLYOFFICE
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the ONLYOFFICE specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_onlyoffice
 * @copyright  2021 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/vendor/autoload.php');

use mod_onlyoffice\util;
use mod_onlyoffice\onlyoffice_file_utility;

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function onlyoffice_supports($feature) {

    switch ($feature) {
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the ONLYOFFICE into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $data Submitted data from the form in mod_form.php
 * @param mod_onlyoffice_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted ONLYOFFICE record
 */
function onlyoffice_add_instance(stdClass $data, mod_onlyoffice_mod_form $mform = null) {
    global $CFG, $DB;

    $cmid = $data->coursemodule;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    util::save_document_permissions($data);
    util::save_file($data);

    $data->id = $DB->insert_record('onlyoffice', $data);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'onlyoffice', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Updates an instance of the ONLYOFFICE in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $data An object from the form in mod_form.php
 * @param mod_onlyoffice_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function onlyoffice_update_instance(stdClass $data, mod_onlyoffice_mod_form $mform = null) {
    global $CFG, $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    util::save_document_permissions($data);
    util::save_file($data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'onlyoffice', $data->id, $completiontimeexpected);

    $result = $DB->update_record('onlyoffice', $data);

    return $result;
}

/**
 * Removes an instance of the ONLYOFFICE from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function onlyoffice_delete_instance($id) {
    global $DB;

    if (!$onlyoffice = $DB->get_record('onlyoffice', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('onlyoffice', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'onlyoffice', $id, null);

    $DB->delete_records('onlyoffice', array('id' => $onlyoffice->id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info info
 */
function onlyoffice_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/filelib.php");
    require_once($CFG->libdir . '/completionlib.php');

    $context = \context_module::instance($coursemodule->id);

    if (!$onlyoffice = $DB->get_record('onlyoffice', array('id' => $coursemodule->instance), 'id, name, display, displayoptions, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $onlyoffice->name;
    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('onlyoffice', $onlyoffice, $coursemodule->id, false);
    }

    // See if there is at least one file.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_onlyoffice', 'content', 0, 'sortorder DESC, id ASC', false, 0, 0, 1);
    if (count($files) >= 1) {
        $file = reset($files);
        $info->icon = file_file_icon($file, 24);
        $onlyoffice->file = $file->get_filename();
    }

    return $info;
}

/**
 * Called when viewing course page. Shows extra details after the link if
 * enabled.
 * @todo Custom module instance display, similar to https://api.onlyoffice.com/editors/alfresco
 * @param cm_info $cm Course module information
 */
function onlyoffice_cm_info_view(cm_info $cm) {
    global $OUTPUT, $CFG;
    $context = $cm->context;

    $icon = $OUTPUT->pix_icon('icon', get_string('onlyofficeactivityicon', 'onlyoffice'), 'onlyoffice', array('class' => 'onlyofficeactivityicon'));
    $afterlinkicons = ' ' . html_writer::tag('i', $icon);

    if (has_capability('mod/onlyoffice:addinstance', $context)) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_onlyoffice', 'content', 0, 'sortorder DESC, id ASC', false, 0, 0, 1);
        if (count($files) >= 1) {
            $file = reset($files);
            $ext = '.' . pathinfo($file->get_filename(), PATHINFO_EXTENSION);
            $editableext = onlyoffice_file_utility::get_editable_ext($ext);

            if (in_array($ext, \mod_onlyoffice\onlyoffice_file_utility::get_convertible_extensions()) && $editableext != null) {
                $courseid = $cm->get_course()->id;
                $ajaxurl = $CFG->wwwroot . "/mod/onlyoffice/convertaction.php?courseid=" . $courseid . "&cmid=" . $cm->id . "&ext=" . $editableext;
                $jscode = "var startConvert = function(url) {
                    $.ajax(url, {
                        type: 'POST',
                        dataType: 'json',
                        complete: function(data) {
                            var responseText = data.responseText;
                            try {
                                var response = $.parseJSON(responseText);
                            } catch (e) {
                                window.open(url + '&res=failure');
                            }
                            if (response.percent < 100) {
                                startConvert(url);
                            } else if (response.percent >= 100) {
                                window.open(url + '&res=suc');
                                location.reload();
                            }
                        }
                    });
                };
                startConvert('$ajaxurl')";
                $afterlinkicons .= html_writer::tag('i', '',
                    [
                        'class' => 'icon fa fa-refresh onlyoffice-convert-icon',
                        'title' => get_string('onlyofficeconverticon', 'onlyoffice'),
                        'onclick' => $jscode
                    ]);
            }
        }
    }
    $cm->set_after_link($afterlinkicons);
}

/**
 * @todo Custom module instance display, similar to https://api.onlyoffice.com/editors/alfresco
 * @param cm_info $cm
 */
function onlyoffice_cm_info_dynamic(cm_info $cm) {
    
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @todo implement properly
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $onlyoffice The ONLYOFFICE instance record
 * @return stdClass|null
 */
function onlyoffice_user_outline($course, $user, $mod, $onlyoffice) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 * @todo Implement this function
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $onlyoffice the module instance record
 */
function onlyoffice_user_complete($course, $user, $mod, $onlyoffice) {
    
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in ONLYOFFICE activities and print it out.
 *
 * @todo implement
 * 
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function onlyoffice_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link onlyoffice_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function onlyoffice_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
    
}

/**
 * Prints single activity item prepared by {@link onlyoffice_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function onlyoffice_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function onlyoffice_get_extra_capabilities() {
    return array();
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function onlyoffice_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for ONLYOFFICE file areas
 *
 * @package mod_onlyoffice
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function onlyoffice_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the ONLYOFFICE file areas
 *
 * @package mod_onlyoffice
 * 
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the ONLYOFFICE's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function onlyoffice_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {

    $doc = required_param('doc', PARAM_RAW);

    $crypt = new \mod_onlyoffice\crypt();
    list($hash, $error) = $crypt->read_hash($doc);
    if ($error || ($hash == NULL)) {
        return false;
    }

    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_onlyoffice', $filearea, false, 'sortorder DESC, id ASC', false, 0, 0, 1);
    if (count($files) >= 1) {
        $file = reset($files);
        if ($hash->contenthash == $file->get_contenthash() && (is_enrolled($context, $hash->userid, '', true) || has_any_capability(['moodle/course:manageactivities', 'mod/onlyoffice:editdocument'], $context, $hash->userid))) {
            send_stored_file($file, null, 0, true);
        }
    }
    return false;
}
