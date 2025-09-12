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
 * Moodle page for controlling the course synchronization.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use enrol_campusonline\sync;

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
$PAGE->set_url('/enrol/campusonline/sync_course.php', ['courseid' => $courseid]);
$PAGE->set_title(get_string('syncthiscourse', 'enrol_campusonline'));
$PAGE->set_heading(get_string('syncthiscourse', 'enrol_campusonline'));

// Init sync.
$trace = new \text_progress_trace();
$sync = new sync($trace);

// Start output.
echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('syncingcourse', 'enrol_campusonline'));
echo "<pre>";

if ($sync->is_connected()) {

    // Sync course.
    $courseuid = explode(':', $course->idnumber)[0];
    $sync->sync_courses([$courseuid]);

} else {

    // Write error.
    $message = get_string('connectionerror', 'enrol_campusonline');
    $trace->output($message);
}

echo "</pre>";

// Back to course button.
$url = new moodle_url('/user/index.php', ['id' => $courseid]);
echo html_writer::link($url, get_string('back'), ['class' => 'btn btn-secondary m-1']);
echo $OUTPUT->footer();



