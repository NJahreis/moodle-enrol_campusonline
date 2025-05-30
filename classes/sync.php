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

use moodle_exception;
use moodle_url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

require_once($CFG->dirroot . '/user/lib.php');

defined('MOODLE_INTERNAL') || die;

class sync {

    private $config;
    private $error;
    private $token;
    private $trace;
    private $externalkey;
    private $externalsystemkey;
    private $customfieldids;
    private $orgdata;
    private $orgroles;
    private $semesterdata;
    private $grouptogroup;
    private $grouptocourse;
    private $flatcourse;

    const CREATED_BY = "Created by CAMPUSonline";
    const GROUP_TO_COURSE = 'GROUP_TO_COURSE';
    const GROUP_TO_GROUP = 'GROUP_TO_GROUP';
    const FLAT_COURSE = 'FLAT_COURSE';

    /**
     * Constructor.
     */
    public function __construct($trace) {

        global $DB;

        // Do nothing if our enrolment method is disabled.
        if (enrol_is_enabled('campusonline')) {

            // Remove old logs.
            locallib::cleanup_logs();

            // Get settings.
            $this->config = get_config('enrol_campusonline');
            $this->trace = $trace;
            $this->externalkey = $this->config->user_externalkey;
            $this->externalsystemkey = $this->config->user_externalsystemkey;
            $this->grouptocourse = preg_split('/\s*,\s*/', $this->config->grouptocourse);
            $this->grouptogroup = preg_split('/\s*,\s*/', $this->config->grouptogroup);
            $this->flatcourse = preg_split('/\s*,\s*/', $this->config->flatcourse);

            // Get roles to sync for organisations.
            $this->orgroles = locallib::get_org_roles();

            // Get custom field ids so we dont have to deal with Moodle custom field API.
            $fields = ['user_info_field:campusonline_person_uid',
                    'customfield_field:campusonline_other_co_course_uids'
                    ];
            foreach ($fields as $field) {
                list($table, $shortname) = explode(':', $field);
                if (!$value = $DB->get_field($table, 'id', ['shortname' => $shortname])) {
                    // Show error.
                    $message = get_string('error:uidfieldnotfound', 'enrol_campusonline', "$table: $shortname");
                    \core\notification::add($message,
                        \core\output\notification::NOTIFY_ERROR);
                    locallib::write_log('general', $message, 2, null, $this->trace, null);
                } else {
                    $this->customfieldids[$shortname] = $value;
                }
            }

            // Get token.
            $this->update_token();
        }
    }

    /**
     * Checks if connection was successful.
     */
    public function is_connected() {
        return !empty($this->token);
    }

