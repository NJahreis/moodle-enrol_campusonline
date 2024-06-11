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

namespace enrol_campusonline;

use local_table_sql\table_sql;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/tablelib.php");

class log_table extends table_sql {

    protected function define_table_configs() {

        // Set SQL.
        $sql = "
            SELECT *
            FROM {enrol_campusonline_logs}
            ORDER BY timestamp DESC
            ";
        $this->set_sql_query($sql, array());

        // Define headers and columns.
        $cols = array();
        $cols['timestamp'] = get_string('time');
        $cols['courseid'] = get_string('course');
        $cols['status'] = get_string('status');
        $cols['event'] = get_string('event', 'enrol_campusonline');
        $cols['message'] = get_string('message');

        $this->set_table_columns($cols);
        $this->sortable(true, 'lastname', SORT_ASC);
        $this->no_filter('username');
        $this->is_downloadable(true);
    }

    // Format status.
    function col_status($row) {
        if ($row->status == 0) {
            return '<div class="badge badge-success p-2">' . get_string('success') . '</div>';
        } elseif ($row->status == 1) {
            return '<div class="badge badge-warning p-2">' . get_string('warning') . '</div>';
        } elseif ($row->status == 2) {
            return '<div class="badge badge-danger p-2">' . get_string('error') . '</div>';
        }
    }

    // Format course.
    function col_courseid($row) {
        global $DB;

        if ($courseid = $row->courseid) {
            if ($course = $DB->get_record('course', array('id' => $courseid))) {
                $link = new \moodle_url('/course/view.php', ['id' => $courseid]);
                return '<a href="' . $link . '">' . $course->fullname . '</a>';
            } else {
                return get_string('deletedcourse', 'enrol_campusonline', $courseid);
            }
        } else {
            return '';
        }
    }
}
