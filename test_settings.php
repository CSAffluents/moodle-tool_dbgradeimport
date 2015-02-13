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
 * Test database grade import plugin settings.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('dbgradeimporttestsettings');

$plugin = new tool_dbgradeimport_base();

$returnurl = new moodle_url('/admin/settings.php', array('section' => 'tool_dbgradeimport'));

echo $OUTPUT->header();

$importer = new tool_dbgradeimport_importer();
if (!$importer or !method_exists($importer, 'test_settings')) {
    redirect($returnurl);
}

echo $OUTPUT->heading($plugin->get_string('testsettings', $plugin->get_string('testsettings')));

$importer->test_settings();

echo $OUTPUT->continue_button($returnurl);
echo $OUTPUT->footer();
