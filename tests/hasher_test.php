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
 * Unit tests for hasher.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

\defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for {@see hasher}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\hasher
 */
final class hasher_test extends \advanced_testcase {

    /**
     * Fixed appkey so each test runs with a known signing key.
     */
    private const APPKEY = 'test-appkey-for-tests';

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('appkey', self::APPKEY, 'onlyofficeeditor');
    }

    /**
     * Encoding then decoding with the same appkey returns the original payload.
     */
    public function test_get_hash_then_read_hash_round_trip(): void {
        $hasher = new hasher();
        $payload = ['userid' => 42, 'pathnamehash' => 'abc123'];

        $hash = $hasher->get_hash($payload);
        [$decoded, $error] = $hasher->read_hash($hash);

        $this->assertNull($error);
        $this->assertSame($payload, (array) $decoded);
    }

    /**
     * Identical payloads produce identical hashes.
     */
    public function test_get_hash_is_deterministic_for_same_payload(): void {
        $hasher = new hasher();
        $payload = ['userid' => 42];

        $this->assertSame($hasher->get_hash($payload), $hasher->get_hash($payload));
    }

    /**
     * Different payloads must produce different hashes.
     */
    public function test_get_hash_differs_for_different_payloads(): void {
        $hasher = new hasher();

        $this->assertNotSame(
            $hasher->get_hash(['userid' => 42]),
            $hasher->get_hash(['userid' => 43]),
        );
    }

    /**
     * A null hash short-circuits with the 'hash is empty' error.
     */
    public function test_read_hash_returns_error_for_null(): void {
        $hasher = new hasher();

        [$result, $error] = $hasher->read_hash(null);

        $this->assertNull($result);
        $this->assertSame('hash is empty', $error);
    }

    /**
     * A base64 blob without the '?' separator is rejected as 'incorrect hash'.
     */
    public function test_read_hash_returns_error_for_malformed_input(): void {
        $hasher = new hasher();
        // base64-encoded string without the "?" separator that hasher expects.
        $malformed = base64_encode('justsomedata');

        [$result, $error] = $hasher->read_hash($malformed);

        $this->assertNull($result);
        $this->assertSame('incorrect hash', $error);
    }

    /**
     * Modifying the JSON segment after signing must fail signature verification.
     */
    public function test_read_hash_detects_tampered_payload(): void {
        $hasher = new hasher();
        $hash = $hasher->get_hash(['userid' => 42]);

        // Modify the JSON segment but leave the signature intact.
        [$sig, $json] = explode('?', base64_decode($hash), 2);
        $tamperedjson = str_replace('42', '43', $json);
        $tampered = base64_encode($sig . '?' . $tamperedjson);

        [$result, $error] = $hasher->read_hash($tampered);

        $this->assertNull($result);
        $this->assertSame('hash not equal', $error);
    }

    /**
     * Modifying the signature segment must fail signature verification.
     */
    public function test_read_hash_detects_tampered_signature(): void {
        $hasher = new hasher();
        $hash = $hasher->get_hash(['userid' => 42]);

        // Replace the signature segment with garbage of the same length.
        [$sig, $json] = explode('?', base64_decode($hash), 2);
        $tamperedsig = str_repeat('A', \strlen($sig));
        $tampered = base64_encode($tamperedsig . '?' . $json);

        [$result, $error] = $hasher->read_hash($tampered);

        $this->assertNull($result);
        $this->assertSame('hash not equal', $error);
    }

    /**
     * A hash signed with one appkey must be rejected after the appkey changes.
     */
    public function test_hash_made_with_one_appkey_fails_with_another(): void {
        $hasher1 = new hasher();
        $hash = $hasher1->get_hash(['userid' => 42]);

        // Change the appkey and re-construct so the hasher reads the new key.
        set_config('appkey', 'completely-different-key', 'onlyofficeeditor');
        $hasher2 = new hasher();

        [$result, $error] = $hasher2->read_hash($hash);

        $this->assertNull($result);
        $this->assertSame('hash not equal', $error);
    }
}
