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
 * Version metadata for the enrol_campusonline plugin.
 *
 * @package   enrol_campusonline
 * @copyright 2024, Lucas Reeh <lr86gm@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Basics.
$string['pluginname'] = 'CAMPUSonline enrolment';
$string['privacy:metadata'] = 'The CAMPUSonline enrolment plugin does not store any personal data.';
$string['task:sync'] = 'CAMPUSonline courses & enrolments FULL sync';
$string['task:sync_delta'] = 'CAMPUSonline courses & enrolments MODIFICATION sync';
$string['task:user_sync'] = 'CAMPUSonline user sync';

// Settings page.
$string['connectionsettings'] = 'Connection';
$string['endpoint'] = 'CAMPUSonline endpoint';
$string['endpoint_desc'] = 'Address of the CAMPUSonline oauth2 endpoint';
$string['clientid'] = 'Client ID';
$string['clientid_desc'] = 'Client ID to access CAMPUSonline';
$string['clientsecret'] = 'Client secret';
$string['clientsecret_desc'] = 'Secret key to access CAMPUSonline';

$string['enrolmentsyncsettings'] = 'Course & enrolment sync';
$string['enrolmentsyncsettings_desc'] = '<ul>
    <li>These sync tasks create and update <strong>courses</strong> and their <strong>enrolments</strong></li>
    <li>There are two sync tasks: one for <strong>full</strong> sync, and one for <strong>modifications</strong> only</li>
    <li>It is recommended to run the full sync task during the night, as it can take quite a long time</li>
    <li>The modifications sync task can be scheduled to run more often, or run manually if needed. By default, it is not scheduled.</li>
    <li>When scheduling the modification task, make sure to configure the <strong>timeframe</strong> to fetch modifications in line with the schedule for the modification sync task</li>
    <li>Please be aware that modifications in the CAMPUSonline data will be deleted after <strong>7 days</strong></li>
    <li>In addition, you can sync a single course, using the "Sync course with CAMPUSonline" button on the course participants page (only available for courses created via CAMPUSonline, and requires the permission enrol/campusonline:synccourse</li></ul>';
$string['configuretask'] = 'Configure scheduled task';
$string['configuretask_full'] = 'Configure scheduled task for FULL sync';
$string['configuretask_delta'] = 'Configure scheduled task for MODIFICATIONS sync';
$string['runtask'] = 'Run scheduled task';
$string['runtask_full'] = 'Run scheduled task for FULL sync';
$string['runtask_delta'] = 'Run scheduled task for MODIFICATIONS sync';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester(s) to be synced. For multiple semesters, separate them with a comma.';
$string['updateexistingcourses'] = 'Update existing courses';
$string['updateexistingcourses_desc'] = 'Allows the enrolment sync task to change names or categories of existing Moodle courses if they change in CAMPUSonline.';
$string['enrolsynccreateusers'] = 'Create users';
$string['enrolsynccreateusers_desc'] = 'Allows the enrolment sync task to create users that do not exist or cannot be found in Moodle.';

$string['showrawcoursedata'] = 'Show tokens and raw course data';
$string['previewcourses'] = 'Preview courses with these settings';

$string['coursecatsettings'] = 'Course category';
$string['coursecatsettings_desc'] = '<ul>
    <li>The course category tree can be built using values from CAMPUSonline as tokens</li>
    <li>If the resulting course category changes for a course that is actively synced, the course will be moved</li></ul>';

$string['rootcoursecategory'] = 'Root course category';
$string['rootcoursecategory_desc'] = 'Course category to sync courses into. If you select "TOP", then you will need to have rules to create subcategories, otherwise the sync will fail.';
$string['subcategories'] = 'Subcategories';
$string['subcategories_desc'] = 'Specify how to build the subcategory structure.
    <li>Use tokens to build the category names, and backslashes to separate categories, eg: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Make sure that no subcategory name ends up being empty</li>
    <li>Show raw data from CAMPUSonline to see available fields/tokens</li>';
$string['createcoursecatetories'] = 'Create course categories';
$string['createcoursecatetories_desc'] = 'Allows the enrolment sync task to create course categories if they do not exist.';

$string['coursesyncsettings'] = 'Course values';
$string['coursesyncsettings_desc'] = '<ul>
    <li>Moodle course <strong>idnumber</strong> will always be filled with the CAMPUSonline course <strong>uid</strong></li>
    <li>Make sure the course <strong>shortnames</strong> are unique, and fields are filled with valid values for their respective field types, or there will be errors creating courses!</li>
    <li>These values are <strong>required</strong>, otherwise course creation will fail: course_fullname, course_shortname, course_format</li>
    <li>Choose values for other course fields (including course custom fields) by combining text and <strong>tokens</strong> for CAMPUSonline fields, eg: "CAMPUSONLINE_COURSE_{title}</li>
    <li>Show raw data from CAMPUSonline to see available fields/tokens</li></ul>';
$string['groupsyncsettings'] = 'Group settings';
$string['groupsyncsettings_desc'] = 'Not yet implemented';

$string['useridsettings'] = 'User identification';
$string['useridsettings_desc'] = '<ul>
    <li>Moodle users will be identified by matching the CAMPUSOnline person UID to the user profile fields
        <ul><li> <strong>campusonline_student_uid</strong> for students</li>
        <li><strong>campusonline_employee_uid</strong> for employees</li></ul>
    <li>If a user is not found via its person UID, the username will be used as a fallback, as configured in the user sync values below</li>
    <li>Additionally, a secondary fallback identification criteria can be configured for the user sync, to find users that already exist in Moodle without these identifiers</li>
    <li><strong>When configuring a secondary identification criteria, be sure to use the same value in user sync & values for this field!</strong></li></ul>';
$string['userclaims'] = 'Personal data to get from CAMPUSonline';
$string['userclaims_desc'] = 'Specify which personal data to get from CAMPUSonline. Only claims configured here will be able to be assigned as tokens.';
$string['usermoodlefield'] = 'Secondary identifier: field in Moodle';
$string['usermoodlefield_desc'] = 'If a user is not found via its person UID or username, this field will be used to find the user in Moodle.';
$string['usercovalue'] = 'Secondary identifier: value in CAMPUSonline';
$string['usercovalue_desc'] = 'This CAMPUSonline value will be matched against the Moodle field specified above.';

$string['usersyncsettings'] = 'User sync & values';
$string['usersyncsettings_desc'] = '<ul>
    <li>This sync task is disabled by default</li>
    <li>Make sure usernames are unique, and fields are filled with valid values for their respective field types, or there will be errors creating users!</li>
    <li>These values are <strong>required</strong>, otherwise course creation will fail: user_auth, user_password, user_username, user_email</li>
    <li>Only enable this task if your user data is not already synced via other means (eg SSO systems)</li>
    <li>Show raw data to see available fields/tokens</li></ul>';
$string['showrawuserdata'] = 'Show tokens and raw user data';
$string['previewusers'] = 'Preview users with these settings';
$string['authmethod'] = 'Authentification method';
$string['initialpassword'] = 'Initial password';
$string['initialpassword_desc'] = 'Be sure to set an initial password that adheres to password complexity standards, or user creation will fail, even for users with authentification methods that will not even use the password!';

$string['rolemappings'] = 'Role mappings';
$string['rolemappings_desc'] = 'Select Moodle roles to use for CAMPUSonline students and lectureship roles.';
$string['rolemappings_notconnected'] = 'Could not connect to CAMPUSOnline. Check your connection settings and reload this page, to add mappings for CAMPUSonline roles.';
$string['donotsyncrole'] = '- do not sync this role -';
$string['studentrole'] = 'Students';
$string['lectureshiproles'] = 'Select Moodle roles to use for CAMPUSonline lectureship roles.';

$string['logsettings'] = 'Log settings';
$string['loglevel'] = 'Log level';
$string['allevents'] = 'All events';
$string['warningsanderrors'] = 'Errors and warnings';
$string['errorsonly'] = 'Errors only';
$string['logduration'] = 'Keep logs for (days)';
$string['viewlogs'] = 'View logs';
$string['logs'] = 'Logs';
$string['event'] = 'Event';
$string['deletedcourse'] = 'deleted course (id: {$a})';


$string['coursepreview'] = 'Course sync preview';
$string['coursecount'] = 'Raw data for {$a} courses:';
$string['userpreview'] = 'User sync preview.';
$string['usercount'] = 'Raw data for {$a} users:';
$string['availabletokens'] = 'Available tokens';
$string['availabletokens_disclaimer'] = 'Some of these might only be available for employees or students, but not for both.';
$string['students'] = 'Students';
$string['employees'] = 'Employees';
$string['testsettings'] = 'Test these settings';
$string['testconnection'] = 'Test connection';
$string['backtosettings'] = 'Back to module settings';

$string['syncthiscourse'] = 'Sync course with CAMPUSonline';
$string['syncingcourse'] = 'Syncing Moodle course with CAMPUSonline...';
$string['connectionerror'] = 'Could not connect to CAMPUSOnline. Check your connection settings. Please contact your administrator.';

// Alerts.
$string['success:connected'] = 'Successfully connected to CAMPUSonline endpoint.';
$string['error:cannotconnect'] = 'Cannot connect to CAMPUSonline endpoint. Error: {$a}';
$string['error:endpointmissing'] = 'You have to provide a valid endpoint in settings.';
$string['error:unknown'] = 'Unknown error.';
$string['error:uidfieldnotfound'] = 'CAMPUSOnline user profile field not found - reinstall the plugin or re-create the field(s) manually.';