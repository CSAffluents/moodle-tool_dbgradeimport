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
 * Strings for component 'tool_dbgradeimport', language 'en'.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['cannotconnect'] = 'Cannot connect to the database.';
$string['cannotreadexternaltable'] = 'Cannot connect to the database.';
$string['courseid'] = 'Course ID';
$string['dbencoding'] = 'Database encoding';
$string['dbhost'] = 'Database host';
$string['dbhost_desc'] = 'Type database server IP address or host name. Use a system DSN name if using ODBC.';
$string['dbname'] = 'Database name';
$string['dbname_desc'] = 'Leave empty if using a DSN name in database host.';
$string['dbpass'] = 'Database password';
$string['dbsetupsql'] = 'Database setup command';
$string['dbsetupsql_desc'] = 'SQL command for special database setup, often used to setup communication encoding - example for MySQL and PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other databases. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb database driver name, type of the external database engine.';
$string['dbuser'] = 'Database user';
$string['debugdb'] = 'Debug ADOdb';
$string['debugdb_desc'] = 'Debug ADOdb connection to external database - use when getting empty page during login. Not suitable for production sites!';
$string['externaltablenotspecified'] = 'External {$a} table not specified.';
$string['externaltableempty'] = 'External {$a} table is empty.';
$string['externaltablecontains'] = 'External {$a} table contains following columns:';
$string['gradeitems'] = 'Grade items';
$string['gradeitemsprefix'] = 'Grade items prefix';
$string['gradeitemsprefix_desc'] = 'This prefix will be inserted in the ID number during the creation of grade items and will be used to sync student grades.';
$string['ignorehiddencourses'] = 'Ignore hidden courses';
$string['ignorehiddencourses_desc'] = 'If enabled grades will not be imported on courses that are set to be unavailable to students.';
$string['localcoursefield'] = 'Local course field';
$string['localcoursefield_desc'] = 'The field used to identify the local course.';
$string['localuserfield'] = 'Local user field';
$string['localuserfield_desc'] = 'The field used to identify the local user.';
$string['pluginname'] = 'External database grade import';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to import grades in your courses. It is assumed that the external database includes two tables. One to create the elements of assessment and the other to insert student grades.';
$string['remotegradeitemscoursefield'] = 'Remote grade items course field';
$string['remotegradeitemscoursefield_desc'] = 'The field used to identify the course to create the grade items.';
$string['remotegradeitemsfield'] = 'Remote grade items field';
$string['remotegradeitemsfield_desc'] = 'The field used to fill the idnumber of the grade items.';
$string['remotegradeitemsnamefield'] = 'Remote description field';
$string['remotegradeitemsnamefield_desc'] = 'The field used to fill the name of the grade items.';
$string['remotegradeitemstable'] = 'Remote grade items table';
$string['remotegradeitemstable_desc'] = 'The name of the table used to create grade items. The existing grade items will not be be updated. No grade item will be created if leaved empty.';
$string['remotegradescoursefield'] = 'Remote grades course field';
$string['remotegradescoursefield_desc'] = 'The field used to identify the course to fill the grades.';
$string['remotegradesfield'] = 'Remote grade items field';
$string['remotegradesfield_desc'] = 'The field used to identify the grade items based on their idnumber.';
$string['remotegradestable'] = 'Remote grades table';
$string['remotegradestable_desc'] = 'The name of the table used to fill grades of the students. No grade will filled if leaved empty.';
$string['remotegradesuserfield'] = 'Remote grades user field';
$string['remotegradesuserfield_desc'] = 'The field used to identify the user to fill the grades.';
$string['remotegradesvaluefield'] = 'Remote grades value field';
$string['remotegradesvaluefield_desc'] = 'The field that contain the grades.';
$string['remotemaxgradefield'] = 'Remote max grade field';
$string['remotemaxgradefield_desc'] = 'The field used to fill the max grade of the grade items.';
$string['settings'] = 'Settings';
$string['settingsheaderdb'] = 'External database connection';
$string['settingsheaderlocal'] = 'Local field mapping';
$string['settingsheaderremotegrade'] = 'Grade items sync';
$string['settingsheaderremotegradeitem'] = 'Grades creation';
$string['testsettings'] = 'Test settings';
$string['userid'] = 'User ID';
$string['usergrades'] = 'User grades';
