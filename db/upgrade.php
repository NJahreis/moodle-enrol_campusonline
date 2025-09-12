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
 * CAMPUSonline enrolment plugin.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to handle install tasks.
 *
 * @return void
 */
function xmldb_enrol_campusonline_install(): void {
    $categoryid = enrol_campusonline_create_custom_profile_field_category('enrol_campusonline');
    enrol_campusonline_create_custom_profile_field(
        'campusonline_id_attempts',
        'Failed attempts to find this user in CAMPUSonline',
        'text',
        $categoryid
    );
    enrol_campusonline_create_custom_profile_field(
        'campusonline_person_uid',
        'campusonline_person_uid',
        'text',
        $categoryid
    );
    enrol_campusonline_create_other_co_course_uids_field();
}

/**
 * Function to handle upgrade tasks.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_enrol_campusonline_upgrade($oldversion): bool {
    global $DB;

    // Add other_co_course_uids course field for shadow courses.
    if ($oldversion < 2024111202) {
        enrol_campusonline_create_other_co_course_uids_field();
        upgrade_plugin_savepoint(true, 2024111202, 'enrol', 'campusonline');
    }

    // Rename our custom user profile field for person_uid.
    if ($oldversion < 2024102402) {
        $field = $DB->get_record('user_info_field', ['shortname' => 'campusonline_person_uid']);
        if ($field) {
            $field->name = 'campusonline_person_uid';
            $DB->update_record('user_info_field', $field);
        }
        upgrade_plugin_savepoint(true, 2024102402, 'enrol', 'campusonline');
    }

    // Create custom user profile field for identification retries.
    if ($oldversion < 2024101601) {
        $categoryid = enrol_campusonline_create_custom_profile_field_category('enrol_campusonline', 'CAMPUSonline');
        enrol_campusonline_create_custom_profile_field(
            'campusonline_id_attempts',
            'Failed attempts to find this user in CAMPUSonline',
            'text',
            $categoryid
        );
        upgrade_plugin_savepoint(true, 2024101601, 'enrol', 'campusonline');
    }

    // Update attributes of personUID custom user profile field.
    if ($oldversion < 2024101501) {
        $field = $DB->get_record('user_info_field', ['shortname' => 'campusonline_person_uid']);
        if ($field) {
            $field->visible = 0;
            $field->locked = 1;
            $DB->update_record('user_info_field', $field);
        }
        upgrade_plugin_savepoint(true, 2024101501, 'enrol', 'campusonline');
    }

    // Create new personUID custom user profile field.
    if ($oldversion < 2024091701) {
        $categoryid = enrol_campusonline_create_custom_profile_field_category('enrol_campusonline', 'CAMPUSonline');
        enrol_campusonline_create_custom_profile_field('campusonline_person_uid', 'Person UID', 'text', $categoryid);
        upgrade_plugin_savepoint(true, 2024091701, 'enrol', 'campusonline');
    }

    return true;
}

/**
 * Function to create a custom user profile field category.
 *
 * @param string $name The name of the custom profile field category.
 * @return int The ID of the newly created category.
 */
function enrol_campusonline_create_custom_profile_field_category($name) {
    global $DB;

    // Check if the profile field category already exists.
    if ($existing = $DB->get_record('user_info_category', ['name' => $name])) {
        return $existing->id;
    }

    // Define the new custom profile field category.
    $data = new stdClass();
    $data->name = $name;
    $data->sortorder = 99;

    // Insert the custom profile field category into the database.
    return $DB->insert_record('user_info_category', $data);
}
