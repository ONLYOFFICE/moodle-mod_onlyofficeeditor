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
 * Strings for component 'onlyofficeeditor', language 'en'.
 *
 * @package     mod_onlyofficeeditor
 * @subpackage
 * @copyright   2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright   based on work by 2018 Olumuyiwa <muyi.taiwo@logicexpertise.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['banner_description'] = 'Easily launch the editors in the cloud without downloading and installation';
$string['banner_link_title'] = 'Get Now';
$string['banner_title'] = 'ONLYOFFICE Docs Cloud';
$string['checkdocserverbutton'] = 'Check Docs connection';
$string['connectionerror'] = 'Connection error';
$string['connectionerror:command'] = 'Error when trying to check CommandService';
$string['connectionerror:convert'] = 'Error when trying to check ConvertService';
$string['connectionerror:unexpected'] = 'Unexpected error occurred while checking connection';
$string['connectionsuccess'] = 'Connection is stable';
$string['disable_verify_ssl'] = 'Disable certificate verification (insecure)';
$string['disable_verify_ssl:description'] = 'Only use when accessing Document Server with a self-signed certificate';
$string['docserverunreachable'] = 'ONLYOFFICE Document Server cannot be reached. Please contact admin';
$string['documentpermissions'] = 'Document permissions';
$string['documentservererror'] = 'Unable to connect to ONLYOFFICE Docs. Please check if the server is running and accessible.';
$string['documentserverinternal'] = 'ONLYOFFICE Docs address for internal requests from the server';
$string['documentserverinternal:description'] = 'Fill in only when using internal routing between the Moodle server and ONLYOFFICE Docs, without relying on public addresses';
$string['documentserversecret'] = 'Document Server Secret';
$string['documentserversecret_desc'] = 'The secret is used to generate the token (an encrypted signature) in the browser for the document editor opening and calling the methods and the requests to the document command service and document conversion service. The token prevents the substitution of important parameters in ONLYOFFICE Document Server requests.';
$string['documentserverurl'] = 'Document Editing Service Address';
$string['documentserverurl_desc'] = 'The Document Editing Service Address specifies the address of the server with the document services installed. Please replace \'https://documentserver.url\' above with the correct server address';
$string['docxformname'] = 'Document';
$string['download'] = 'Document can be downloaded';
$string['download_help'] = 'If this is off, documents will not be downloadable in the ONLYOFFICE editor app. Note, users with <strong>course:manageactivities</strong> capability are always able to download documents via the app';
$string['editor_security'] = 'Security';
$string['editor_security_macros'] = 'Run document macros';
$string['editor_security_plugin'] = 'Enable plugins';
$string['editor_view'] = 'Editor customization settings';
$string['editor_view_chat'] = 'Display Chat menu button';
$string['editor_view_description'] = 'Learn more about customizing the editor <a href="{$a->url}" target="_blank">here</a>.';
$string['editor_view_feedback'] = 'Display Feedback & Support menu button';
$string['editor_view_header'] = 'Display the header more compact';
$string['editor_view_help'] = 'Display Help menu button';
$string['editor_view_toolbar'] = 'Display monochrome toolbar header';
$string['editorenterfullscreen'] = 'Open full screen';
$string['editorexitfullscreen'] = 'Exit full screen';
$string['forcesave'] = 'Enable Force Save';
$string['jwtheader'] = 'Authorization header';
$string['mentioncontexturlname'] = 'Link to the comment.';
$string['mentionnotifier:notification'] = '{$a->notifier} mentioned in the {$a->course}';
$string['messageprovider:mentionnotifier'] = 'ONLYOFFICE mentioning notification in module document.';
$string['modulename'] = 'ONLYOFFICE document';
$string['modulename_help'] = 'The ONLYOFFICE module enables the users to create and edit office documents stored locally in Moodle using ONLYOFFICE Document Server, allows multiple users to collaborate in real time and to save back those changes to Moodle.

