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

defined('MOODLE_INTERNAL') || die();

/**
 * Function to handle install tasks.
 *
 * @return bool
 */
function xmldb_enrol_campusonline_install() {
    $categoryid = enrol_campusonline_create_custom_profile_field_category('enrol_campusonline', 'CAMPUSonline');
    enrol_campusonline_create_custom_profile_field('campusonline_id_attempts', 'Failed attempts to find this user in CAMPUSonline', 'text', $categoryid);
    enrol_campusonline_create_custom_profile_field('campusonline_person_uid', 'campusonline_person_uid', 'text', $categoryid);
    enrol_campusonline_create_other_co_course_uids_field();
}

/**
 * Function to handle upgrade tasks.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_enrol_campusonline_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

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
        enrol_campusonline_create_custom_profile_field('campusonline_id_attempts', 'Failed attempts to find this user in CAMPUSonline', 'text', $categoryid);
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
 * @param string $shortname The shortname of the custom profile field category.
 * @param string $name The name of the custom profile field category.
 * @return int The ID of the newly created category.
 */
function enrol_campusonline_create_custom_profile_field_category($shortname, $name) {
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
function enrol_campusonline_create_custom_profile_field($shortname, $name, $datatype, $categoryid) {
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

/**
 * Function to create the other_co_course_uids course field for shadow courses.
 */
function enrol_campusonline_create_other_co_course_uids_field() {
    global $DB;

    // Create category.
    if ($category = $DB->get_records('customfield_category',
        ['name' => 'CAMPUSonline', 'component' => 'core_course', 'area' => 'course'])) {
     $categoryid = reset($category)->id;
    } else {
        $category = new stdClass();
        $category->name = 'CAMPUSonline';
        $category->component = 'core_course';
        $category->area = 'course';
        $category->sortorder = 99;
        $category->contextid = 1;
        $category->itemid = 0;
        $category->timecreated = time();
        $category->timemodified = time();
        $categoryid = $DB->insert_record('customfield_category', $category);
    }

    // Create field.
    if (!$DB->get_record('customfield_field', ['shortname' => 'campusonline_other_co_course_uids'])) {
        $field = new stdClass();
        $field->shortname = 'campusonline_other_co_course_uids';
        $field->name = 'Other CAMPUSonline courses linked to this Moodle course.';
        $field->type = 'text';
        $field->description = '<p dir="ltr">Comma-separated list of CAMPUSonline course UIDs that share this Moodle course.</p><p dir="ltr">Use this if you want to use the same Moodle course for multiple CAMPUSonline courses, so that the URL for this course will be written back to all of those courses.</p><p dir="ltr">This will not affect syncing of enrollments.</p>';
        $field->descriptionformat = 1;
        $field->sortorder = 0;
        $field->categoryid = $categoryid;
        $field->configdata = '{"required":"0","uniquevalues":"0","defaultvalue":"","displaysize":50,"maxlength":1333,"ispassword":"0","link":"","locked":"0","visibility":"1"}';
        $field->timecreated = time();
        $field->timemodified = time();
        $DB->insert_record('customfield_field', $field);
    }
}