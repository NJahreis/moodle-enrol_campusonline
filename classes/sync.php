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

    /**
     * Constructor.
     */
    public function __construct() {

        // Get settings.
        $this->path = get_config('enrol_campusonline', 'endpoint');
        $this->path = rtrim($this->path, '/');
        $clientid = get_config('enrol_campusonline', 'clientid');
        $secret = get_config('enrol_campusonline', 'clientsecret');

        // Make request.
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
     * Gets courses.
     */
    public function getCourses() {

        // Get courses.
        $endpoint = 'co-tm-core/course/api/courses';
        $query = [
            'semester_key' => get_config('enrol_campusonline', 'semester'),
            'only_elearning_courses' => 'true',
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
     * Gets enrolments for a course from CAMPUSonline.
     *
     * @param object $course
     *
     * @return array $enrolments
     */
    public function getEnrolments($course) {

        // Get courses.
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

        return $enrolments;
    }

    /**
     * Syncs courses.
     *
     * @param progress_trace $trace
     *
     * @return void
     */
    public function syncCourses($trace = null) {

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

            // Prepare new course data.
            $newcourse = array();
            $newcourse['idnumber'] = $coursedata->uid;
            $newcourse['shortname'] = locallib::getCourseField('shortname', $coursedata);
            $newcourse['fullname'] = locallib::getCourseField('fullname', $coursedata);
            $newcourse['category'] = locallib::getCourseCategory($coursedata);

            if (!$course = $DB->get_record('course', ['idnumber' => $coursedata->uid])) {

                // Create new course.
                $course = new \stdClass();
                foreach ($newcourse as $key => $value) {
                    $course->$key = $value;
                }

                // Log course creation.
                $log = new \stdClass();
                $log->timestamp = time();
                $log->event = 'create_course';
                if (create_course($course)) {
                    $log->courseid = $course->id;
                    $log->status = 0;
                    $message = "Created course for CAMPUSonline UID $course->idnumber (Moodle course id $course->id).";
                    $log->message = $message;
                    $trace->output(" - $message");
                } else {
                    $log->status = 2;
                    $log->message = 'error creating course';
                }
                $DB->insert_record('enrol_campusonline_logs', $log);

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
                        $message = " - Skipped existing course with idnumber $course->idnumber (Moodle course id $course->id).";
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
                    $message = "Updated course with idnumber $course->idnumber (Moodle course id $course->id).";
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
     * @param progress_trace $trace
     *
     * @return void
     */
    public function syncEnrolments($course, $trace = null) {

        global $CFG, $DB;

        require_once("$CFG->dirroot/user/lib.php");

        $enrolments = $this->getEnrolments($course);

        if ($trace) {
            $trace->output('Syncing enrolments for course: '. $course->idnumber);
        }

        foreach ($enrolments as $enrolmentdata) {

            $useridnumber = $DB->get_record('course', ['idnumber' => $enrolmentdata->uid]);

            echo $useridnumber;

        }
    }

    /**
     * Calls REST API.
     *
     * @param string $endpoint
     * @param array $query
     *
     * @return object
     */
    private function restCall($endpoint, $query) {

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
