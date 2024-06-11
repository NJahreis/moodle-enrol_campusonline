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
$string['task:sync'] = 'CAMPUSonline sync';

// Settings page.
$string['connectionsettings'] = 'Connection settings';
$string['endpoint'] = 'CAMPUSonline endpoint';
$string['endpoint_desc'] = 'Address of the CAMPUSonline oauth2 endpoint';
$string['clientid'] = 'Client ID';
$string['clientid_desc'] = 'Client ID to access CAMPUSonline';
$string['clientsecret'] = 'Client secret';
$string['clientsecret_desc'] = 'Secret key to access CAMPUSonline';

$string['syncsettings'] = 'Sync settings';
$string['semester'] = 'Semester';
$string['semester_desc'] = 'Semester to be synced.';
$string['rootcoursecategory'] = 'Root course category';
$string['rootcoursecategory_desc'] = 'Course category to sync courses into. If you select "TOP", then you will need to have rules to create subcategories, otherwise the sync will fail.';
$string['updateexistingcourses'] = 'Update existing courses';
$string['updateexistingcourses_desc'] = 'Will change names or categories of existing Moodle courses if they changes in CAMPUSonline.';
$string['configuretask'] = 'Configure scheduled sync task';
$string['activatecoursesync'] = 'Activate course sync';
$string['activatecoursesync_desc'] = 'If enabled, new courses will be generated based on the CAMPUSonline data.';

$string['coursesyncsettings'] = 'Course field settings';
$string['coursesyncsettings_desc'] = '
<li>Moodle course <strong>idnumber</strong> will always be filled with the CAMPUSonline course <strong>uid</strong></li>
<li>You can choose how to build your course <strong>fullname</strong> and <strong>shortname</strong> by combining text and tokens for CAMPUSonline fields, eg: "CAMPUSONLINE_COURSE_{title}</li>
<li>Make sure the course shortnames are unique, or there will be errors creating courses!</li>';

$string['enrolmentsyncsettings'] = 'Enrolment sync settings';

$string['logsettings'] = 'Log settings';
$string['logduration'] = 'Keep logs for (days)';
$string['viewlogs'] = 'View logs';
$string['logs'] = 'Logs';
$string['event'] = 'Event';
$string['deletedcourse'] = 'deleted course (id: {$a})';

$string['showrawcoursedata'] = 'Show raw data from CAMPUSonline';
$string['previewcourses'] = 'Preview courses with these settings';
$string['coursecount'] = '{$a} courses found.';
$string['testsettings'] = 'Test these settings';
$string['testconnection'] = 'Test connection';
$string['backtosettings'] = 'Back to module settings';

// Alerts.
$string['success:connected'] = 'Successfully connected to CAMPUSonline endpoint.';
$string['error:cannotconnect'] = 'Cannot connect to CAMPUSonline endpoint. Error: {$a}';
$string['error:endpointmissing'] = 'You have to provide a valid endpoint in settings.';
$string['error:unknown'] = 'Unknown error.';