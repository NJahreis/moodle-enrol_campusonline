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
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright  2024, Michael Lorenzoni
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
        if (!$person_uid = locallib::getPersonUid($userid)) {
            return;
        }
        $person_uids = [$person_uid];

        // Initialize sync.
        $trace = new \text_progress_trace();
        $sync = new sync($trace);

        if ($sync->isConnected()) {

            // Sync user.
            $persons = $sync->getPersons(5, $person_uids);
            $person = reset($persons);
            $sync->updateMoodleUser($user, $person);

        } else {

            // Log error.
            $message = 'ERROR: could not connect to CAMPUSonline. Check your connection settings.';
            locallib::writeLog('connect', $message, 2);
        }

    }
}

