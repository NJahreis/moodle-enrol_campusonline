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
 * Settings for the CAMPUSonline enrolment plugin.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use enrol_campusonline\sync;
use enrol_campusonline\locallib;

if ($ADMIN->fulltree) {
    // Readme.
    $url = new moodle_url('/enrol/campusonline/show_readme.php');
    $link = html_writer::link($url, get_string('readme', 'enrol_campusonline'));
    $settings->add(
        new admin_setting_heading(
            'enrol_campusonline/readme',
            '',
            $link
        )
    );

    // Connection settings.
    $url = new moodle_url('/enrol/campusonline/test.php', ['function' => 'testconnection']);
    $button = html_writer::link($url, get_string('testconnection', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/connectionsettings',
        get_string('connectionsettings', 'enrol_campusonline'),
        $button . '<br>' . get_string('connectionsettings_desc', 'enrol_campusonline',
            new moodle_url('/admin/settings.php', ['section' => 'manageenrols']))));

    // ... CO endpoint.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/endpoint',
        get_string('endpoint', 'enrol_campusonline'),
        get_string('endpoint_desc', 'enrol_campusonline'),
        '',
        PARAM_TEXT,
        50
    ));

    // ... Client ID.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/clientid',
        get_string('clientid', 'enrol_campusonline'),
        get_string('clientid_desc', 'enrol_campusonline'),
        '',
        PARAM_TEXT,
        50
    ));

    // ... Client secret.
    $settings->add(new admin_setting_configpasswordunmask(
        'enrol_campusonline/clientsecret',
        get_string('clientsecret', 'enrol_campusonline'),
        get_string('clientsecret_desc', 'enrol_campusonline'),
        '',
    ));

    // Organisation synchronization settings.
    $url = new moodle_url('/admin/tool/task/scheduledtasks.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\org_sync_task']);
    $buttons = html_writer::link($url, get_string('configuretask', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-secondary m-1']);
    $url = new moodle_url('/admin/tool/task/schedule_task.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\org_sync_task']);
    $buttons .= html_writer::link($url, get_string('runtask', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-primary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/orgsyncsettings',
        get_string('orgsyncsettings', 'enrol_campusonline'),
        $buttons  . '<br>' . get_string('orgsyncsettings_desc', 'enrol_campusonline'), ));

    // ... Organisaztion key.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/orgkey',
        get_string('orgkey', 'enrol_campusonline'),
        get_string('orgkey_desc', 'enrol_campusonline'),
        'MOODLE',
        PARAM_TEXT
    ));

    // ... Root course category.
    $cats = core_course_category::make_categories_list();
    $top = ['0' => 'TOP'];
    $options = $top + $cats;
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/rootcoursecategory',
        get_string('rootcoursecategory', 'enrol_campusonline'),
        get_string('rootcoursecategory_desc', 'enrol_campusonline'),
        0,
        $options,
    ));

    // ... Organisation roles.
    // Only call this when actually on our settings page, to avoid instanciating the sync class on other admin pages.
    $rolestring = get_string('none');
    if (array_key_exists('section', $_GET) && $_GET['section'] == 'enrolsettingscampusonline') {
        if ($orgroles = locallib::get_org_roles()) {
            $rolestring = implode(', ', $orgroles);
        }
    }
    $roleurl = new moodle_url('/admin/roles/manage.php');
    $roleurl = $roleurl->__toString();
    $rolestring = get_string('configureorgroles', 'enrol_campusonline', ['roleurl' => $roleurl,
        'rolestring' => $rolestring]);
    $setting = new admin_setting_configcheckbox(
        'enrol_campusonline/syncorgroles',
        get_string('syncorgroles', 'enrol_campusonline'),
        $rolestring,
        '1'
    );
    $settings->add($setting);

    // Course category settings.
    $url = new moodle_url('/enrol/campusonline/test.php', ['function' => 'showrawcoursedata', 'limit' => 20]);
    $buttons = html_writer::link($url, get_string('showrawcoursedata', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $url = new moodle_url('/enrol/campusonline/test.php', ['function' => 'previewcourses', 'limit' => 20]);
    $buttons .= html_writer::link($url, get_string('previewcourses', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/coursecatsettings',
        get_string('coursecatsettings', 'enrol_campusonline'),
        get_string('coursecatsettings_desc', 'enrol_campusonline') . $buttons,
    ));

    // ... Subcategories.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/subcategories',
        get_string('subcategories', 'enrol_campusonline'),
        get_string('subcategories_desc', 'enrol_campusonline'),
        '{course:semesterKey}\{course:courseClassificationKey}',
        PARAM_TEXT,
        100
    ));

    // ... Create course categories.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/createcoursecategories',
        get_string('createcoursecategories', 'enrol_campusonline'),
        get_string('createcoursecategories_desc', 'enrol_campusonline'),
        1,
    ));

    // Enrolment sync settings.
    $url = new moodle_url('/admin/tool/task/scheduledtasks.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\sync_task']);
    $buttons = html_writer::link($url, get_string('configuretask_full', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-secondary m-1']);
    $url = new moodle_url('/admin/tool/task/scheduledtasks.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\sync_delta_task']);
    $buttons .= html_writer::link($url, get_string('configuretask_delta', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-secondary m-1']);
    $buttons .= '<br>';
    $url = new moodle_url('/admin/tool/task/schedule_task.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\sync_task']);
    $buttons .= html_writer::link($url, get_string('runtask_full', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-primary m-1']);
    $url = new moodle_url('/admin/tool/task/schedule_task.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\sync_delta_task']);
    $buttons .= html_writer::link($url, get_string('runtask_delta', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-primary m-1']);
    $buttons .= '<br>';
    $url = new moodle_url('/enrol/campusonline/test.php', ['function' => 'previewcourses', 'limit' => 20]);
    $buttons .= html_writer::link($url, get_string('previewcourses', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/enrolmentsyncsettings',
        get_string('enrolmentsyncsettings', 'enrol_campusonline'),
        $buttons  . '<br>' . get_string('enrolmentsyncsettings_desc', 'enrol_campusonline'), ));

    // ... Semester.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/semester',
        get_string('semester', 'enrol_campusonline'),
        get_string('semester_desc', 'enrol_campusonline'),
        '2024W, 2025S',
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/orgfilter',
        get_string('orgfilter', 'enrol_campusonline'),
        get_string('orgfilter_desc', 'enrol_campusonline'),
        '',
    ));

    // ... Create users.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/enrolsynccreateusers',
        get_string('enrolsynccreateusers', 'enrol_campusonline'),
        get_string('enrolsynccreateusers_desc', 'enrol_campusonline'),
        0,
    ));

    // ... Update existing courses.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/updateexistingcourses',
        get_string('updateexistingcourses', 'enrol_campusonline'),
        get_string('updateexistingcourses_desc', 'enrol_campusonline'),
        1,
    ));

    // ... Update Course URLs.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/updatecourseurls',
        get_string('updatecourseurls', 'enrol_campusonline'),
        get_string('updatecourseurls_desc', 'enrol_campusonline'),
        0,
    ));

    // ... Timeframe for modification sync.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/modificationtimeframe',
        get_string('modificationtimeframe', 'enrol_campusonline'),
        get_string('modificationtimeframe_desc', 'enrol_campusonline'),
        0,
        PARAM_INT,
    ));

    // Course synchronization settings.
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/coursesyncsettings',
        get_string('coursesyncsettings', 'enrol_campusonline'),
        get_string('coursesyncsettings_desc', 'enrol_campusonline'),
    ));

    // ... Course fullname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/course_fullname',
        get_string('fullname'),
        '',
        '{course:title}',
        PARAM_TEXT,
        50
    ));

    // ... Course shortname.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/course_shortname',
        get_string('shortname'),
        '',
        '{course:semesterKey} - {course:courseCode}',
        PARAM_TEXT,
        50
    ));

    // ... Course format.
    // Get available course_format plugins.
    $courseformats = core_plugin_manager::instance()->get_plugins_of_type('format');
    $courseformats = array_keys($courseformats);
    $courseformatoptions = [];
    foreach ($courseformats as $courseformat) {
        $courseformatoptions[$courseformat] = get_string('pluginname', 'format_' . $courseformat);
    }
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/course_format',
        get_string('format'),
        '',
        'topics',
        $courseformatoptions,
    ));

    // ... Other configurable fields.
    foreach (locallib::COURSE_FIELDS as $field => $default) {
        if ($field == 'lang') {
            $string = get_string('language');
        } else {
            $string = get_string($field);
        }
        if (in_array($field, locallib::BOOL_FIELDS)) {
            $type = PARAM_BOOL;
        } else {
            $type = PARAM_TEXT;
        }
        $settings->add(new admin_setting_configtext(
            'enrol_campusonline/course_' . $field,
            $string,
            '',
            $default,
            $type,
            50
        ));
    }

    // ... Custom fields.
    $customfields = locallib::get_custom_course_field_data(null);
    foreach ($customfields as $shortname => $fullname) {
        if (in_array($shortname, locallib::IGNORE_FIELDS)) {
            continue;
        }
        $settings->add(new admin_setting_configtext(
            'enrol_campusonline/course_customfield_' . $shortname,
            $fullname,
            '',
            '',
            PARAM_TEXT,
            50
        ));
    }

    // Group synchronization settings.
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/groupsyncsettings',
        get_string('groupsyncsettings', 'enrol_campusonline'),
        get_string('groupsyncsettings_desc', 'enrol_campusonline')
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/grouptocourse',
        get_string('grouptocourse', 'enrol_campusonline'),
        get_string('grouptocourse_desc', 'enrol_campusonline'),
        'GROUP_TO_COURSE',
        PARAM_TEXT,
        50
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/grouptogroup',
        get_string('grouptogroup', 'enrol_campusonline'),
        get_string('grouptogroup_desc', 'enrol_campusonline'),
        'EVOR, ESEM, EUEB',
        PARAM_TEXT,
        50
    ));
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/flatcourse',
        get_string('flatcourse', 'enrol_campusonline'),
        get_string('flatcourse_desc', 'enrol_campusonline'),
        '',
        PARAM_TEXT,
        50
    ));

    // Role mappings.
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/rolemappings',
        get_string('rolemappings', 'enrol_campusonline'),
        get_string('rolemappings_desc', 'enrol_campusonline'),
    ));

    // ... Student role.
    $rolesraw = role_get_names();
    $roles = ['0' => get_string('donotsyncrole', 'enrol_campusonline')];
    foreach ($rolesraw as $role) {
        if (!$DB->get_record('role_context_levels', ['roleid' => $role->id, 'contextlevel' => CONTEXT_COURSE])) {
            continue;
        }
        $roles[$role->id] = $role->localname;
    }
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/studentrole',
        get_string('studentrole', 'enrol_campusonline'),
        '',
        5,
        $roles,
    ));

    // ... Lectureship roles.
    // Only call this when actually on our settings page, to avoid instanciating the sync class on other admin pages.
    if (array_key_exists('section', $_GET) && $_GET['section'] == 'enrolsettingscampusonline') {
        $sync = new sync(new \text_progress_trace());
        if ($sync->is_connected()) {
            $functions = $sync->get_lectureship_functions();
            $default = 0;
            foreach ($functions as $function) {
                $settings->add(new admin_setting_configselect(
                    'enrol_campusonline/role_' . $function,
                    $function,
                    '',
                    $default,
                    $roles,
                ));
                $default = 4;
            }
        } else {
            $settings->add(new admin_setting_heading(
                'enrol_campusonline/rolemappings',
                get_string('rolemappings', 'enrol_campusonline'),
                get_string('rolemappings_notconnected', 'enrol_campusonline'),
            ));
        }
    }

    // User ID settings.
    $url = new moodle_url('/admin/tool/task/scheduledtasks.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\user_id_task']);
    $buttons = html_writer::link($url, get_string('configuretask', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-secondary m-1']);
    $url = new moodle_url('/admin/tool/task/schedule_task.php',
        ['action' => 'edit', 'task' => 'enrol_campusonline\task\user_id_task']);
    $buttons .= html_writer::link($url, get_string('runtask', 'enrol_campusonline'),
        ['target' => '_blank', 'class' => 'btn btn-primary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/useridsettings',
        get_string('useridsettings', 'enrol_campusonline'),
        get_string('useridsettings_desc', 'enrol_campusonline') . $buttons,
    ));

    // ... Source claim.
    $options = ['CO_CLAIM_MATRICULATION_NUMBER' => 'MATRICULATION_NUMBER',
                'CO_CLAIM_PERSON_UID' => 'PERSON_UID',
                'CO_CLAIM_PERSON_INTERNAL_ID ' => 'PERSON_INTERNAL_ID',
                'CO_CLAIM_EXT_IDENT_ID' => 'EXT_IDENT_ID',
                'CO_CLAIM_STUDENT_INTERNAL_ID' => 'STUDENT_INTERNAL_ID',
                'CO_CLAIM_EMPLOYEE_INTERNAL_ID ' => 'EMPLOYEE_INTERNAL_ID',
                'CO_CLAIM_EXTPERS_INTERNAL_ID ' => 'EXTPERS_INTERNAL_ID',
                'CO_CLAIM_USERNAME' => 'USERNAME',
                'CO_CLAIM_EXTERNAL_SYSTEM_UID' => 'EXTERNAL_SYSTEM_UID',
                'CO_CLAIM_EMAIL_ALL' => 'CO_CLAIM_EMAIL_ALL',
                ];
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/sourceclaim',
        get_string('sourceclaim', 'enrol_campusonline'),
        get_string('sourceclaim_desc', 'enrol_campusonline'),
        'CO_CLAIM_PERSON_UID',
        $options,
    ));

    // ... Source field.
    $options = ['username' => get_string('username'),
                'email' => get_string('email'),
                'idnumber' => get_string('idnumber')];
    $profilefields = profile_get_custom_fields();
    foreach ($profilefields as $profilefield) {
        if ($profilefield->shortname == 'profile_field_campusonline_person_uid') {
            continue;
        }
        $options['profile_field_' . $profilefield->shortname] = $profilefield->name;
    }
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/sourcefield',
        get_string('sourcefield', 'enrol_campusonline'),
        get_string('sourcefield_desc', 'enrol_campusonline'),
        'idnumber',
        $options,
    ));

    // ... External key.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/user_externalkey',
        get_string('externalkey', 'enrol_campusonline'),
        '',
        '',
        PARAM_TEXT,
        50
    ));

    // ... External system key.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/user_externalsystemkey',
        get_string('externalsystemkey', 'enrol_campusonline'),
        get_string('externalsystemkey_desc', 'enrol_campusonline'),
        '',
        PARAM_TEXT,
        50
    ));

    // ... Attempts.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/idattempts',
        get_string('idattempts', 'enrol_campusonline'),
        get_string('idattempts_desc', 'enrol_campusonline'),
        3,
        PARAM_INT,
    ));

    // ... Auto-id new users.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/autoidnewusers',
        get_string('autoidnewusers', 'enrol_campusonline'),
        get_string('autoidnewusers_desc', 'enrol_campusonline'),
        1,
    ));

    // User synchronization settings.
    $url = new moodle_url('/enrol/campusonline/test.php', ['function' => 'showrawuserdata', 'limit' => 20]);
    $buttons = html_writer::link($url, get_string('showrawuserdata', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $url = new moodle_url('/enrol/campusonline/test.php', ['function' => 'previewusers', 'limit' => 20]);
    $buttons .= html_writer::link($url, get_string('previewusers', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/usersyncsettings',
        get_string('usersyncsettings', 'enrol_campusonline'),
        get_string('usersyncsettings_desc', 'enrol_campusonline') . $buttons,
    ));

    // ... Sync user data upon login.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/syncusersonlogin',
        get_string('syncusersonlogin', 'enrol_campusonline'),
        get_string('syncusersonlogin_desc', 'enrol_campusonline'),
        1,
    ));

    // ... User auth.
    $authplugins = core_component::get_plugin_list('auth');
    $options = [];
    foreach ($authplugins as $key => $path) {
        $options[$key] = get_string('pluginname', "auth_$key");
    }
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/user_auth',
        get_string('authmethod', 'enrol_campusonline'),
        '',
        'manual',
        $options,
    ));

    // ... Initial password.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/user_password',
        get_string('initialpassword', 'enrol_campusonline'),
        get_string('initialpassword_desc', 'enrol_campusonline'),
        '',
        PARAM_TEXT,
        50
    ));

    // ... Other configurable fields.
    foreach (locallib::USER_FIELDS as $field => $default) {
        $string = get_string($field);
        if (in_array($field, locallib::BOOL_FIELDS)) {
            $type = PARAM_BOOL;
        } else {
            $type = PARAM_TEXT;
        }
        $settings->add(new admin_setting_configtext(
            'enrol_campusonline/user_' . $field,
            $string,
            '',
            $default,
            $type,
            50
        ));

        // Allow username/email update.
        if ($field == 'username' || $field == 'email') {
            $settings->add(new admin_setting_configcheckbox(
                'enrol_campusonline/user_allow' . $field . 'update',
                get_string('allow' . $field . 'update', 'enrol_campusonline'),
                get_string('allow' . $field . 'update_desc', 'enrol_campusonline'),
                ($field == 'username') ? 0 : 1,
            ));
        }
    }

    // ... Custom fields.
    foreach ($profilefields as $profilefield) {
        if (in_array($profilefield->shortname, locallib::IGNORE_FIELDS)) {
            continue;
        }
        $settings->add(new admin_setting_configtext(
            'enrol_campusonline/user_profile_field_' . $profilefield->shortname,
            $profilefield->name,
            '',
            '',
            PARAM_TEXT,
            50
        ));
    }

    // Log settings.
    $url = new moodle_url('/enrol/campusonline/logs.php');
    $button = html_writer::link($url, get_string('viewlogs', 'enrol_campusonline'),
        ['class' => 'btn btn-secondary m-1']);
    $settings->add(new admin_setting_heading(
        'enrol_campusonline/logsettings',
        get_string('logsettings', 'enrol_campusonline'),
        $button,
    ));

    // ... Log level.
    $options = [0 => get_string('allevents', 'enrol_campusonline'),
                1 => get_string('warningsanderrors', 'enrol_campusonline'),
                2 => get_string('errorsonly', 'enrol_campusonline'),
    ];
    $settings->add(new admin_setting_configselect(
        'enrol_campusonline/loglevel',
        get_string('loglevel', 'enrol_campusonline'),
        '',
        0,
        $options,
    ));

    // ... Log duration.
    $settings->add(new admin_setting_configtext(
        'enrol_campusonline/logduration',
        get_string('logduration', 'enrol_campusonline'),
        '',
        7,
        PARAM_INT,
    ));

    // ... API info.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/restcalls',
        get_string('restcalls', 'enrol_campusonline'),
        get_string('restcalls_desc', 'enrol_campusonline'),
        0,
    ));

    // ... PHP logging.
    $settings->add(new admin_setting_configcheckbox(
        'enrol_campusonline/phplogging',
        get_string('phplogging', 'enrol_campusonline'),
        '',
        0,
    ));

}
