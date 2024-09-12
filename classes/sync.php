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
 * Class sync
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_campusonline;

use moodle_url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

defined('MOODLE_INTERNAL') || die;

class sync {

    private $config;
    private $error;
    private $token;
    private $trace;
    private $employee_uid_fieldid;
    private $student_uid_fieldid;

    /**
     * Constructor.
     */
    public function __construct($trace) {

        global $DB;

        // Remove old logs.
        locallib::cleanupLogs();

        // Get settings.
        $this->config = get_config('enrol_campusonline');
        $this->trace = $trace;
        $path = $this->config->endpoint;
        $clientid = $this->config->clientid;
        $secret = $this->config->clientsecret;

        try {
            $this->student_uid_fieldid = $DB->get_record('user_info_field', ['shortname' => 'campusonline_student_uid'])->id;
            $this->employee_uid_fieldid = $DB->get_record('user_info_field', ['shortname' => 'campusonline_employee_uid'])->id;
        } catch (Exception $e) {
            throw new moodle_exception('error:uidfieldnotfound', 'enrol_campusonline', '', $shortname);
        }

        // Make request.
        if ($path && $clientid && $secret) {
            $path = rtrim($path, '/');
            $url = $path . '/public/sec/auth/realms/CAMPUSonline_SP/protocol/openid-connect/token';
            $client = new Client([
                'base_uri' => $url,
                'timeout' => 10.0,
                'connect_timeout' => 2.0,
            ]);
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientid,
                    'client_secret' => $secret
                ]
            ]);

            // Analyze response.
            $response_body = $response->getBody()->getContents();
            $response_object = json_decode($response_body, false);
            $response_array = (array)$response_object;

            // Analyze response.
            if (array_key_exists('error', $response_array)) {
                $this->error = $response_array['error'] . ': ' . $response_array['error_description'];
            } elseif (array_key_exists('access_token', $response_array)) {
                $this->token = $response_array['access_token'];
            } else {
                $this->error = $response_array['error'] . ': ' . get_string('error:unknown', 'enrol_campusonline');
            }
        }
    }

    /**
     * Checks if connection was successful.
     */
    public function isConnected() {
        return !empty($this->token);
    }

    /**
     * Returns the error message.
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Calls REST API.
     *
     * @param string $endpoint
     * @param array $query
     * @param string $method
     * @param string $cursor
     * @param array $items
     *
     * @return object
     */
    private function restCall($endpoint, $query = null, $method = 'GET', $items = null) {

        // Set params.
        $url = $this->config->endpoint . '/' . $endpoint;
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 10.0,
            'connect_timeout' => 2.0,
        ]);

        // Make request.
        $response = $client->request($method, $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ],
            'query' => $query,
            'json' => $query
        ]);

        // Analyze response.
        $response_body = $response->getBody()->getContents();
        $response_object = json_decode($response_body, false);

        // Attach previous items.
        if ($items) {
            $response_object->items = array_merge($items, $response_object->items);
        }

        // Check if there are more results to fetch.
        if (!array_key_exists('limit', $_GET) && property_exists($response_object, 'nextCursor')) {
            $items = $response_object->items;
            $query['cursor'] = $response_object->nextCursor;
            $response_object = $this->restCall($endpoint, $query, $method, $items);
        }

        return $response_object;
    }

    /**
     * Gets a category for a course.
     *
     * @param array $coursedata
     *
     * @return string $categoryid
     */
    public function getCourseCategory($coursedata) {

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

                if (get_config('enrol_campusonline', 'createcoursecatetories') == 0) {

                    // Log error.
                    $message = "ERROR: could not find Moodle course category $name and not allowed to create new categories. Create the category manually, or configure CAMPUSOnline to be able to create new categories.";
                    $this->trace->output(" - $message");
                    locallib::writeLog('create_category', $message, 2);

                    return false;

                } else {

                    // Log creation.
                    $message = "SUCCESS: creating new Moodle course category $name.";
                    $this->trace->output(" - $message");
                    locallib::writeLog('create_category', $message, 0);

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

        return $categoryid;
    }

    /**
     * Gets courses for preview or sync.
     *
     * @param int $limit how many courses to get.
     * @param string $courseid if provided, only fetches this one course.
     * @return array
     */
    public function getCourses($limit = null, $course_uids = null) {

        $allcourses = array();

        // Get semester(s).
        $semesters = get_config('enrol_campusonline', 'semester');
        $semesters = explode(',', $semesters);

        foreach ($semesters as $semester) {

            $semester = trim($semester);

            // Get courses.
            $endpoint = 'co-tm-core/course/api/courses';
            if ($course_uids) {
                // Only request single course.
                $query['course_uids'] = $course_uids;
            } else {
                // Request all courses for configured semester(s).
                $query = [
                    'semester_key' => $semester,
                    'only_elearning_courses' => 'true',
                    'limit' => $limit,
                ];
            }
            $result = $this->restCall($endpoint, $query);

            // Analyze response.
            if (property_exists($result, 'items')) {
                $courses = $result->items;
                $courses = $this->enrichCourses($courses);

            } else {
                $courses = array();
            }

            $allcourses = array_merge($allcourses, $courses);

            // Stop if we only want one course.
            if ($course_uids) {
                break;
            }
        }

        return $allcourses;
    }

    /**
     * Gets enrolments for a course from CAMPUSonline.
     *
     * @param object $course
     *
     * @return array $enrolments
     */
    public function getEnrolments($course) {

        // Get student enrolments.
        $endpoint = 'co-tm-core/course/api/registrations';
        $query = [
            'course_uid' => $course->idnumber,
        ];
        $result = $this->restCall($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $enrolments = $result->items;
        } else {
            $enrolments = array();
        }

        // Get teacher enrolments.
        $endpoint = 'co-tm-core/course/api/lectureships';
        $query = [
            'course_uid' => $course->idnumber,
        ];
        $result = $this->restCall($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $enrolments = array_merge($result->items, $enrolments);
        }

        return $enrolments;
    }

    /**
     * Gets lectureship functions from CAMPUSonline.
     */
    public function getLectureshipFunctions() {
        $endpoint = 'co-tm-core/course/api/lectureship-functions';
        $result = $this->restCall($endpoint);

        // Analyze response.
        $lectureship_functions = array();
        if (property_exists($result, 'items')) {
            foreach ($result->items as $item) {
                $lectureship_functions[] = $item->key;
            }
        }

        return $lectureship_functions;
    }

    /**
     * Gets the Moodle User ID of a CAMPUSOnline user via its uid or email.
     *
     * @param string $uid
     * @param string $usertype 'student' or 'employee'
     *
     * @return int $userid
     */
    public function getMoodleUserId($uid, $usertype) {

        global $DB;

        // Get field id of our user profile field.
        $property = $usertype . '_uid_fieldid';
        $fieldid = $this->$property;

        // Get user id via uid in our user profile fields..
        $sql = "SELECT * FROM {user_info_data} WHERE fieldid = ? AND data = ?";
        $params = array('fieldid' => $fieldid, 'data' => $uid);
        if ($records = $DB->get_records_sql($sql, $params)) {
            $record = reset($records);
            return $record->userid;
        }

        // Get full person data from CAMPUSOnline.
        $person = $this->getPerson($uid, $usertype);
        $persondata = $this->getPersonData($person->uid);
        $userdata = array_merge((array) $person, (array) $persondata);

        // Try to find user via username.
        $value = locallib::getFieldValue('username', $userdata, 'user');
        if ($record = $DB->get_record('user', ['username' => $value])) {
            $userid = $record->id;
            $this->updateMoodleUserUids($userid, $uid, $usertype);
            return $userid;
        }

        // Try to find user via secondary identifier.
        $field = get_config('enrol_campusonline', 'usermoodlefield');
        $valueconfig = get_config('enrol_campusonline', 'usercovalue');

        if ($field && $valueconfig) {

            $value = locallib::getFieldValue($field, $userdata, 'user');

            // Try to find user using secondary identifier.
            if ($user = $DB->get_record('user', [$field => $value])) {
                $userid = $user->id;
                $this->updateMoodleUserUids($userid, $uid, $usertype);
                return $userid;
            }
        }

        // Log warning.
        $message = "WARNING could not find Moodle user for CAMPUSOnline $usertype $uid.";
        $this->trace->output("   - $message");
        locallib::writeLog('get_user', $message, 1);

        return null;
    }

    /**
     * Gets data for a student from CAMPUSonline.
     *
     * @param string $uid
     * @param bool $usertype 'student' or 'employee'
     *
     * @return array $studentdata
     */
    public function getPerson($uid, $usertype) {

        // Get person.
        if ($usertype == 'student') {
            $endpoint = "/co-sm-core/study/api/student-persons/$uid";
        } else {
            $endpoint = "/co-brm-core/org/api/employee-persons/$uid";
        }
        $person = $this->restCall($endpoint);
        $person->__type = $usertype;

        return $person;
    }

    /**
     * Gets additional person data from CAMPUSOnline.
     *
     * @param string $uid
     *
     * @return object $persondata
     */
    public function getPersonData($uid) {

        $endpoint = "/co-brm-core/org/api/personal-claims/$uid";
        $query = [
            'claims' => get_config('enrol_campusonline', 'userclaims'),
        ];

        return $this->restCall($endpoint, $query);
    }

    /**
     * Gets persons from CAMPUSonline for preview.
     *
     * @param int $limit
     *
     * @return array $persons
     */
    public function getPersons($limit = null) {

        $limit = $limit / 2;

        // Get employees.
        $endpoint = 'co-brm-core/org/api/employee-persons';
        $query = [
            'limit' => $limit,
        ];
        $result = $this->restCall($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $employees = $result->items;
            foreach ($employees as $employee) {
                $employee->__type = 'employee';
            }
        } else {
            $employees = array();
        }

        // Get students.
        $endpoint = '/co-sm-core/study/api/student-persons/';
        $query = [
            'limit' => $limit,
        ];
        $result = $this->restCall($endpoint, $query);
        if (property_exists($result, 'items')) {
            $students = $result->items;
            foreach ($students as $student) {
                $student->__type = 'student';
            }
        } else {
            $students = array();
        }

        // Merge and return.
        $persons['employees'] = $employees;
        $persons['students'] = $students;
        return $persons;
    }

    /**
     * Syncs courses.
     *
     * @param string $courseid if provided, only syncs this one course.
     * @return void
     */
    public function syncCourses($course_uids = null) {

        global $CFG, $DB;

        require_once("$CFG->dirroot/course/lib.php");

        // Get course(s).
        if ($course_uids) {
            $courses = $this->getCourses(null, $course_uids);
        } else {
            $courses = $this->getCourses();
        }

        $number = count($courses);
        $this->trace->output("Syncing $number courses ...");

        // Sync courses.
        foreach ($courses as $coursedata) {

            $uid = $coursedata['course:uid'];
            $this->trace->output(" - Syncing CAMPUSonline course $uid");

            // Prepare new course data.
            $newcourse = locallib::buildCourse($coursedata);
            $customfields = locallib::getCustomFields($coursedata);

            // Get category.
            if (!$newcourse['category'] = $this->getCourseCategory($coursedata)) {
                continue;
            }

            if (!$course = $DB->get_record('course', ['idnumber' => $uid])) {

                // Create new course.
                $course = new \stdClass();
                foreach ($newcourse as $key => $value) {
                    $course->$key = $value;
                }

                // Create course and log course creation.
                if (create_course($course)) {

                    $courseid = $course->id;

                    // Add custom fields.
                    locallib::setCourseCustomFields($courseid, $coursedata);

                    // Write back URL to CAMPUSonline.
                    $this->setMoodleCourseUrl($course);

                    // Log success.
                    $message = "SUCCESS: created Moodle course $courseid for CAMPUSonline course $uid.";
                    $this->trace->output(" - $message");
                    locallib::writeLog('create_course', $message, 0, $courseid);

                } else {

                    // Log error.
                    $message = "ERROR: could not reate Moodle course for CAMPUSonline course $uid.";
                    $this->trace->output(" - $message");
                    locallib::writeLog('create_course', $message, 2);
                }

                // Add our enrolment method.
                locallib::addEnrolmentMethod($course);

            } else {

                $courseid = $course->id;

                // Check if update is necessary.
                $needsupdate = false;
                foreach ($newcourse as $key => $value) {

                    // Skip category.
                    if ($key == 'coursecategory') {
                        continue;
                    }

                    if ($course->$key != $value) {

                        // Skip empty values.
                        if (!$course->$key && !$value) {
                            continue;
                        }
                        $this->trace->output($course->$key);
                        $needsupdate = true;
                        break;
                    }
                }

                // Update.
                if ($needsupdate) {

                    // Skip.
                    if (get_config('enrol_campusonline', 'updateexistingcourses') == 0) {
                        $message = " - Skipped existing Moodle course $courseid for CAMPUSonline course $uid.";
                        $this->trace->output($message);
                        continue;
                    }

                    // Update course.
                    foreach ($newcourse as $key => $value) {
                        $course->$key = $value;
                    }
                    $DB->update_record('course', $course);

                    // Log success.
                    $message = "SUCCESS: updated Moodle course $courseid with data from CAMPUSonline course $uid.";
                    $this->trace->output(" - $message");
                    locallib::writeLog('update_course', $message, 0, $courseid);
                }

                // Set course custom fields.
                if (locallib::setCourseCustomFields($courseid, $coursedata)) {
                    $message = "SUCCESS: updated course custom fields in Moodle course $courseid with data from CAMPUSonline course $uid.";
                    $this->trace->output(" - $message");
                    locallib::writeLog('update_course', $message, 0, $courseid);
                }
            }

            // Sync enrolments.
            $this->syncEnrolments($course);
        }
    }

    /**
     * Syncs enrolments.
     *
     * @param object $course
     *
     * @return void
     */
    public function syncEnrolments($course) {

        global $CFG, $DB;
        require_once("$CFG->dirroot/user/lib.php");
        $courseid = $course->id;

        // Check if enrolment method is active.
        if (!$enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'campusonline', 'status' => 0])) {
            $message = "WARNING: Skipping enrolments for Moodle course $courseid - enrolment method has been deactivated.";
            $this->trace->output("   - $message");
            locallib::writeLog('enrol_user', $message, 1, $courseid);
            return;
        }

        // Sum up existing enrolments in Moodle course, to save on DB queries.
        $existing_enrolments_userids = array();
        $existing_enrolments = $DB->get_records('user_enrolments', ['enrolid' => $enrol->id]);
        foreach ($existing_enrolments as $existing_enrolment) {
            $existing_enrolments_userids[] = $existing_enrolment->userid;
        }

        // Get enrolments from CAMPUSonline.
        $enrolments = $this->getEnrolments($course);
        foreach ($enrolments as $enrolment) {

            // Get role.
            if (property_exists($enrolment, 'functionKey')) {
                $usertype = 'employee';
                $roleid = get_config('enrol_campusonline', 'role_' . $enrolment->functionKey);
            } else {
                $usertype = 'student';
                $roleid = get_config('enrol_campusonline', 'studentrole');
            }

            // Skip if role should not be synced.
            if (!$roleid || $roleid == 0) {
                continue;
            }

            // Get Moodle user.
            $uid = $enrolment->personUid;
            $userid = $this->getMoodleUserId($uid, $usertype);

            // Create new user if needed & allowed.
            if (!$userid) {
                if (get_config('enrol_campusonline', 'enrolsynccreateusers')) {

                    if (!$userid = $this->createMoodleUser($uid, $usertype)) {
                        continue;
                    }

                } else {

                    // Log warning.
                    $message = "WARNING: Skipping enrolment for CAMPUSonline user $uid - user does not exist in Moodle.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('enrol_user', $message, 1, $courseid);
                    continue;
                }
            }

            // Create enrolment if needed.
            if (!in_array($userid, $existing_enrolments_userids)) {

                // Create enrolment.
                $enrolment = new \stdClass();
                $enrolment->enrolid = $enrol->id;
                $enrolment->userid = $userid;
                $enrolment->timestart = time();
                $enrolment->timeend = 0;
                $enrolment->modifierid = 0;
                $enrolment->timecreated = time();
                $enrolment->timemodified = time();
                $DB->insert_record('user_enrolments', $enrolment);

                // Log success.
                $message = "SUCCESS: Enrolled Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
                locallib::writeLog('enrol_user', $message, 0, $courseid);
            }

            // Add role.
            $context = \context_course::instance($courseid);
            if (!user_has_role_assignment($userid, $roleid, $context->id)) {
                $success = role_assign($roleid, $userid, $context->id);
                $message = "Assigned role $roleid to Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
            }

            // Remove roles.
            $roles = get_user_roles($context, $userid);
            foreach ($roles as $role) {
                if ($role->roleid != $roleid) {
                    $success = role_unassign($role->roleid, $userid, $context->id);
                    $message = "Removed role $role->roleid from Moodle user $userid in Moodle course $courseid.";
                    $this->trace->output("   - $message");
                }
            }
        }
    }

    /**
     * Creates a new Moodle user.
     *
     * @param string $uid
     * @param string $usertype 'student' or 'employee'
     *
     * @return int $userid
     */
    private function createMoodleUser($uid, $usertype) {

        global $CFG;

        // Get full person data from CAMPUSOnline.
        $person = $this->getPerson($uid, $usertype);
        $persondata = $this->getPersonData($person->uid);
        $userdata = array_merge((array) $person, (array) $persondata);

        // Build user.
        $user = new \stdClass();
        foreach (locallib::USER_FIELDS as $field => $default) {
            $value = locallib::getFieldValue($field, $userdata, 'user');

            // Sanitize usernames.
            if ($field == 'username') {
                $value = strtolower($value);
            }

            $user->$field = $value;
        }
        $user->auth = locallib::getFieldValue('auth', $userdata, 'user');
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;
        $user->password = locallib::getFieldValue('password', $userdata, 'user');

        // Create user.
        if ($userid = user_create_user($user)) {
            $message = "SUCCESS: created Moodle user $userid for CAMPUSonline user $uid.";
            $status = 0;

        } else {
            $message = "ERROR: could not create Moodle user for CAMPUSonline user $uid.";
            $status = 2;
        }

        $this->trace->output("   - $message");
        locallib::writeLog('create_user', $message, $status);
        return $userid;
    }

    /**
     * Enriches courses with additional data from other endpoints.
     *
     * @param array $courses
     *
     * @return array $courses
     */
    private function enrichCourses($courses) {

        $enriched_courses = array();
        foreach ($courses as $course) {

            $sanitized_course = array();
            $course = (array)$course;

            // Values from course endpoint.
            foreach ($course as $key => $value) {
                $value = locallib::normalizeValue($value);
                $sanitized_course["course:$key"] = $value;
            }

            // Get values from org endpoint. TODO: einmal abholen.
            $org = $this->restCall('co-brm-core/org/api/organisations/' . $course['organisationUid']);
            if (property_exists($org, 'items')) {
                $org = reset($org->items);
            }

            // Attach values from org endpoint.
            $org = (array)$org;

            foreach ($org as $key => $value) {
                $value = locallib::normalizeValue($value);
                $sanitized_course["org:$key"] = $value;
            }

            $enriched_courses[] = $sanitized_course;
        }

        return $enriched_courses;
    }

    /**
     * Sets the moodle course URL in CAMPUSonline.
     */
    private function setMoodleCourseUrl($course) {

        $endpoint = 'co-tm-core/course/api/e-learning-infos';
        $moodle_url = new moodle_url('/course/view.php', array('id' => $course->id));
        $url = $moodle_url->__toString();
        $query = [
            'courseUid' => $course->idnumber,
            'externalUrl' => $url,
        ];

        return $this->restCall($endpoint, $query, 'POST');
    }

    /**
     * Updates user profile fields holding CAMPUSonline UIDs.
     *
     * @param string $userid Moodle user id
     * @param string $uid CAMPUSonline person uid
     * @param string $usertype 'student' or 'employee'
     *
     */
    private function updateMoodleUserUids($userid, $uid, $usertype) {

        global $DB;

        // Get field id of our user profile field.
        $property = $usertype . '_uid_fieldid';
        $fieldid = $this->$property;

        // Update field value.
        $sql = "SELECT * FROM {user_info_data} WHERE fieldid = ? AND userid = ?";
        $params = array('fieldid' => $fieldid, 'userid' => $userid);
        $records = $DB->get_records_sql($sql, $params);
        if ($records) {
            $record = reset($records);
            $record->data = $uid;
            $DB->update_record('user_info_data', $record);
        } else {
            $record = new \stdClass();
            $record->userid = $userid;
            $record->fieldid = $fieldid;
            $record->data = $uid;
            $DB->insert_record('user_info_data', $record);
        }

        // Log UID update.
        $fieldname = 'campusonline_' . $usertype . '_uid';
        $message = "SUCCESS: Updated Moodle user $userid profile field $fieldname with CAMPUSonline uid $uid.";
        $this->trace->output(" - $message");
        locallib::writeLog('update_user', $message, 0, $userid);
    }

}
