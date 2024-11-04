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

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

global $DB;

// Check permissions.
$userid = required_param('userid', PARAM_RAW);
$context = context_system::instance();
require_login($course);
require_capability('enrol/campusonline:syncuser', $context);

// Set page.
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/enrol/campusonline/sync_user.php',
    array('userid' => $userid, 'limit' => 1));
$PAGE->set_title(get_string('syncthisuser', 'enrol_campusonline'));
$PAGE->set_heading(get_string('syncthisuser', 'enrol_campusonline'));

// Init sync.
$trace = new \text_progress_trace();
$sync = new sync($trace);

// Start output.
echo $OUTPUT->header();
echo html_writer::tag('h3', get_string('syncinguser', 'enrol_campusonline'));
echo "<pre>";

if ($sync->isConnected()) {

    // Get person UID.
    $userid = required_param('userid', PARAM_INT);
    $user = \core_user::get_user($userid);

    // Identify user first.
    if (!$person_uid = locallib::getPersonUid($userid)) {
        $users = [$user];
        $sync->identifyMoodleUsers($users);
    }

    if ($person_uid = locallib::getPersonUid($userid)) {

        // Sync user.
        $person_uids = [$person_uid];
        $persons = $sync->getPersons($person_uids);
        $person = reset($persons);
        $sync->updateMoodleUser($user, $person);
    }

} else {

    // Write error.
    $message = get_string('connectionerror', 'enrol_campusonline');
    $trace->output($message);
}

echo "</pre>";

// Back to user button.
$url = new moodle_url('/user/profile.php', array('id' => $userid));
echo html_writer::link($url, get_string('back'), array('class' => 'btn btn-secondary m-1'));
echo $OUTPUT->footer();



