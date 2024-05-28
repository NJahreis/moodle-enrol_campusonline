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
 * Class sync
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_campusonline\task;

defined('MOODLE_INTERNAL') || die;

use enrol_campusonline\sync;

class sync_task extends \core\task\scheduled_task {

    /**
     * Task name.
     */
    public function get_name() {
        return get_string('task:sync', 'enrol_campusonline');
    }

    /**
     * Executes the task.
     */
    public function execute() {
        global $DB;

        // We may need a lot of memory here.
        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        // Remove old logs. TODO: implement

        // Initialize sync.
        $sync = new sync;
        $trace = new \text_progress_trace();

        if (!$sync->isConnected()) {
            $trace->output('Error connecting to CAMPUSOnline. Check your connection settings.');

            // Log error.
            $log = new \stdClass();
            $log->timestamp = time();
            $log->event = 'connect';
            $log->status = 2;
            $log->message = 'could not connect to CAMPUSOnline';
            $DB->insert_record('enrol_campusonline_logs', $log);

            return;
        }

        // Sync courses and enrollments.
        $sync->syncCourses($trace);
        $sync->syncEnrolments($trace);
    }
}