For more information, visit <a href="https://helpcenter.onlyoffice.com/integration/moodle.aspx" target="_blank">Help Center</a>.';
$string['modulenameplural'] = 'ONLYOFFICE Documents';
$string['oldversion'] = 'Please update ONLYOFFICE Docs to version 7.0 to work on fillable forms online.';
$string['onlyofficeactivityicon'] = 'Open in ONLYOFFICE';
$string['onlyofficeeditor:addinstance'] = 'Add a new ONLYOFFICE document activity';
$string['onlyofficeeditor:editdocument'] = 'Edit ONLYOFFICE document activity';
$string['onlyofficeeditor:view'] = 'View ONLYOFFICE document activity';
$string['onlyofficename'] = 'Activity Name';
$string['onmentionerror'] = 'Error on mentioning.';
$string['pdfformname'] = 'PDF form';
$string['pluginadministration'] = 'ONLYOFFICE document activity administration';
$string['pluginname'] = 'ONLYOFFICE document';
$string['pptxformname'] = 'Presentation';
$string['print'] = 'Document can be printed';
$string['print_help'] = 'If this is off, documents will not be printable via the ONLYOFFICE editor app. Note, users with <strong>course:manageactivities</strong> capability are always able to print documents via the app';
$string['privacy:metadata'] = 'No information is stored about user personal data.';
$string['privacy:metadata:onlyofficeeditor'] = 'Information about documents edited with ONLYOFFICE.';
$string['privacy:metadata:onlyofficeeditor:core_files'] = 'ONLYOFFICE document activity stores documents which have been edited.';
$string['privacy:metadata:onlyofficeeditor:course'] = 'Course ONLYOFFICE activity belongs to.';
$string['privacy:metadata:onlyofficeeditor:intro'] = 'General introduction of the ONLYOFFICE activity';
$string['privacy:metadata:onlyofficeeditor:introformat'] = 'Format of the intro field (MOODLE, HTML, MARKDOWN...).';
$string['privacy:metadata:onlyofficeeditor:name'] = 'Name of the ONLYOFFICE activity.';
$string['privacy:metadata:onlyofficeeditor:permissions'] = 'Document permissions.';
$string['privacy:metadata:onlyofficeeditor:userid'] = 'Actual user ID is not sent to the ONLYOFFICE editor.';
$string['protect'] = 'Hide Protection tab';
$string['protect_help'] = 'If this off, users have access to protection settings in the ONLYOFFICE editor. Note, users with <strong>course:manageactivities</strong> capability always have access to protection settings.';
$string['readmore'] = 'Read more';
$string['returntodocument'] = 'Return to course page';
$string['saveasbutton'] = 'Choose';
$string['saveaserror'] = 'Something went wrong.';
$string['saveassuccess'] = 'Document was successfully saved.';
$string['saveastitle'] = 'Choose Course Section to Save the document';
$string['savewarning'] = 'Don\'t forget to Save changes at the bottom of the page';
$string['selectfile'] = 'Select existing file or create new by clicking one of the icons';
$string['settingsintro'] = 'Edit and collaborate on office documents online directly within Moodle course structure. Share documents for viewing or real-time co-editing. Create, share and grade students\' assignments.';
$string['storageurl'] = 'Server address for internal requests from ONLYOFFICE Docs';
$string['suggestfeature'] = 'Suggest a feature';
$string['unsupportedfileformat'] = 'File format is not supported';
$string['uploadformname'] = 'Upload file';
$string['validationerror:apijsunavailable'] = 'Cannot fetch the API JavaScript file.  Please check the Document Server URL.';
$string['validationerror:documentserverunreachable'] = 'Unable to connect to ONLYOFFICE Docs. Please check if the server is running and accessible.';
$string['validationerror:emptyurl'] = 'ONLYOFFICE Docs URL cannot be empty.';
$string['validationerror:incorrectjwtheader'] = 'Unable to connect to ONLYOFFICE Docs. Please check if the Authorization header is correct.';
$string['validationerror:incorrectsecret'] = 'Unable to connect to ONLYOFFICE Docs. Please check if the Secret key is correct.';
$string['validationerror:invalidurl'] = 'Document Server URL is invalid. Please check the URL format.';
$string['validationerror:mixedcontent'] = 'Mixed Active Content is not allowed. HTTPS address for ONLYOFFICE Docs is required.';
$string['xlsxformname'] = 'Spreadsheet';
