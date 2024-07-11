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

    // Course fields available for mapping and their default value.
    public const COURSE_FIELDS = ['summary' => '',
                                  'format' => 'topics',
                                  'startdate' => '',
                                  'enddate' => '',
                                  'visible' => '1',
                                  'lang' => '{course:mainLanguageOfInstruction}',
                                  'groupmode' => '1',
                                  'groupmodeforce' => '0',
    ];

    // User fields available for identification.
    public const USER_ID_FIELDS = ['id',
                                   'username',
                                   'idnumber',
                                   'email',
    ];

    // User fields available for mapping and their default value.
    public const USER_FIELDS = ['username' => 'co_{uid}',
                                'idnumber' => '{uid}',
                                'firstname' => '{givenName}',
                                'lastname' => '{surname}',
                                'email' => '{email}',
                                'phone1' => '',
                                'institution' => '',
                                'department' => '',
    ];


    /**
     * Add our enrolment method to a course.
     *
     * @param object $course
     *
     * @return void
     */
    public static function addEnrolmentMethod($course) {

        global $DB;

        if (!$enrol = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => 'campusonline'])) {

            // Create enrolment method.
            $enrol = new \stdClass();
            $enrol->enrol = 'campusonline';
            $enrol->status = 0;
            $enrol->courseid = $course->id;
            $enrol->timecreated = time();
            $enrol->timemodified = time();
            $enrol->id = $DB->insert_record('enrol', $enrol);

        } elseif ($enrol->status == 1) {

            // Set to active.
            $enrol->status = 0;
            $enrol->timemodified = time();
            $DB->update_record('enrol', $enrol);
        }
    }

    /**
     * Builds a course from CAMPUSOnline data.
     *
     * @param object $coursedata
     *
     * @return array $course
     */
    public static function buildCourse($coursedata){
        $course = array();
        $course['coursecategory'] = null;
        $course['idnumber'] = $coursedata['course:uid'];
        $course['shortname'] = self::getFieldValue('shortname', $coursedata);
        $course['fullname'] = self::getFieldValue('fullname', $coursedata);

        // Map additional fields.
        foreach(self::COURSE_FIELDS as $field => $default) {
            $course[$field] = self::getFieldValue($field, $coursedata);
        }

        return $course;
    }

    /**
     * Builds a user from CAMPUSOnline data.
     *
     * @param object $userdata
     *
     * @return array $user
     */
    public static function buildUser($userdata){

        $user = array();
        $user['auth'] = self::getFieldValue('auth', $userdata, 'user');
        $user['password'] = self::getFieldValue('password', $userdata, 'user');

        // Map additional fields.
        foreach(self::USER_FIELDS as $field => $default) {
            $user[$field] = self::getFieldValue($field, $userdata, 'user');
        }

        return $user;
    }

    /**
     * Removes old logs.
     */
    public static function cleanupLogs() {
        global $DB;

        $duration = get_config('enrol_campusonline', 'logduration');
        $time = time() - $duration * 24 * 60 * 60;
        $DB->delete_records_select('enrol_campusonline_logs', "timestamp < $time");
    }

    /**
     * Gets custom fields for courses.
     *
     * @param object $coursedata
     *
     * @return array $customfields
     */
    public static function getCustomFields($coursedata) {

        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        if ($custom_fields = $handler->get_fields()) {
            foreach ($custom_fields as $field) {
                $name = $field->get('shortname');
                if ($coursedata) {
                    $customfields[$name] = self::getFieldValue('customfield_' . $name, $coursedata);
                } else {
                    $customfields[$name] = $field->get('name');
                }
            }
        }
        return $customfields;
    }

    /**
     * Get user profile fields.
     *
     * @param object $coursedata
     * @param string $type 'course' or 'user'
     *
     * @return array $customfields
     */
    public static function getCustomUserFields() {

        global $DB;

        $customfields = array();
        $records = $DB->get_records('user_info_field');
        foreach ($records as $record) {
            if ($record->shortname == 'campusonline_student_uid' ||
                $record->shortname == 'campusonline_employee_uid') {
                continue;
            }
            $customfields[$record->shortname] = $record->name;
        }
        return $customfields;
    }

    /**
     * Gets a value for a field in Moodle, replacing tokens in configured values.
     *
     * @param string $field
     * @param array $data
     * @param string $type 'course' or 'user'
     *
     * @return string $value
     */
    public static function getFieldValue($field, $data, $type = 'course') {
        global $DB;

        // Get configured value.
        $fieldvalue = get_config('enrol_campusonline', $type . '_' . $field);

        // Replace tokens.
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fieldvalue = str_replace('{' . $key . '}', $value, $fieldvalue);
            }
        }

        return $fieldvalue;
    }

    /**
     * Converts object or array data into strings.
     *
     * @param mixed $value
     *
     * @return string $value
     */
    public static function normalizeValue($value) {
        if (is_object($value)) {
            $value = locallib::getObjectValue($value);
        } elseif (is_array($value)) {
            $value = implode(' ', $value);
        } else {
            $value = (string) $value;
        }

        return $value;
    }

    /**
     * Sets course custom fields course.
     *
     * @param string $courseid
     * @param array $coursedata
     *
     * @return boolean $updated
     */
    public static function setCourseCustomFields($courseid, $coursedata) {

        global $DB;

        $updated = false;
        $course = get_course($courseid);
        $customfields = self::getCustomFields($coursedata);

        // We update customfields directly via the DB,
        // because dealing with the customfield API is ridiculously complicated.
        foreach ($customfields as $shortname => $value) {
            if (!$field = $DB->get_record('customfield_field', ['shortname' => $shortname])) {
                continue;
            }
            if ($data = $DB->get_record('customfield_data', ['fieldid' => $field->id, 'instanceid' => $course->id])) {
                $oldvalue = $data->value;
                if ($oldvalue != $value) {
                    $data->intvalue = (int)$value;
                    $data->value = $value;
                    $data->charvalue = $value;
                    $DB->update_record('customfield_data', $data);
                    $updated = true;
                }
            } else {
                $data = new \stdClass();
                $data->fieldid = $field->id;
                $data->instanceid = $course->id;
                $data->value = $value;
                $data->intvalue = (int)$value;
                $data->charvalue = $value;
                $data->valueformat = 0;
                $data->timecreated = time();
                $data->timemodified = time();
                $DB->insert_record('customfield_data', $data);
                $updated = true;
            }
        }

        return $updated;
    }

    /**
     * Writes a log entry.
     *
     * @param string $event
     * @param string $message
     * @param int $status
     * @param string $courseid
     *
     */
    public static function writeLog($event, $message, $status, $courseid = null) {

        global $DB;

        if ($status < get_config('enrol_campusonline', 'loglevel')) {
            return;
        }

        $log = new \stdClass();
        $log->timestamp = time();
        $log->event = $event;
        $log->message = $message;
        $log->status = $status;
        $log->courseid = $courseid;
        $DB->insert_record('enrol_campusonline_logs', $log);
    }

    /**
     * Gets a value for a CAMPUSonline value that is an object.
     *
     * @param object $value
     *
     * @return string $value
     */
    private static function getObjectValue($value) {

        if (property_exists($value, 'name')) {
            $value = $value->name;
        }

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
