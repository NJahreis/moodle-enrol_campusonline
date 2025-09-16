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
 * UID form element for showing raw data for specific users/courses.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_campusonline\form;

require_once($CFG->libdir . '/formslib.php');

/**
 * UID form element for showing raw data for specific users/courses.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uid_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Hidden element for function.
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_RAW);

        // Hidden element for limit.
        $mform->addElement('hidden', 'limit');
        $mform->setType('limit', PARAM_INT);

        // Single form element (text field).
        $mform->addElement('text', 'uid', get_string('campusonline_uid', 'enrol_campusonline'));
        $mform->addHelpButton('uid', 'campusonline_uid', 'enrol_campusonline');
        $mform->setType('uid', PARAM_TEXT);

        // Add submit button.
        $mform->addElement('submit', 'submitbutton', get_string('submit'));
    }
}