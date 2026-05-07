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
 * Unit tests for check_documentserver_connection.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\external;

\defined('MOODLE_INTERNAL') || die();

use mod_onlyofficeeditor\configuration_constants;

/**
 * Unit tests for {@see check_documentserver_connection}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\external\check_documentserver_connection
 */
final class check_documentserver_connection_test extends \advanced_testcase {

    /**
     * Web-service function name as registered in db/services.php.
     */
    private const WS_FUNCTION = 'mod_onlyofficeeditor_check_documentserver_connection';

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Default parameter payload accepted by the external function.
     */
    private function default_params(): array {
        return [
            configuration_constants::CONFIG_SECRET => '',
            configuration_constants::CONFIG_JWT_HEADER => '',
            configuration_constants::CONFIG_DOCS_INTERNAL_URL => '',
            configuration_constants::CONFIG_STORAGE_INTERNAL_URL => '',
            configuration_constants::CONFIG_DISABLE_VERIFY_SSL => false,
        ];
    }

    /**
     * A user without moodle/site:config triggers a required_capability_exception.
     */
    public function test_execute_throws_required_capability_for_non_admin(): void {
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);

        $this->expectException(\required_capability_exception::class);
        check_documentserver_connection::execute('', '', '', '', false);
    }

    /**
     * The return value follows the documented shape: {status, errors[{field, message}]}.
     */
    public function test_execute_returns_documented_shape(): void {
        $this->setAdminUser();

        $result = check_documentserver_connection::execute('', '', '', '', false);

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertIsString($result['status']);
        $this->assertIsArray($result['errors']);
        foreach ($result['errors'] as $error) {
            $this->assertArrayHasKey('field', $error);
            $this->assertArrayHasKey('message', $error);
        }
    }
}
