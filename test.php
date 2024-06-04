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

// Begin output.
echo $OUTPUT->header();

echo '<a class="btn btn-secondary m-1"
    href=' . new moodle_url('/admin/settings.php?section=enrolsettingscampusonline') . '>' .
    get_string('backtosettings', 'enrol_campusonline') . '</a>';

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

// Preview course sync.
} elseif ($function == 'showrawcoursedata') {

    $courses = $sync->getCourses();

    echo '<h3>' . get_string('coursecount', 'enrol_campusonline', count($courses)) . '</h3>';
    echo '<pre>';
    print_r($courses);
    echo '</pre>';

// Show actual course data.
} elseif ($function == 'previewcourses') {

    $table = new html_table();
    $table->head = ['idnumber', 'shortname', 'fullname', 'coursecategory'];
    $table->align = array('right', 'left', 'left');

    $data = array();
    $courses = $sync->getCourses();
    foreach ($courses as $course) {
        $coursedata['idnumber'] = $course->uid;
        $coursedata['shortname'] = locallib::getCourseField('shortname', $course);
        $coursedata['fullname'] = locallib::getCourseField('fullname', $course);
        $categoryid = locallib::getCourseCategory($course);
        $coursedata['coursecategory'] = $DB->get_field('course_categories', 'name', ['id' => $categoryid]);
        $data[] = $coursedata;
    }

    $table->data = $data;

    echo '<h3>' . get_string('coursecount', 'enrol_campusonline', count($courses)) . '</h3>';
    echo html_writer::table($table);

// For development only. TODO: remove
} elseif ($function == 'sync_courses') {
    $sync->syncCourses();
}


echo $OUTPUT->footer();



