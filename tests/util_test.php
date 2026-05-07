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
}
