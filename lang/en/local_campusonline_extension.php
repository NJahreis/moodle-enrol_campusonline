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
 * Version metadata for the tool_campusonline plugin.
 *
 * @package   local_campusonline_extension
 * @copyright 2024, Lucas Reeh <lr86gm@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Basics.
$string['pluginname'] = 'CAMPUSonline Extension';

// Settings page.
$string['connectionsettings'] = 'Connection settings';
$string['endpoint'] = 'CAMPUSonline endpoint';
$string['endpoint_desc'] = 'Address of the CAMPUSonline oauth2 endpoint';
$string['clientid'] = 'Client ID';
$string['clientid_desc'] = 'Client ID to access CAMPUSonline';
$string['clientsecret'] = 'Client secret';
$string['clientsecret_desc'] = 'Secret key to access CAMPUSonline';

$string['coursesyncsettings'] = 'Course sync settings';

$string['enrolsyncsettings'] = 'Enrolment sync settings';

$string['testsettings'] = 'Test these settings';
$string['testconnection'] = 'Test connection';
$string['backtosettings'] = 'Back to module settings';

// Alerts.
$string['success:connected'] = 'Successfully connected to CAMPUSonline endpoint.';
$string['error:cannotconnect'] = 'Cannot connect to CAMPUSonline endpoint. Error: {$a}';
$string['error:unknown'] = 'Unknown error.';