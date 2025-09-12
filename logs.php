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
 * CAMPUSonline enrolment plugin.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

global $DB;

// Check capabilities.
$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Set page.
$PAGE->set_context($context);
$PAGE->set_url('/enrol/campusonline/logs.php');

// Create table.
$download = optional_param('download', '', PARAM_ALPHA);
$table = new \enrol_campusonline\log_table('enrol_campusonline', $PAGE->url);
$table->is_downloading($download, 'test', 'campusonline_logs');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data.
    // Print the page header.
    $PAGE->set_title(get_string('pluginname', 'enrol_campusonline'));
    $PAGE->set_heading(get_string('logs', 'enrol_campusonline'));
    echo $OUTPUT->header();
}

// Back to menu settings link.
$url = new moodle_url('/admin/settings.php?section=enrolsettingscampusonline');
echo html_writer::link($url, get_string('backtosettings', 'enrol_campusonline'), ['class' => 'btn btn-secondary m-1']);

// Work out the sql for the table.
$table->set_sql('*', "{enrol_campusonline_logs}", '1=1');

$table->define_baseurl("$CFG->wwwroot/enrol/campusonline/logs.php");

$perpage = $table->get_default_per_page();
$table->out($perpage, true);

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
