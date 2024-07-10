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

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

if ($ADMIN->fulltree) {

    // ----- Connection settings -----
    $url = new moodle_url('/enrol/campusonline/test.php', array('function' => 'testconnection'));
    $button = html_writer::link($url, get_string('testconnection', 'enrol_campusonline'),
        array('class' => 'btn btn-secondary m-1'));
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
        PARAM_RAW,
        50
    ));
    // Client ID.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/clientid',
        get_string('clientid', 'enrol_campusonline'),
        get_string('clientid_desc', 'enrol_campusonline'),
        '',
        PARAM_RAW,
        50
    ));
    // Client secret.
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_campusonline/clientsecret',
        get_string('clientsecret', 'enrol_campusonline'),
        get_string('clientsecret_desc', 'enrol_campusonline'),
        '',
    ));

    // ----- Enrolment sync settings -----
    $url = new moodle_url('/admin/tool/task/scheduledtasks.php',
        array('action' => 'edit', 'task' => 'enrol_campusonline\task\sync_task'));
    $button = html_writer::link($url, get_string('configuretask', 'enrol_campusonline'),
        array('target' => '_blank', 'class' => 'btn btn-secondary m-1'));
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/enrolmentsyncsettings',
        get_string('enrolmentsyncsettings', 'enrol_campusonline'),
        $button  . '<br>' . get_string('enrolmentsyncsettings_desc', 'enrol_campusonline'),));
    // Semester.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/semester',
        get_string('semester', 'enrol_campusonline'),
        get_string('semester_desc', 'enrol_campusonline'),
        '2022W',
    ));
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/updateexistingcourses',
        get_string('updateexistingcourses', 'enrol_campusonline'),
        get_string('updateexistingcourses_desc', 'enrol_campusonline'),
        1,
    ));
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/enrolsynccreateusers',
        get_string('enrolsynccreateusers', 'enrol_campusonline'),
        get_string('enrolsynccreateusers_desc', 'enrol_campusonline'),
        0,
    ));

    // ----- Course category settings -----
    $url = new moodle_url('/enrol/campusonline/test.php', array('function' => 'showrawcoursedata'));
    $buttons = html_writer::link($url, get_string('showrawcoursedata', 'enrol_campusonline'),
        array('class' => 'btn btn-secondary m-1'));
    $url = new moodle_url('/enrol/campusonline/test.php', array('function' => 'coursepreview'));
    $buttons .= html_writer::link($url, get_string('previewcourses', 'enrol_campusonline'),
        array('class' => 'btn btn-secondary m-1'));
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/coursecatsettings',
        get_string('coursecatsettings', 'enrol_campusonline'),
        $buttons,
    ));
    $options = core_course_category::make_categories_list();
    array_unshift($options, 'TOP');
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/rootcoursecategory',
        get_string('rootcoursecategory', 'enrol_campusonline'),
        get_string('rootcoursecategory_desc', 'enrol_campusonline'),
        0,
        $options,
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/subcategories',
        get_string('subcategories', 'enrol_campusonline'),
        get_string('subcategories_desc', 'enrol_campusonline'),
        '{org:code}\{course:semesterKey}\{course:courseClassificationKey}',
        PARAM_RAW,
        100
    ));
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/createcoursecatetories',
        get_string('createcoursecatetories', 'enrol_campusonline'),
        get_string('createcoursecatetories_desc', 'enrol_campusonline'),
        1,
    ));

    // ----- Course sync settings -----
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/coursesyncsettings',
        get_string('coursesyncsettings', 'enrol_campusonline'),
        $buttons . get_string('coursesyncsettings_desc', 'enrol_campusonline'),
    ));
    // Course fullname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/course_fullname',
        get_string('fullname'),
        '',
        '{title}',
        PARAM_RAW,
        50
    ));
    // Course shortname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/course_shortname',
        get_string('shortname'),
        '',
        '{semesterKey} - {courseCode}',
        PARAM_RAW,
        50
    ));
    // Other configurable fields.
    foreach (locallib::COURSE_FIELDS as $field) {
        if ($field == 'lang') {
            $string = get_string('language');
        } else {
            $string = get_string($field);
        }
        $settings->add(new admin_setting_configtext(
            'enrol_campusonline/course_' . $field,
            $string,
            '',
            '',
            PARAM_RAW,
            50
        ));
    }
    // Custom fields.
    $customfields = locallib::getCourseCustomFields(null);
    foreach ($customfields as $shortname => $fullname) {
        $settings->add(new admin_setting_configtext(
            'enrol_campusonline/course_customfield_' . $shortname,
            $fullname,
            '',
            '',
            PARAM_RAW,
            50
        ));
    }

    // ----- Role mappings -----
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/rolemappings',
        get_string('rolemappings', 'enrol_campusonline'),
        get_string('rolemappings_desc', 'enrol_campusonline'),
    ));
    // Student role.
    $rolesraw = role_get_names();
    $roles = ['0' => get_string('donotsyncrole', 'enrol_campusonline')];
    foreach ($rolesraw as $role) {
        $roles[$role->id] = $role->localname;
    }
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/studentrole',
        get_string('studentrole', 'enrol_campusonline'),
        '',
        5,
        $roles,
    ));
    // Lectureship roles.
    $sync = new sync;
    if ($sync->isConnected()) {
        $functions = $sync->getLectureshipFunctions();
        foreach ($functions as $function) {
            $settings->add(new admin_setting_configselect(
                'enrol_campusonline/role_' . $function,
                $function,
                '',
                0,
                $roles,
            ));
        }
    } else {
        $settings->add(new admin_setting_heading(
            'enrol_campusonline/rolemappings',
            get_string('rolemappings', 'enrol_campusonline'),
            get_string('rolemappings_notconnected', 'enrol_campusonline'),
        ));
    }

    // ----- User sync settings -----
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/usersyncsettings',
        get_string('usersyncsettings', 'enrol_campusonline'),
        '',
    ));
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/usersynccreateusers',
        get_string('usersynccreateusers', 'enrol_campusonline'),
        get_string('usersynccreateusers_desc', 'enrol_campusonline'),
        0,
    ));

    // ----- Log settings -----
    $url = new moodle_url('/enrol/campusonline/logs.php');
    $button = html_writer::link($url, get_string('viewlogs', 'enrol_campusonline'),
        array('class' => 'btn btn-secondary m-1'));
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/logsettings',
        get_string('logsettings', 'enrol_campusonline'),
        $button,
    ));
    // Log duration.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/logduration',
        get_string('logduration', 'enrol_campusonline'),
        '',
        7,
        PARAM_INT,
    ));
}