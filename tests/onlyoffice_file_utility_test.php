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
 * Unit tests for onlyoffice_file_utility.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see onlyoffice_file_utility}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\onlyoffice_file_utility
 */
final class onlyoffice_file_utility_test extends \basic_testcase {

    /**
     * Every format list must be a non-empty array of dotted extensions.
     */
    public function test_format_lists_are_non_empty_and_dotted(): void {
        $lists = [
            'spreadsheet'  => onlyoffice_file_utility::get_accepted_spreadsheet_formats(),
            'document'     => onlyoffice_file_utility::get_accepted_document_formats(),
            'presentation' => onlyoffice_file_utility::get_accepted_presentation_formats(),
            'pdf'          => onlyoffice_file_utility::get_accepted_pdf_formats(),
            'editable'     => onlyoffice_file_utility::get_editable_extensions(),
        ];
        foreach ($lists as $name => $list) {
            $this->assertIsArray($list, "$name list should be an array");
            $this->assertNotEmpty($list, "$name list should not be empty");
            foreach ($list as $ext) {
                $this->assertIsString($ext);
                $this->assertStringStartsWith('.', $ext, "Format '$ext' in $name should start with a dot");
            }
        }
    }

    /**
     * get_accepted_formats() should be a deduplicated union of all per-type lists.
     */
    public function test_get_accepted_formats_is_deduplicated_union(): void {
        $formats = onlyoffice_file_utility::get_accepted_formats();

        $this->assertSame(
            array_values(array_unique($formats)),
            array_values($formats),
            'Combined list should not contain duplicates'
        );

        $buckets = [
            onlyoffice_file_utility::get_accepted_spreadsheet_formats(),
            onlyoffice_file_utility::get_accepted_document_formats(),
            onlyoffice_file_utility::get_accepted_presentation_formats(),
            onlyoffice_file_utility::get_accepted_pdf_formats(),
        ];
        foreach ($buckets as $bucket) {
            foreach ($bucket as $ext) {
                $this->assertContains($ext, $formats);
            }
        }
    }

    /**
     * @dataProvider is_format_supported_provider
     */
    public function test_is_format_supported(string $extension, bool $expected): void {
        $this->assertSame($expected, onlyoffice_file_utility::is_format_supported($extension));
    }

    /**
     * Cases for {@see test_is_format_supported}.
     *
     * Extensions are normalised: leading dots are stripped and case is folded
     * before comparison against the canonical (lowercase, dotted) format list.
     */
    public static function is_format_supported_provider(): array {
        return [
            'docx is supported'          => ['docx', true],
            'xlsx is supported'          => ['xlsx', true],
            'pptx is supported'          => ['pptx', true],
            'pdf is supported'           => ['pdf', true],
            'odt is supported'           => ['odt', true],
            'unknown extension'          => ['xyz', false],
            'empty string'               => ['', false],
            'leading dot is accepted'    => ['.docx', true],
            'uppercase is accepted'      => ['DOCX', true],
            'mixed case is accepted'     => ['DocX', true],
        ];
    }

    /**
     * @dataProvider get_document_type_provider
     */
    public function test_get_document_type(string $ext, string $expected): void {
        $this->assertSame($expected, onlyoffice_file_utility::get_document_type($ext));
    }

    /**
     * Cases for {@see test_get_document_type}.
     *
     * Extensions are normalised: a leading dot is optional and case is folded
     * before lookup. Anything that does not match a known list falls back to 'word'.
     */
    public static function get_document_type_provider(): array {
        return [
            'docx is word'                => ['.docx', 'word'],
            'odt is word'                 => ['.odt', 'word'],
            'xlsx is cell'                => ['.xlsx', 'cell'],
            'csv is cell'                 => ['.csv', 'cell'],
            'pptx is slide'               => ['.pptx', 'slide'],
            'pdf is pdf'                  => ['.pdf', 'pdf'],
            'oform is pdf'                => ['.oform', 'pdf'],
            'unknown defaults to word'    => ['.unknown', 'word'],
            'empty defaults to word'      => ['', 'word'],
            'missing dot is normalised'   => ['docx', 'word'],
            'uppercase docx is word'      => ['.DOCX', 'word'],
            'uppercase xlsx is cell'      => ['.XLSX', 'cell'],
            'uppercase pptx is slide'     => ['.PPTX', 'slide'],
            'uppercase pdf is pdf'        => ['.PDF', 'pdf'],
            'mixed case xlsx is cell'     => ['.XlSx', 'cell'],
            'no-dot xlsx is cell'         => ['xlsx', 'cell'],
            'no-dot pptx is slide'        => ['pptx', 'slide'],
        ];
    }

    /**
     * Every editable extension must also be in the global accepted-formats list.
     */
    public function test_editable_extensions_are_subset_of_accepted_formats(): void {
        $editable = onlyoffice_file_utility::get_editable_extensions();
        $all = onlyoffice_file_utility::get_accepted_formats();
        foreach ($editable as $ext) {
            $this->assertContains($ext, $all, "Editable ext '$ext' must also be in accepted formats");
        }
    }
}
