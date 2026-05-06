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
 * Unit tests for jwt_wrapper.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor;

\defined('MOODLE_INTERNAL') || die();

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

/**
 * Unit tests for {@see jwt_wrapper}.
 *
 * @package    mod_onlyofficeeditor
 * @category   test
 * @copyright  2026 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_onlyofficeeditor\jwt_wrapper
 */
final class jwt_wrapper_test extends \basic_testcase {

    /**
     * encode() then decode() with the same secret returns the original payload.
     */
    public function test_encode_decode_roundtrip(): void {
        $payload = ['userid' => 42, 'email' => 'user@example.test'];

        $token = jwt_wrapper::encode($payload, 'secret');
        $this->assertIsString($token);

        $decoded = jwt_wrapper::decode($token, 'secret');
        $this->assertSame($payload, (array) $decoded);
    }

    /**
     * Decoding with a different secret must fail signature verification.
     */
    public function test_decode_with_wrong_secret_throws(): void {
        $token = jwt_wrapper::encode(['x' => 1], 'right-secret');

        $this->expectException(SignatureInvalidException::class);
        jwt_wrapper::decode($token, 'wrong-secret');
    }

    /**
     * Tampering with the signature segment must fail signature verification.
     */
    public function test_decode_rejects_tampered_token(): void {
        $token = jwt_wrapper::encode(['x' => 1], 'secret');

        // Replace the signature segment with garbage of the same length.
        $parts = explode('.', $token);
        $parts[2] = str_repeat('A', strlen($parts[2]));
        $tampered = implode('.', $parts);

        $this->expectException(SignatureInvalidException::class);
        jwt_wrapper::decode($tampered, 'secret');
    }

    /**
     * The wrapper hardcodes HS256; tokens signed with a different algorithm must be rejected.
     */
    public function test_decode_rejects_token_with_different_algorithm(): void {
        // Encode directly with HS512, bypassing the wrapper's HS256 enforcement.
        $token = JWT::encode(['x' => 1], 'secret', 'HS512');

        $this->expectException(\UnexpectedValueException::class);
        jwt_wrapper::decode($token, 'secret');
    }

    /**
     * Tokens whose exp is recently in the past must still decode (60s leeway).
     */
    public function test_decode_accepts_recently_expired_within_leeway(): void {
        // 30 seconds ago — within the 60s leeway the wrapper sets.
        $token = JWT::encode(['exp' => time() - 30, 'x' => 1], 'secret', 'HS256');

        $decoded = jwt_wrapper::decode($token, 'secret');
        $this->assertSame(1, $decoded->x);
    }

    /**
     * Tokens whose exp is well past the leeway window must be rejected.
     */
    public function test_decode_rejects_token_expired_beyond_leeway(): void {
        // 200 seconds ago — well past the 60s leeway.
        $token = JWT::encode(['exp' => time() - 200, 'x' => 1], 'secret', 'HS256');

        $this->expectException(ExpiredException::class);
        jwt_wrapper::decode($token, 'secret');
    }
}
