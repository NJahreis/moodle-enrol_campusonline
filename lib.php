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
 */

use enrol_campusonline\sync;

defined('MOODLE_INTERNAL') || die();

/**
 * Database enrolment plugin implementation.
 * @author  Petr Skoda - based on code by Martin Dougiamas, Martin Langhoff and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_campusonline_plugin extends enrol_plugin {

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('moodle/course:enrolconfig', $context);
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('moodle/course:enrolconfig', $context);
    }

    /**
     * Test plugin settings, print info to output.
     */
    public function test_settings() {
        global $CFG, $OUTPUT;

        // Initialize sync.
        $trace = new \text_progress_trace();
        $sync = new sync($trace);

        if ($sync->isConnected()) {
            echo '<div class="alert alert-success">';
            echo get_string('success:connected', 'enrol_campusonline');
            echo '</div>';
        } else {
            $error = $sync->getError();
            echo get_string('error:cannotconnect', 'enrol_campusonline', $error);
        }
    }

    /**
     * Adds a button to sync a single course with CAMPUSonline data.
     *
     * @param course_enrolment_manager $manager
     * @return enrol_user_button|false
     */
    public function get_manual_enrol_button(course_enrolment_manager $manager) {

        $instance = null;
        $instances = [];
        foreach ($manager->get_enrolment_instances() as $tempinstance) {
            if ($tempinstance->enrol == 'campusonline') {
                if ($instance === null) {
                    $instance = $tempinstance;
                }
                $instances[] = ['id' => $tempinstance->id, 'name' => $this->get_instance_name($tempinstance)];
            }
        }
        if (empty($instance)) {
            return false;
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/campusonline:synccourse', $context)) {
            $synclink = new moodle_url(
                '/enrol/campusonline/sync_course.php',
                ['courseid' => $instance->courseid]
            );
            $button = new enrol_user_button($synclink, get_string('syncthiscourse', 'enrol_campusonline'), 'get');
            return $button;
        } else {
            return false;
        }
    }

}
