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
 * Unit tests for conversion_service_exception.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\exceptions;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see conversion_service_exception}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\local\exceptions\conversion_service_exception
 */
final class conversion_service_exception_test extends \basic_testcase {

    /**
     * Prefix every conversion-service message starts with.
     */
    private const PREFIX = 'Error when trying to check ConvertService: ';

    /**
     * Each known error code maps to the expected message and preserves the code on the exception.
     *
     * @dataProvider known_code_provider
     */
    public function test_known_code_produces_expected_message(int $code, string $expected): void {
        $e = new conversion_service_exception($code);
        $this->assertSame(self::PREFIX . $expected, $e->getMessage());
        $this->assertSame($code, $e->getCode());
    }

    /**
     * Cases for {@see test_known_code_produces_expected_message}.
     */
    public static function known_code_provider(): array {
        return [
            'unknown' => [
                conversion_service_exception::ERROR_UNKNOWN,
                'Unknown error.',
            ],
            'conversion timeout' => [
                conversion_service_exception::ERROR_CONVERSION_TIMEOUT,
                'Conversion timeout error.',
            ],
            'conversion error' => [
                conversion_service_exception::ERROR_CONVERSION,
                'Conversion error.',
            ],
            'download' => [
                conversion_service_exception::ERROR_DOWNLOAD,
                'Error while downloading the document file to be converted.',
            ],
            'incorrect password' => [
                conversion_service_exception::ERROR_INCORRECT_PASSWORD,
                'Incorrect password.',
            ],
            'database access' => [
                conversion_service_exception::ERROR_DATABASE_ACCESS,
                'Error while accessing the conversion result database.',
            ],
            'input' => [
                conversion_service_exception::ERROR_INPUT,
                'Input error.',
            ],
            'invalid token' => [
                conversion_service_exception::ERROR_INVALID_TOKEN,
                'Invalid token.',
            ],
            'format detection' => [
                conversion_service_exception::ERROR_FORMAT_DETECTION,
                'Error while trying to automatically determine the output file format.',
            ],
            'size limit' => [
                conversion_service_exception::ERROR_SIZE_LIMIT,
                'Size limit exceeded.',
            ],
        ];
    }

    /**
     * An unrecognised code is replaced with 0 and the message uses the generic 'Unknown error code.' fallback.
     */
    public function test_unknown_code_resets_code_and_uses_generic_message(): void {
        $e = new conversion_service_exception(999);
        $this->assertSame(self::PREFIX . 'Unknown error code.', $e->getMessage());
        $this->assertSame(0, $e->getCode());
    }

    /**
     * Extends document_server_exception so callers can catch at either level.
     */
    public function test_inheritance_chain(): void {
        $e = new conversion_service_exception(conversion_service_exception::ERROR_INVALID_TOKEN);
        $this->assertInstanceOf(document_server_exception::class, $e);
        $this->assertInstanceOf(\Exception::class, $e);
    }
}
