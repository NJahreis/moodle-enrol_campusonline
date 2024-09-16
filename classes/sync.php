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
    private $person_uid_fieldid;
    private $orgdata;
    private $semesterdata;

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
            $this->person_uid_fieldid = $DB->get_record('user_info_field', ['shortname' => 'campusonline_person_uid'])->id;
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
    private function restCall($endpoint, $query = null, $method = 'GET', $alwayspage = false, $items = null) {

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

        // Set paging.
        if (!array_key_exists('limit', $_GET)) {
            $page = true;
        } else {
            $page = $alwayspage;
        }

        // Check if there are more results to fetch.
        if ($page && property_exists($response_object, 'nextCursor')) {
            $items = $response_object->items;
            $query['cursor'] = $response_object->nextCursor;
            $response_object = $this->restCall($endpoint, $query, $method, true, $items);
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
     * @param string $course_uids if provided, only fetches these courses.
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
                // Only request specific courses.
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

            // Stop if we specified course_uids, since semester filter will be ignored anyways.
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

        // Get user id via uid in our user profile field.
        $fieldid = $this->person_uid_fieldid;
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

        // Try to find user via fallback identifiers.
        $fields = locallib::USER_ID_FIELDS_IGNORE;
        $fields = array_diff($fields, ['id']);

        foreach ($fields as $field) {
            $valueconfig = get_config('enrol_campusonline', "user_$field");

            if ($field && $valueconfig) {
                $value = locallib::getFieldValue('user_' . $field, $userdata);

                if ($user = $DB->get_record('user', [$field => $value])) {
                    $userid = $user->id;
                    $this->updateMoodleUserUids($userid, $uid, $usertype);

                    // Log success.
                    $message = "SUCCESS: found Moodle user $userid for CAMPUSOnline $usertype $uid via the value for field $field. UIDs will be updated in Moodle.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('get_user', $message, 0);

                    return $userid;
                }
            }
        }

        // Try to find user via custom fallback.
        if ($field = get_config('enrol_campusonline', 'usermoodlefield')) {
            $fields[] = $field;
        }

        // Log warning.
        $message = "WARNING: could not find Moodle user for CAMPUSOnline $usertype $uid.";
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
     * @return array $persondata
     */
    public function getPersonData($uid) {

        $endpoint = "/co-brm-core/pers/api/personal-claims";
        $query = [
            'claim' => 'CO_CLAIM_ALL',
            'person_uid' => $uid,
        ];

        $result = $this->restCall($endpoint, $query);

        // Log error.
        if (!property_exists($result, 'items') || empty($result->items)) {
            $message = "WARNING: could not get full person data for CAMPUSonline user $uid.";
            $this->trace->output("   - $message");
            locallib::writeLog('get_user_data', $message, 1);
            return array();
        }

        // Return persondata.
        return $result->items[0];
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
     * Syncs modified courses.
     *
     * @return void
     */
    public function syncCourseDelta() {

        // Set timeframe.
        $timeframe = get_config('enrol_campusonline', 'modificationtimeframe');

        // Get todays date minus timeframe days.
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $timeframe . 'D'));
        $date = $date->format('Y-m-d');

        // Get semester(s).
        $semesters = get_config('enrol_campusonline', 'semester');
        $semesters = explode(',', $semesters);

        // Get modified courses.
        $course_uids = array();
        $components = ['courses', 'registrations', 'lectureships'];
        foreach ($semesters as $semester) {

            foreach ($components as $component) {
                $endpoint = "co-tm-core/course/api/$component/modifications";
                $query = [
                    'since' => $date,
                    'semestery_key' => $semester
                ];
                $result = $this->restCall($endpoint, $query);
                if (property_exists($result, 'items')) {
                    foreach ($result->items as $item) {
                        $course_uids[$item->courseUid] = 'modified';
                    }
                }
            }
        }

        // Sync modified courses.
        $course_uids = array_keys($course_uids);
        foreach ($course_uids as $course_uid) {
            $this->syncCourses($course_uid);
        }

    }

    /**
     * Syncs courses.
     *
     * @param string $course_uids if provided, only syncs these courses.
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

        // Get config.
        $updatecourseurls = get_config('enrol_campusonline', 'updatecourseurls');
        $updatecourses = get_config('enrol_campusonline', 'updateexistingcourses');

        // Start output.
        $number = count($courses);
        $this->trace->output("Syncing $number courses ...");

        // Sync courses.
        foreach ($courses as $coursedata) {

            $uid = $coursedata['course:uid'];
            $this->trace->output(" - Syncing CAMPUSonline course $uid");

            // Prepare new course data.
            $newcourse = locallib::buildCourse($coursedata);

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
                    locallib::setCustomCourseFields($courseid, $coursedata);

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

                // Update course URL in CAMPUSonline.
                if ($updatecourseurls == 1) {
                    $this->setMoodleCourseUrl($course);
                }

                // Skip.
                if (!$course_uids && $updatecourses == 0) {
                    continue;
                }

                // Update course.
                foreach ($newcourse as $key => $value) {
                    $course->$key = $value;
                }
                $DB->update_record('course', $course);

                // Update course custom fields.
                locallib::setCustomCourseFields($courseid, $coursedata);

                // Log success.
                $message = "SUCCESS: updated Moodle course $courseid with data from CAMPUSonline course $uid.";
                $this->trace->output(" - $message");
                locallib::writeLog('update_course', $message, 0, $courseid);
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
        $context = \context_course::instance($courseid);

        // Check if enrolment method is active.
        if (!$enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'campusonline', 'status' => 0])) {
            $message = "WARNING: skipping enrolments for Moodle course $courseid - enrolment method has been deactivated.";
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
        $assigned_roles = array();
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
                    $message = "WARNING: skipping enrolment for CAMPUSonline user $uid - user does not exist in Moodle.";
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
                $existing_enrolments_userids[] = $userid;

                // Log success.
                $message = "SUCCESS: enrolled Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
                locallib::writeLog('enrol_user', $message, 0, $courseid);
            }

            // Add role.
            if (!user_has_role_assignment($userid, $roleid, $context->id)) {
                $success = role_assign($roleid, $userid, $context->id);
                $message = "Assigned role $roleid to Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
            }

            // Add to assigned roles for later cleanup.
            $assigned_roles[$userid][] = $roleid;
        }

        // Cleanup - remove roles and suspend empty enrolments.
        $enrolid = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'campusonline'])->id;
        $moodle_co_enrolments = $DB->get_records('user_enrolments', ['enrolid' => $enrolid, 'status' => 0]);

        foreach ($moodle_co_enrolments as $moodle_co_enrolment) {

            $userid = $moodle_co_enrolment->userid;

            // Check if user is enrolled via our own enrolment method.
            if (!$DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $userid, 'status' => 0])) {
                continue;
            }

            // Remove roles.
            $roles = get_user_roles($context, $userid);
            foreach ($roles as $role) {
                if (!array_key_exists($userid, $assigned_roles) || !in_array($role->roleid, $assigned_roles[$userid])) {
                    $success = role_unassign($role->roleid, $userid, $context->id);
                    $message = "Removed role $role->roleid from Moodle user $userid in Moodle course $courseid.";
                    $this->trace->output("   - $message");
                }
            }

            // Suspend enrolments with no roles are left.
            $roles = get_user_roles($context, $userid);
            if (empty($roles)) {
                $enrolment = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $userid]);
                $enrolment->status = 1;
                $DB->update_record('user_enrolments', $enrolment);
                $message = "Suspended enrolment for Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
            }
        }
    }

    /**
     * Syncs users.
     *
     * @return void
     */
    public function syncUsers() {

        echo "not yet implemented";
        die();

        global $CFG, $DB;

        require_once("$CFG->dirroot/course/lib.php");

        // Get config.
        $updateemails = get_config('enrol_campusonline', 'updatecourseurlsuser_allowemailupdate');

        // Get users.
        $users = $this->getUsers();

        // Start output.
        $number = count($courses);
        $this->trace->output("Syncing $number courses ...");

        // Sync courses.
        foreach ($courses as $coursedata) {

            $uid = $coursedata['course:uid'];
            $this->trace->output(" - Syncing CAMPUSonline course $uid");

            // Prepare new course data.
            $newcourse = locallib::buildCourse($coursedata);

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
                    locallib::setCustomCourseFields($courseid, $coursedata);

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

                // Update course URL in CAMPUSonline.
                if ($updatecourseurls == 1) {
                    $this->setMoodleCourseUrl($course);
                }

                // Skip.
                if (!$course_uid && $updatecourses == 0) {
                    continue;
                }

                // Update course.
                foreach ($newcourse as $key => $value) {
                    $course->$key = $value;
                }
                $DB->update_record('course', $course);

                // Update course custom fields.
                locallib::setCustomCourseFields($courseid, $coursedata);

                // Log success.
                $message = "SUCCESS: updated Moodle course $courseid with data from CAMPUSonline course $uid.";
                $this->trace->output(" - $message");
                locallib::writeLog('update_course', $message, 0, $courseid);
            }

            // Sync enrolments.
            $this->syncEnrolments($course);
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
            $value = locallib::getFieldValue('user_' . $field, $userdata);

            // Sanitize usernames.
            if ($field == 'username') {
                $value = strtolower($value);
            }

            $user->$field = $value;
        }
        $user->auth = locallib::getFieldValue('user_auth', $userdata);
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;
        $user->password = locallib::getFieldValue('user_password', $userdata);

        // Create user.
        if ($userid = user_create_user($user)) {
            $message = "SUCCESS: created Moodle user $userid for CAMPUSonline user $uid.";
            $status = 0;

        } else {
            $message = "ERROR: could not create Moodle user for CAMPUSonline user $uid.";
            $status = 2;
        }

        // Update custom fields.
        locallib::setCustomUserFields($userid, $userdata);

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

        $this->getOrgData();
        $this->getSemesterData();

        $enriched_courses = array();
        foreach ($courses as $course) {

            $sanitized_course = array();
            $course = (array)$course;

            // Values from course endpoint.
            foreach ($course as $key => $value) {
                $value = locallib::normalizeValue($value);
                $sanitized_course["course:$key"] = $value;
            }

            // Attach values from org endpoint.
            $org = $this->orgdata[$course['organisationUid']];
            $org = (array)$org;

            foreach ($org as $key => $value) {
                $value = locallib::normalizeValue($value);
                $sanitized_course["org:$key"] = $value;
            }

            // Attach values from semester endpoint.
            $semester = $this->semesterdata[$course['semesterKey']];
            $semester = (array)$semester;

            foreach ($semester as $key => $value) {
                $value = locallib::normalizeValue($value);
                $sanitized_course["semester:$key"] = $value;
            }

            $enriched_courses[] = $sanitized_course;
        }

        return $enriched_courses;
    }

    /**
     * Gets org data from CAMPUSonline.
     */
    private function getOrgData() {

        $endpoint = 'co-brm-core/org/api/organisations';
        $result = $this->restCall($endpoint, null, 'GET', true);

        // Add to class property.
        $this->orgdata = array();
        if (property_exists($result, 'items')) {
            foreach ($result->items as $item) {
                $this->orgdata[$item->uid] = $item;
            }
        }
    }

    /**
     * Gets semester data from CAMPUSonline.
     */
    private function getSemesterData() {

        $endpoint = 'co-sm-core/semester/api/semesters';
        $result = $this->restCall($endpoint, null, 'GET', true);

        // Add to class property.
        $this->semesterdata = array();
        if (property_exists($result, 'items')) {
            foreach ($result->items as $item) {
                $this->semesterdata[$item->key] = $item;
            }
        }
    }

    /**
     * Sets the moodle course URL in CAMPUSonline.
     *
     * @param object $course
     * @return void
     */
    private function setMoodleCourseUrl($course) {

        $endpoint = 'co-tm-core/course/api/e-learning-infos';
        $moodle_url = new moodle_url('/course/view.php', array('id' => $course->id));
        $url = $moodle_url->__toString();
        $query = [
            'courseUid' => $course->idnumber,
            'externalUrl' => $url,
        ];

        // Log success.
        if ($result = $this->restCall($endpoint, $query, 'POST')) {
            if (property_exists($result, 'externalUrl')) {
                $url = $result->externalUrl;
                $message = "SUCCESS: updated CAMPUSonline course $course->idnumber with Moodle course URL $url.";
                $this->trace->output(" - $message");
                locallib::writeLog('update_course', $message, 0, $course->id);
                return;
            }
        }

        // Error.
        $message = "ERROR: could not update CAMPUSonline course $course->idnumber with Moodle course URL.";
        $this->trace->output(" - $message");
        locallib::writeLog('update_course', $message, 2, $course->id);
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

        foreach (locallib::CO_USER_FIELDS as $field => $value) {

            // Get field id of our user profile field.
            $property = str_replace('user_profilefield_campusonline_', '', $field);
            $property = $property . '_fieldid';
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

        }

        // Log UID update.
        $fieldname = 'campusonline_' . $usertype . '_uid';
        $message = "SUCCESS: updated Moodle user $userid profile fields CAMPUSonline uids for person $uid.";
        $this->trace->output(" - $message");
        locallib::writeLog('update_user', $message, 0, $userid);
    }

}
