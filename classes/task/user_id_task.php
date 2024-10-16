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
 * Class user_sync_task
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright  2024, Michael Lorenzoni
 */

namespace enrol_campusonline\task;

defined('MOODLE_INTERNAL') || die;

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

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

        if ($sync->isConnected()) {

            // Identify user.
            $sync->identifyMoodleUsers($users);

        } else {

            // Log error.
            $message = 'ERROR: could not connect to CAMPUSonline. Check your connection settings.';
            $trace->output($message);
            locallib::writeLog('connect', $message, 2);
        }
    }
}
