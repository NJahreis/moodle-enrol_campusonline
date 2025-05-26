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
$trace = new \text_progress_trace();
$sync = new sync($trace);

// Test connection.
if ($function == 'testconnection') {

    if (!enrol_is_enabled('campusonline')) {
        \core\notification::add(get_string('error:notenabled', 'enrol_campusonline'),
            \core\output\notification::NOTIFY_ERROR);
    } elseif ($sync->is_connected()) {
        \core\notification::add(get_string('success:connected', 'enrol_campusonline'),
            \core\output\notification::NOTIFY_SUCCESS);
    } else {
        $error = $sync->get_error();
        \core\notification::add(get_string('error:cannotconnect', 'enrol_campusonline', $error),
            \core\output\notification::NOTIFY_ERROR);
    }

    redirect(new moodle_url('/admin/settings.php', array('section' => 'enrolsettingscampusonline')));
}

// Begin output.
echo $OUTPUT->header();

$url = new moodle_url('/admin/settings.php?section=enrolsettingscampusonline');
echo html_writer::link($url, get_string('backtosettings', 'enrol_campusonline'), array('class' => 'btn btn-secondary m-1'));

// Get config.
$orgfilter = get_config('enrol_campusonline', 'orgfilter');
$orgs = explode(',', $orgfilter);

// Show raw course data.
if ($function == 'showrawcoursedata') {

    // Count and get tokens.
    $courses = $sync->get_courses(null, $limit);
    $count = 0;
    $tokens = array();

    foreach ($courses as $key => $course) {

        // Skip courses that are not in the configured orgs.
        if ($orgfilter) {
            if (!in_array($course['org:uid'], $orgs)) {
                unset($courses[$key]);
                continue;
            }
        }

        foreach ($course as $key => $value) {
            if (is_scalar($value)) {
                $tokens[$key] = $key;
            }
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
    foreach ($courses as $course) {
        echo html_writer::tag('h4', $course['course:title']);
        foreach ($course as $key => $value) {
            if (is_scalar($value)) {
                echo '<strong>{' . $key . '}</strong>: ' . $value . '<br>';
            }
        }
        echo '<br>';
    }
    echo '</pre>';

// Preview course sync.
} elseif ($function == 'previewcourses') {

    // Table header.
    $table = new html_table();
    $table->head = ['CO course_uid', 'mode', 'coursecategory', 'idnumber', 'shortname', 'fullname'];
    $table->head = array_merge($table->head, array_keys(locallib::COURSE_FIELDS));
    $customfields = locallib::get_custom_course_field_data(null);
    $table->head = array_merge($table->head, $customfields);

    // Table data.
    $count = 0;
    $data = array();
    $courses = $sync->get_courses(null, $limit);

    // No courses found.
    if (!$courses) {
        echo \html_writer::div(
            get_string('nocoursesfound', 'enrol_campusonline'),
            'alert alert-warning',
            ['role' => 'alert']
        );
    }

    foreach ($courses as $key => $coursedata) {

        $course_uid = $coursedata['course:uid'];

        // Skip courses that are not in the configured orgs.
        if ($orgfilter) {
            if (!in_array($coursedata['org:uid'], $orgs)) {
                unset($coursedata[$key]);
                continue;
            }
        }

        // Get course sync strategy.
        if (!$strategy = $sync->get_course_sync_strategy($coursedata)) {
            $mode = 'SKIP';
        } else {
            $mode = $strategy;
        }
        $co = [$course_uid, $mode];

        if ($strategy == $sync::GROUP_TO_COURSE) {
            $groups = $sync->get_course_groups($course_uid);
        } else {
            $groups = [0 => 'dummy'];
        }

        // Loop through groups.
        foreach ($groups as $group_uid => $group_name) {

            // Prepare new course data.
            if ($strategy == $sync::GROUP_TO_COURSE) {
                $course = locallib::build_course($coursedata, $group_name, $group_uid);
            } else {
                $course = locallib::build_course($coursedata);
            }
            $categoryid = $sync->get_course_category($coursedata);

            // Add custom fields.
            $customfields = locallib::get_custom_course_field_data($coursedata);
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
            $data[] = array_merge($co, $course);
            $count++;
        }
    }

    $table->data = $data;

    echo html_writer::tag('h3', get_string('coursecount_syncdata', 'enrol_campusonline',
        array('co' => count($courses), 'moodle' => $count)));
    echo html_writer::table($table);

// Show raw user data.
} elseif ($function == 'showrawuserdata') {

    // Count and get tokens.
    $persons = $sync->get_persons(null, $limit);
    $tokens = array();
    foreach ($persons as $person) {
        $person = (array) $person;
        foreach ($person as $key => $value) {
            if (is_scalar($value)) {
                $tokens[$key] = $key;
            }
        }
    }

    // List tokens.
    echo html_writer::tag('h3', get_string('availabletokens', 'enrol_campusonline', count($persons)));
    echo get_string('availabletokens_disclaimer', 'enrol_campusonline');
    echo '<ul>';
    foreach ($tokens as $token) {
        echo '<li>{' . $token . '}</li>';
    }
    echo '</ul>';

    // List raw user data.
    echo html_writer::tag('h3', get_string('usercount', 'enrol_campusonline', count($persons)));
    foreach ($persons as $person) {
        echo html_writer::tag('h4', $person['givenName'] . ' ' . $person['surname']);
        foreach ($person as $key => $value) {
            if (is_scalar($value)) {
                echo '<strong>{' . $key . '}</strong>: ' . $value . '<br>';
            }
        }
        echo '<br>';
    }

// Preview user sync.
} elseif ($function == 'previewusers') {

    // Table header.
    $table = new html_table();
    $table->head = ['auth', 'password'];
    $table->head = array_merge($table->head, array_keys(locallib::USER_FIELDS));
    $customfields = locallib::get_custom_user_field_data(null);
    $table->head = array_merge($table->head, $customfields);

    // Table data.
    $data = array();
    $persons = $sync->get_persons(null, $limit);

    foreach ($persons as $persondata) {

        // Get person data.
        $user = locallib::build_user($persondata);

        // Add custom fields.
        foreach ($customfields as $key => $value) {
            $user['user_profilefield_' . $key] = $value;
        }

        // Add to table.
        $data[] = $user;

    }

    $table->data = $data;

    echo html_writer::tag('h3', get_string('usercount_syncdata', 'enrol_campusonline', $limit));
    echo html_writer::table($table);
}

echo $OUTPUT->footer();



