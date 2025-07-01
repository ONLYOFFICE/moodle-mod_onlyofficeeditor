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
 * Document server validation exception.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_onlyofficeeditor\local\exceptions;

use Exception;

/**
 * Exception indicating document server validation errors.
 *
 * @package    mod_onlyofficeeditor
 * @copyright  2025 Ascensio System SIA <integration@onlyoffice.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class docs_validation_exception extends Exception {
    /**
     * The field that caused the validation error.
     *
     * @var string
     */
    protected $field;

    /**
     * Constructor.
     *
     * @param string $field The field that caused the validation error.
     * @param string $message The error message.
     */
    public function __construct($field, $message) {
        $this->field = $field;
        parent::__construct($message, 0);
    }

    /**
     * Get the field that caused the validation error.
     *
     * @return string The field name.
     */
    public function get_field() {
        return $this->field;
    }
}
