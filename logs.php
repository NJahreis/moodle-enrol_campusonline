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
 * CAMPUSOnline enrolment plugin.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

global $DB;

// Get orgid.
$orgid = optional_param('orgid', null, PARAM_INT);

// Check capabilities.
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Set page.
$PAGE->set_context($context);
$PAGE->set_url('/enrol/campusonline/logs.php');

// Set page.
$PAGE->set_title(get_string('pluginname', 'enrol_campusonline'));
$PAGE->set_heading(get_string('logs', 'enrol_campusonline'));

// Create table.
$table = new \enrol_campusonline\log_table($orgid);
$table->is_downloadable(true);

// Output page.
echo $OUTPUT->header();
$table->out();
echo $OUTPUT->footer();
