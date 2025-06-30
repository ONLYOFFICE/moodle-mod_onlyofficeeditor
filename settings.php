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
 * ONLYOFFICE module admin settings and defaults
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @copyright  based on work by 2018 Olumuyiwa Taiwo <muyi.taiwo@logicexpertise.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_onlyofficeeditor\local\admin\settings\check_document_server_button;
use mod_onlyofficeeditor\local\admin\settings\onlyoffice_admin_setting_text;
use mod_onlyofficeeditor\local\admin\settings\onlyoffice_admin_setting_url;

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $defaulthost = 'https://documentserver.url';
    $linktodocs = 'https://www.onlyoffice.com/docs-registration.aspx?referer=moodle';
    $customizationapiurl =
    'https://api.onlyoffice.com/docs/docs-api/usage-api/config/editor/customization/customization-standard-branding';
    $helpcentermoodleurl = 'https://helpcenter.onlyoffice.com/integration/moodle.aspx';
    $defaultjwtheader = 'Authorization';
    $bannerdata = [
        'title' => get_string('banner_title', 'onlyofficeeditor'),
        'description' => get_string('banner_description', 'onlyofficeeditor'),
        'link' => [
            'title' => get_string('banner_link_title', 'onlyofficeeditor'),
            'href' => $linktodocs,
        ],
    ];
    $banner = $OUTPUT->render_from_template('mod_onlyofficeeditor/banner', $bannerdata);
    $intro = $OUTPUT->render_from_template('mod_onlyofficeeditor/settings_intro', [
        'readmoreurl' => $helpcentermoodleurl,
        'suggesturl' => '#',
    ]);

    $settings->add(new admin_setting_heading('onlyofficeeditor/intro', '', $intro));

    $settings->add(new onlyoffice_admin_setting_url('onlyofficeeditor/documentserverurl',
        get_string('documentserverurl', 'onlyofficeeditor'), get_string('documentserverurl_desc', 'onlyofficeeditor'),
        true, $defaulthost));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/disable_verify_ssl',
        get_string('disable_verify_ssl', 'onlyofficeeditor'), get_string('disable_verify_ssl:description', 'onlyofficeeditor'), 0));
    $settings->add(new onlyoffice_admin_setting_text('onlyofficeeditor/documentserversecret',
        get_string('documentserversecret', 'onlyofficeeditor'), get_string('documentserversecret_desc', 'onlyofficeeditor')));
    $settings->add(new onlyoffice_admin_setting_text('onlyofficeeditor/jwtheader',
        get_string('jwtheader', 'onlyofficeeditor'), '', false, $defaultjwtheader));
    $settings->add(new onlyoffice_admin_setting_url('onlyofficeeditor/documentserverinternal',
        get_string('documentserverinternal', 'onlyofficeeditor'), get_string('documentserverinternal:description', 'onlyofficeeditor')));
    $settings->add(new onlyoffice_admin_setting_url('onlyofficeeditor/storageurl',
        get_string('storageurl', 'onlyofficeeditor'), get_string('documentserverinternal:description', 'onlyofficeeditor')));
    $settings->add(new check_document_server_button());
    $settings->add(new admin_setting_heading('onlyofficeeditor/banner', '', $banner));

    $settings->add(new admin_setting_heading('onlyofficeeditor/editor_view',
        get_string('editor_view', 'onlyofficeeditor'),
        get_string('editor_view_description', 'onlyofficeeditor', ['url' => $customizationapiurl])));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/forcesave',
        get_string('forcesave', 'onlyofficeeditor'), '', 0));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_view_chat',
        get_string('editor_view_chat', 'onlyofficeeditor'), '', 1));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_view_help',
        get_string('editor_view_help', 'onlyofficeeditor'), '', 1));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_view_header',
        get_string('editor_view_header', 'onlyofficeeditor'), '', 0));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_view_feedback',
        get_string('editor_view_feedback', 'onlyofficeeditor'), '', 1));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_view_toolbar',
        get_string('editor_view_toolbar', 'onlyofficeeditor'), '', 0));

    $settings->add(new admin_setting_heading('onlyofficeeditor/editor_security',
        get_string('editor_security', 'onlyofficeeditor'), ''));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_security_plugin',
        get_string('editor_security_plugin', 'onlyofficeeditor'), '', 1));
    $settings->add(new admin_setting_configcheckbox('onlyofficeeditor/editor_security_macros',
        get_string('editor_security_macros', 'onlyofficeeditor'), '', 1));
}