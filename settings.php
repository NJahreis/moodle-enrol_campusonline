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
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Connection settings.
    $button = '<a class="btn btn-secondary m-1"
        href=' . new moodle_url('/enrol/campusonline/test.php?function=testconnection') . '>' .
        get_string('testconnection', 'enrol_campusonline') . '</a>';
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/connectionsettings',
        get_string('connectionsettings', 'enrol_campusonline'),
        $button));
    // CO endpoint.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/endpoint',
        get_string('endpoint', 'enrol_campusonline'),
        get_string('endpoint_desc', 'enrol_campusonline'),
        '',
    ));
    // Client ID.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/clientid',
        get_string('clientid', 'enrol_campusonline'),
        get_string('clientid_desc', 'enrol_campusonline'),
        '',
    ));
    // Client secret.
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_campusonline/clientsecret',
        get_string('clientsecret', 'enrol_campusonline'),
        get_string('clientsecret_desc', 'enrol_campusonline'),
        '',
    ));

    // General sync settings.
    $button = '<a target="_blank" class="btn btn-secondary m-1"
    href=' . new moodle_url('/admin/tool/task/scheduledtasks.php?action=edit&task=enrol_campusonline%5Ctask%5Csync_task') . '>' .
    get_string('configuretask', 'enrol_campusonline') . '</a>';
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/syncsettings',
        get_string('syncsettings', 'enrol_campusonline'),
        $button,));
    // Semester.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/semester',
        get_string('semester', 'enrol_campusonline'),
        get_string('semester_desc', 'enrol_campusonline'),
        '2022W',
    ));
    // Root course category.
    $options = core_course_category::make_categories_list();
    array_unshift($options, 'TOP');
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/rootcoursecategory',
        get_string('rootcoursecategory', 'enrol_campusonline'),
        '',
        0,
        $options,
    ));
    // Allow overwrite of coursename.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/updateexistingcourses',
        get_string('updateexistingcourses', 'enrol_campusonline'),
        get_string('updateexistingcourses_desc', 'enrol_campusonline'),
        1,
    ));

    // Course sync settings.
    $buttons = '<a class="btn btn-secondary m-1"
    href=' . new moodle_url('/enrol/campusonline/test.php?function=showrawcoursedata') . '>' .
    get_string('showrawcoursedata', 'enrol_campusonline') . '</a>';
    $buttons .= '<a class="btn btn-secondary m-1"
    href=' . new moodle_url('/enrol/campusonline/test.php?function=previewcourses') . '>' .
    get_string('previewcourses', 'enrol_campusonline') . '</a>';
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/coursesyncsettings',
        get_string('coursesyncsettings', 'enrol_campusonline'),
        $buttons . get_string('coursesyncsettings_desc', 'enrol_campusonline'),
    ));
    // Course fullname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/coursefullname',
        get_string('fullname'),
        '',
        '{title}',
    ));
    // Course shortname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/courseshortname',
        get_string('shortname'),
        '',
        '{semesterKey} - {courseCode}',
    ));

    // Enrolment sync settings.
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/enrolmentsyncsettings',
        get_string('enrolmentsyncsettings', 'enrol_campusonline'),
        '',
    ));

    // Log settings.
    $button = '<a target="_blank" class="btn btn-secondary m-1"
    href=' . new moodle_url('/enrol/campusonline/logs.php') . '>' .
    get_string('viewlogs', 'enrol_campusonline') . '</a>';
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/logsettings',
        get_string('logsettings', 'enrol_campusonline'),
        $button,
    ));
    // Course shortname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/logduration',
        get_string('logduration', 'enrol_campusonline'),
        '',
        7,
        PARAM_INT,
    ));


}