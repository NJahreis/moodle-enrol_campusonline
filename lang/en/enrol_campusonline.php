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
$string['pluginname'] = 'CAMPUSonline Enrollment';
$string['privacy:metadata'] = 'The CAMPUSonline enrolment plugin does not store any personal data.';
$string['task:sync'] = 'CAMPUSonline sync for courses & enrolments';
$string['task:usersync'] = 'CAMPUSonline sync for users';

// Settings page.
$string['connectionsettings'] = 'Connection settings';
$string['endpoint'] = 'CAMPUSonline endpoint';
$string['endpoint_desc'] = 'Address of the CAMPUSonline oauth2 endpoint';
$string['clientid'] = 'Client ID';
$string['clientid_desc'] = 'Client ID to access CAMPUSonline';
$string['clientsecret'] = 'Client secret';
$string['clientsecret_desc'] = 'Secret key to access CAMPUSonline';

$string['enrolmentsyncsettings'] = 'Enrolment sync settings';
$string['enrolmentsyncsettings_desc'] = 'The enrolment sync will create and update enrolments and the respective courses.';
$string['configuretask'] = 'Configure scheduled sync task';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester to be synced.';
$string['updateexistingcourses'] = 'Update existing courses';
$string['updateexistingcourses_desc'] = 'Allows the enrolment sync task to change names or categories of existing Moodle courses if they change in CAMPUSonline.';
$string['enrolsynccreateusers'] = 'Create users';
$string['enrolsynccreateusers_desc'] = 'Allows the enrolment sync task will create users that do not exist or cannot be found in Moodle.';

$string['coursecatsettings'] = 'Course category settings';
$string['rootcoursecategory'] = 'Root course category';
$string['rootcoursecategory_desc'] = 'Course category to sync courses into. If you select "TOP", then you will need to have rules to create subcategories, otherwise the sync will fail.';
$string['subcategories'] = 'Subcategories';
$string['subcategories_desc'] = 'Specify how to build the subcategory structure.
    <li>Use tokens to build the category names, and backslashes to separate categories, eg: "{org:code}\{course:semesterKey}\{course:courseClassificationKey}"</li>
    <li>Make sure that no subcategory name ends up being empty</li>
    <li>Show raw data from CAMPUSonline to see available fields/tokens</li>';
$string['createcoursecatetories'] = 'Create course categories';
$string['createcoursecatetories_desc'] = 'Allows the enrolment sync task to create course categories if they do not exist.';

$string['coursesyncsettings'] = 'Course field settings';
$string['coursesyncsettings_desc'] = '
    <li>Moodle course <strong>idnumber</strong> will always be filled with the CAMPUSonline course <strong>uid</strong></li>
    <li>Choose values for other course fields (including course custom fields) by combining text and <strong>tokens</strong> for CAMPUSonline fields, eg: "CAMPUSONLINE_COURSE_{title}</li>
    <li>Show raw data from CAMPUSonline to see available fields/tokens</li>
    <li>Make sure the course shortnames are unique, and fields are filled with valid values for their respective field types, or there will be errors creating courses!</li>';



$string['usersyncsettings'] = 'User sync settings';
$string['usersynccreateusers'] = 'Allow user sync to create users';
$string['usersynccreateusers_desc'] = 'When activated, the user sync task will create users that do not exist or cannot be found in Moodle';


$string['rolemappings'] = 'Role mappings';
$string['rolemappings_desc'] = 'Select Moodle roles to use for CAMPUSonline students and lectureship roles.';
$string['rolemappings_notconnected'] = 'Could not connect to CAMPUSOnline. Check your connection settings and reload this page, to add mappings for CAMPUSonline roles.';
$string['donotsyncrole'] = '- do not sync this role -';
$string['studentrole'] = 'Students';
$string['lectureshiproles'] = 'Select Moodle roles to use for CAMPUSonline lectureship roles.';

$string['logsettings'] = 'Log settings';
$string['logduration'] = 'Keep logs for (days)';
$string['viewlogs'] = 'View logs';
$string['logs'] = 'Logs';
$string['event'] = 'Event';
$string['deletedcourse'] = 'deleted course (id: {$a})';

$string['showrawcoursedata'] = 'Show raw data from CAMPUSonline';
$string['previewcourses'] = 'Preview courses with these settings';
$string['coursepreview'] = 'Courses preview';
$string['coursecount'] = '{$a} courses found.';
$string['testsettings'] = 'Test these settings';
$string['testconnection'] = 'Test connection';
$string['backtosettings'] = 'Back to module settings';

// Alerts.
$string['success:connected'] = 'Successfully connected to CAMPUSonline endpoint.';
$string['error:cannotconnect'] = 'Cannot connect to CAMPUSonline endpoint. Error: {$a}';
$string['error:endpointmissing'] = 'You have to provide a valid endpoint in settings.';
$string['error:unknown'] = 'Unknown error.';
$string['error:uidfieldnotfound'] = 'CAMPUSOnline user profile field not found - reinstall the plugin or re-create the field(s) manually.';