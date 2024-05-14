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
 * @package    local_campusonline_extension
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_category('local_campusonline_extension_settings', get_string('pluginname', 'local_campusonline_extension'));
    $ADMIN->add('localplugins', $settings);

    // Setup settings pages.
    $pagesetup = [
        'connection' => new admin_settingpage(
            'local_campusonline_extension_connection',
            get_string('connectionsettings', 'local_campusonline_extension'),
        ),
        'coursesyncsettings' => new admin_settingpage(
            'local_campusonline_extension_course"',
            get_string('coursesyncsettings', 'local_campusonline_extension')
        ),
        'enrolsyncsettings' => new admin_settingpage(
            'local_campusonline_extension_enrol"',
            get_string('enrolsyncsettings', 'local_campusonline_extension')
        ),
    ];

    // Add pages to admin tree.
    $pages = (object)[1];
    foreach ($pagesetup as $page) {
        $ADMIN->add('local_campusonline_extension_settings', $page);
        $pages->{str_replace('local_campusonline_extension_', '', $page->name)} = $page;
    }

    // Only show in admin settings.
    if ($ADMIN->fulltree) {

        // CO endpoint.
        $pages->connection->add(new admin_setting_configtext(
            'local_campusonline_extension/endpoint',
            get_string('endpoint', 'local_campusonline_extension'),
            get_string('endpoint_desc', 'local_campusonline_extension'),
            '',
        ));

        // Client ID.
        $pages->connection->add(new admin_setting_configtext(
            'local_campusonline_extension/clientid',
            get_string('clientid', 'local_campusonline_extension'),
            get_string('clientid_desc', 'local_campusonline_extension'),
            '',
        ));

        // Client secret.
        $pages->connection->add(new admin_setting_configpasswordunmask(
            'local_campusonline_extension/clientsecret',
            get_string('clientsecret', 'local_campusonline_extension'),
            get_string('clientsecret_desc', 'local_campusonline_extension'),
            '',
        ));

        // Test settings.
        $buttons = '<a class="btn btn-secondary m-1"
        href=' . new moodle_url('/local/campusonline_extension/test.php?function=connection') . '>' .
        get_string('testconnection', 'local_campusonline_extension') . '</a>';
        $buttons .= '<a class="btn btn-secondary m-1"
        href=' . new moodle_url('/local/campusonline_extension/test.php?function=previewcourses') . '>' .
        get_string('previewcourses', 'local_campusonline_extension') . '</a>';

        $pages->connection->add(new admin_setting_heading(
            'local_campusonline_extension/testsettings',
            get_string('testsettings', 'local_campusonline_extension'),
            $buttons,
        ));



    }

}