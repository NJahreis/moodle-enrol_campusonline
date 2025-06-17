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

namespace enrol_campusonline;

use DateTime;
use core\context\course as context_course;

/**
 * Class locallib
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class locallib {

    /**
     * Course fields available for mapping and their default value.
     * @var array
     */
    public const COURSE_FIELDS = [
        'summary' => '',
        'startdate' => '{semester:validFrom}',
        'enddate' => '{semester:validUntil}',
        'visible' => '1',
        'lang' => '{course:mainLanguageOfInstruction}',
        'groupmode' => '1',
        'groupmodeforce' => '0',
    ];

    /**
     * User fields not available for identification.
     * @var array
     */
    public const USER_ID_FIELDS_IGNORE = [
        'id',
        'username',
        'idnumber',
        'email',
    ];

    /**
     * User fields available for mapping and their default value.
     * @var array
     */
    public const USER_FIELDS = [
        'username' => 'co_{uid}',
        'email' => '{email}',
        'idnumber' => '{uid}',
        'firstname' => '{givenName}',
        'lastname' => '{surname}',
        'phone1' => '',
        'institution' => '',
        'department' => '',
    ];

    /**
     * User fields that cannot be empty.
     * @var array
     */
    public const USER_FIELDS_NOEMPTY = ['username', 'email'];

    /**
     * User fields that need to be unique.
     * @var array
     */
    public const USER_FIELDS_UNIQUE = ['username', 'email'];

    /**
     * CAMPUSonline internal custom user fields.
     * @var array
     */
    public const CO_USER_FIELDS = ['user_profile_field_campusonline_person_uid' => '{uid}',
    ];

    /**
     * Fields that use PARAM_BOOL instead of PARAM_TEXT.
     * @var array
     */
    public const BOOL_FIELDS = [
        'visible',
        'groupmode',
        'groupmodeforce',
    ];

    /**
     * Our own custom fields that should be ignored in mapping.
     * @var array
     */
    public const IGNORE_FIELDS = [
        'campusonline_person_uid',
        'campusonline_id_attempts',
        'campusonline_other_co_course_uids',
    ];

    /**
     * Add our enrolment method to a course.
     *
     * @param object $course
     *
     * @return void
     */
    public static function add_enrolment_method($course): void {

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

        } else if ($enrol->status == 1) {

            // Set to active.
            $enrol->status = 0;
            $enrol->timemodified = time();
            $DB->update_record('enrol', $enrol);
        }
    }

    /**
     * Builds a course from CAMPUSonline data.
     *
     * @param object $coursedata
     * @param string $groupname
     * @param string $groupuid
     *
     * @return array $course
     */
    public static function build_course($coursedata, $groupname = null, $groupuid = null): array {
        $course = [];
        $course['coursecategory'] = null;
        $course['idnumber'] = $coursedata['course:uid'];
        $course['shortname'] = self::get_field_value('course_shortname', $coursedata);
        $course['fullname'] = self::get_field_value('course_fullname', $coursedata);

        // Include group information.
        if ($groupname && $groupuid) {
            $course['idnumber'] .= ":$groupuid";
            $course['shortname'] .= ":$groupuid";
            $course['fullname'] .= ":$groupname";
        }

        // Map additional fields.
        foreach (self::COURSE_FIELDS as $field => $default) {
            $course[$field] = self::get_field_value('course_' . $field, $coursedata);
        }

        return $course;
    }

    /**
     * Builds a user from CAMPUSonline data.
     *
     * @param object|array $userdata
     *
     * @return array $user
     */
    public static function build_user($userdata): array {

        $user = [];
        $user['auth'] = self::get_field_value('user_auth', $userdata);
        $user['password'] = self::get_field_value('user_password', $userdata);

        // Set random password.
        if (!$user['password']) {
            $user['password'] = self::generate_password();
        }

        // Map additional fields.
        foreach (self::USER_FIELDS as $field => $default) {
            $value = self::get_field_value('user_' . $field, $userdata);

            // Sanitize usernames.
            if ($field == 'username') {
                $value = strtolower($value);
            }

            $user[$field] = $value;
        }

        return $user;
    }

    /**
     * Removes old logs.
     */
    public static function cleanup_logs() {
        global $DB;

        $duration = get_config('enrol_campusonline', 'logduration');
        $time = time() - $duration * 24 * 60 * 60;
        $DB->delete_records_select('enrol_campusonline_logs', "timestamp < $time");
    }

    /**
     * Gets custom fields.
     *
     * @param array $coursedata
     *
     * @return array $customfields
     */
    public static function get_custom_course_field_data($coursedata = []) {

        $customfields = [];
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        if ($customfields = $handler->get_fields()) {
            foreach ($customfields as $field) {
                $name = $field->get('shortname');
                if (!empty($coursedata)) {
                    $customfields[$name] = self::get_field_value('course_customfield_' . $name, $coursedata);
                } else {
                    // For settings.php.
                    $customfields[$name] = $field->get('name');
                }
            }
        }
        return $customfields;
    }

    /**
     * Get user profile fields.
     *
     * @param object $userdata
     *
     * @return array $customfields
     */
    public static function get_custom_user_field_data($userdata) {

        global $DB;

        $customfields = [];
        $records = $DB->get_records('user_info_field');
        foreach ($records as $record) {
            $name = $record->shortname;
            if ($userdata) {
                $customfields[$record->id] = self::get_field_value('user_profile_field_' . $name, $userdata);
            } else {
                // For settings.php.
                $customfields[$record->shortname] = $record->name;
            }

        }
        return $customfields;
    }

    /**
     * Gets a value for a field in Moodle, replacing tokens in configured values.
     *
     * @param string $field
     * @param array|object $data
     *
     * @return string $value
     */
    public static function get_field_value($field, $data) {

        // Get hardcoded defaults.
        if (array_key_exists($field, self::CO_USER_FIELDS)) {
            $fieldvalue = self::CO_USER_FIELDS[$field];

        } else {
            // Get configured value.
            $fieldvalue = get_config('enrol_campusonline', $field);
        }

        // Replace tokens.
        foreach ($data as $key => $value) {

            // Convert integers into string values.
            if (is_int($value)) {
                $value = (string)$value;
            }

            if (is_string($value)) {
                $fieldvalue = str_replace('{' . $key . '}', $value, $fieldvalue);
            }
        }

        // Remove leftover empty tokens.
        $fieldvalue = preg_replace('/\{[^}]*\}/', '', $fieldvalue);

        // Special case: lang field.
        if ($field == 'course_lang') {
            $fieldvalue = strtolower(substr($fieldvalue, 0, 2));
            $langs = array_keys(get_string_manager()->get_list_of_translations());
            if (!in_array($fieldvalue, $langs)) {
                $fieldvalue = '';
            }
        }

        return $fieldvalue;
    }

    /**
     * Gets organisation roles to sync into Moodle.
     *
     * @return array
     */
    public static function get_org_roles() {

        global $DB;

        $orgroles = [];

        $sql = "
            SELECT *
            FROM {role} role
            JOIN {role_context_levels} ctx ON ctx.roleid = role.id
            WHERE role.shortname LIKE :shortname
            AND ctx.contextlevel = :contextlevel";

        $params = [
            'shortname' => 'campusonline%',
            'contextlevel' => 40,
        ];

        if ($roles = $DB->get_records_sql($sql, $params)) {
            foreach ($roles as $role) {
                $orgroles[$role->roleid] = $role->name;
            }
        }
        return $orgroles;
    }

    /**
     * Gets a person UID for a user.
     *
     * @param int $userid
     *
     * @return string $person_uid
     */
    public static function get_person_uid($userid) {

        global $DB;

        if ($fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => 'campusonline_person_uid'])) {
            return $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => $fieldid]);
        }
        return null;
    }

    /**
     * Converts object or array data into strings.
     *
     * @param mixed $value
     *
     * @return string $value
     */
    public static function normalize_value($value) {
        if (is_object($value)) {
            $value = self::get_object_value($value);
        } else if (is_array($value)) {
            $value = implode(' ', $value);
        } else {
            $value = (string) $value;
        }

        // Convert dates into timestamps.
        // Create a new DateTime object from the date string.
        if (!$date = DateTime::createFromFormat('Y-m-d\TH:i:sP', $value)) {
            return $value;
        }

        // Check if the DateTime object was created successfully.
        $errors = $date->getLastErrors();
        if (($errors['warning_count'] ?? 0) == 0 &&
            ($errors['error_count'] ?? 0) == 0
        ) {
            return (string)$date->getTimestamp();
        } else {
            return $value;
        }
    }

    /**
     * Sets custom fields for course.
     *
     * @param string $courseid
     * @param array $coursedata
     *
     * @return void
     */
    public static function set_custom_course_fields($courseid, $coursedata) {

        global $DB;

        $updated = false;
        $course = get_course($courseid);
        $context = context_course::instance($courseid);
        $customfields = self::get_custom_course_field_data($coursedata);

        // We update customfields directly via the DB,
        // because dealing with the customfield API is ridiculously complicated.
        foreach ($customfields as $shortname => $value) {
            if (!$field = $DB->get_record('customfield_field', ['shortname' => $shortname])) {
                continue;
            }
            if (!$data = $DB->get_record('customfield_data', ['fieldid' => $field->id, 'instanceid' => $course->id])) {
                $data = new \stdClass();
                $create = true;
            } else {
                $create = false;
            }
            $data->fieldid = $field->id;
            $data->instanceid = $course->id;
            $data->value = $value;
            $data->intvalue = (int)$value;
            $data->charvalue = $value;
            $data->valueformat = 0;
            $data->timecreated = time();
            $data->timemodified = time();
            $data->contextid = $context->id;
            if ($create) {
                $DB->insert_record('customfield_data', $data);
            } else {
                $DB->update_record('customfield_data', $data);
            }
        }
    }

    /**
     * Sets user custom fields.
     *
     * @param object $user
     * @param array $person
     * @param boolean $onlyuid only set person uid.
     *
     * @return boolean $updated
     */
    public static function set_custom_user_fields($user, $person, $onlyuid = false) {

        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $updated = false;
        profile_load_data($user);
        if ($onlyuid) {
            $profilefields = self::CO_USER_FIELDS;
        } else {
            $profilefields = self::get_custom_user_field_data(null);
        }

        // Update profile fields.
        foreach ($profilefields as $profilefield => $name) {

            $fieldname = "profile_field_$profilefield";

            if ($value = self::get_field_value("user_profile_field_$profilefield", $person)) {
                if (!property_exists($user, $fieldname) || $user->$fieldname != $value) {
                    $user->$fieldname = $value;
                    $updated = true;
                }
            }
        }

        if ($updated) {
            profile_save_data($user);
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
     * @param \progress_trace $trace
     * @param int $indent
     */
    public static function write_log($event, $message, $status, $courseid = null, $trace = null, $indent = 0) {

        global $DB;

        // Write trace.
        if ($trace && (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET))) {
            $output = str_repeat(' ', $indent) . "- $message";
            $trace->output($output);
        }

        // Write log.
        if ($status < get_config('enrol_campusonline', 'loglevel')) {
            return;
        }

        // Write PHP log.
        if (get_config('enrol_campusonline', 'phplogging')) {
            debugging("enrol_campusonline: $event: $message");
        }

        // Truncate message to 255 chars.
        $message = substr($message, 0, 255);

        // Write moodle log.
        $log = new \stdClass();
        $log->timestamp = time();
        $log->event = $event;
        $log->message = $message;
        $log->status = $status;
        $log->courseid = $courseid;
        $DB->insert_record('enrol_campusonline_logs', $log);

    }

    /**
     * Generates a random password.
     *
     * @return string $password
     */
    private static function generate_password() {

        global $CFG;

        // Define character sets.
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialchars = '!@#$%^&*()-_=+{}[]<>?';

        // Ensure at least one character from each type.
        $password = '';
        $length = (int)$CFG->minpasswordlength;
        $length = $length / 4;
        for ($i = 0; $i < $length; $i++) {
            $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
            $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
            $password .= $numbers[random_int(0, strlen($numbers) - 1)];
            $password .= $specialchars[random_int(0, strlen($specialchars) - 1)];
        }

        // Shuffle because why not.
        return str_shuffle($password);
    }

    /**
     * Gets a value for a CAMPUSonline value that is an object.
     *
     * @param object $value
     *
     * @return string $value
     */
    private static function get_object_value($value) {

        if (property_exists($value, 'name')) {
            $value = $value->name;
        }

        if (property_exists($value, 'value')) {
            $lang = 'de'; // TODO: make configurable?
            if (property_exists($value->value, $lang)) {
                return $value->value->$lang;
            }
        }

        $value = (array)$value;
        $value = implode(' ', $value);

        return $value;
    }
}
