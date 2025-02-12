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
 * Conversion service exception.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\exceptions;

/**
 * Exception indicating conversion service errors.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class conversion_service_exception extends document_server_exception {
    /**
     * Error codes for conversion service
     */
    public const ERROR_UNKNOWN = 1;
    public const ERROR_CONVERSION_TIMEOUT = 2;
    public const ERROR_CONVERSION = 3;
    public const ERROR_DOWNLOAD = 4;
    public const ERROR_INCORRECT_PASSWORD = 5;
    public const ERROR_DATABASE_ACCESS = 6;
    public const ERROR_INPUT = 7;
    public const ERROR_INVALID_TOKEN = 8;
    public const ERROR_FORMAT_DETECTION = 9;
    public const ERROR_SIZE_LIMIT = 10;

    /**
     * Validates if the given code is a known error code
     *
     * @param int $code Error code to validate
     * @return bool True if code is valid
     */
    private static function is_valid_code(int $code): bool {
        return in_array($code, [
            self::ERROR_UNKNOWN,
            self::ERROR_CONVERSION_TIMEOUT,
            self::ERROR_CONVERSION,
            self::ERROR_DOWNLOAD,
            self::ERROR_INCORRECT_PASSWORD,
            self::ERROR_DATABASE_ACCESS,
            self::ERROR_INPUT,
            self::ERROR_INVALID_TOKEN,
            self::ERROR_FORMAT_DETECTION,
            self::ERROR_SIZE_LIMIT
        ]);
    }

    /**
     * Constructor
     *
     * @param int $code The error code
     */
    public function __construct(int $code) {
        $message = 'Error when trying to check ConvertService: ';

        if (!self::is_valid_code($code)) {
            $message .= 'Unknown error code.';
            $code = 0;
        } else {
            switch ($code) {
                case self::ERROR_UNKNOWN:
                    $message .= 'Unknown error.';
                    break;
                case self::ERROR_CONVERSION_TIMEOUT:
                    $message .= 'Conversion timeout error.';
                    break;
                case self::ERROR_CONVERSION:
                    $message .= 'Conversion error.';
                    break;
                case self::ERROR_DOWNLOAD:
                    $message .= 'Error while downloading the document file to be converted.';
                    break;
                case self::ERROR_INCORRECT_PASSWORD:
                    $message .= 'Incorrect password.';
                    break;
                case self::ERROR_DATABASE_ACCESS:
                    $message .= 'Error while accessing the conversion result database.';
                    break;
                case self::ERROR_INPUT:
                    $message .= 'Input error.';
                    break;
                case self::ERROR_INVALID_TOKEN:
                    $message .= 'Invalid token.';
                    break;
                case self::ERROR_FORMAT_DETECTION:
                    $message .= 'Error while trying to automatically determine the output file format.';
                    break;
                case self::ERROR_SIZE_LIMIT:
                    $message .= 'Size limit exceeded.';
                    break;
            }
        }

        parent::__construct($message, $code);
    }
}