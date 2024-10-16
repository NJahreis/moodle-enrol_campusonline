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
 * Version metadata for the enrol_CAMPUSonline plugin.
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
$string['task:user_id'] = 'CAMPUSonline user identification';
$string['task:user_sync'] = 'CAMPUSonline user data sync';

$string['allevents'] = 'All events';
$string['allowemailupdate'] = 'User sync can change email address';
$string['allowemailupdate_desc'] = 'Allows the user sync to change existing user\'s email addresses. Be aware, that this might lead to problems, since on some Moodle sites users might use their email to login.';
$string['allowusernameupdate'] = 'User sync can change username';
$string['allowusernameupdate_desc'] = 'Allows the user sync to change existing user\'s usernames. Be aware, that this might lead login problems for users.';
$string['authmethod'] = 'Authentification method';
$string['autoidnewusers'] = 'Auto-identify new users';
$string['autoidnewusers_desc'] = 'Will automatically try to identify any newly created users. Useful if users are created via SSO.';
$string['availabletokens'] = 'Available tokens';
$string['availabletokens_disclaimer'] = 'Some of these might only be available for employees or students, but not for both.';
$string['backtosettings'] = 'Back to module settings';
$string['clientid'] = 'Client ID';
$string['clientid_desc'] = 'Client ID to access CAMPUSonline';
$string['clientsecret'] = 'Client secret';
$string['clientsecret_desc'] = 'Secret key to access CAMPUSonline';
$string['configuretask'] = 'Configure scheduled task';
$string['configuretask_delta'] = 'Configure scheduled task for MODIFICATIONS sync';
$string['configuretask_full'] = 'Configure scheduled task for FULL sync';
$string['connectionerror'] = 'Could not connect to CAMPUSonline. Check your connection settings. Please contact your administrator.';
$string['connectionsettings'] = 'Connection';
$string['coursecatsettings'] = 'Course category';
$string['coursecatsettings_desc'] = '<ul>
    <li>The course category tree can be built using values from CAMPUSonline as <strong>tokens</strong></li>
    <li>If the resulting course category changes for a course that is actively synced, the course will be <strong>moved</strong></li></ul>';
$string['coursecount'] = 'Raw data for {$a} courses:';
$string['coursepreview'] = 'Course sync preview';
$string['coursesyncsettings'] = 'Course values';
$string['coursesyncsettings_desc'] = '<ul>
    <li>Moodle course <strong>idnumber</strong> will always be filled with the CAMPUSonline course <strong>uid</strong></li>
    <li>Make sure the course <strong>shortnames</strong> are unique, and fields are filled with valid values for their respective field types, or there will be errors creating courses!</li>
    <li>These values are <strong>required</strong>, otherwise course creation will fail: course_fullname, course_shortname, course_format</li>
    <li>Choose values for other course fields (including course custom fields) by combining text and <strong>tokens</strong> for CAMPUSonline fields, eg: "CAMPUSonline_COURSE_{title}</li>
    <li>Show raw data from CAMPUSonline to see available fields/tokens</li></ul>';