    /**
     * Returns the error message.
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Gets a category for a course.
     *
     * @param array $coursedata
     *
     * @return string $categoryid
     */
    public function get_course_category($coursedata) {

        global $DB;

        // Get category for this course's org.
        $orgid = $coursedata['org:uid'];
        if ($orgcategory = $DB->get_record('course_categories', ['idnumber' => $orgid])) {
            $categoryid = $orgcategory->id;
        } else {
            // Log warning.
            $message = get_string('warning:orgcategorynotfound', 'enrol_campusonline', $orgid);
            $categoryid = $this->config->rootcoursecategory;
        }

        // Get subcategories.
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

                if ($this->config->createcoursecategories == 0) {

                    // Log error.
                    $message = get_string('error:coursecategorynotfoundandcreationdisabled', 'enrol_campusonline', $name);
                    locallib::write_log('create_category', $message, 2, null, $this->trace, 3);

                    return false;

                } else {

                    // Log creation.
                    $message = get_string('info:coursecategorycreated', 'enrol_campusonline', $name);
                    locallib::write_log('create_category', $message, 0, null, $this->trace, 3);

                    // Create new category.
                    $categorydata = new \stdClass();
                    $categorydata->name = $name;
                    $categorydata->parent = $categoryid;
                    $categorydata->description = self::CREATED_BY;
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
     * Gets groups for a course.
     *
     * @param string $course_uid
     * @return array groups
     */
    public function get_course_groups($course_uid) {

        $endpoint = "co-tm-core/course/api/courses/$course_uid/groups";
        $result = $this->rest_call($endpoint, null);

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
     * Gets courses for preview or sync.
     *
     * @param string $course_uids if provided, only fetches these courses.
     * @return array
     */
    public function get_courses($course_uids = null, $limit = null) {

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

            $result = $this->rest_call($endpoint, $query);

            // Analyze response.
            if (property_exists($result, 'items')) {
                $courses = $result->items;
                $courses = $this->enrich_courses($courses);
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
     * Gets course sync strategy for a course.
     *
     * @param array $coursedata
     * @return string $strategy
     */
    public function get_course_sync_strategy($coursedata) {

        $course_uid = $coursedata['course:uid'];

        // Get eLearningEventTypeKey.
        if (array_key_exists('course:elearningEventTypeKey', $coursedata)) {
            $elearning_type = $coursedata['course:elearningEventTypeKey'];
        } else {

            // Log error.
            $message = get_string('error:noelearningeventtypekey', 'enrol_campusonline', $course_uid);
            locallib::write_log('update_course', $message, 2, null, $this->trace, 3);
            return null;
        }

        if (in_array($elearning_type, $this->grouptocourse)) {
            return self::GROUP_TO_COURSE;
        } elseif(in_array($elearning_type, $this->grouptogroup)) {
            return self::GROUP_TO_GROUP;
        } elseif(in_array($elearning_type, $this->flatcourse)) {
            return self::FLAT_COURSE;
        } else {
            if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
                $this->trace->output(get_string('info:skippingcourse', 'enrol_campusonline', ['course_uid' => $course_uid, 'elearning_type' => $elearning_type]));
            }
            return null;
        }
    }

    /**
     * Gets enrolments for a course from CAMPUSonline.
     *
     * @param object $course
     *
     * @return array $enrolments
     */
    public function get_enrolments($course) {

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
        $result = $this->rest_call($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $enrolments = $result->items;
        } else {
            $enrolments = array();
        }

        // Get teacher enrolments.
        $endpoint = 'co-tm-core/course/api/lectureships';
        $query = [
            'course_uid' => $course_uid,
        ];
        $result = $this->rest_call($endpoint, $query);

        // Analyze response.
        if (property_exists($result, 'items')) {
            $enrolments = array_merge($result->items, $enrolments);
        }

        return $enrolments;
    }

    /**
     * Gets lectureship functions from CAMPUSonline.
     *
     * @return array $lectureship_functions
     */
    public function get_lectureship_functions() {
        $endpoint = 'co-tm-core/course/api/lectureship-functions';
        $result = $this->rest_call($endpoint);

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
     * Gets the Moodle User ID of a CAMPUSonline user via its uid.
     *
     * @param string $uid
     * @param bool $log
     *
     * @return mixed $userid
     */
    public function get_moodle_user_id($uid, $log = true) {

        global $DB;
        $userid = null;

        // Get user id via uid in our user profile field.
        $fieldid = $this->customfieldids['campusonline_person_uid'];
        $sql = "SELECT * FROM {user_info_data} WHERE fieldid = ? AND data = ?";
        $params = array('fieldid' => $fieldid, 'data' => $uid);
        if ($records = $DB->get_records_sql($sql, $params)) {
            $record = reset($records);
            return $record->userid;
        }

        // Log warning.
        if ($log) {
            $message = get_string('warning:moodleusernotfound', 'enrol_campusonline', $uid);
            locallib::write_log('get_user', $message, 1, null, $this->trace, 5);
        }

        return null;
    }

    /**
     * Gets additional person data from CAMPUSonline.
     *
     * @param string $uid
     * @param bool $log
     *
     * @return array $persondata
     */
    public function get_person_data($uid, $log = true) {

        $endpoint = "co-brm-core/pers/api/person-claims";
        $query = [
            'claim' => 'CO_CLAIM_ALL',
            'person_uid' => $uid,
        ];

        $result = $this->rest_call($endpoint, $query);

        // Log error.
        if (!property_exists($result, 'items') || empty($result->items)) {
            if ($log) {
                $message = get_string('warning:couldnotgetpersondata', 'enrol_campusonline', $uid);
                locallib::write_log('get_user_data', $message, 1, null, $this->trace, 3);
            }
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
    public function get_person_external_key($uid) {

        $endpoint = "co-brm-core/pers/api/person-identifiers/mappings";
        $query = [
            'source_claim' => 'CO_CLAIM_PERSON_UID',
            'target_claim' => 'CO_CLAIM_EXTERNAL_SYSTEM_UID',
            'uid' => $uid,
            'external_key' => $this->externalkey,
            'external_system_key' => $this->externalsystemkey,
        ];

        $result = $this->rest_call($endpoint, $query);

        if (property_exists($result, 'mappings')) {
            return (array)$result->mappings;
        } else {
            return array();
        }
    }

    /**
     * Gets persons from CAMPUSonline.
     *
     * @return array $persons
     */
    public function get_persons($person_uids = null, $limit = null) {

        // Get employees.
        $endpoint = "co-brm-core/pers/api/person-claims";
        $query = [
            'claim' => 'CO_CLAIM_ALL',
            'limit' => $limit,
        ];

        if ($person_uids) {
            $query['person_uid'] = implode(',', $person_uids);
        }

        $result = $this->rest_call($endpoint, $query);

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
     * Maps CAMPUSonline persons to existing Moodle users and updates their person UID.
     *
     * @param array $users
     *
     */
    public function identify_moodle_users($users) {

        global $CFG, $DB;

        require_once($CFG->dirroot . '/user/profile/lib.php');

        $sourcefield = $this->config->sourcefield;
        $sourceclaim = $this->config->sourceclaim;
        $max_attempts = (int) $this->config->idattempts;
        if (str_contains($sourcefield, 'profile_field_')) {
            $sourcefield = str_replace('profile_field_', '', $sourcefield);
            $profilefield = true;
        } else {
            $profilefield = false;
        }

        $total = count($users);
        $skipped = 0;
        foreach ($users as $key => $user) {

            // Skip users that already have a Person UID.
            if (locallib::get_person_uid($user->id)) {
                unset($users[$key]);
                $skipped++;
            }
        }

        $number = count($users);
        if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
            if ($number > 1) {
                $this->trace->output(get_string('info:identifyingusers', 'enrol_campusonline', ['number' => $number, 'total' => $total, 'skipped' => $skipped]));
            } else {
                $this->trace->output(get_string('info:identifyinguser', 'enrol_campusonline'));
            }

        }

        foreach ($users as $user) {

            $userid = $user->id;
            if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
                $this->trace->output(get_string('info:identifyinguserwithid', 'enrol_campusonline', $userid));
            }
            profile_load_custom_fields($user);

            // Skip users that have reached the maximum attempts.
            if (array_key_exists('campusonline_id_attempts', $user->profile)) {
                $attempt = (int) $user->profile['campusonline_id_attempts'];
                if ($attempt >= $max_attempts) {
                    if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
                        $this->trace->output(get_string('info:maxattemptsreached', 'enrol_campusonline', $userid));
                    }
                    continue;
                }
            }

            // Get uid value.
            $uid = null;
            if ($profilefield) {
                if (property_exists($user, 'profile')) {
                    if (array_key_exists($sourcefield, $user->profile)) {
                        $uid = $user->profile[$sourcefield];
                    }
                }
            } else {
                $uid = $user->$sourcefield;
            }

            // Skip users that do not have an uid value.
            if (!$uid) {

                // Update attempts.
                $user->profile_field_campusonline_id_attempts = $attempt + 1;
                $result = profile_save_data($user);

                // Log.
                $message = get_string('info:couldnotfinduidvalue', 'enrol_campusonline', ['sourcefield' => $sourcefield, 'userid' => $userid, 'attempt' => $attempt]);
                locallib::write_log('identify_user', $message, 0, null, $this->trace, 3);
                continue;
            }

            // Skip users that have a uid that is already set to another Moodle user.
            if ($moodle_user_id = $this->get_moodle_user_id($uid, false)) {
                $message = get_string('warning:uidalreadyassigned', 'enrol_campusonline', ['uid' => $uid, 'moodle_user_id' => $moodle_user_id, 'userid' => $userid]);
                locallib::write_log('identify_user', $message, 1, null, $this->trace, 3);
                continue;
            }

            // If source claim equals target claim, that means we already have the Person UID, and can try fetching the person to see if it is valid.
            if ($this->config->sourceclaim == 'CO_CLAIM_PERSON_UID') {
                if($this->get_person_data($uid, false)) {

                    // Save Person UID to user profile.
                    $user->profile_field_campusonline_person_uid = $uid;
                    profile_save_data($user);

                    // Log success.
                    $message = get_string('info:useridentified', 'enrol_campusonline', ['userid' => $userid, 'uid' => $uid]);
                    locallib::write_log('identify_user', $message, 0, null, $this->trace, 3);
                    continue;
                }

            } else {

                // Fetch Person UID using the source claim.
                $endpoint = 'co-brm-core/pers/api/person-identifiers/mappings';
                $query = [
                    'target_claim' => 'CO_CLAIM_PERSON_UID',
                    'source_claim' => $sourceclaim,
                    'uid' => $uid
                ];
                if ($this->config->sourceclaim == 'CO_CLAIM_EXTERNAL_SYSTEM') {
                    $query['external_key'] = $this->config->externalkey;
                    $query['external_system_key'] = $this->config->externalsystemkey;
                }
                $result = $this->rest_call($endpoint, $query);

                // Analyze response.
                if (property_exists($result, 'mappings')) {

                    $mapping = $result->mappings;
                    if (property_exists($mapping, $uid)) {
                        $person_uid = $mapping->$uid;

                        // Save Person UID to user profile.
                        $user->profile_field_campusonline_person_uid = $person_uid;
                        profile_save_data($user);

                        // Log success.
                        $message = get_string('info:useridentifiedvia', 'enrol_campusonline', ['userid' => $userid, 'person_uid' => $person_uid, 'sourceclaim' => $sourceclaim, 'uid' => $uid]);
                        locallib::write_log('identify_user', $message, 0, null, $this->trace, 3);
                        continue;
                    }
                }
            }

            // Update attempts.
            $user->profile_field_campusonline_id_attempts = $attempt + 1;
            $result = profile_save_data($user);

            // Log.
            $message = get_string('info:couldnotfindperson', 'enrol_campusonline', ['uid' => $uid, 'userid' => $userid, 'attempt' => $attempt]);
            locallib::write_log('identify_user', $message, 0, null, $this->trace, 3);
            continue;

        }
    }

    /**
     * Syncs modified courses.
     *
     * @return void
     */
    public function sync_course_delta() {

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
                $result = $this->rest_call($endpoint, $query);
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
            $this->sync_courses([$course_uid]);
        }
    }

    /**
     * Syncs courses.
     *
     * @param string $course_uids if provided, only syncs these courses.
     * @return void
     */
    public function sync_courses($course_uids = null) {

        global $CFG, $DB;

        require_once("$CFG->dirroot/course/lib.php");

        // Get course(s).
        if ($course_uids) {
            $courses = $this->get_courses($course_uids);
        } else {
            $courses = $this->get_courses();
        }

        // Get config.
        $updatecourseurls = $this->config->updatecourseurls;
        $updatecourses = $this->config->updateexistingcourses;

        // Start output.
        $number = count($courses);
        if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
            $this->trace->output(get_string('info:syncingcourses', 'enrol_campusonline', $number));
        }

        // Sync courses.
        foreach ($courses as $coursedata) {

            $course_uid = $coursedata['course:uid'];

            // Skip courses that are not in the configured orgs.
            if ($orgfilter = $this->config->orgfilter) {
                $orgs = explode(',', $orgfilter);
                if (!in_array($coursedata['org:uid'], $orgs)) {
                    if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
                        $this->trace->output(get_string('info:skippingcourseorgfilter', 'enrol_campusonline', $course_uid));
                    }
                    continue;
                }
            }

            // Determine course sync strategy.
            if (!$strategy = self::get_course_sync_strategy($coursedata)) {
                continue;
            }

            // Start sync & log.
            if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
                $this->trace->output(get_string('info:syncingcourse', 'enrol_campusonline', ['course_uid' => $course_uid, 'strategy' => $strategy]));
            }

            // Get groups if necessary.
            if ($strategy !== self::FLAT_COURSE) {
                $groups = $this->get_course_groups($course_uid);
            } else {
                $groups = [0 => 'dummy'];
            }

            foreach ($groups as $group_uid => $group_name) {

                // Prepare new course data.
                if ($strategy == self::GROUP_TO_COURSE) {
                    $idnumber = "$course_uid:$group_uid";
                    $newcourse = locallib::build_course($coursedata, $group_name, $group_uid);
                } else {
                    $idnumber = $course_uid;
                    $newcourse = locallib::build_course($coursedata);
                }

                // Get category (only for first loop).
                if (!$newcourse['category'] = $this->get_course_category($coursedata)) {
                    continue;
                }

                // Try to find existing course.
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
                        locallib::set_custom_course_fields($courseid, $coursedata);

                        // Log success.
                        if ($strategy == self::GROUP_TO_COURSE) {
                            $this->set_moodle_course_url($course, $group_uid);
                            $message = get_string('info:createdmoodlecoursegroup', 'enrol_campusonline', ['courseid' => $courseid, 'course_uid' => $course_uid, 'group_uid' => $group_uid]);
                        } else {
                            $this->set_moodle_course_url($course);
                            $message = get_string('info:createdmoodlecourse', 'enrol_campusonline', ['courseid' => $courseid, 'course_uid' => $course_uid]);
                        }
                        locallib::write_log('create_course', $message, 0, $courseid, $this->trace, 3);

                    } else {

                        // Log error.
                        if ($strategy == self::GROUP_TO_COURSE) {
                            $message = get_string('error:couldnotcreatemoodlecoursegroup', 'enrol_campusonline', ['course_uid' => $course_uid, 'group_uid' => $group_uid]);
                        } else {
                            $message = get_string('error:couldnotcreatemoodlecourse', 'enrol_campusonline', $course_uid);
                        }
                        locallib::write_log('create_course', $message, 2, null, $this->trace, 3);
                    }

                    // Add our enrolment method.
                    locallib::add_enrolment_method($course);

                } else {

                    // Update existing course.
                    $courseid = $course->id;

                    // Update course URL in CAMPUSonline.
                    if ($updatecourseurls == 1) {
                        if ($strategy == self::GROUP_TO_COURSE) {
                            $this->set_moodle_course_url($course, $group_uid);
                        } else {
                            $this->set_moodle_course_url($course);
                        }
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
                        locallib::set_custom_course_fields($courseid, $coursedata);

                        // Log success.
                        if ($strategy == self::GROUP_TO_COURSE) {
                            $message = get_string('info:updatedmoodlecoursegroup', 'enrol_campusonline', ['courseid' => $courseid, 'course_uid' => $course_uid, 'group_uid' => $group_uid]);
                        } else {
                            $message = get_string('info:updatedmoodlecourse', 'enrol_campusonline', ['courseid' => $courseid, 'course_uid' => $course_uid]);
                        }
                        locallib::write_log('update_course', $message, 0, $courseid, $this->trace, 3);
                    }
                }

                // Sync enrolments.
                if ($strategy == self::GROUP_TO_GROUP) {
                    $this->sync_enrolments($course, $groups);
                } else {
                    $this->sync_enrolments($course);
                }

                // Break foreach loop in case of no separate groups.
                if ($strategy !== self::GROUP_TO_COURSE) {
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
    public function sync_enrolments($course, $groups = null) {

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
            $message = get_string('info:createdgrouping', 'enrol_campusonline', $courseid);
            locallib::write_log('sync_groups', $message, 0, $courseid, $this->trace, 3);
        }

        // Check if enrolment method is active.
        if (!$enrol = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'campusonline', 'status' => 0])) {
            $message = get_string('warning:skippingenrolments', 'enrol_campusonline', $courseid);
            locallib::write_log('enrol_user', $message, 1, $courseid, $this->trace, 3);
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
        $enrolments = $this->get_enrolments($course);

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
                if (property_exists($this->config, $rolekey)) {
                    $roleid = $this->config->$rolekey;
                } else {
                    continue;
                }
            } else {
                $roleid = $this->config->studentrole;
            }

            // Skip if role should not be synced.
            if (!$roleid || $roleid == 0) {
                continue;
            }

            // Get Moodle user.
            $uid = $enrolment->personUid;
            $userid = $this->get_moodle_user_id($uid);

            // Create new user if needed & allowed.
            if (!$userid) {
                if ($this->config->enrolsynccreateusers) {

                    if (!$userid = $this->create_moodle_user($uid)) {
                        continue;
                    }

                } else {

                    // Log warning.
                    $message = get_string('warning:skippingenrolment', 'enrol_campusonline', $uid);
                    locallib::write_log('enrol_user', $message, 1, $courseid, $this->trace, 3);
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
                $message = get_string('info:enrolleduser', 'enrol_campusonline', ['userid' => $userid, 'courseid' => $courseid]);
                locallib::write_log('enrol_user', $message, 0, $courseid, $this->trace, 3);

            // Activate enrolment if needed.
            } else {
                if ($existing_enrolments[$userid]->status == 1) {
                    $enrolment = $existing_enrolments[$userid];
                    $enrolment->status = 0;
                    $enrolment->timemodified = time();
                    $DB->update_record('user_enrolments', $enrolment);

                    // Log success.
                    $message = get_string('info:activatedenrolment', 'enrol_campusonline', ['userid' => $userid, 'courseid' => $courseid]);
                    locallib::write_log('enrol_user', $message, 0, $courseid, $this->trace, 3);
                }
            }

            // Add role.
            if (!user_has_role_assignment($userid, $roleid, $context->id)) {
                $success = role_assign($roleid, $userid, $context->id);
                $message = get_string('info:assignedrole', 'enrol_campusonline', ['roleid' => $roleid, 'userid' => $userid, 'courseid' => $courseid]);
                locallib::write_log('enrol_user', $message, 0, $courseid, $this->trace, 3);
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
                    $message = get_string('info:removedrole', 'enrol_campusonline', ['roleid' => $role->roleid, 'userid' => $userid, 'courseid' => $courseid]);
                    locallib::write_log('enrol_user', $message, 0, $courseid, $this->trace, 3);
                }
            }

            // Suspend enrolments with no roles left.
            $roles = get_user_roles($context, $userid);
            if (empty($roles)) {
                $enrolment = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $userid]);
                $enrolment->status = 1;
                $DB->update_record('user_enrolments', $enrolment);
                $message = get_string('info:suspendedenrolment', 'enrol_campusonline', ['userid' => $userid, 'courseid' => $courseid]);
                locallib::write_log('enrol_user', $message, 0, $courseid, $this->trace, 3);
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
                $group->description = self::CREATED_BY;
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

