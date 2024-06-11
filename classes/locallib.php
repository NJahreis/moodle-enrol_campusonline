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
 * Class locallib
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_campusonline;

defined('MOODLE_INTERNAL') || die;

class locallib {

    /**
     * Gets a category for a course.
     *
     * @param object $course
     *
     * @return string $value
     */
    public static function getCourseCategory($course) {

        // TODO: implement.
        return 1;
    }

    /**
     * Gets a value for a course field.
     *
     * @param string $field
     * @param object $course
     *
     * @return string $value
     */
    public static function getCourseField($field, $course) {
        global $DB;

        $course = (array)$course;
        $fieldvalue = get_config('enrol_campusonline', 'course' . $field);

        foreach ($course as $field => $value) {
            if (is_object($value)) {
                $value = self::getObjectValue($value);
            } elseif (is_array($value)) {
                $value = implode(' ', $value);
            } else {
                $value = (string) $value;
            }

            if (is_string($value)) {
                $fieldvalue = str_replace('{' . $field . '}', $value, $fieldvalue);
            }
        }

        return $fieldvalue;
    }

    /**
     * Gets a value for a CAMPUSonline value that is an object.
     *
     * @param object $value
     *
     * @return string $value
     */
    private static function getObjectValue($value) {
        if (property_exists($value, 'value')) {
            $lang = 'de'; //TODO: make configurable?
            if (property_exists($value->value, $lang)) {
                return $value->value->$lang;
            }
        }

        $value = (array)$value;
        $value = implode(' ', $value);

        return $value;
    }
}