$string['createcoursecatetories'] = 'Create course categories';
$string['createcoursecatetories_desc'] = 'Allows the enrolment sync task to create course categories if they do not exist.';
$string['deletedcourse'] = 'deleted course (id: {$a})';
$string['donotsyncrole'] = '- do not sync this role -';
$string['employees'] = 'Employees';
$string['endpoint'] = 'CAMPUSonline endpoint';
$string['endpoint_desc'] = 'Address of the CAMPUSonline oauth2 endpoint';
$string['enrolmentsyncsettings'] = 'Course sync';
$string['enrolmentsyncsettings_desc'] = '<ul>
    <li>These sync tasks create and update <strong>courses</strong> and their <strong>enrolments</strong></li>
    <li>There are two sync tasks: one for <strong>full</strong> sync, and one for <strong>modifications</strong> only</li>
    <li>To improve performance, it is recommended to run a <strong>full sync</strong> manually or weekly, and schedule only the <strong>modification sync</strong> task.
    <li>It is recommended to schedul the full sync task during the night, as it can take quite a long time</li>
    <li>In addition, you can <strong>sync a single course</strong>, using the "Sync course with CAMPUSonline" button on the course participants page (only available for courses created via CAMPUSonline, and requires the permission enrol/CAMPUSonline:synccourse</li></ul>';
$string['enrolsynccreateusers'] = 'Create users';
$string['enrolsynccreateusers_desc'] = 'Allows the course sync task to create users that do not exist or cannot be found in Moodle. <strong>Only activate this after making sure that user identification works correctly</strong>, otherwise you might end up with a lot of duplicate users in Moodle!';
$string['error:cannotconnect'] = 'Cannot connect to CAMPUSonline endpoint. Error: {$a}';
$string['error:config'] = 'Missing connection configuration!';
$string['error:endpointmissing'] = 'You have to provide a valid endpoint in settings.';
$string['error:uidfieldnotfound'] = 'CAMPUSonline user profile field not found - reinstall the plugin or re-create the field(s) manually.';
$string['error:unknown'] = 'Unknown error.';
$string['event'] = 'Event';
$string['errorsonly'] = 'Errors only';
$string['externalkey'] = 'External key';
$string['externalsystemkey'] = 'External system key';
$string['externalsystemkey_desc'] = 'When using <strong>EXTERNAL_SYSTEM_UID</strong> to identify your users, you need to provide the external_system_key and the external_key to fetch it from CAMPUSonline.';
$string['grouptocourse'] = 'Group to course';
$string['grouptocourse_desc'] = 'Comma-separated list of elearningEventTypeKeys. For these elearning Event types, separate Moodle courses will be created for each of the groups.';
$string['grouptogroup'] = 'Group to group';
$string['grouptogroup_desc'] = 'Comma-separated list of elearningEventTypeKeys. For these elearning Event types, CAMPUSonline groups will be synced into Moodle groups. <p>Leave this empty to sync groups into Moodle groups for <strong>all</strong> types except the ones configured for separate courses (recommended).</p>';
$string['groupsyncsettings'] = 'Group settings';
$string['groupsyncsettings_desc'] = '<ul>
    <li>CAMPUSonline <strong>groups</strong> can either be synced into Moodle course groups, or <strong>separate courses</strong> can be created for each group</li>
    <li>If you do no not want to sync groups at all for some elearning types, you can configure group to group only for specific event types</li>
    </ul>';
$string['idattempts'] = 'Max attempts';
$string['idattempts_desc'] = 'A counter for the number of failed attempts to identfy a user will be kept in a custom user profile field created by CAMPUSonline. You can reset this counter to re-try identifying a user.';
$string['initialpassword'] = 'Initial password';
$string['initialpassword_desc'] = 'Be sure to set an password that adheres to password complexity standards, or user creation will fail, <strong>even for users with authentification methods that will not even use the password!</strong>';
$string['lectureshiproles'] = 'Select Moodle roles to use for CAMPUSonline lectureship roles.';
$string['logduration'] = 'Keep logs for (days)';
$string['loglevel'] = 'Log level';
$string['logsettings'] = 'Log settings';
$string['logs'] = 'Logs';
$string['modificationtimeframe'] = 'Days to include in modification sync';
$string['modificationtimeframe_desc'] = '<ul>
    <li>How many days back modifications should be fetched from CAMPUSonline for the <strong>modification sync</strong></li>
    <li>0 = only get today\'s modifications</li>
    <li>At the moment, CAMPUSonline provides a maximum of <strong>7 days</strong> worth of modifications - when running the modification sync task in longer intervals, modifications will get lost, so make sure to <strong>configure the task schedule accordingly</strong></li></ul>';
$string['previewcourses'] = 'Preview courses with these settings';
$string['previewusers'] = 'Preview users with these settings';
$string['readme'] = 'Please read the readme file for more information on how to correctly setup this plugin in various scenarios.';
$string['rolemappings'] = 'Role mappings';
$string['rolemappings_desc'] = 'Select Moodle roles to use for CAMPUSonline students and lectureship roles.';
$string['rolemappings_notconnected'] = 'Could not connect to CAMPUSonline. Check your connection settings and reload this page, to add mappings for CAMPUSonline roles.';
$string['rootcoursecategory'] = 'Root course category';
$string['rootcoursecategory_desc'] = 'Course category to sync courses into. If you select "TOP", then you will need to have rules to create subcategories, otherwise the sync will fail.';
$string['restcalls'] = 'Show REST Calls when running tasks';
$string['restcalls_desc'] = 'Shows information about every individual REST Call when running the task. Does not write to log. For debugging only.';
$string['runtask'] = 'Run scheduled task';
$string['runtask_delta'] = 'Run scheduled task for MODIFICATIONS sync';
$string['runtask_full'] = 'Run scheduled task for FULL sync';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester(s) to be synced. For multiple semesters, separate them with a comma.';
$string['showrawcoursedata'] = 'Show tokens and raw course data';
$string['showrawuserdata'] = 'Show tokens and raw user data';
$string['sourceclaim'] = 'CAMPUSonline ID';
$string['sourceclaim_desc'] = 'Select the ID value that your Moodle users have set.';
$string['sourcefield'] = 'Moodle field';
$string['sourcefield_desc'] = 'Select the Moodle field that holds the ID value.';
$string['studentrole'] = 'Students';
$string['students'] = 'Students';
$string['subcategories'] = 'Subcategories';
$string['subcategories_desc'] = 'Specify how to build the subcategory structure.
    <li>Use tokens to build the category names, and backslashes to separate categories, eg: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Make sure that no subcategory name ends up being empty</li>
    <li>Show raw data from CAMPUSonline to see available fields/tokens</li>';
$string['success:connected'] = 'Successfully connected to CAMPUSonline endpoint.';
$string['syncthiscourse'] = 'Sync course with CAMPUSonline';
$string['syncingcourse'] = 'Syncing Moodle course with CAMPUSonline...';
$string['syncusersonlogin'] = 'Sync user data upon login';
$string['syncusersonlogin_desc'] = 'Apart from the sync task, user data will also be synced on every user login.';
$string['testconnection'] = 'Test connection';
$string['testsettings'] = 'Test these settings';
$string['updatecourseurls'] = 'Update course URLs';
$string['updatecourseurls_desc'] = 'Writes back the Moodle course URL to CAMPUSonline each time a course is synced. Normally this is only done upon course creation. If something went wrong, you can activate this, but it should be left unchecked in the long term for performance reasons.';
$string['updateexistingcourses'] = 'Update existing courses';
$string['updateexistingcourses_desc'] = 'Allows the sync task to change names or categories of existing Moodle courses if they change in CAMPUSonline.';
$string['usercount'] = 'Raw data for {$a} users:';
$string['useridsettings'] = 'Identification of existing users';
$string['useridsettings_desc'] = '<ul>
    <li>This sync task matches existing Moodle users to CAMPUSonline users, it is not scheduled by default, and should only be run manually</li>
    <li>It uses the CAMPUSonline person-identifiers/mappings endpoint to retrieve the <strong>Person UID</strong> via any other ID.</li>
    <li>Once identified, the CAMPUSonline person UID will be set in the custom user profile field <strong>CAMPUSonline_person_uid</strong> (created upon plugin installation)</li></ul>';
$string['usermoodlefield'] = 'Custom field as callback for user identification';
$string['usermoodlefield_desc'] = 'If a user is not found via any other means (see above), this field will be used to find the user in Moodle.';
$string['usersynccreateusers'] = 'Create users';
$string['usersynccreateusers_desc'] = 'Allows the user sync task to create users that do not exist or cannot be found in Moodle. <strong>Only activate this after making sure that user identification works correctly</strong>, otherwise you might end up with a lot of duplicate users in Moodle!';
$string['usersyncsettings'] = 'User sync & values';
$string['usersyncsettings_desc'] = '<ul>
    <li>This sync task is <strong>optional</strong>, and should only be enabled if userdata is <strong>not already synced via other means</strong> (eg SSO systems)</li>
    <li>Regardless whether the sync task is active, you should still set the field values, since they are also used for <strong>user identification</strong> and <strong>user creation</strong> (if configured)</li>
    <li><strong>usernames need to be unique</strong>, and fields are filled with valid values for their respective field types, or there will be errors creating users!</li>
    <li>These values are <strong>required</strong>, otherwise user creation will fail: user_auth, user_password, user_username, user_email</li>
    <li>CAMPUSonline <strong>person UID</strong>, <strong>student UID</strong> and <strong>employee UID</strong> will be automatically synced in the respective user profile fields</li>
    <li>Click on <strong>Show tokens and raw data</strong> to see available fields/tokens</li></ul>';
$string['viewlogs'] = 'View logs';
$string['warningsanderrors'] = 'warnings and errors';