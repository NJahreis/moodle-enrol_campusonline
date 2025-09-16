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
 * Moodle page for menually testing the CAMPUSonline sync.
 *
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

// Set page.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/enrol/campusonline/customquery.php');
$PAGE->set_title(get_string('pluginname', 'enrol_campusonline'));
$PAGE->set_heading(get_string('customquery', 'enrol_campusonline'));

// Begin output.
echo $OUTPUT->header();

$mform = new \enrol_campusonline\form\customquery_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {

    // Form cancelled, redirect to module settings.
    redirect(new moodle_url('/admin/settings.php', ['section' => 'enrolsettingscampusonline']));

} else if ($data = $mform->get_data()) {

    // Init sync.
    $trace = new \text_progress_trace();
    $sync = new sync($trace);

    // Get data for custom query.
    $endpoint = $data->endpoint;
    $parameters = json_decode($data->parameters, true) ?: [];

    // Do the call.
    $result = $sync->rest_call($endpoint, $parameters);

}

$mform->display();

// Show result.
if (isset($result)) {
    echo '<pre>' . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
}

echo $OUTPUT->footer();



