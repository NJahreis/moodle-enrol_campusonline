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
 * Moodle page to show the plugins README file.
 *
 * @package    enrol_campusonline
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_login();

// Set page.
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/enrol/campusonline/readme.php');
$PAGE->set_title(get_string('pluginname', 'enrol_campusonline'));

// Begin output.
echo $OUTPUT->header();

// Get language of logged in user.
$language = current_language();
$markdownfile = $language == 'de' ? 'README_de.md' : 'README.md';

// Read the Markdown file content.
$markdowncontent = file_get_contents($markdownfile);

// Convert Markdown to HTML using basic replacements.

// Convert headers (###, ##, #).
$markdowncontent = preg_replace('/### (.+)/', '<h3>$1</h3>', $markdowncontent);
$markdowncontent = preg_replace('/## (.+)/', '<h2>$1</h2>', $markdowncontent);
$markdowncontent = preg_replace('/# (.+)/', '<h1>$1</h1>', $markdowncontent);

// Convert bold text (**text** or __text__).
$markdowncontent = preg_replace('/\*\*(.+)\*\*/', '<strong>$1</strong>', $markdowncontent);
$markdowncontent = preg_replace('/__(.+)__/', '<strong>$1</strong>', $markdowncontent);

// Convert italic text (*text* or _text_).
$markdowncontent = preg_replace('/\*(.+)\*/', '<em>$1</em>', $markdowncontent);
$markdowncontent = preg_replace('/_(.+)_/', '<em>$1</em>', $markdowncontent);

// Convert links [text](url).
$markdowncontent = preg_replace('/\[(.+)\]\((.+)\)/', '<a href="$2">$1</a>', $markdowncontent);

// Convert newlines.
$markdowncontent = preg_replace('/\R/', '<br>', $markdowncontent);

// Output the HTML in the browser.
echo $markdowncontent;

echo $OUTPUT->footer();
