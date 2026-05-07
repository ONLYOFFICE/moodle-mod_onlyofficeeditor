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
 * Unit tests for document.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

\defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see document}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\document
 */
final class document_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Create a course + onlyofficeeditor activity + cm + module context.
     *
     * @param array $overrides Override values for create_module().
     * @return array
     */
    private function create_activity(array $overrides = []): array {
        $course = $this->getDataGenerator()->create_course();
        $oo = $this->getDataGenerator()->create_module(
            'onlyofficeeditor',
            ['course' => $course] + $overrides,
        );
        $cm = get_coursemodule_from_instance('onlyofficeeditor', $oo->id);
        return [
            'course'  => $course,
            'oo'      => $oo,
            'cm'      => $cm,
            'context' => \context_module::instance($cm->id),
        ];
    }

    /**
     * Enrol a user with the given role and switch to them.
     */
    private function login_as(int $courseid, string $role): \stdClass {
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $courseid, $role);
        $this->setUser($user);
        return $user;
    }

    /**
     * Generates a 20-char key when none is stored and persists it to the database.
     */
    public function test_get_key_lazy_creates_when_missing(): void {
        global $DB;
        $this->setAdminUser();
        ['oo' => $oo, 'cm' => $cm] = $this->create_activity();

        $this->assertEmpty(
            $DB->get_field('onlyofficeeditor', 'documentkey', ['id' => $oo->id]),
            'Generator does not set documentkey, so it should start empty.'
        );

        $key = document::get_key($cm);

        $this->assertSame(20, \strlen($key));
        $this->assertSame(
            $key,
            $DB->get_field('onlyofficeeditor', 'documentkey', ['id' => $oo->id]),
            'Generated key should be persisted.'
        );
    }

    /**
     * Returns the key value already stored in the database.
     */
    public function test_get_key_returns_existing_value(): void {
        global $DB;
        $this->setAdminUser();
        ['oo' => $oo, 'cm' => $cm] = $this->create_activity();
        $DB->set_field('onlyofficeeditor', 'documentkey', 'preset-key-value-12', ['id' => $oo->id]);

        $this->assertSame('preset-key-value-12', document::get_key($cm));
    }

    /**
     * Repeated calls return the same key.
     */
    public function test_get_key_is_idempotent(): void {
        $this->setAdminUser();
        ['cm' => $cm] = $this->create_activity();

        $this->assertSame(document::get_key($cm), document::get_key($cm));
    }

    /**
     * set_key always overwrites the existing key with a new 20-char string.
     */
    public function test_set_key_overwrites_existing_key(): void {
        global $DB;
        $this->setAdminUser();
        ['oo' => $oo, 'cm' => $cm] = $this->create_activity();
        $DB->set_field('onlyofficeeditor', 'documentkey', 'old-key', ['id' => $oo->id]);

        document::set_key($cm);

        $newkey = $DB->get_field('onlyofficeeditor', 'documentkey', ['id' => $oo->id]);
        $this->assertSame(20, \strlen($newkey));
        $this->assertNotSame('old-key', $newkey);
    }

    /**
     * Admin viewing an editable file gets edit/review/print/download/protect/chat all true.
     */
    public function test_get_permissions_admin_with_editable_file(): void {
        $this->setAdminUser();
        ['cm' => $cm, 'context' => $context] = $this->create_activity();
        set_config('editor_view_chat', 1, 'onlyofficeeditor');

        $permissions = document::get_permissions($context, $cm, 'doc.docx');

        $this->assertTrue($permissions['edit']);
        $this->assertTrue($permissions['review']);
        $this->assertFalse($permissions['fillForms'], 'fillForms only for .oform and .pdf');
        $this->assertTrue($permissions['print']);
        $this->assertTrue($permissions['download']);
        $this->assertTrue($permissions['protect']);
        $this->assertTrue($permissions['chat']);
    }

    /**
     * Student viewing an editable file gets edit/review true.
     */
    public function test_get_permissions_student_can_edit_editable_extension(): void {
        ['course' => $course, 'cm' => $cm, 'context' => $context] = $this->create_activity();
        $this->login_as($course->id, 'student');

        $permissions = document::get_permissions($context, $cm, 'doc.docx');

        $this->assertTrue($permissions['edit']);
        $this->assertTrue($permissions['review']);
    }

    /**
     * Student viewing a non-editable extension gets edit/review/fillForms false.
     */
    public function test_get_permissions_student_cannot_edit_non_editable_extension(): void {
        ['course' => $course, 'cm' => $cm, 'context' => $context] = $this->create_activity();
        $this->login_as($course->id, 'student');

        $permissions = document::get_permissions($context, $cm, 'notes.txt');

        $this->assertFalse($permissions['edit']);
        $this->assertFalse($permissions['review']);
        $this->assertFalse($permissions['fillForms']);
    }

    /**
     * fillForms is true only for .oform and .pdf when the user can edit.
     *
     * @dataProvider fill_forms_extension_provider
     */
    public function test_get_permissions_fillforms_only_for_oform_and_pdf(
        string $filename,
        bool $expected,
    ): void {
        $this->setAdminUser();
        ['cm' => $cm, 'context' => $context] = $this->create_activity();

        $permissions = document::get_permissions($context, $cm, $filename);

        $this->assertSame($expected, $permissions['fillForms']);
    }

    /**
     * Cases for {@see test_get_permissions_fillforms_only_for_oform_and_pdf}.
     */
    public static function fill_forms_extension_provider(): array {
        return [
            '.pdf gets fillForms'   => ['form.pdf', true],
            '.oform gets fillForms' => ['form.oform', true],
            '.docx no fillForms'    => ['doc.docx', false],
            '.xlsx no fillForms'    => ['sheet.xlsx', false],
            '.txt no fillForms'     => ['notes.txt', false],
        ];
    }

    /**
     * Chat reflects editor_view_chat config when user is not a guest.
     */
    public function test_get_permissions_chat_follows_config_when_not_guest(): void {
        $this->setAdminUser();
        ['cm' => $cm, 'context' => $context] = $this->create_activity();

        set_config('editor_view_chat', 1, 'onlyofficeeditor');
        $this->assertTrue(document::get_permissions($context, $cm, 'doc.docx')['chat']);

        set_config('editor_view_chat', 0, 'onlyofficeeditor');
        $this->assertFalse(document::get_permissions($context, $cm, 'doc.docx')['chat']);
    }

    /**
     * Chat is always false for the guest user, regardless of config.
     */
    public function test_get_permissions_chat_disabled_for_guest(): void {
        ['cm' => $cm, 'context' => $context] = $this->create_activity();
        set_config('editor_view_chat', 1, 'onlyofficeeditor');
        $this->setGuestUser();

        $this->assertFalse(document::get_permissions($context, $cm, 'doc.docx')['chat']);
    }

    /**
     * print and download inherit stored truthy values directly.
     */
    public function test_get_permissions_print_download_inherit_stored_truthy(): void {
        ['course' => $course, 'cm' => $cm, 'context' => $context] = $this->create_activity([
            'download' => 1, 'print' => 1, 'protect' => 1,
        ]);
        $this->login_as($course->id, 'student');

        $permissions = document::get_permissions($context, $cm, 'doc.docx');

        $this->assertTrue($permissions['print']);
        $this->assertTrue($permissions['download']);
    }

    /**
     * print and download fall back to canmanage when stored values are empty.
     */
    public function test_get_permissions_print_download_fall_back_to_canmanage(): void {
        ['course' => $course, 'cm' => $cm, 'context' => $context] = $this->create_activity([
            'download' => 0, 'print' => 0, 'protect' => 0,
        ]);
        $this->login_as($course->id, 'student');

        $permissions = document::get_permissions($context, $cm, 'doc.docx');

        // Student cannot manage activities so the fallback evaluates to false.
        $this->assertFalse($permissions['print']);
        $this->assertFalse($permissions['download']);
    }
}
