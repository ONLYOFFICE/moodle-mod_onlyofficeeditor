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
 * Command service exception.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\exceptions;

/**
 * Exception indicating command service errors.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class command_service_exception extends document_server_exception {
    /**
     * Error codes for command service
     */
    /** @var int Error code when required key is missing */
    public const ERROR_MISSING_KEY = 1;

    /** @var int Error code when provided URL is invalid */
    public const ERROR_INVALID_URL = 2;

    /** @var int Error code when internal server error occurs */
    public const ERROR_INTERNAL_SERVER = 3;

    /** @var int Error code when no changes were made to the document */
    public const ERROR_NO_CHANGES = 4;

    /** @var int Error code when command is invalid or not recognized */
    public const ERROR_INVALID_COMMAND = 5;

    /** @var int Error code when provided token is invalid */
    public const ERROR_INVALID_TOKEN = 6;

    /**
     * Validates if the given code is a known error code
     *
     * @param int $code Error code to validate
     * @return bool True if code is valid
     */
    private static function is_valid_code(int $code): bool {
        return in_array($code, [
            self::ERROR_MISSING_KEY,
            self::ERROR_INVALID_URL,
            self::ERROR_INTERNAL_SERVER,
            self::ERROR_NO_CHANGES,
            self::ERROR_INVALID_COMMAND,
            self::ERROR_INVALID_TOKEN,
        ]);
    }

    /**
     * Constructor
     *
     * @param int $code The error code
     */
    public function __construct(int $code) {
        $message = 'Error when trying to check CommandService: ';

        if (!self::is_valid_code($code)) {
            $message .= 'Unknown error code.';
            $code = 0;
        } else {
            switch ($code) {
                case self::ERROR_MISSING_KEY:
                    $message .= 'Document key is missing or no document with such key could be found.';
                    break;
                case self::ERROR_INVALID_URL:
                    $message .= 'Callback url not correct.';
                    break;
                case self::ERROR_INTERNAL_SERVER:
                    $message .= 'Internal server error.';
                    break;
                case self::ERROR_NO_CHANGES:
                    $message .= 'No changes were applied to the document before the forcesave command was received.';
                    break;
                case self::ERROR_INVALID_COMMAND:
                    $message .= 'Command not correct.';
                    break;
                case self::ERROR_INVALID_TOKEN:
                    $message .= 'Invalid token.';
                    break;
            }
        }

        parent::__construct($message, $code);
    }
}
