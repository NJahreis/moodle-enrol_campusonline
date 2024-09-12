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
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_login();
require_admin();

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

global $DB;

// Get function.
$function = required_param('function', PARAM_RAW);
$limit = optional_param('limit', 0, PARAM_RAW);

// Set page.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/enrol/campusonline/test.php');
$PAGE->set_title(get_string('pluginname', 'enrol_campusonline'));
$PAGE->set_heading(get_string($function, 'enrol_campusonline'));

// Init sync.
$sync = new sync(new \text_progress_trace());

// Test connection.
if ($function == 'testconnection') {

    if ($sync->isConnected()) {
        \core\notification::add(get_string('success:connected', 'enrol_campusonline'),
            \core\output\notification::NOTIFY_SUCCESS);
    } else {
        $error = $sync->getError();
        \core\notification::add(get_string('error:cannotconnect', 'enrol_campusonline', $error),
            \core\output\notification::NOTIFY_ERROR);
    }

    redirect(new moodle_url('/admin/settings.php', array('section' => 'enrolsettingscampusonline')));
}

// Begin output.
echo $OUTPUT->header();

$url = new moodle_url('/admin/settings.php?section=enrolsettingscampusonline');
echo html_writer::link($url, get_string('backtosettings', 'enrol_campusonline'), array('class' => 'btn btn-secondary m-1'));

// Show raw course data.
if ($function == 'showrawcoursedata') {

    // Count and get tokens.
    $courses = $sync->getCourses($limit);
    $count = 0;
    $tokens = array();
    foreach ($courses as $course) {
        foreach ($course as $key => $value) {
            $tokens[$key] = $key;
        }
    }

    // List tokens.
    echo html_writer::tag('h3', get_string('availabletokens', 'enrol_campusonline'));
    echo '<ul>';
    foreach ($tokens as $token) {
        echo '<li>{' . $token . '}</li>';
    }
    echo '</ul>';

    // List raw course data.
    echo html_writer::tag('h3', get_string('coursecount', 'enrol_campusonline', count($courses)));
    echo '<pre>';
    foreach ($courses as $course) {
        var_dump((object) $course);
    }
    echo '</pre>';

// Preview course sync.
} elseif ($function == 'previewcourses') {

    // Table header.
    $table = new html_table();
    $table->head = ['coursecategory', 'idnumber', 'shortname', 'fullname'];
    $table->head = array_merge($table->head, array_keys(locallib::COURSE_FIELDS));
    $customfields = locallib::getCustomFields(null);
    $table->head = array_merge($table->head, $customfields);

    // Table data.
    $data = array();
    $courses = $sync->getCourses($limit);
    foreach ($courses as $coursedata) {
        $course = locallib::buildCourse($coursedata);
        $categoryid = $sync->getCourseCategory($coursedata);

        // Add custom fields.
        $customfields = locallib::getCustomFields($coursedata);
        foreach ($customfields as $key => $value) {
            $course['customfield_' . $key] = $value;
        }

        // Convert category id to linked name of full category tree.
        $categoryname = '';
        $categoryidforlink = $categoryid;
        while ($categoryid > 0) {
            $category = $DB->get_record('course_categories', ['id' => $categoryid]);
            $categoryname = $category->name . ' / ' . $categoryname;
            $categoryid = $category->parent;
        }
        $categoryname = trim($categoryname, ' / ');
        $url = new moodle_url('/course/index.php', array('categoryid' => $categoryidforlink));
        $course['coursecategory'] = html_writer::link($url, $categoryname);

        // Add to table.
        $data[] = $course;
    }

    $table->data = $data;

    echo html_writer::tag('h3', get_string('coursecount', 'enrol_campusonline', count($courses)));
    echo html_writer::table($table);

// Show raw user data.
} elseif ($function == 'showrawuserdata') {

    // Count and get tokens.
    $persons = $sync->getPersons($limit);
    $count = 0;
    $tokens = array();
    foreach ($persons as $personlist) {
        $count += count($personlist);
        foreach ($personlist as $person) {
            $properties = get_object_vars($person);
            foreach ($properties as $key => $value) {
                $tokens[$key] = $key;
            }
            $persondata = $sync->getPersonData($person->uid);
            $properties = get_object_vars($persondata);
            foreach ($properties as $key => $value) {
                $tokens[$key] = $key;
                $person->$key = $value;
            }
        }
    }

    // List tokens.
    echo html_writer::tag('h3', get_string('availabletokens', 'enrol_campusonline', $count));
    echo get_string('availabletokens_disclaimer', 'enrol_campusonline');
    echo '<ul>';
    foreach ($tokens as $token) {
        echo '<li>{' . $token . '}</li>';
    }
    echo '</ul>';

    // List raw user data.
    echo html_writer::tag('h3', get_string('usercount', 'enrol_campusonline', $count));
    foreach ($persons as $type => $personlist) {

        echo html_writer::tag('h4', get_string($type, 'enrol_campusonline'));
        echo '<pre>';
        foreach ($personlist as $person) {
            var_dump($person);
        }
        echo '</pre>';
    }

// Preview user sync.
} elseif ($function == 'previewusers') {

    // Table header.
    $table = new html_table();
    $table->head = ['auth', 'password'];
    $table->head = array_merge($table->head, array_keys(locallib::USER_FIELDS));
    $customfields = locallib::getCustomUserFields(null);
    $table->head = array_merge($table->head, $customfields);

    // Table data.
    $data = array();
    $persons = $sync->getPersons($limit);

    foreach ($persons as $usertype => $personlist) {
        foreach ($personlist as $person) {

            // Get person data.
            $persondata = $sync->getPersonData($person->uid);
            $userdata = array_merge((array) $person, (array) $persondata);
            $user = locallib::buildUser($userdata);

            // Add custom fields.
            foreach ($customfields as $key => $value) {
                $user['user_profilefield_' . $key] = $value;
            }

            // Add to table.
            $data[] = $user;
        }
    }

    $table->data = $data;

    echo html_writer::tag('h3', get_string('usercount', 'enrol_campusonline', $limit));
    echo html_writer::table($table);
}

echo $OUTPUT->footer();



