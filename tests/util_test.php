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
 * Unit tests for util.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

\defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see util}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\util
 */
final class util_test extends \advanced_testcase {

    /**
     * Saved value of $_SERVER['HTTP_USER_AGENT'].
     */
    private ?string $originaluseragent = null;

    /**
     * True if HTTP_USER_AGENT was set originally.
     */
    private bool $useragentwasset = false;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->useragentwasset = isset($_SERVER['HTTP_USER_AGENT']);
        $this->originaluseragent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    protected function tearDown(): void {
        if ($this->useragentwasset) {
            $_SERVER['HTTP_USER_AGENT'] = $this->originaluseragent;
        } else {
            unset($_SERVER['HTTP_USER_AGENT']);
        }
        parent::tearDown();
    }

    /**
     * Concatenates name and extension when within the length limit.
     */
    public function test_generate_filename_concatenates_name_and_extension(): void {
        $this->assertSame('Report.docx', util::generate_filename('Report', 'docx'));
    }

    /**
     * Names exceeding FILENAME_MAXIMUM_LENGTH are truncated; the extension is preserved.
     */
    public function test_generate_filename_truncates_when_too_long(): void {
        $longname = str_repeat('a', 300);

        $result = util::generate_filename($longname, 'docx');

        $this->assertSame(util::FILENAME_MAXIMUM_LENGTH, \strlen($result));
        $this->assertStringEndsWith('.docx', $result);
    }

    /**
     * Names exactly at the boundary length are kept untouched.
     */
    public function test_generate_filename_preserves_boundary_length(): void {
        $name = str_repeat('a', util::FILENAME_MAXIMUM_LENGTH - \strlen('.docx'));

        $this->assertSame($name . '.docx', util::generate_filename($name, 'docx'));
    }

    /**
     * All three flags set produces a serialised array containing each entry.
     */
    public function test_save_document_permissions_includes_truthy_flags(): void {
        $data = (object) ['download' => 1, 'print' => 1, 'protect' => 1];

        util::save_document_permissions($data);

        $this->assertSame(
            ['download' => 1, 'print' => 1, 'protect' => 1],
            unserialize($data->permissions),
        );
    }

    /**
     * Falsy and unset flags are omitted from the serialised array.
     */
    public function test_save_document_permissions_omits_falsy_flags(): void {
        $data = (object) ['download' => 1, 'print' => 0];

        util::save_document_permissions($data);

        $this->assertSame(['download' => 1], unserialize($data->permissions));
    }

    /**
     * With no flags set, the serialised array is empty.
     */
    public function test_save_document_permissions_empty_when_no_flags(): void {
        $data = new \stdClass();

        util::save_document_permissions($data);

        $this->assertSame([], unserialize($data->permissions));
    }

    /**
     * Returns true when HTTP_USER_AGENT contains the AscDesktopEditor marker.
     */
    public function test_desktop_detect_true_for_onlyoffice_desktop_useragent(): void {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 AscDesktopEditor/7.0';

        $this->assertTrue(util::desktop_detect());
    }

    /**
     * Returns false for a regular browser user-agent string.
     */
    public function test_desktop_detect_false_for_other_useragent(): void {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Linux x86_64) Chrome/120.0.0.0';

