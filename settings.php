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
 * Database grade import plugin settings and presets.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_dbgradeimport', get_string('settings'));

    $plugin = new tool_dbgradeimport_base();

    $settings->add(new admin_setting_heading('tool_dbgradeimport_settings', '',
            $plugin->get_string('pluginname_desc')));

    // General settings.
    $settings->add(new admin_setting_heading('tool_dbgradeimport_generalsettings', get_string('generalsettings', 'admin'), ''));
    $settings->add(new admin_setting_configtext('tool_dbgradeimport_gradeitemsprefix',
            $plugin->get_string('gradeitemsprefix'), $plugin->get_string('gradeitemsprefix_desc'), ''));
    $settings->add(new admin_setting_configcheckbox('tool_dbgradeimport_ignorehiddencourses',
            $plugin->get_string('ignorehiddencourses'),
            $plugin->get_string('ignorehiddencourses_desc'), 1));


    // External database connection.
    $settings->add(new admin_setting_heading('tool_dbgradeimport_exdbheader',
            $plugin->get_string('settingsheaderdb'), ''));

    $options = array(
        '',
        "access",
        "ado_access",
        "ado",
        "ado_mssql",
        "borland_ibase",
        "csv",
        "db2",
        "fbsql",
        "firebird",
        "ibase",
        "informix72",
        "informix",
        "mssql", "mssql_n",
        "mssqlnative",
        "mysql",
        "mysqli",
        "mysqlt",
        "oci805",
        "oci8",
        "oci8po",
        "odbc",
        "odbc_mssql",
        "odbc_oracle",
        "oracle",
        "postgres64",
        "postgres7",
        "postgres",
        "proxy",
        "sqlanywhere",
        "sybase",
        "vfp"
    );
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('tool_dbgradeimport/dbtype', $plugin->get_string('dbtype'),
            $plugin->get_string('dbtype_desc'), '', $options));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/dbhost', $plugin->get_string('dbhost'),
            $plugin->get_string('dbhost_desc'), 'localhost'));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/dbuser', $plugin->get_string('dbuser'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('tool_dbgradeimport/dbpass',
            $plugin->get_string('dbpass'), '', ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/dbname', $plugin->get_string('dbname'),
            $plugin->get_string('dbname_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/dbencoding',
            $plugin->get_string('dbencoding'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/dbsetupsql', $plugin->get_string('dbsetupsql'),
            $plugin->get_string('dbsetupsql_desc'), ''));

    $settings->add(new admin_setting_configcheckbox('tool_dbgradeimport/dbsybasequoting',
            $plugin->get_string('dbsybasequoting'), $plugin->get_string('dbsybasequoting_desc'), 0));

    $settings->add(new admin_setting_configcheckbox('tool_dbgradeimport/debugdb', $plugin->get_string('debugdb'),
            $plugin->get_string('debugdb_desc'), 0));

    // Local field mapping.
    $settings->add(new admin_setting_heading('tool_dbgradeimport_localheader',
            $plugin->get_string('settingsheaderlocal'), ''));

    $options = array(
        'id' => $plugin->get_string('courseid'),
        'idnumber' => get_string('idnumber'),
        'shortname' => get_string('shortname')
    );
    $settings->add(new admin_setting_configselect('tool_dbgradeimport/localcoursefield',
            $plugin->get_string('localcoursefield'), $plugin->get_string('localcoursefield_desc'),
            'idnumber', $options));

    // Only local users if username selected, no mnet users!
    $options = array(
        'id' => $plugin->get_string('userid'),
        'idnumber' => get_string('idnumber'),
        'email' => get_string('email'),
        'username' => get_string('username')
    );
    $settings->add(new admin_setting_configselect('tool_dbgradeimport/localuserfield',
            $plugin->get_string('localuserfield'), $plugin->get_string('localuserfield_desc'),
            'idnumber', $options));

    // Remote grade items sync.
    $settings->add(new admin_setting_heading('tool_dbgradeimport_remotegradeitemsheader',
            $plugin->get_string('settingsheaderremotegradeitem'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradeitemstable',
            $plugin->get_string('remotegradeitemstable'),
            $plugin->get_string('remotegradeitemstable_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradeitemscoursefield',
            $plugin->get_string('remotegradeitemscoursefield'),
            $plugin->get_string('remotegradeitemscoursefield_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradeitemsfield',
            $plugin->get_string('remotegradeitemsfield'),
            $plugin->get_string('remotegradeitemsfield_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradeitemsnamefield',
            $plugin->get_string('remotegradeitemsnamefield'),
            $plugin->get_string('remotegradeitemsnamefield_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotemaxgradefield',
            $plugin->get_string('remotemaxgradefield'),
            $plugin->get_string('remotemaxgradefield_desc'), ''));

    // Remote grades sync.
    $settings->add(new admin_setting_heading('tool_dbgradeimport_remotegradesheader',
            $plugin->get_string('settingsheaderremotegrade'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradestable',
            $plugin->get_string('remotegradestable'),
            $plugin->get_string('remotegradestable_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradescoursefield',
            $plugin->get_string('remotegradescoursefield'),
            $plugin->get_string('remotegradescoursefield_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradesfield',
            $plugin->get_string('remotegradesfield'),
            $plugin->get_string('remotegradesfield_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradesuserfield',
            $plugin->get_string('remotegradesuserfield'),
            $plugin->get_string('remotegradesuserfield_desc'), ''));

    $settings->add(new admin_setting_configtext('tool_dbgradeimport/remotegradesvaluefield',
            $plugin->get_string('remotegradesvaluefield'),
            $plugin->get_string('remotegradesvaluefield_desc'), ''));

    $ADMIN->add('courses', new admin_category('tool_dbgradeimportfolder', $plugin->get_string('pluginname')));
    $ADMIN->add('tool_dbgradeimportfolder', $settings);
    $ADMIN->add('tool_dbgradeimportfolder', new admin_externalpage('dbgradeimporttestsettings',
            $plugin->get_string('testsettings'), "$CFG->wwwroot/$CFG->admin/tool/dbgradeimport/test_settings.php"));
}
