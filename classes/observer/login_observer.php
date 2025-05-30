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

namespace enrol_campusonline\observer;

defined('MOODLE_INTERNAL') || die();

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

class login_observer {

    /**
     * Triggered when a user logs in.
     *
     * @param object $event
     */
    public static function event($event) {
        global $DB;

        if (get_config('enrol_campusonline', 'syncusersonlogin') == 0) {
            return;
        }

        // Get user.
        $userid = $event->__get('objectid');
        $user = \core_user::get_user($userid);

        // Get person UID.
        if (!$person_uid = locallib::get_person_uid($userid)) {
            return;
        }
        $person_uids = [$person_uid];

        // Initialize sync.
        $trace = new \text_progress_trace();
        $sync = new sync($trace);

        if ($sync->is_connected()) {

            // Sync user.
            $persons = $sync->get_persons($person_uids);
            $person = reset($persons);
            $sync->update_moodle_user($user, $person);

        } else {

            // Log error.
            $message = get_string('connectionerror', 'enrol_campusonline');
            locallib::write_log('connect', $message, 2);
        }

    }
}

