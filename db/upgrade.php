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
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright  2024, Michael Lorenzoni
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to handle the installation and upgrade tasks.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_enrol_campusonline_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Create custom user profile field for identification retries.
    if ($oldversion < 2024101601) {
        $categoryid = create_custom_profile_field_category('enrol_campusonline', 'CAMPUSonline');
        create_custom_profile_field('campusonline_id_attempts', 'Failed attempts to find this user in CAMPUSonline', 'text', $categoryid);
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
        $categoryid = create_custom_profile_field_category('enrol_campusonline', 'CAMPUSonline');
        create_custom_profile_field('campusonline_person_uid', 'Person UID', 'text', $categoryid);
        upgrade_plugin_savepoint(true, 2024091701, 'enrol', 'campusonline');
    }

    return true;
}

/**
 * Function to create a custom user profile field category.
 *
 * @param string $shortname The shortname of the custom profile field category.
 * @param string $name The name of the custom profile field category.
 * @return int The ID of the newly created category.
 */
function create_custom_profile_field_category($shortname, $name) {
    global $DB;

    // Check if the profile field category already exists.
    if ($existing = $DB->get_record('user_info_category', array('name' => $name))) {
        return $existing->id;
    }

    // Define the new custom profile field category.
    $data = new stdClass();
    $data->name = $name;
    $data->sortorder = 99;

    // Insert the custom profile field category into the database.
    return $DB->insert_record('user_info_category', $data);
}

/**
 * Function to create a custom user profile field.
 *
 * @param string $shortname The shortname of the custom profile field.
 * @param string $name The name of the custom profile field.
 * @param string $datatype The data type of the custom profile field.
 * @param int $categoryid The ID of the custom profile field category.
 */
function create_custom_profile_field($shortname, $name, $datatype, $categoryid) {
    global $DB;

    // Check if the profile field already exists.
    if ($DB->record_exists('user_info_field', ['shortname' => $shortname])) {
        return;
    }

    // Define the new custom profile field.
    $data = new stdClass();
    $data->shortname = $shortname;
    $data->name = $name;
    $data->datatype = $datatype;
    $data->description = 'Created by enrol_campusonline plugin.';
    $data->descriptionformat = FORMAT_HTML;
    $data->categoryid = $categoryid;
    $data->sortorder = 1;
    $data->required = 0;
    $data->locked = 1;
    $data->visible = 0;
    $data->forceunique = 0;
    $data->signup = 0;
    $data->defaultdata = '';
    $data->defaultdataformat = FORMAT_HTML;
    $data->param1 = 30; // Maximum length for shorttext.
    $data->param2 = null;
    $data->param3 = null;
    $data->param4 = null;
    $data->param5 = null;

    // Insert the custom profile field into the database.
    $DB->insert_record('user_info_field', $data);
}
