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
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

require_once('../../config.php');

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

global $DB;

// Check permissions.
$courseid = required_param('courseid', PARAM_RAW);
$course = get_course($courseid);
$context = context_course::instance($courseid);
require_login($course);
require_capability('enrol/campusonline:synccourse', $context);

// Set page.
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/enrol/campusonline/sync_course.php', array('courseid' => $courseid));
$PAGE->set_title(get_string('syncthiscourse', 'enrol_campusonline'));
$PAGE->set_heading(get_string('syncthiscourse', 'enrol_campusonline'));

// Init sync.
$trace = new \text_progress_trace();
$sync = new sync($trace);

// Start output.
echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('syncingcourse', 'enrol_campusonline'));
echo "<pre>";

if ($sync->isConnected()) {

    // Sync course.
    $course_uid = explode(':', $course->idnumber)[0];
    $sync->syncCourses([$course_uid]);

} else {

    // Write error.
    $message = get_string('connectionerror', 'enrol_campusonline');
    $trace->output($message);
}

echo "</pre>";

// Back to course button.
$url = new moodle_url('/user/index.php', array('id' => $courseid));
echo html_writer::link($url, get_string('back'), array('class' => 'btn btn-secondary m-1'));
echo $OUTPUT->footer();



