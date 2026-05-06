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
 * Unit tests for docs_validation_exception.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\exceptions;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see docs_validation_exception}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\local\exceptions\docs_validation_exception
 */
final class docs_validation_exception_test extends \basic_testcase {

    /**
     * Constructor stores both the field name and the message; getters return them.
     */
    public function test_field_and_message_are_stored(): void {
        $e = new docs_validation_exception('documentserverurl', 'unreachable');

        $this->assertSame('documentserverurl', $e->get_field());
        $this->assertSame('unreachable', $e->getMessage());
    }

    /**
     * The exception code is always 0 regardless of constructor arguments.
     */
    public function test_code_is_always_zero(): void {
        $e = new docs_validation_exception('general', 'something');

        $this->assertSame(0, $e->getCode());
    }

    /**
     * Empty strings are accepted as field and message without coercion.
     */
    public function test_field_with_empty_values(): void {
        $e = new docs_validation_exception('', '');

        $this->assertSame('', $e->get_field());
        $this->assertSame('', $e->getMessage());
    }

    /**
     * The class extends \Exception so callers can catch it generically.
     */
    public function test_inheritance_chain(): void {
        $e = new docs_validation_exception('general', 'msg');

        $this->assertInstanceOf(\Exception::class, $e);
    }
}
