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
 * Unit tests for docs_settings_validator.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\docs;

\defined('MOODLE_INTERNAL') || die();

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use mod_onlyofficeeditor\configuration_constants;
use mod_onlyofficeeditor\local\exceptions\command_service_exception;
use mod_onlyofficeeditor\local\exceptions\conversion_service_exception;

/**
 * Unit tests for {@see docs_settings_validator}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\local\docs\docs_settings_validator
 */
final class docs_settings_validator_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Default config payload accepted by validate(). Tests can override individual keys.
     */
    private function build_data(array $overrides = []): array {
        return $overrides + [
            configuration_constants::CONFIG_SECRET => 'secret',
            configuration_constants::CONFIG_JWT_HEADER => 'Authorization',
            configuration_constants::CONFIG_DOCS_INTERNAL_URL => 'http://docs.example.test',
            configuration_constants::CONFIG_STORAGE_INTERNAL_URL => 'http://storage.example.test',
            configuration_constants::CONFIG_DISABLE_VERIFY_SSL => false,
        ];
    }

    /**
     * Build a validator wired to a MockHandler with the given queued responses (or exceptions).
     *
     * @param array $responses Items returned by Guzzle's MockHandler in order.
     * @param array|null $history If provided (passed by reference), the request history is captured here.
     */
    private function arrange(array $responses, ?array &$history = null): docs_settings_validator {
        ['client' => $client, 'mock' => $mock] = $this->get_mocked_http_client($history);
        foreach ($responses as $r) {
            $mock->append($r);
        }
        return new docs_settings_validator($client);
    }

    /**
     * All three checks succeed, validate() returns an empty errors array.
     */
    public function test_validate_returns_no_errors_when_all_checks_pass(): void {
        $validator = $this->arrange([
            new Response(200, [], 'true'),
            new Response(200, [], json_encode(['version' => '7.0'])),
            new Response(200, [], json_encode(['endConvert' => true])),
        ]);

        $this->assertSame([], $validator->validate($this->build_data()));
    }

    /**
     * Healthcheck body other than 'true' produces an error keyed by the documentserverinternal field.
     */
    public function test_document_server_non_true_response_returns_url_field_error(): void {
        $validator = $this->arrange([
            new Response(200, [], 'false'),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey(configuration_constants::CONFIG_DOCS_INTERNAL_URL, $errors);
    }

    /**
     * A connection exception during healthcheck produces a 'general' error.
     */
    public function test_document_server_connection_failure_returns_general_field_error(): void {
        $validator = $this->arrange([
            new ConnectException(
                'Connection refused',
                new Request('GET', 'http://docs.example.test/healthcheck')
            ),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey('general', $errors);
    }

    /**
     * Command service responding with ERROR_INVALID_TOKEN produces an error keyed by documentserversecret.
     */
    public function test_command_service_invalid_token_returns_secret_field_error(): void {
        $validator = $this->arrange([
            new Response(200, [], 'true'),
            new Response(200, [], json_encode(['error' => command_service_exception::ERROR_INVALID_TOKEN])),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey(configuration_constants::CONFIG_SECRET, $errors);
    }

    /**
     * Command service responding with any other error code produces a 'general' error.
     */
    public function test_command_service_other_error_returns_general_field_error(): void {
        $validator = $this->arrange([
            new Response(200, [], 'true'),
            new Response(200, [], json_encode(['error' => command_service_exception::ERROR_INTERNAL_SERVER])),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey('general', $errors);
    }

    /**
     * Conversion service responding with ERROR_INVALID_TOKEN produces an error keyed by jwtheader.
     */
    public function test_conversion_service_invalid_token_returns_jwtheader_field_error(): void {
        $validator = $this->arrange([
            new Response(200, [], 'true'),
            new Response(200, [], json_encode(['version' => '7.0'])),
            new Response(200, [], json_encode(['error' => conversion_service_exception::ERROR_INVALID_TOKEN])),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey(configuration_constants::CONFIG_JWT_HEADER, $errors);
    }

    /**
     * Conversion service responding with any other error code produces a 'general' error.
     */
    public function test_conversion_service_other_error_returns_general_field_error(): void {
        $validator = $this->arrange([
            new Response(200, [], 'true'),
            new Response(200, [], json_encode(['version' => '7.0'])),
            new Response(200, [], json_encode(['error' => conversion_service_exception::ERROR_CONVERSION])),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey('general', $errors);
    }

    /**
     * Conversion service responding without endConvert=true produces a 'general' error.
     */
    public function test_conversion_service_missing_end_convert_returns_general_field_error(): void {
        $validator = $this->arrange([
            new Response(200, [], 'true'),
            new Response(200, [], json_encode(['version' => '7.0'])),
            new Response(200, [], json_encode(['endConvert' => false])),
        ]);

        $errors = $validator->validate($this->build_data());

        $this->assertArrayHasKey('general', $errors);
    }

    /**
     * The first failing check short-circuits the rest — no further HTTP requests are made.
     */
    public function test_first_failure_short_circuits_remaining_checks(): void {
        $history = [];
        $validator = $this->arrange(
            [new Response(200, [], 'false')],
            $history,
        );

        $validator->validate($this->build_data());

        $this->assertCount(1, $history, 'Only the healthcheck request should be issued.');
    }

    /**
     * get_errors() returns the same array that validate() returned.
     */
    public function test_get_errors_returns_same_errors_as_validate(): void {
        $validator = $this->arrange([
            new Response(200, [], 'false'),
        ]);

        $returned = $validator->validate($this->build_data());

        $this->assertSame($returned, $validator->get_errors());
    }
}
