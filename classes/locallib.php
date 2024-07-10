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

    // Course fields available for mapping.
    public const COURSE_FIELDS = ['summary',
                                  'format',
                                  'startdate',
                                  'enddate',
                                  'visible',
                                  'lang',
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
        $course['idnumber'] = $coursedata['course:uid'];
        $course['shortname'] = self::getCourseField('shortname', $coursedata);
        $course['fullname'] = self::getCourseField('fullname', $coursedata);

        // Map additional fields.
        foreach(self::COURSE_FIELDS as $field) {
            $course[$field] = self::getCourseField($field, $coursedata);
        }

        return $course;
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
     * Gets a category for a course.
     *
     * @param array $coursedata
     * @param progress_trace $trace
     *
     * @return string $categoryid
     */
    public static function getCourseCategory($coursedata, $trace) {

        global $DB;

        $categoryid = get_config('enrol_campusonline', 'rootcoursecategory');
        $subcategories = get_config('enrol_campusonline', 'subcategories');
        $subcategories = explode('\\', $subcategories);

        foreach ($subcategories as $name) {
            foreach ($coursedata as $key => $value) {
                if (is_string($value)) {
                    $name = str_replace('{' . $key . '}', $value, $name);
                }
            }
            $category = $DB->get_record('course_categories', ['name' => $name, 'parent' => $categoryid]);

            if (!$category) {

                $log = new \stdClass();
                $log->timestamp = time();
                $log->event = 'create_category';

                if (get_config('enrol_campusonline', 'createcoursecatetories') == 0) {

                    // Log error.
                    $log->status = 2;
                    $message = "Could not find Moodle course category $name and not allowed to create new categories. Create the category manually, or configure CAMPUSOnline to be able to create new categories.";
                    $log->message = $message;
                    $trace->output(" - $message");
                    return false;

                } else {

                    // Log creation.
                    $log = new \stdClass();
                    $log->status = 0;
                    $message = "Creating new Moodle course category $name.";
                    $log->message = $message;
                    $trace->output(" - $message");

                    // Create new category.
                    $categorydata = new \stdClass();
                    $categorydata->name = $name;
                    $categorydata->parent = $categoryid;
                    $categorydata->description = 'Created by CAMPUSOnline';
                    $category = \core_course_category::create($categorydata);
                    $categoryid = $category->id;
                }

            } else {
                $category = $DB->get_record('course_categories', ['name' => $name, 'parent' => $categoryid]);
                $categoryid = $category->id;
            }
        }

        // TODO: implement.
        return $categoryid;
    }

    /**
     * Gets a category for a course.
     *
     * @param object $coursedata
     *
     * @return array $customfields
     */
    public static function getCourseCustomFields($coursedata) {
        $customfields = array();
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        if ($custom_fields = $handler->get_fields()) {
            foreach ($custom_fields as $field) {
                $name = $field->get('shortname');
                if ($coursedata) {
                    $customfields[$name] = self::getCourseField('customfield_' . $name, $coursedata);
                } else {
                    $customfields[$name] = $field->get('name');
                }
            }
        }
        return $customfields;
    }

    /**
     * Gets a value for a course field.
     *
     * @param string $field
     * @param array $coursedata
     *
     * @return string $value
     */
    public static function getCourseField($field, $coursedata) {
        global $DB;

        $fieldvalue = get_config('enrol_campusonline', 'course_' . $field);

        foreach ($coursedata as $key => $value) {
            if (is_string($value)) {
                $fieldvalue = str_replace('{' . $key . '}', $value, $fieldvalue);
            }
        }

        return $fieldvalue;
    }

    /**
     * Gets a the Moodle User ID of a CAMPUSOnline user via its uid or email.
     *
     * @param string $uid
     * @param string $usertype 'student' or 'employee'
     *
     * @return int $userid
     */
    public static function getMoodleUserId($uid, $type) {

        global $DB;

        // Get field id of our user profile field.
        $shortname = 'campusonline_' . $type . '_uid';
        if (!$field = $DB->get_record('user_info_field', ['shortname' => $shortname])) {
            throw new moodle_exception('error:uidfieldnotfound', 'enrol_campusonline', '', $shortname);
        }

        // Get user id via uid.
        $sql = "SELECT * FROM {user_info_data} WHERE data = ?";
        $params = array('data' => $uid);
        $data = $DB->get_records_sql($sql, $params);
        if ($data) {
            return $data->userid;
        }

        // TODO: Get user id via email.


        // TODO: Save uid in our custom user profile field.
        return null;
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
        $customfields = self::getCourseCustomFields($coursedata);

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
