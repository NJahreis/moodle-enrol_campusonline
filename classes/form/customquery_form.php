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
 * Form for custom API queries.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace enrol_campusonline\form;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for custom API queries.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customquery_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Explanation.
        $mform->addElement('static', 'description', '', get_string('customquery_desc', 'enrol_campusonline'));

        // Endpoint.
        $mform->addElement('text', 'endpoint', get_string('endpoint', 'enrol_campusonline'), ['size' => '80']);
        $mform->setType('endpoint', PARAM_TEXT);
        $mform->addHelpButton('endpoint', 'endpoint', 'enrol_campusonline');
        $mform->addRule('endpoint', null, 'required', null, 'client');

        // Parameters.
        $mform->addElement('textarea', 'parameters', get_string('parameters', 'enrol_campusonline'));
        $mform->setType('parameters', PARAM_TEXT);
        $mform->addHelpButton('parameters', 'parameters', 'enrol_campusonline');

        $this->add_action_buttons(true, get_string('submit'));
    }
}