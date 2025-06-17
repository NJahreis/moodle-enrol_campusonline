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

namespace enrol_campusonline;

use moodle_url;
use table_sql;

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/tablelib.php");

/**
 * Class defining a table that shows the logs of the CAMPUSonline enrolment plugin.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_table extends table_sql {
    /**
     * Set up the table.
     *
     * @param string $uniqueid Unique id of table.
     * @param moodle_url $url The base URL.
     */
    public function __construct($uniqueid, $url) {
        parent::__construct($uniqueid);
        $this->define_table_columns();
        $this->define_baseurl($url);
        $this->define_table_configs();
    }

    /**
     * Define table configs.
     */
    protected function define_table_configs() {
        $this->collapsible(false);
        $this->sortable(true);
        $this->pageable(true);
        $this->set_default_per_page(50);
    }

    /**
     * Set up the columns and headers.
     */
    protected function define_table_columns() {
        // Define headers and columns.
        $cols = [];
        $cols['timestamp'] = get_string('time');
        $cols['courseid'] = get_string('course');
        $cols['status'] = get_string('status');
        $cols['event'] = get_string('event', 'enrol_campusonline');
        $cols['message'] = get_string('message');

        $this->define_columns(array_keys($cols));
        $this->define_headers(array_values($cols));
        $this->column_class('status', 'text-center');
    }

    /**
     * Builds the SQL query.
     *
     * @param bool $count When true, return the count SQL.
     * @return array containing sql to use and an array of params.
     */
    protected function get_sql_and_params($count = false) {
        if ($count) {
            $select = "COUNT(1)";
        } else {
            $select = "l.*, c.fullname";
        }

        $sql = "SELECT $select
                FROM {enrol_campusonline_logs} AS l
                LEFT JOIN {course} AS c ON l.courseid = c.id
                ";

        $params = [];

        if (!$count) {
            $sql .= "ORDER BY timestamp DESC";
        }

        return [$sql, $params];
    }

    /**
     * Query the DB.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        list($countsql, $countparams) = $this->get_sql_and_params(true);
        list($sql, $params) = $this->get_sql_and_params();
        $total = $DB->count_records_sql($countsql, $countparams);
        $this->pagesize($pagesize, $total);
        $this->rawdata = $DB->get_records_sql($sql, $params, $this->get_page_start(), $this->get_page_size());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Format the status column.
     *
     * @param mixed $row
     * @return string
     */
    public function col_status($row) {
        switch ($row->status) {
            case 0:
                return '<div class="badge badge-success p-2">' . get_string('success') . '</div>';
            case 1:
                return '<div class="badge badge-warning p-2">' . get_string('warning') . '</div>';
            case 2:
                return '<div class="badge badge-danger p-2">' . get_string('error') . '</div>';
            default:
                return '';
        }
    }

    /**
     * Format course column.
     *
     * @param mixed $row
     * @return string
     */
    public function col_courseid($row) {

        if ($courseid = $row->courseid) {
            if ($row->fullname) {
                $link = new \moodle_url('/course/view.php', ['id' => $courseid]);
                return '<a href="' . $link . '">' . $row->fullname . '</a>';
            } else {
                return get_string('deletedcourse', 'enrol_campusonline', $courseid);
            }
        } else {
            return '';
        }
    }

    /**
     * Format timestamp.
     *
     * @param mixed $row
     * @return string
     */
    public function col_timestamp($row) {
        return userdate($row->timestamp);
    }
}