                $message = get_string('info:createdmoodlegroup', 'enrol_campusonline', ['groupname' => $group->name, 'group_uid' => $group_uid]);
                locallib::write_log('sync_groups', $message, 0, $courseid, $this->trace, 3);
            }

            // Add to array with moodle group ids for later cleanup.
            $group_members_moodle[$groupid] = $members;

            // Add members.
            foreach ($members as $userid) {
                if (!groups_is_member($groupid, $userid)) {
                    groups_add_member($groupid, $userid);
                    $message = get_string('info:addedusertogroup', 'enrol_campusonline', ['userid' => $userid, 'groupname' => $group->name]);
                    locallib::write_log('sync_groups', $message, 0, $courseid, $this->trace, 3);
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
                    $message = get_string('info:removeduserfromgroup', 'enrol_campusonline', ['userid' => $member->id, 'groupname' => $group->name]);
                    locallib::write_log('sync_groups', $message, 0, $courseid, $this->trace, 3);
                }
            }
        }
    }

    /**
     * Syncs selected organisations from CAMPUSonline.
     *
     * @return void
     */
    public function sync_orgs() {

        // Get all organisations.
        $this->get_org_data();

        // Get selected organisations.
        $orgids = array();
        $endpoint = 'co-brm-core/org/api/selected-organisation-uids';
        $key = $this->config->orgkey;
        $query = ['key' => $key];
        $result = $this->rest_call($endpoint, $query);
        if (property_exists($result, 'items')) {
            foreach ($result->items as $item) {
                if (property_exists($item, 'organisationUids')) {
                    $orgids = $item->organisationUids;
                }
            }
        }

        // Start output.
        $number = count($orgids);
        if (PHP_SAPI == 'cli' || array_key_exists('traceoutput', $_GET)) {
            $this->trace->output(get_string('info:syncingorganisations', 'enrol_campusonline', $number));
        }

        // Sort selected organisations by number of parents that are also selected,
        // so that sync progresses in the correct order.
        $sorted_orgids = array();
        foreach ($orgids as $orgid) {
            $parents = 0;
            $org = $this->orgdata[$orgid];
            while (property_exists($org, 'parentUid')) {
                $parentid = $org->parentUid;
                $org = $this->orgdata[$parentid];
                $parents++;
            }
            $sorted_orgids[$parents][] = $orgid;
        }
        asort($sorted_orgids);

        // Sync organisations.
        foreach ($sorted_orgids as $orgids) {
            foreach ($orgids as $orgid) {
                $this->sync_org($orgid);
            }
        }
    }

    /**
     * Syncs a single organisation and its role assignments.
     *
     * @param string $orgid id of org to be synced
     *
     * @return void
     */
    public function sync_org($orgid) {

        global $DB;

        // Get org.
        $org = $this->orgdata[$orgid];
        $org_uid = $org->uid;

        // Get parent.
        if (property_exists($org, 'parentUid')) {
            $parentid = $org->parentUid;
            if ($parent_cat = $DB->get_record('course_categories', ['idnumber' => $parentid])) {
                $parent = $parent_cat->id;
            } else {
                // Log error.
                $message = get_string('error:couldnotcreatecategory', 'enrol_campusonline', ['org_uid' => $org_uid, 'parentid' => $parentid]);
                locallib::write_log('sync_org', $message, 2, null, $this->trace, 3);
                return;
            }
        } else {
            $parent = $this->config->rootcoursecategory;
        }

        // Get course category.
        if ($category = $DB->get_record('course_categories', ['idnumber' => $org_uid])) {

            // Get category object.
            $categoryid = $category->id;
            $cat = \core_course_category::get($categoryid);

            // Check if something needs updating.
            $actions = array();
            $name = locallib::normalize_value($org->name);
            if ($category->name != $name) {
                $cat->__set('name', $name);
                $actions[] = "updated";
            }

            // Move category.
            if ($category->parent != $parent) {
                $cat->change_parent($parent);
                $actions[] = "moved";
            }

            if (count($actions) > 0) {
                $actions = implode('&', $actions);
                $actions = ucfirst($actions);
                $message = get_string('info:updatedcategory', 'enrol_campusonline', ['actions' => $actions, 'categoryid' => $category->id, 'org_uid' => $org_uid, 'categoryname' => $category->name]);
                locallib::write_log('sync_org', $message, 0, null, $this->trace, 3);
            } else {
                $message = get_string('info:categoryexists', 'enrol_campusonline', ['categoryid' => $category->id, 'org_uid' => $org_uid, 'categoryname' => $category->name]);
                locallib::write_log('sync_org', $message, 0, null, $this->trace, 3);
            }

        } else {

            // Create new course category.
            $data = new \stdClass();
            $data->name = locallib::normalize_value($org->name);
            $data->parent = $parent;
            $data->idnumber = $org_uid;
            $data->description = self::CREATED_BY;
            $data->timecreated = time();
            $data->timemodified = time();
            if ($category = \core_course_category::create($data)) {
                $message = get_string('info:createdcategory', 'enrol_campusonline', ['categoryid' => $category->id, 'org_uid' => $org_uid, 'categoryname' => $category->name]);
                locallib::write_log('sync_org', $message, 0, null, $this->trace, 3);
            } else {
                $message = get_string('error:couldnotcreatecategorysimple', 'enrol_campusonline', $org_uid);
                locallib::write_log('sync_org', $message, 2, null, $this->trace, 3);
            }
        }

        // Sync org enrollments.
        if ($this->config->sync_orgroles && $this->orgroles) {
            $this->sync_org_enrolments($org_uid, $category->id);
        }
    }

    /**
     * Syncs role assignments for a single organisation.
     *
     * @param string $org_uid
     * @param string $categoryid
     *
     * @return void
     */
    public function sync_org_enrolments($org_uid, $categoryid) {

        global $DB;

        foreach ($this->orgroles as $roleid => $rolename) {

            $userids = array();

            // Convert moodle rolename to CO rolename.
            $co_role = strtoupper(substr($rolename, 3));

            // Get role assignments from CO.
            $endpoint = 'co-auth/auth/api/person-uids';
            $query = [
                'role_name' => $rolename,
                'context' => "org-$org_uid"
            ];
            if ($result = $this->rest_call($endpoint, $query))
            if (property_exists($result, 'items')) {
                foreach ($result->items as $uid) {
                    if ($userid = $this->get_moodle_user_id($uid)) {
                        $userids[] = $userid;
                    }
                }
            }

            // Remove Moodle roles.
            $context = \context_coursecat::instance($categoryid);
            $users = get_role_users($roleid, $context);
            foreach ($users as $user) {
                if (!in_array($user->id, $userids)) {
                    role_unassign($roleid, $user->id, $context->id);
                    $message = get_string('info:removedrolefromuser', 'enrol_campusonline', ['roleid' => $roleid, 'userid' => $user->id, 'org_uid' => $org_uid]);
                    locallib::write_log('sync_org_roles', $message, 0, null, $this->trace, 5);
                }
            }

            // Assign Moodle roles.
            foreach ($userids as $userid) {
                if (!user_has_role_assignment($userid, $roleid, $context->id)) {
                    $success = role_assign($roleid, $userid, $context->id);
                    $message = get_string('info:assignedroletouser', 'enrol_campusonline', ['roleid' => $roleid, 'userid' => $userid, 'org_uid' => $org_uid]);
                    locallib::write_log('sync_org_roles', $message, 0, null, $this->trace, 5);
                }
            }
        }
    }

    /**
     * Syncs users.
     *
     * @return void
     */
    public function sync_users() {

        $persons = $this->get_persons();

        foreach ($persons as $person) {

            // Get Moodle user.
            $uid = $person['uid'];
            $userid = $this->get_moodle_user_id($uid);

            // Create new user if needed & allowed.
            if (!$userid) {
                if ($this->config->enrolsynccreateusers) {

                    if (!$userid = $this->create_moodle_user($uid)) {
                        continue;
                    }

                } else {

                    // Log warning.
                    $message = get_string('warning:skippedsyncinguserdata', 'enrol_campusonline', $uid);
                    locallib::write_log('sync_user', $message, 1, $courseid, $this->trace, 3);
                    continue;
                }
            }

            // Load Moodle User.
            $user = \core_user::get_user($userid);
            $this->update_moodle_user($user, $person);
        }
    }

    /**
     * Updates a single Moodle users's data.
     *
     * @param object $user
     * @param array $person
     *
     * @return void
     */
    public function update_moodle_user($user, $person) {

        $uid = $person['uid'];
        $needsupdate = false;
        foreach (locallib::USER_FIELDS as $field => $default) {

            // Only update username if allowed.
            if ($field == 'username' && !$this->config->user_allowusernameupdate) {
                continue;
            }

            // Only update email if allowed.
            if ($field == 'email' && !$this->config->user_allowemailupdate) {
                continue;
            }

            $value = locallib::get_field_value("user_$field", $person);

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
        $needsupdatep = locallib::set_custom_user_fields($user, $person);

        // Log update.
        if ($needsupdate || $needsupdatep) {
            $message = get_string('info:updatedmoodleuser', 'enrol_campusonline', ['userid' => $user->id, 'uid' => $uid]);
            locallib::write_log('sync_user', $message, 0, null, $this->trace, 3);
        } else {

            // Write trace output that no update is necessary.
            if (array_key_exists('traceoutput', $_GET)) {
                $this->trace->output(get_string('info:noupdatenecessary', 'enrol_campusonline', ['userid' => $user->id, 'uid' => $uid]));
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
    private function create_moodle_user($uid) {

        global $CFG;

        // Get full person data from CAMPUSonline.
        $userdata = $this->get_person_data($uid);

        // Build user.
        $user = new \stdClass();
        foreach (locallib::USER_FIELDS as $field => $default) {
            $value = locallib::get_field_value('user_' . $field, $userdata);

            // Sanitize usernames.
            if ($field == 'username') {
                $value = strtolower($value);
            }

            $user->$field = $value;
        }
        $user->auth = locallib::get_field_value('user_auth', $userdata);
        $user->password = locallib::get_field_value('user_password', $userdata);
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;

        foreach (locallib::USER_FIELDS_NOEMPTY as $check) {
            if ($user->$check == '') {
                $message = get_string('error:couldnotcreateuser', 'enrol_campusonline', ['uid' => $uid, 'check' => $check]);
                locallib::write_log('create_user', $message, 2, null, $this->trace, 3);
                return null;
            }
        }

        // Create user.
        if ($userid = user_create_user($user)) {
            $message = get_string('info:createdmoodleuser', 'enrol_campusonline', ['userid' => $userid, 'uid' => $uid]);
            $status = 0;

        } else {
            $message = get_string('error:couldnotcreateuser', 'enrol_campusonline', $uid);
            $status = 2;
        }

        // Update custom fields.
        $user = \core_user::get_user($userid);
        locallib::set_custom_user_fields($user, $userdata);
        locallib::write_log('create_user', $message, $status, null, $this->trace, 3);
        return $userid;
    }

    /**
     * Enriches courses with additional data from other endpoints.
     *
     * @param array $courses
     *
     * @return array $courses
     */
    private function enrich_courses($courses) {

        $this->get_org_data();
        $this->get_semester_data();

        $enriched_courses = array();
        foreach ($courses as $course) {

            $sanitized_course = array();
            $course = (array)$course;

            // Values from course endpoint.
            foreach ($course as $key => $value) {
                $value = locallib::normalize_value($value);
                $sanitized_course["course:$key"] = $value;
            }

            // Attach values from org endpoint.
            $org = $this->orgdata[$course['organisationUid']];
            $org = (array)$org;

            foreach ($org as $key => $value) {
                $value = locallib::normalize_value($value);
                $sanitized_course["org:$key"] = $value;
            }

            // Attach values from semester endpoint.
            $semester = $this->semesterdata[$course['semesterKey']] ?? null;
            $semester = (array)$semester;

            foreach ($semester as $key => $value) {
                $value = locallib::normalize_value($value);
                $sanitized_course["semester:$key"] = $value;
            }

            $enriched_courses[] = $sanitized_course;
        }

        return $enriched_courses;
    }

    /**
     * Gets org data from CAMPUSonline.
     */
    private function get_org_data() {

        if ($this->orgdata) {
            return;
        }

        $endpoint = 'co-brm-core/org/api/organisations';
        $result = $this->rest_call($endpoint, null, 'GET', true);

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
    private function get_semester_data() {

        if ($this->semesterdata) {
            return;
        }

        $endpoint = 'co-sm-core/semester/api/semesters';
        $result = $this->rest_call($endpoint, null, 'GET', true);

        // Add to class property.
        $this->semesterdata = array();
        if (property_exists($result, 'items')) {
            foreach ($result->items as $item) {
                $this->semesterdata[$item->key] = $item;
            }
        }
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
    private function rest_call($endpoint, $query = null, $method = 'GET', $alwayspage = false) {

        // Set base URL and initialize the HTTP client.
        $url = $this->config->endpoint . '/' . $endpoint;
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 100.0,
            'connect_timeout' => 10.0,
        ]);

        // Set payload key.
        if ($method == 'GET') {
            $payload = 'query';
        } else {
            $payload = 'json';
        }

        // Initialize variables.
        $all_items = [];
        $cursor = null;
        $response_object = new \stdClass();
        $page = !array_key_exists('limit', $_GET) || $alwayspage;

        do {

            // Update the query with the cursor, if available.
            if ($cursor !== null) {
                $query['cursor'] = $cursor;

                // Debug message.
                if ($this->config->restcalls && PHP_SAPI === 'cli') {
                    $count = count($all_items);
                    $this->trace->output(get_string('info:pagingcursor', 'enrol_campusonline', ['cursor' => $cursor, 'count' => $count]));
                }
            }

            // Make the API request.
            $retry = 0; // Track retries.
            $retries = 3; // Define the maximum number of retries.
            do {

                // Update the token.
                $this->update_token();

                try {

                    // Debug message.
                    if ($this->config->restcalls && PHP_SAPI === 'cli') {
                        if ($query) {
                            $json_query = json_encode($query);
                        } else {
                            $json_query = '';
                        }
                        $this->trace->output(get_string('info:apirequest', 'enrol_campusonline', ['method' => $method, 'endpoint' => $endpoint, 'query' => $json_query]));
                    }

                    $response = $client->request($method, $url, [
                        'headers' => [
                            'accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $this->token
                        ],
                        $payload => $query
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

                    // Exit the loop on success
                    break;

                // Error handling.
                } catch (\Throwable $e) {
                    $retry++;
                    if ($retry > $retries) {
                        $error = $e->getMessage();
                        $message = get_string('error:requestexception', 'enrol_campusonline', $error);
                        locallib::write_log('error', $message, 2, null, $this->trace);

                        // Create object with exception message.
                        $response_object = new \stdClass();
                        $response_object->exception = $error;
                        return $response_object;
                    } else {
                        locallib::write_log('warning', get_string('warning:retryingfailedrequest', 'enrol_campusonline', $e->getMessage()), 1, null, $this->trace);
                        sleep(3);
                    }
                }
            } while (true);

        } while ($page && $cursor !== null);

        // Return the complete collection of items.
        if ($all_items) {
            return (object)[
                'items' => $all_items
            ];
        }
        return ($response_object);
    }

    /**
     * Sets the moodle course URL in CAMPUSonline.
     *
     * @param object $course
     * @param string $group_uid
     *
     * @return void
     */
    private function set_moodle_course_url($course, $group_uid = null) {

        global $DB;

        // Add own uid.
        $course_uid = explode(':', $course->idnumber)[0];
        $course_uids[] = $course_uid;

        // Add other uids.
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $datas = $handler->get_instance_data($course->id);
        $metadata = [];
        foreach ($datas as $data) {
            if (empty($data->get_value())) {
                continue;
            }
            $cat = $data->get_field()->get_category()->get('name');
            $metadata[$data->get_field()->get('shortname')] = $cat . ': ' . $data->get_value();
        }

        // Get course custom field directly via DB, to avoid having to deal with custom field API.
        $other_uids = $DB->get_field('customfield_data', 'charvalue',
            ['instanceid' => $course->id, 'fieldid' => $this->customfieldids['campusonline_other_co_course_uids']]);
        if ($other_uids) {
            $other_uids = preg_split('/\s*,\s*/', $other_uids);
            $course_uids = array_merge($course_uids, $other_uids);
        }

        // Create URL.
        $endpoint = 'co-tm-core/course/api/e-learning-infos';
        $moodle_url = new moodle_url('/course/view.php', array('id' => $course->id));
        $url = $moodle_url->__toString();

        $query['externalUrl'] = $url;
        if ($group_uid) {
            $query['courseGroupUid'] = $group_uid;
        }

        // Set Moodle course URL in CAMPUSonline.
        foreach ($course_uids as $course_uid) {
            $query['courseUid'] = $course_uid;

            // Log success.
            if ($result = $this->rest_call($endpoint, $query, 'POST')) {
                if (property_exists($result, 'externalUrl')) {
                    $url = $result->externalUrl;
                    $message = get_string('info:updatedcampuscourse', 'enrol_campusonline', ['course_uid' => $course_uid, 'url' => $url]);
                    locallib::write_log('update_course', $message, 0, $course->id, $this->trace, 3);
                    continue;
                }

                // Error.
                $message = get_string('error:couldnotupdatecampuscourse', 'enrol_campusonline', ['course_uid' => $course_uid, 'url' => $url]);
                locallib::write_log('update_course', $message, 2, $course->id, $this->trace, 3);
            }
        }
    }

    /**
     * Gets access token for REST calls.
     *
     * @return string $token
     */
    private function update_token() {

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
            'timeout' => 100.0,
            'connect_timeout' => 10.0,
        ]);

        try {

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

        // Error handling.
        } catch (\Throwable $e) {

            $error = $e->getMessage();
            $this->error = $error;
            $message = get_string('error:requestexception', 'enrol_campusonline', $error);
            locallib::write_log('update_token', $message, 2, null, $this->trace);
        }
    }
}
