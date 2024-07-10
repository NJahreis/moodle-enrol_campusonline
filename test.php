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

// Set page.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/idpush/logs.php');
$PAGE->set_title(get_string('pluginname', 'enrol_campusonline'));
$PAGE->set_heading(get_string($function, 'enrol_campusonline'));

// Init sync.
$sync = new sync;

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

// Preview course sync.
if ($function == 'showrawcoursedata') {

    if ($courses = $sync->getCourses()) {
        echo html_writer::tag('h3', get_string('coursecount', 'enrol_campusonline', count($courses)));
        echo '<pre>';
        print_r($courses);
        echo '</pre>';
    }

// Show actual course data.
} elseif ($function == 'coursepreview') {

    $table = new html_table();
    $table->head = ['idnumber', 'shortname', 'fullname'];
    $table->head = array_merge($table->head, locallib::COURSE_FIELDS);
    $customfields = locallib::getCourseCustomFields(null);
    $table->head = array_merge($table->head, $customfields);
    $table->head[] = 'coursecategory';
    $table->align = array('right', 'left', 'left');

    $data = array();
    $courses = $sync->getCourses();

    foreach ($courses as $coursedata) {
        $course = locallib::buildCourse($coursedata);
        $customfields = locallib::getCourseCustomFields($coursedata);
        $categoryid = locallib::getCourseCategory($coursedata);

        // Add custom fields.
        foreach ($customfields as $key => $value) {
            $course['customfield_' . $key] = $value;
        }

        // Convert category id to linked name.
        $categoryname = $DB->get_field('course_categories', 'name', ['id' => $categoryid]);
        $url = new moodle_url('/course/index.php', array('id' => $categoryid));
        $course['coursecategory'] = html_writer::link($url, $categoryname);

        // Add to table.
        $data[] = $course;
    }

    $table->data = $data;

    echo html_writer::tag('h3', get_string('coursecount', 'enrol_campusonline', count($courses)));
    echo html_writer::table($table);

// Preview course sync.
} elseif ($function == 'showrawuserdata') {

    $courses = $sync->getPersons();

    echo html_writer::tag('h3', get_string('coursecount', 'enrol_campusonline', count($courses)));
    echo '<pre>';
    print_r($courses);
    echo '</pre>';

// For development only. TODO: remove
} elseif ($function == 'sync_courses') {
    $sync->syncCourses();
}

echo $OUTPUT->footer();



