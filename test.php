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
 * @package    local_campusonline_extension
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_login();
require_admin();

use local_campusonline_extension\sync;

// Get function.
$function = required_param('function', PARAM_RAW);

// Set page.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/local/idpush/logs.php');
$PAGE->set_title(get_string('pluginname', 'local_campusonline_extension'));
$PAGE->set_heading(get_string($function, 'local_campusonline_extension'));

// Init sync.
$sync = new sync;

// Test connection.
if ($function == 'connection') {

    if ($sync->isConnected()) {
        \core\notification::add(get_string('success:connected', 'local_campusonline_extension'),
            \core\output\notification::NOTIFY_SUCCESS);
    } else {
        $error = $sync->getError();
        \core\notification::add(get_string('error:cannotconnect', 'local_campusonline_extension', $error),
            \core\output\notification::NOTIFY_ERROR);
    }

    // Redirect to settings page.
    redirect(new moodle_url('/admin/settings.php?section=local_campusonline_extension_connection'));

// Preview course sync.
} elseif ($function == 'previewcourses') {


    $content = $sync->getCourses();

}

echo $OUTPUT->header();

echo '<a class="btn btn-secondary m-1"
    href=' . new moodle_url('/admin/settings.php?section=local_campusonline_extension_connection') . '>' .
    get_string('backtosettings', 'local_campusonline_extension') . '</a>';

echo "<pre>";
print_r($content);
echo "</pre>";


echo $OUTPUT->footer();



