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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

defined('MOODLE_INTERNAL') || die;

class sync {

    protected $path;
    protected $token;
    protected $error;
    public $lectureship_functions;

    /**
     * Constructor.
     */
    public function __construct() {

        // Remove old logs.
        $this->cleanupLogs();

        // Get settings.
        $this->path = get_config('enrol_campusonline', 'endpoint');
        $clientid = get_config('enrol_campusonline', 'clientid');
        $secret = get_config('enrol_campusonline', 'clientsecret');

        // Make request.
        if ($this->path && $clientid && $secret) {
            $this->path = rtrim($this->path, '/');
            $url = $this->path . '/public/sec/auth/realms/CAMPUSonline_SP/protocol/openid-connect/token';
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
     * Gets courses for preview.
     */
    public function getCourses($limit = null) {

        // Get courses.
        $endpoint = 'co-tm-core/course/api/courses';
        $query = [
            'semester_key' => get_config('enrol_campusonline', 'semester'),
            'only_elearning_courses' => 'true',
            'limit' => $limit,
        ];
        $result = $this->restCall($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $courses = $result->items;
        } else {
            $courses = array();
        }

        return $courses;
    }

    /**
     * Gets persons from CAMPUSonline for preview.
     */
    public function getPersons($limit = null) {

        // Get employees.
        $endpoint = 'co-brm-core/org/api/employee-persons';
        $query = [
            'limit' => $limit,
        ];
        $result = $this->restCall($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $persons = $result->items;
        } else {
            $persons = array();
        }

        // Get students.
        $endpoint = '/co-sm-core/study/api/student-persons/';
        $query = [
            'limit' => $limit,
        ];
        $result = $this->restCall($endpoint, $query);
        if (property_exists($result, 'items')) {
            $persons = array_merge($result->items, $persons);
        }

        return $persons;
    }

    /**
     * Add our enrolment method to a course.
     *
     * @param object $course
     *
     * @return void
     */
    public function addEnrolmentMethod($course) {

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
     * Gets data for a student from CAMPUSonline.
     *
     * @param string $uid
     * @param bool $is_student
     *
     * @return array $studentdata
     */
    public function getPerson($uid, $is_student) {

        // Get person.
        if ($is_student) {
            $endpoint = "/co-sm-core/study/api/student-persons/$uid";
        } else {
            $endpoint = "/co-brm-core/org/api/employee-persons/$uid";
        }
        return $this->restCall($endpoint);
    }

    /**
     * Syncs courses.
     *
     * @param progress_trace $trace
     *
     * @return void
     */
    public function syncCourses($trace) {

        global $CFG, $DB;

        require_once("$CFG->dirroot/course/lib.php");

        // Get courses.
        $courses = $this->getCourses();

        if ($trace) {
            $number = count($courses);
            $trace->output("Syncing $number courses ...");
        }

        // Sync courses.
        foreach ($courses as $coursedata) {

            $uid = $coursedata->uid;
            $trace->output(" - Syncing CAMPUSonline course $uid");

            // Prepare new course data.
            $newcourse = array();
            $newcourse['idnumber'] = $uid;
            $newcourse['shortname'] = locallib::getCourseField('shortname', $coursedata);
            $newcourse['fullname'] = locallib::getCourseField('fullname', $coursedata);
            $newcourse['category'] = locallib::getCourseCategory($coursedata);

            if (!$course = $DB->get_record('course', ['idnumber' => $uid])) {

                // Create new course.
                $course = new \stdClass();
                foreach ($newcourse as $key => $value) {
                    $course->$key = $value;
                }

                // Create course and log course creation.
                $log = new \stdClass();
                $log->timestamp = time();
                $log->event = 'create_course';
                if (create_course($course)) {
                    $log->courseid = $course->id;
                    $log->status = 0;
                    $message = "Created Moodle course $course->id for CAMPUSonline course $uid.";
                    $log->message = $message;
                    $trace->output(" - $message");
                } else {
                    $log->status = 2;
                    $log->message = 'error creating course';
                }
                $DB->insert_record('enrol_campusonline_logs', $log);

                // Add our enrolment method.
                $this->addEnrolmentMethod($course);

            } else {

                // Check if update is necessary.
                $needsupdate = false;
                foreach ($newcourse as $key => $value) {
                    if ($course->$key != $value) {
                        $trace->output($value);
                        $trace->output($course->$key);
                        $needsupdate = true;
                        break;
                    }
                }

                // Update.
                if ($needsupdate) {

                    // Skip.
                    if (get_config('enrol_campusonline', 'updateexistingcourses') == 0) {
                        $message = " - Skipped existing Moodle course $course->id for CAMPUSonline course $uid.";
                        $trace->output($message);
                        continue;
                    }

                    // Update course.
                    foreach ($newcourse as $key => $value) {
                        $course->$key = $value;
                    }
                    $DB->update_record('course', $course);

                    // Log update.
                    $log = new \stdClass();
                    $log->timestamp = time();
                    $log->event = 'update_course';
                    $log->courseid = $course->id;
                    $log->status = 0;
                    $message = "Updated Moodle course $course->id with data from CAMPUSonline course $uid.";
                    $trace->output(" - $message");
                    $log->message = $message;
                    $DB->insert_record('enrol_campusonline_logs', $log);
                }
            }

            // Sync enrolments.
            $this->syncEnrolments($course, $trace);
        }
    }

    /**
     * Syncs enrolments.
     *
     * @param object $course
     * @param progress_trace $trace
     *
     * @return void
     */
    public function syncEnrolments($course, $trace) {

        global $CFG, $DB;
        require_once("$CFG->dirroot/user/lib.php");
        $courseid = $course->id;

        // Check if enrolment method is active.
        if (!$enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'campusonline', 'status' => 0])) {
            $trace->output("   - Skipping enrolments for Moodle course $courseid - enrolment method has been deactivated.");
            return;
        }

        $enrolments = $this->getEnrolments($course);

        foreach ($enrolments as $enrolment) {

            // Get role.
            if (property_exists($enrolment, 'functionKey')) {
                $is_student = false;
                $roleid = get_config('enrol_campusonline', 'role_' . $enrolment->functionKey);
            } else {
                $is_student = true;
                $roleid = get_config('enrol_campusonline', 'studentrole');
            }

            // Skip if role should not be synced.
            if (!$roleid || $roleid == 0) {
                continue;
            }

            // Create new user if needed.
            $uid = $enrolment->personUid;
            if (!$userid = $DB->get_field('user', 'id', ['idnumber' => $uid])) {

                // Get person data.
                $person = $this->getPerson($uid, $is_student);

                // Create user.
                $user = new \stdClass();
                $user->username = strtolower($uid); // TODO: make configurable.
                $user->password = $uid; // TODO: make configurable.
                $user->idnumber = $uid;
                $user->firstname = $person->givenName;
                $user->lastname = $person->surname;
                $user->email = "$uid@example.com"; // TODO: make configurable.
                $user->auth = 'manual';  // TODO: make configurable?.
                $user->mnethostid = $CFG->mnet_localhost_id; // Local host ID
                $user->confirmed = 1; // Confirm the user

                // Create user & log it.
                $log = new \stdClass();
                $log->timestamp = time();
                $log->event = 'create_user';
                $log->courseid = $courseid;
                if (!$userid = user_create_user($user, false, false)) {
                    $message = "Error creating Moodle user for CAMPUSonline user $uid.";
                    $log->status = 2;
                } else {
                    $message = "Created Moodle user $userid for CAMPUSonline user $uid to enrol in Moodle course $course->id.";
                    $log->status = 0;
                }
                $trace->output("   - $message");
                $log->message = $message;
                $DB->insert_record('enrol_campusonline_logs', $log);
            }

            // Create enrolment if needed.
            if (!$DB->get_record('user_enrolments', ['enrolid' => $enrol->id, 'userid' => $userid])) {

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

                // Log enrolment creation.
                $log = new \stdClass();
                $log->timestamp = time();
                $log->event = 'enrol_user';
                $log->courseid = $courseid;
                $log->status = 0;
                $message = "Enrolled Moodle user $userid in Moodle course $courseid.";
                $log->message = $message;
                $trace->output("   - $message");
                $DB->insert_record('enrol_campusonline_logs', $log);
            }

            // Add role.
            $context = \context_course::instance($courseid);
            if (!user_has_role_assignment($userid, $roleid, $context->id)) {
                $success = role_assign($roleid, $userid, $context->id);
                $message = "Assigned role $roleid to Moodle user $userid in Moodle course $courseid.";
                $trace->output("   - $message");
            }

            // Remove roles.
            $roles = get_user_roles($context, $userid);
            foreach ($roles as $role) {
                if ($role->roleid != $roleid) {
                    $success = role_unassign($role->roleid, $userid, $context->id);
                    $message = "Removed role $role->roleid from Moodle user $userid in Moodle course $courseid.";
                    $trace->output("   - $message");
                }
            }
        }
    }

    /**
     * Removes old logs.
     */
    private function cleanupLogs() {
        global $DB;

        $duration = get_config('enrol_campusonline', 'logduration');
        $time = time() - $duration * 24 * 60 * 60;
        $DB->delete_records_select('enrol_campusonline_logs', "timestamp < $time");
    }

    /**
     * Calls REST API.
     *
     * @param string $endpoint
     * @param array $query
     *
     * @return object
     */
    private function restCall($endpoint, $query = null) {

        // Set params.
        $url = $this->path . '/' . $endpoint;
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 10.0,
            'connect_timeout' => 2.0,
        ]);

        // Make request.
        $response = $client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ],
            'query' => $query
        ]);

        // Analyze response.
        $response_body = $response->getBody()->getContents();
        $response_object = json_decode($response_body, false);
        return $response_object;
    }

}
