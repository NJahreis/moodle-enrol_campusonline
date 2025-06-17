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
 * Class file for user synchronization task.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_campusonline\task;

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

/**
 * Scheduled task class for user synchronization task.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_id_task extends \core\task\scheduled_task {

    /**
     * Task name.
     */
    public function get_name() {
        return get_string('task:user_id', 'enrol_campusonline');
    }

    /**
     * Executes the task.
     */
    public function execute() {
        global $CFG, $DB;

        // We may need a lot of memory here.
        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        // Fetch all users who are not deleted, not suspended, and not the guest user.
        $sql = "SELECT id, username, idnumber, email
        FROM {user}
        WHERE deleted = 0
        AND suspended = 0
        AND id != :guestuserid";

        // Execute the query and exclude the guest user.
        $params = ['guestuserid' => $CFG->siteguest];
        $users = $DB->get_records_sql($sql, $params);

        // Initialize sync.
        $trace = new \text_progress_trace();
        $sync = new sync($trace);

        if ($sync->is_connected()) {

            // Identify user.
            $sync->identify_moodle_users($users);

        } else {

            // Log error.
            $message = get_string('connectionerror', 'enrol_campusonline');
            $trace->output($message);
            locallib::write_log('connect', $message, 2);
        }
    }
}