        $this->assertFalse(util::desktop_detect());
    }

    /**
     * Returns the appkey value when one is already configured.
     */
    public function test_get_appkey_returns_existing_value(): void {
        set_config('appkey', 'pre-existing-key', 'onlyofficeeditor');

        $this->assertSame('pre-existing-key', util::get_appkey());
    }

    /**
     * Generates and persists a new appkey when none is configured; subsequent calls return the same value.
     */
    public function test_get_appkey_generates_and_persists_when_missing(): void {
        $this->assertFalse(get_config('onlyofficeeditor', 'appkey'));

        $key = util::get_appkey();

        $this->assertNotEmpty($key);
        $this->assertSame($key, get_config('onlyofficeeditor', 'appkey'),
            'Generated key should be persisted to config.');
        $this->assertSame($key, util::get_appkey(),
            'Subsequent calls should return the same key.');
    }

    /**
     * Maps a known user lang to the corresponding template directory.
     */
    public function test_get_template_path_returns_mapped_locale(): void {
        global $CFG;
        $user = (object) ['lang' => 'en_us'];

        $this->assertSame(
            $CFG->dirroot . '/mod/onlyofficeeditor/newdocs/en/new.docx',
            util::get_template_path('docx', $user)
        );
    }

    /**
     * Falls back to the 'default' locale folder when the requested template doesn't exist for the user's lang.
     */
    public function test_get_template_path_falls_back_to_default_when_file_missing(): void {
        global $CFG;
        $user = (object) ['lang' => 'en_us'];

        $this->assertSame(
            $CFG->dirroot . '/mod/onlyofficeeditor/newdocs/default/new.unknown',
            util::get_template_path('unknown', $user)
        );
    }

    /**
     * Uses the global $USER when no user is passed.
     */
    public function test_get_template_path_uses_global_user_when_param_null(): void {
        global $CFG, $USER;
        $this->setAdminUser();
        $USER->lang = 'en_us';

        $this->assertSame(
            $CFG->dirroot . '/mod/onlyofficeeditor/newdocs/en/new.docx',
            util::get_template_path('docx')
        );
    }

    /**
     * Each supported template format maps to the correct file extension.
     *
     * @dataProvider create_from_onlyoffice_template_format_provider
     */
    public function test_create_from_onlyoffice_template_maps_format_to_extension(
        string $format,
        string $expectedext
    ): void {
        global $USER;
        $this->setAdminUser();
        $USER->lang = 'en_us';
        $contextid = \context_user::instance($USER->id)->id;
        $itemid = file_get_unused_draft_itemid();

        util::create_from_onlyoffice_template($format, $USER, $contextid, $itemid, 'NewDoc');

        $fs = get_file_storage();
        $file = $fs->get_file($contextid, 'mod_onlyofficeeditor', 'content', $itemid, '/', "NewDoc.$expectedext");
        $this->assertNotFalse($file, "File NewDoc.$expectedext should be created.");
        $this->assertGreaterThan(0, $file->get_filesize());
    }

    /**
     * Cases for {@see test_create_from_onlyoffice_template_maps_format_to_extension}.
     */
    public static function create_from_onlyoffice_template_format_provider(): array {
        return [
            'Document maps to docx'     => ['Document', 'docx'],
            'Spreadsheet maps to xlsx'  => ['Spreadsheet', 'xlsx'],
            'Presentation maps to pptx' => ['Presentation', 'pptx'],
            'PDF form maps to pdf'      => ['PDF form', 'pdf'],
        ];
    }

    /**
     * Copies draft-area files into the activity's content area, renaming to <data->name>.<ext>.
     */
    public function test_save_file_copies_draft_to_content_area_with_renamed_filename(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $course = $this->getDataGenerator()->create_course();
        $oo = $this->getDataGenerator()->create_module('onlyofficeeditor', ['course' => $course]);
        $cm = get_coursemodule_from_instance('onlyofficeeditor', $oo->id);

        $draftitemid = file_get_unused_draft_itemid();
        $usercontext = \context_user::instance($user->id);
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftitemid,
            'filepath'  => '/',
            'filename'  => 'original.docx',
        ], 'fake content');

        $data = (object) [
            'coursemodule' => $cm->id,
            'file'         => $draftitemid,
            'name'         => 'My document',
        ];
        util::save_file($data);

        $modulecontext = \context_module::instance($cm->id);
        $file = $fs->get_file(
            $modulecontext->id,
            'mod_onlyofficeeditor',
            'content',
            0,
            '/',
            'My document.docx'
        );
        $this->assertNotFalse($file, 'Renamed file should be in the activity content area.');
    }

    /**
     * Without a draft itemid the function leaves the content area untouched.
     */
    public function test_save_file_is_noop_without_draft_itemid(): void {
        $course = $this->getDataGenerator()->create_course();
        $oo = $this->getDataGenerator()->create_module('onlyofficeeditor', ['course' => $course]);
        $cm = get_coursemodule_from_instance('onlyofficeeditor', $oo->id);

        $data = (object) [
            'coursemodule' => $cm->id,
            'name'         => 'Empty activity',
        ];

        util::save_file($data);

        $modulecontext = \context_module::instance($cm->id);
        $files = get_file_storage()->get_area_files(
            $modulecontext->id,
            'mod_onlyofficeeditor',
            'content',
            0,
            'id',
            false
        );
        $this->assertEmpty($files);
    }

    /**
     * Excludes the current user from the mention list and includes other capable users with name+email.
     */
    public function test_get_users_to_mention_excludes_current_user(): void {
        $course = $this->getDataGenerator()->create_course();
        $alice = $this->getDataGenerator()->create_user([
            'firstname' => 'Alice', 'lastname' => 'Smith', 'email' => 'alice@test.local',
        ]);
        $bob = $this->getDataGenerator()->create_user([
            'firstname' => 'Bob', 'lastname' => 'Jones', 'email' => 'bob@test.local',
        ]);
        $this->getDataGenerator()->enrol_user($alice->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($bob->id, $course->id, 'student');
        $oo = $this->getDataGenerator()->create_module('onlyofficeeditor', ['course' => $course]);
        $cm = get_coursemodule_from_instance('onlyofficeeditor', $oo->id);
        $context = \context_module::instance($cm->id);

        $this->setUser($alice);
        $result = util::get_users_to_mention_in_comments($context);

        $emails = array_column($result, 'email');
        $this->assertNotContains('alice@test.local', $emails, 'Current user should be excluded.');
        $this->assertContains('bob@test.local', $emails, 'Other enrolled users should be included.');

        $bobindex = array_search('bob@test.local', $emails, true);
        $this->assertSame('Bob Jones', $result[$bobindex]['name']);
    }

    /**
     * Creates a new onlyofficeeditor instance in the given section, propagating download/print flags.
     */
    public function test_generate_new_module_info_creates_new_activity(): void {
        global $DB;
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $oo = $this->getDataGenerator()->create_module('onlyofficeeditor', ['course' => $course]);
        $cm = get_coursemodule_from_instance('onlyofficeeditor', $oo->id);

        $moduleinfo = $DB->get_record('onlyofficeeditor', ['id' => $oo->id]);
        $moduleinfo->modulename = 'onlyofficeeditor';
        $moduleinfo->module = $DB->get_field('modules', 'id', ['name' => 'onlyofficeeditor']);

        $before = $DB->count_records('onlyofficeeditor', ['course' => $course->id]);
        $result = util::generate_new_module_info($moduleinfo, $course, $cm, 0);

        $this->assertSame(
            $before + 1,
            $DB->count_records('onlyofficeeditor', ['course' => $course->id]),
            'A new onlyofficeeditor instance should exist in the course.'
        );
        $this->assertNotEmpty($result->coursemodule);
        $this->assertNotSame($cm->id, $result->coursemodule);
    }
}
