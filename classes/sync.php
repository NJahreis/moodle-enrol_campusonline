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
    private $externalkey;
    private $externalsystemkey;
    private $person_uid_fieldid;
    private $orgdata;
    private $semesterdata;

    const GROUP_DESC = "Created by CAMPUSOnline";

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
        $this->externalkey = $this->config->user_externalkey;
        $this->externalsystemkey = $this->config->user_externalsystemkey;

        try {
            $this->person_uid_fieldid = $DB->get_record('user_info_field', ['shortname' => 'campusonline_person_uid'])->id;
        } catch (Exception $e) {
            throw new moodle_exception('error:uidfieldnotfound', 'enrol_campusonline', '', $shortname);
        }

        // Get token.
        $this->updateToken();
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
 * Calls REST API with pagination.
 *
 * @param string $endpoint The API endpoint to call.
 * @param array $query Query parameters to pass to the API.
 * @param string $method HTTP method (GET by default).
 * @param string $alwayspage Whether to always page through the API.
 *
 * @return object All collected items from paginated API responses.
 */
private function restCall($endpoint, $query = null, $method = 'GET', $alwayspage = false) {

    // Set base URL and initialize the HTTP client.
    $url = $this->config->endpoint . '/' . $endpoint;
    $client = new Client([
        'base_uri' => $url,
        'timeout' => 10.0,
        'connect_timeout' => 2.0,
    ]);

    $all_items = [];
    $cursor = null;
    if ($this->config->restcalls) {
        $this->trace->output("        debug: fetching data from CAMPUSonline endpoint $endpoint");
    }

    do {

        // Update the query with the cursor, if available.
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
            $this->updateToken();

            if ($this->config->restcalls) {
                $count = count($all_items);
                $this->trace->output("        debug: paging to cursor $cursor, collected $count items so far");
            }
        }

        // Make the API request.
        $response = $client->request($method, $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ],
            'query' => $query
        ]);

        // Decode the response.
        $response_body = $response->getBody()->getContents();
        $response_object = json_decode($response_body, false);

        // Merge the current page's items with the collected items.
        if (property_exists($response_object, 'items')) {
            $all_items = array_merge($all_items, $response_object->items);
        }

        // Check if there is a next cursor for pagination.
        if (property_exists($response_object, 'nextCursor')) {
            $cursor = $response_object->nextCursor;
        } else {
            $cursor = null;
        }

        // Determine if we need to keep paging.
        $page = !array_key_exists('limit', $_GET) || $alwayspage;

    } while ($page && $cursor !== null);

    // Return the complete collection of items.
    return (object)[
        'items' => $all_items
    ];
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

        $categoryid = $this->config->rootcoursecategory;
        $subcategories = $this->config->subcategories;
        $subcategories = explode('\\', $subcategories);

        foreach ($subcategories as $name) {
            foreach ($coursedata as $key => $value) {
                if (is_string($value)) {
                    $name = str_replace('{' . $key . '}', $value, $name);
                }
            }
            $category = $DB->get_record('course_categories', ['name' => $name, 'parent' => $categoryid]);

            if (!$category) {

                if ($this->config->createcoursecatetories == 0) {

                    // Log error.
                    $message = "ERROR: could not find Moodle course category $name and not allowed to create new categories. Create the category manually, or configure CAMPUSOnline to be able to create new categories.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('create_category', $message, 2);

                    return false;

                } else {

                    // Log creation.
                    $message = "Created new Moodle course category $name.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('create_category', $message, 0);

                    // Create new category.
                    $categorydata = new \stdClass();
                    $categorydata->name = $name;
                    $categorydata->parent = $categoryid;
                    $categorydata->description = self::GROUP_DESC;
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
        $semesters = $this->config->semester;
        $semesters = explode(',', $semesters);

        foreach ($semesters as $semester) {

            $semester = trim($semester);

            // Get courses.
            $endpoint = 'co-tm-core/course/api/courses';
            if ($course_uids) {
                // Only request specific courses.
                $query['course_uids'] = implode(', ', $course_uids);
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

        $uids = explode(':', $course->idnumber);
        $course_uid = $uids[0];
        if (count($uids) > 1) {
            $group_uid = $uids[1];
        }

        // Get student enrolments.
        $endpoint = 'co-tm-core/course/api/registrations';
        $query = [
            'course_uid' => $course_uid,
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
     * @param array $userdata
     *
     * @return int $userid
     */
    public function getMoodleUserId($uid, $userdata = null) {

        global $DB;
        $userid = null;

        // Get user id via uid in our user profile field.
        $fieldid = $this->person_uid_fieldid;
        $sql = "SELECT * FROM {user_info_data} WHERE fieldid = ? AND data = ?";
        $params = array('fieldid' => $fieldid, 'data' => $uid);
        if ($records = $DB->get_records_sql($sql, $params)) {
            $record = reset($records);
            return $record->userid;
        }

        // Get full person data from CAMPUSOnline if needed.
        if (!$userdata) {
            $userdata = $this->getPersonData($uid);
        }

        // Get external keys if configured. TODO: check if this works with real data.
        if ($this->externalkey && $this->externalsystemkey) {
            $externalkeys = $this->getPersonExternalKey($uid);
            $userdata = array_merge($userdata, $externalkeys);
        }

        // Try to find user via fallback identifiers.
        $fields = locallib::USER_ID_FIELDS_IGNORE;
        $fields = array_diff($fields, ['id']);

        foreach ($fields as $field) {
            $fieldname = "user_$field";
            $valueconfig = $this->config->$fieldname;

            if ($field && $valueconfig) {
                $value = locallib::getFieldValue("user_$field", $userdata);

                if ($user = $DB->get_record('user', [$field => $value])) {
                    $userid = $user->id;
                }
            }
        }

        // Fallback profile field value.
        if (!$userid) {
            if ($field = $this->config->usermoodlefield) {
                $value = locallib::getFieldValue("user_profile_field_$field", $userdata);

                // Find user via profile field value.
                $fieldid = $DB->get_record('user_info_field', ['shortname' => $field])->id;
                $sql = "SELECT * FROM {user_info_data} WHERE fieldid = ? AND data = ?";
                $params = array('fieldid' => $fieldid, 'data' => $value);
                if ($records = $DB->get_records_sql($sql, $params)) {
                    $record = reset($records);
                    $userid = $record->userid;
                }
            }
        }


        if ($userid) {
            // Log success.
            $message = "Found Moodle user $userid for CAMPUSOnline person $uid via the value for field $field. UIDs will be updated in Moodle.";
            $this->trace->output("   - $message");
            locallib::writeLog('get_user', $message, 0);

            // Set UID.
            locallib::setCustomUserFields($user, $userdata, true);

            return $userid;
        }

        // Log warning.
        $message = "WARNING: could not find Moodle user for CAMPUSOnline person $uid.";
        $this->trace->output("   - $message");
        locallib::writeLog('get_user', $message, 1);

        return null;
    }

    /**
     * Gets additional person data from CAMPUSOnline.
     *
     * @param string $uid
     *
     * @return array $persondata
     */
    public function getPersonData($uid) {

        $endpoint = "co-brm-core/pers/api/personal-claims";
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
        } else {
            $persondata = (array)$result->items[0];
        }

        return $persondata;
    }

    /**
     * Gets external keys for person for user identification.
     *
     * @return array
     */
    public function getPersonExternalKey($uid) {

        $endpoint = "co-brm-core/pers/api/person-identifiers/mappings";
        $query = [
            'source_claim' => 'CO_CLAIM_PERSON_UID',
            'target_claim' => 'CO_CLAIM_EXTERNAL_SYSTEM_UID',
            'uid' => $uid,
            'external_key' => $this->externalkey,
            'external_system_key' => $this->externalsystemkey,
        ];

        $result = $this->restCall($endpoint, $query);

        if (property_exists($result, 'mappings')) {
            return (array)$result->mappings;
        } else {
            return array();
        }
    }

    /**
     * Gets persons from CAMPUSonline for preview.
     *
     * @param int $limit
     *
     * @return array $persons
     */
    public function getPersons($limit = null) {

        // Get employees.
        $endpoint = "co-brm-core/pers/api/personal-claims";
        $query = [
            'claim' => 'CO_CLAIM_ALL',
            'limit' => $limit,
        ];

        $result = $this->restCall($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $personobjects = $result->items;
        } else {
            $personobjects = array();
        }

        foreach ($personobjects as $personobject) {
            $persons[] = (array) $personobject;
        }

        return $persons;
    }

    /**
     * Syncs modified courses.
     *
     * @return void
     */
    public function syncCourseDelta() {

        // Set timeframe.
        $timeframe = $this->config->modificationtimeframe;

        // Get todays date minus timeframe days.
        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $timeframe . 'D'));
        $date = $date->format('Y-m-d');

        // Get semester(s).
        $semesters = $this->config->semester;
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
            $this->syncCourses([$course_uid]);
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
        $updatecourseurls = $this->config->updatecourseurls;
        $updatecourses = $this->config->updateexistingcourses;
        $grouptocourse = $this->config->grouptocourse;
        $grouptogroup = $this->config->grouptogroup;

        // Start output.
        $number = count($courses);
        $this->trace->output("Syncing $number courses ...");

        // Sync courses.
        foreach ($courses as $coursedata) {

            $course_uid = $coursedata['course:uid'];
            $this->trace->output(" - Syncing CAMPUSonline course $course_uid");

            // Check if we need to make separate courses for each group.
            $separatecourses = false;
            $groups = $this->getCourseGroups($coursedata['course:uid']);
            if (array_key_exists('course:elearningEventTypeKey', $coursedata)) {
                $elearning_type = $coursedata['course:elearningEventTypeKey'];
                if (str_contains($grouptocourse, $elearning_type)) {
                    $separatecourses = true;
                }
            }

            foreach ($groups as $group_uid => $group_name) {

                // Single course or separate courses for each group.
                if ($separatecourses) {
                    $idnumber = "$course_uid:$group_uid";
                } else {
                    $idnumber = $course_uid;
                }

                // Prepare new course data.
                if ($separatecourses) {
                    $newcourse = locallib::buildCourse($coursedata, $group_name, $group_uid);
                } else {
                    $newcourse = locallib::buildCourse($coursedata);
                }

                // Get category.
                if (!$newcourse['category'] = $this->getCourseCategory($coursedata)) {
                    continue;
                }

                if (!$course = $DB->get_record('course', ['idnumber' => $idnumber])) {

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
                        $this->setMoodleCourseUrl($course, $group_uid);

                        // Log success.
                        if ($separatecourses) {
                            $message = "Created Moodle course $courseid for CAMPUSonline course $course_uid group $group_uid.";
                        } else {
                            $message = "Created Moodle course $courseid for CAMPUSonline course $course_uid.";
                        }

                        $this->trace->output("   - $message");
                        locallib::writeLog('create_course', $message, 0, $courseid);

                    } else {

                        // Log error.
                        if ($$separatecourses) {
                            $message = "ERROR: could not create Moodle course for CAMPUSonline course $course_uid group $group.";
                        } else {
                            $message = "ERROR: could not create Moodle course for CAMPUSonline course $course_uid.";
                        }
                        $this->trace->output("   - $message");
                        locallib::writeLog('create_course', $message, 2);
                    }

                    // Add our enrolment method.
                    locallib::addEnrolmentMethod($course);

                } else {

                    $courseid = $course->id;

                    // Update course URL in CAMPUSonline.
                    if ($updatecourseurls == 1) {
                        $this->setMoodleCourseUrl($course, $group_uid);
                    }

                    // Skip.
                    if (!$course_uids && $updatecourses == 0) {
                        continue;
                    }

                    // Update course.
                    $needsupdate = false;
                    foreach ($newcourse as $key => $value) {
                        if (property_exists($course, $key) && $course->$key != $value) {
                            $course->$key = $value;
                            $needsupdate = true;
                        }
                    }

                    if ($needsupdate) {

                        $DB->update_record('course', $course);

                        // Update course custom fields.
                        locallib::setCustomCourseFields($courseid, $coursedata);

                        // Log success.
                        if ($separatecourses) {
                            $message = "Updated Moodle course settings for $courseid with data from CAMPUSonline course $course_uid group $group_uid.";
                        } else {
                            $message = "Updated Moodle course settings for $courseid with data from CAMPUSonline course $course_uid.";
                        }
                        $this->trace->output("   - $message");
                        locallib::writeLog('update_course', $message, 0, $courseid);
                    }
                }

                // Check if we need to sync groups for enrolments.
                $syncgroups = false;
                if (!$separatecourses) {
                    if (empty($grouptogroup)) {
                        $syncgroups = true;
                    } elseif (array_key_exists('course:elearningEventTypeKey', $coursedata)) {
                        if (str_contains($grouptogroup, $elearning_type)) {
                            $syncgroups = true;
                        }
                    }
                }

                // Sync enrolments.
                if ($syncgroups) {
                    $this->syncEnrolments($course, $groups);
                } else {
                    $this->syncEnrolments($course);
                }

                // Break foreach loop in case of no separate groups.
                if (!$separatecourses) {
                    break;
                }
            }
        }
    }

    /**
     * Syncs enrolments.
     *
     * @param object $course
     * @param array $groups
     *
     * @return void
     */
    public function syncEnrolments($course, $groups = null) {

        global $CFG, $DB;
        require_once("$CFG->dirroot/user/lib.php");
        require_once("$CFG->dirroot/group/lib.php");

        $courseid = $course->id;
        $context = \context_course::instance($courseid);

        // Get or create grouping.
        if ($grouping = $DB->get_record('groupings',
            ['courseid' => $courseid,
             'name' => 'CAMPUSonline',
             'idnumber' => $course->idnumber,
            ])) {
            $grouping_id = $grouping->id;
        } else {
            $grouping = new \stdClass();
            $grouping->courseid = $courseid;
            $grouping->name = 'CAMPUSonline';
            $grouping->idnumber = $course->idnumber;
            $grouping->timecreated = time();
            $grouping->timemodified = time();
            $grouping_id = groups_create_grouping($grouping);
            $course->defaultgroupingid = $grouping_id;
            $DB->update_record('course', $course);
            $message = "Created grouping for CAMPUSonline groups in course $courseid";
            $this->trace->output("   - $message");
            locallib::writeLog('sync_groups', $message, 0, $courseid);
            return;
        }

        // Check if enrolment method is active.
        if (!$enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'campusonline', 'status' => 0])) {
            $message = "WARNING: skipping enrolments for Moodle course $courseid - enrolment method has been deactivated.";
            $this->trace->output("   - $message");
            locallib::writeLog('enrol_user', $message, 1, $courseid);
            return;
        }

        // Sum up existing enrolments in Moodle course, to save on DB queries.
        $existing_enrolments = array();
        $existing_enrolments_userids = array();
        $existing_enrolments_raw = $DB->get_records('user_enrolments', ['enrolid' => $enrol->id]);
        foreach ($existing_enrolments_raw as $existing_enrolment) {
            $existing_enrolments[$existing_enrolment->userid] = $existing_enrolment;
        }

        // Get enrolments from CAMPUSonline.
        $assigned_roles = array();
        $enrolments = $this->getEnrolments($course);

        // Check if this is a course for a single groups.
        $uids = explode(':', $course->idnumber);
        $course_uid = $uids[0];
        $group_uid = null;
        if (count($uids) > 1) {
            $group_uid = $uids[1];
        }

        $group_members = array();

        foreach ($enrolments as $enrolment) {

            // Skip if enrolment is not for this group.
            if ($group_uid) {
                if (property_exists($enrolment, 'courseGroupUid') && $enrolment->courseGroupUid != $group_uid) {
                    continue;
                }
            }

            // Get role.
            if (property_exists($enrolment, 'functionKey')) {
                $rolekey = 'role_' . $enrolment->functionKey;
                $roleid = $this->config->$rolekey;
            } else {
                $roleid = $this->config->studentrole;
            }

            // Skip if role should not be synced.
            if (!$roleid || $roleid == 0) {
                continue;
            }

            // Get Moodle user.
            $uid = $enrolment->personUid;
            $userid = $this->getMoodleUserId($uid);

            // Create new user if needed & allowed.
            if (!$userid) {
                if ($this->config->enrolsynccreateusers) {

                    if (!$userid = $this->createMoodleUser($uid)) {
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
            if (!in_array($userid, array_keys($existing_enrolments))) {

                // Create enrolment.
                $enrolment = new \stdClass();
                $enrolment->enrolid = $enrol->id;
                $enrolment->userid = $userid;
                $enrolment->timestart = time();
                $enrolment->timeend = 0;
                $enrolment->status = 0;
                $enrolment->modifierid = 0;
                $enrolment->timecreated = time();
                $enrolment->timemodified = time();
                $DB->insert_record('user_enrolments', $enrolment);
                $existing_enrolments_userids[] = $userid;

                // Log success.
                $message = "Enrolled Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
                locallib::writeLog('enrol_user', $message, 0, $courseid);

            // Activate enrolment if needed.
            } else {
                if ($existing_enrolments[$userid]->status == 1) {
                    $enrolment = $existing_enrolments[$userid];
                    $enrolment->status = 0;
                    $enrolment->timemodified = time();
                    $DB->update_record('user_enrolments', $enrolment);

                    // Log success.
                    $message = "Activated enrolment for Moodle user $userid in Moodle course $courseid.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('enrol_user', $message, 0, $courseid);
                }
            }

            // Add role.
            if (!user_has_role_assignment($userid, $roleid, $context->id)) {
                $success = role_assign($roleid, $userid, $context->id);
                $message = "Assigned role $roleid to Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
                locallib::writeLog('enrol_user', $message, 0, $courseid);
            }

            // Add to assigned roles for later cleanup.
            $assigned_roles[$userid][] = $roleid;

            // Add to group members, to process later.
            if ($groups && property_exists($enrolment, 'courseGroupUid')) {
                $group_members[$enrolment->courseGroupUid][] = $userid;
            }
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
                    locallib::writeLog('enrol_user', $message, 0, $courseid);
                }
            }

            // Suspend enrolments with no roles left.
            $roles = get_user_roles($context, $userid);
            if (empty($roles)) {
                $enrolment = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $userid]);
                $enrolment->status = 1;
                $DB->update_record('user_enrolments', $enrolment);
                $message = "Suspended enrolment for Moodle user $userid in Moodle course $courseid.";
                $this->trace->output("   - $message");
                locallib::writeLog('enrol_user', $message, 0, $courseid);
            }
        }


        // Create/update groups.
        foreach ($group_members as $group_uid => $members) {

            // Get group.
            if ($group = $DB->get_record('groups', ['courseid' => $courseid, 'idnumber' => $group_uid])) {
                $groupid = $group->id;
            } else {

                // Create group.
                $group = new \stdClass();
                $group->courseid = $courseid;
                $group->name = $groups[$group_uid];
                $group->idnumber = $group_uid;
                $group->description = self::GROUP_DESC;
                $group->timecreated = time();
                $group->timemodified = time();
                $groupid = groups_create_group($group);

                // Add to grouping.
                if (!$DB->get_record('groupings_groups', ['groupid' => $groupid, 'groupingid' => $grouping_id])) {
                    $groupinggroup = new \stdClass();
                    $groupinggroup->groupid = $groupid;
                    $groupinggroup->groupingid = $grouping_id;
                    $groupinggroup->timeadded = time();
                    $DB->insert_record('groupings_groups', $groupinggroup);
                }

                $message = "Created Moodle group $group->name for CAMPUSonline group $group_uid.";
                $this->trace->output("   - $message");
                locallib::writeLog('sync_groups', $message, 0, $courseid);
            }

            // Add to array with moodle group ids for later cleanup.
            $group_members_moodle[$groupid] = $members;

            // Add members.
            foreach ($members as $userid) {
                if (!groups_is_member($groupid, $userid)) {
                    groups_add_member($groupid, $userid);
                    $message = "Added user $userid to Moodle group $group->name.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('sync_groups', $message, 0, $courseid);
                }
            }
        }

        // Remove members.
        $course_groups = $DB->get_records('groupings_groups', ['groupingid' => $grouping_id]);
        foreach ($course_groups as $course_group) {
            $groupid = $course_group->groupid;

            $members = groups_get_members($groupid);
            foreach ($members as $member) {
                if (!array_key_exists($groupid, $group_members_moodle) || !in_array($member->id, $group_members_moodle[$groupid])) {
                    groups_remove_member($groupid, $member->id);
                    $message = "Removed user $member->id from Moodle group $group->name.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('sync_groups', $message, 0, $courseid);
                }
            }
        }

    }

    /**
     * Syncs users.
     *
     * @return void
     */
    public function syncUsers() {

        $persons = $this->getPersons();

        foreach ($persons as $person) {

            // Get Moodle user.
            $uid = $person['uid'];
            $userid = $this->getMoodleUserId($uid, $person);

            // Create new user if needed & allowed.
            if (!$userid) {
                if ($this->config->enrolsynccreateusers) {

                    if (!$userid = $this->createMoodleUser($uid)) {
                        continue;
                    }

                } else {

                    // Log warning.
                    $message = "WARNING: skipped syncing user data for CAMPUSonline user $uid - user does not exist in Moodle.";
                    $this->trace->output("   - $message");
                    locallib::writeLog('sync_user', $message, 1, $courseid);
                    continue;
                }
            }

            // Load Moodle User.
            $user = \core_user::get_user($userid);

            // Update user data.
            $needsupdate = false;
            foreach (locallib::USER_FIELDS as $field => $default) {

                // Only update email if allowed.
                if ($field == 'email' && !$this->config->user_allowemailupdate) {
                    continue;
                }

                $value = locallib::getFieldValue("user_$field", $person);

                // Sanitize usernames.
                if ($field == 'username') {
                    $value = strtolower($value);
                }

                if ($user->$field != $value) {
                    $user->$field = $value;
                    $needsupdate = true;
                }
            }

            if ($needsupdate) {
                user_update_user($user, false);
            }

            // Load profile data into user object.
            $needsupdatep = locallib::setCustomUserFields($user, $person);

            // Log update.
            if ($needsupdate || $needsupdatep) {
                $message = "Updated Moodle user $userid with data from CAMPUSonline user $uid.";
                $this->trace->output("   - $message");
                locallib::writeLog('sync_user', $message, 0, null);
            }
        }
    }

    /**
     * Creates a new Moodle user.
     *
     * @param string $uid
     *
     * @return int $userid
     */
    private function createMoodleUser($uid) {

        global $CFG;

        // Get full person data from CAMPUSOnline.
        $userdata = $this->getPersonData($uid);

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
        $user->password = locallib::getFieldValue('user_password', $userdata);
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;

        foreach (locallib::USER_FIELDS_NOEMPTY as $check) {
            if ($user->$check == '') {
                $message = "ERROR: could not create Moodle user for CAMPUSonline user $uid - required field $check is empty.";
                $this->trace->output("   - $message");
                locallib::writeLog('create_user', $message, 2);
                return null;
            }
        }

        // Create user.
        if ($userid = user_create_user($user)) {
            $message = "Created Moodle user $userid for CAMPUSonline user $uid.";
            $status = 0;

        } else {
            $message = "ERROR: could not create Moodle user for CAMPUSonline user $uid.";
            $status = 2;
        }

        // Update custom fields.
        $user = \core_user::get_user($userid);
        locallib::setCustomUserFields($user, $userdata);

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
     * Gets group for a course.
     *
     * @param string $course_uid
     * @return array groups
     */
    private function getCourseGroups($course_uid) {

        $endpoint = "co-tm-core/course/api/courses/$course_uid/groups";
        $result = $this->restCall($endpoint, null);

        // Analyze response.
        $groups = array();
        if (property_exists($result, 'items')) {
            foreach ($result->items as $item) {
                $groups[$item->uid] = $item->name->value->de;
            }
        }

        return $groups;
    }

    /**
     * Gets org data from CAMPUSonline.
     */
    private function getOrgData() {

        if ($this->orgdata) {
            return;
        }

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

        if ($this->semesterdata) {
            return;
        }

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

        $course_uid = explode(':', $course->idnumber)[0];
        $endpoint = 'co-tm-core/course/api/e-learning-infos';
        $moodle_url = new moodle_url('/course/view.php', array('id' => $course->id));
        $url = $moodle_url->__toString();
        $query = [
            'courseUid' => $course_uid,
            'courseGroupUid' => $group_uid,
            'externalUrl' => $url,
        ];

        // Log success.
        if ($result = $this->restCall($endpoint, $query, 'POST')) {
            if (property_exists($result, 'externalUrl')) {
                $url = $result->externalUrl;
                $message = "Updated CAMPUSonline course $course->idnumber with Moodle course URL $url.";
                $this->trace->output("   - $message");
                locallib::writeLog('update_course', $message, 0, $course->id);
                return;
            }
        }

        // Error.
        $message = "ERROR: could not update CAMPUSonline course $course->idnumber with Moodle course URL.";
        $this->trace->output("   - $message");
        locallib::writeLog('update_course', $message, 2, $course->id);
    }

    /**
     * Gets access token for REST calls.
     *
     * @return string $token
     */
    private function updateToken() {

        $path = $this->config->endpoint;
        $clientid = $this->config->clientid;
        $secret = $this->config->clientsecret;

        if (!$path || !$clientid || !$secret) {
            $this->error = get_string('error:config', 'enrol_campusonline');
            return;
        }

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
