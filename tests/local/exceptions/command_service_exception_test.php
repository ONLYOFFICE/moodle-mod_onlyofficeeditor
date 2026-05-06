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
 * Unit tests for command_service_exception.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\exceptions;

\defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see command_service_exception}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\local\exceptions\command_service_exception
 */
final class command_service_exception_test extends \basic_testcase {

    /** Prefix every command-service message starts with. */
    private const PREFIX = 'Error when trying to check CommandService: ';

    /**
     * Each known error code maps to the expected message and preserves the code on the exception.
     *
     * @dataProvider known_code_provider
     */
    public function test_known_code_produces_expected_message(int $code, string $expected): void {
        $e = new command_service_exception($code);
        $this->assertSame(self::PREFIX . $expected, $e->getMessage());
        $this->assertSame($code, $e->getCode());
    }

    /**
     * Cases for {@see test_known_code_produces_expected_message}.
     */
    public static function known_code_provider(): array {
        return [
            'missing key' => [
                command_service_exception::ERROR_MISSING_KEY,
                'Document key is missing or no document with such key could be found.',
            ],
            'invalid url' => [
                command_service_exception::ERROR_INVALID_URL,
                'Callback url not correct.',
            ],
            'internal server' => [
                command_service_exception::ERROR_INTERNAL_SERVER,
                'Internal server error.',
            ],
            'no changes' => [
                command_service_exception::ERROR_NO_CHANGES,
                'No changes were applied to the document before the forcesave command was received.',
            ],
            'invalid command' => [
                command_service_exception::ERROR_INVALID_COMMAND,
                'Command not correct.',
            ],
            'invalid token' => [
                command_service_exception::ERROR_INVALID_TOKEN,
                'Invalid token.',
            ],
        ];
    }

    /**
     * An unrecognised code is replaced with 0 and the message uses the generic 'Unknown error code.' fallback.
     */
    public function test_unknown_code_resets_code_and_uses_generic_message(): void {
        $e = new command_service_exception(999);
        $this->assertSame(self::PREFIX . 'Unknown error code.', $e->getMessage());
        $this->assertSame(0, $e->getCode());
    }

    /**
     * Extends document_server_exception so callers can catch at either level.
     */
    public function test_inheritance_chain(): void {
        $e = new command_service_exception(command_service_exception::ERROR_INVALID_TOKEN);
        $this->assertInstanceOf(document_server_exception::class, $e);
        $this->assertInstanceOf(\Exception::class, $e);
    }
}
