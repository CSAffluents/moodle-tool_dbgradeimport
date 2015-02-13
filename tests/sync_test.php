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
 * Database grade import plugin tests.
 *
 * External database grade import sync tests, this also tests adodb drivers
 * that are matching our four supported Moodle database drivers.
 *
 * @package    tool_dbgradeimport
 * @category   test
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Database grade import test class.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_dbgradeimport_testcase extends advanced_testcase {

    /**
     * @var The name of the remote table containing the grade items.
     */
    const GRADE_ITEMS_TABLE = 'tool_dbgradeimport_test_gi';

    /**
     * @var The name of the remote table containing the user grades.
     */
    const GRADES_TABLE = 'tool_dbgradeimport_test_gg';

    /**
     * @var array The courses used in this test.
     */
    protected static $courses = array();

    /**
     * @var array The users used in this test.
     */
    protected static $users = array();

    /** @var string Original error log */
    protected $oldlog;

    /**
     * Initialization of the tables and variables required for the tests.
     *
     * @throws exception Unknown database driver.
     */
    protected function init_tool_dbgradeimport() {
        global $DB, $CFG;

        // Discard error logs from AdoDB.
        $this->oldlog = ini_get('error_log');
        ini_set('error_log', "$CFG->dataroot/testlog.log");

        $dbman = $DB->get_manager();

        $plugin = new tool_dbgradeimport_base();

        $plugin->set_config('localcoursefield', 'idnumber');
        $plugin->set_config('localuserfield', 'idnumber');
        $plugin->set_config('ignorehiddencourses', 1);

        $plugin->set_config('dbencoding', 'utf-8');

        $plugin->set_config('dbhost', $CFG->dbhost);
        $plugin->set_config('dbuser', $CFG->dbuser);
        $plugin->set_config('dbpass', $CFG->dbpass);
        $plugin->set_config('dbname', $CFG->dbname);

        if (!empty($CFG->dboptions['dbport'])) {
            $plugin->set_config('dbhost', $CFG->dbhost.':'.$CFG->dboptions['dbport']);
        }

        switch ($DB->get_dbfamily()) {

            case 'mysql':
                $plugin->set_config('dbtype', 'mysqli');
                $plugin->set_config('dbsetupsql', "SET NAMES 'UTF-8'");
                $plugin->set_config('dbsybasequoting', '0');
                if (!empty($CFG->dboptions['dbsocket'])) {
                    $dbsocket = $CFG->dboptions['dbsocket'];
                    if ((strpos($dbsocket, '/') === false and strpos($dbsocket, '\\') === false)) {
                        $dbsocket = ini_get('mysqli.default_socket');
                    }
                    $plugin->set_config('dbtype', 'mysqli://' . rawurlencode($CFG->dbuser) . ':' . rawurlencode($CFG->dbpass) .
                            '@' . rawurlencode($CFG->dbhost) . '/' . rawurlencode($CFG->dbname) . '?socket=' .
                            rawurlencode($dbsocket));
                }
                break;

            case 'oracle':
                $plugin->set_config('dbtype', 'oci8po');
                $plugin->set_config('dbsybasequoting', '1');
                break;

            case 'postgres':
                $plugin->set_config('dbtype', 'postgres7');
                $setupsql = "SET NAMES 'UTF-8'";
                if (!empty($CFG->dboptions['dbschema'])) {
                    $setupsql .= "; SET search_path = '".$CFG->dboptions['dbschema']."'";
                }
                $plugin->set_config('dbsetupsql', $setupsql);
                $plugin->set_config('dbsybasequoting', '0');
                if (!empty($CFG->dboptions['dbsocket']) and ($CFG->dbhost === 'localhost' or $CFG->dbhost === '127.0.0.1')) {
                    if (strpos($CFG->dboptions['dbsocket'], '/') !== false) {
                        $socket = $CFG->dboptions['dbsocket'];
                        if (!empty($CFG->dboptions['dbport'])) {
                            $socket .= ':' . $CFG->dboptions['dbport'];
                        }
                        $plugin->set_config('dbhost', $socket);
                    } else {
                        $plugin->set_config('dbhost', '');
                    }
                }
                break;

            case 'mssql':
                if (get_class($DB) == 'mssql_native_moodle_database') {
                    $plugin->set_config('dbtype', 'mssql_n');
                } else {
                    $plugin->set_config('dbtype', 'mssqlnative');
                }
                $plugin->set_config('dbsybasequoting', '1');
                break;

            default:
                throw new exception('Unknown database driver '.get_class($DB));
        }

        // NOTE: It is stongly discouraged to create new tables in advanced_testcase classes,
        //       but there is no other simple way to test ext database enrol sync, so let's
        //       disable transactions are try to cleanup after the tests.

        $table = new xmldb_table(self::GRADE_ITEMS_TABLE);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('gradeitemid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('gradeitemname', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('maxgrade', XMLDB_TYPE_CHAR, '255', null, null, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $plugin->set_config('remotegradeitemstable', $CFG->prefix . self::GRADE_ITEMS_TABLE);
        $plugin->set_config('remotegradeitemscoursefield', 'courseid');
        $plugin->set_config('remotegradeitemsfield', 'gradeitemid');
        $plugin->set_config('remotegradeitemsnamefield', 'gradeitemname');
        $plugin->set_config('remotemaxgradefield', 'maxgrade');

        $table = new xmldb_table(self::GRADES_TABLE);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('gradeitemid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('userid', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '255', null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);
        $plugin->set_config('remotegradestable', $CFG->prefix . self::GRADES_TABLE);
        $plugin->set_config('remotegradescoursefield', 'courseid');
        $plugin->set_config('remotegradesfield', 'gradeitemid');
        $plugin->set_config('remotegradesuserfield', 'userid');
        $plugin->set_config('remotegradesvaluefield', 'value');

        // Create some test users and courses and enrol them in course 1.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        for ($i = 1; $i <= 4; $i++) {
            self::$courses[$i] = $this->getDataGenerator()->create_course(array('fullname' => 'Test course '.$i,
                'shortname' => 'tc'.$i, 'idnumber' => 'courseid'.$i));
            self::$users[$i] = $this->getDataGenerator()->create_user(array('username' => 'username'.$i,
                'idnumber' => 'userid' . $i, 'email' => 'user'.$i.'@example.com'));
            $this->getDataGenerator()->enrol_user(self::$users[$i]->id, self::$courses[1]->id, $studentrole->id);
        }
    }

    /**
     * Clean up of the tables and variables required for the tests.
     */
    protected function cleanup_tool_dbgradeimport() {
        global $DB;

        $dbman = $DB->get_manager();
        $table = new xmldb_table(self::GRADE_ITEMS_TABLE);
        $dbman->drop_table($table);
        $table = new xmldb_table(self::GRADES_TABLE);
        $dbman->drop_table($table);

        self::$courses = null;
        self::$users = null;

        ini_set('error_log', $this->oldlog);
    }

    /**
     * Reset the records used in this test.
     */
    protected function reset_tool_dbgradeimport() {
        global $DB;
        $DB->delete_records(self::GRADE_ITEMS_TABLE, array());
        $DB->delete_records(self::GRADES_TABLE, array());
        $this->delete_all_grade_items();
    }

    /**
     * Delete all grade items.
     *
     * @param int $courseid The id of the course.
     */
    protected function delete_all_grade_items($courseid = null) {
        if (isset($courseid)) {
            $params = new stdClass();
            $params->courseid = $courseid;
            $gradeitems = grade_item::fetch_all($params);
            if (is_array($gradeitems)) {
                foreach ($gradeitems as $gradeitem) {
                    $gradeitem->delete('manual');
                }
            }
        } else {
            foreach (self::$courses as $course) {
                $this->delete_all_grade_items($course->id);
            }
        }
    }

    /**
     * Delete all user grades.
     * 
     * @param int $courseid The id of the course.
     */
    protected function delete_all_grades($courseid = null) {
        if (isset($courseid)) {
            $params = new stdClass();
            $params->courseid = $courseid;
            $gradeitems = grade_item::fetch_all($params);
            if (is_array($gradeitems)) {
                foreach ($gradeitems as $gradeitem) {
                    $gradeitem->delete_all_grades('manual');
                }
            }
        } else {
            foreach (self::$courses as $course) {
                $this->delete_all_grades($course->id);
            }
        }
    }

    /**
     * Get a specified grade item.
     *
     * @param int $courseid The course id containing the grade item.
     * @param string $idnumber the id number who identified the grade item.
     * @return grade_item $graditem The grade item object.
     */
    protected function get_grade_item($courseid, $idnumber) {
        $params = new stdClass();
        $params->courseid = $courseid;
        $params->idnumber = $idnumber;
        $gradeitem = grade_item::fetch($params);
        return $gradeitem;
    }

    /**
     * Get a specified grade for an user.
     *
     * @param int $courseid The course id containing the grade grade.
     * @param string $idnumber the id number who identified the grade grade.
     * @param string $userid the user id of the grade grade.
     * @return grade_grade $grade The grade grade object.
     */
    protected function get_grade($courseid, $idnumber, $userid) {
        $grade = null;
        if ($gradeitem = $this->get_grade_item($courseid, $idnumber)) {
            $grade = $gradeitem->get_grade($userid, false);
        }
        return $grade;
    }

    /**
     * Insert a grade item in the remote database.
     *
     * @param int $courseindex The index of the course.
     * @param int $gradeitemindex The index of the grade item.
     * @param string $maxgrade The max grade for the grade item.
     */
    protected function insert_remote_grade_item($courseindex = 1, $gradeitemindex = 1, $maxgrade = '100') {
        global $DB;
        $DB->insert_record(self::GRADE_ITEMS_TABLE, array('courseid' => self::$courses[$courseindex]->idnumber,
            'gradeitemid' => 'gradeitem' . $gradeitemindex,
            'gradeitemname' => 'Grade item ' . $gradeitemindex, 'maxgrade' => $maxgrade));
    }

    /**
     * Insert an user grade in the remote database.
     *
     * @param int $courseindex The index of the course.
     * @param int $gradeitemindex The index of the grade item.
     * @param float $userindex The index of the user.
     * @param string $value The value of the grade.
     */
    protected function insert_remote_grade($courseindex = 1, $gradeitemindex = 1, $userindex = 1, $value = '80') {
        global $DB;
        $DB->insert_record(self::GRADES_TABLE, array('courseid' => self::$courses[$courseindex]->idnumber,
            'gradeitemid' => 'gradeitem' . $gradeitemindex,
            'userid' => 'userid' . $userindex, 'value' => $value));
    }

    /**
     * Update an user grade in the remote database.
     *
     * @param int $courseindex The index of the course.
     * @param int $gradeitemindex The index of the grade item.
     * @param int $userindex The index of the user.
     * @param string $value The value of the grade.
     */
    protected function update_remote_grade($courseindex = 1, $gradeitemindex = 1, $userindex = 1, $value = '80') {
        global $DB;
        if ($grade = $DB->get_record(self::GRADES_TABLE, array('courseid' => self::$courses[$courseindex]->idnumber,
                'gradeitemid' => 'gradeitem' . $gradeitemindex, 'userid' => 'userid' . $userindex), 'id')) {
            $DB->update_record(self::GRADES_TABLE, array('id' => $grade->id, 'value' => $value));
        }
    }

    /**
     * Assert that a specified grade item is created.
     *
     * @param int $courseid The course id containing the grade item.
     * @param string $idnumber the id number who identified the grade item.
     * @return grade_item|null The grade item object or null if not found.
     */
    public function assert_grade_item_is_created($courseid, $idnumber) {
        $gradeitem = $this->get_grade_item($courseid, $idnumber);
        $this->assertNotEmpty($gradeitem, 'The grade item of the course ' . $courseid . ' identified by ' . $idnumber .
                ' should be created. It is not.');
        return $gradeitem;
    }

    /**
     * Assert that a specified grade item is not created.
     *
     * @param int $courseid The course id containing the grade item.
     * @param string $idnumber the id number who identified the grade item.
     */
    public function assert_grade_item_is_not_created($courseid, $idnumber) {
        $gradeitem = $this->get_grade_item($courseid, $idnumber);
        $this->assertEmpty($gradeitem, 'The grade item of the course ' . $courseid . ' identified by ' . $idnumber .
                ' should not be created. It is.');
    }

    /**
     * Assert that a specified grade is created.
     *
     * @param int $courseid The course id containing the grade.
     * @param string $idnumber the id number who identified the grade item.
     * @param int $userid The user id of the user graded
     * @return grade_grade|null The grade object or null if not found.
     */
    public function assert_user_is_graded($courseid, $idnumber, $userid) {
        $grade = $this->get_grade($courseid, $idnumber, $userid);
        $this->assertNotEmpty($grade, 'The grade of the user ' . $userid . ' for the course ' . $courseid . ' in the grade item ' .
                $idnumber . ' should be created. It is not.');
        return $grade;
    }

    /**
     * Assert that a specified grade is not created.
     *
     * @param int $courseid The course id containing the grade.
     * @param string $idnumber the id number who identified the grade item.
     * @param int $userid The user id of the user graded
     */
    public function assert_user_is_not_graded($courseid, $idnumber, $userid) {
        $grade = $this->get_grade($courseid, $idnumber, $userid);
        $this->assertEmpty($grade, 'The grade of the user ' . $userid . ' for the course ' . $courseid . ' in the grade item ' .
                $idnumber . ' should not be created. It is.');
    }

    /**
     * Test for the create_grade_items method.
     */
    public function test_create_grade_items() {

        $this->init_tool_dbgradeimport();

        $this->resetAfterTest(true);
        $this->preventResetByRollback();

        $plugin = new tool_dbgradeimport_importer();

        // Set differents grade items values for the test.
        $this->insert_remote_grade_item();
        $this->insert_remote_grade_item(2, 1);
        $this->insert_remote_grade_item(2, 2, '30.5867872');
        $this->insert_remote_grade_item(2, 3, 'Bad grade max');
        $this->insert_remote_grade_item(2, 4, '-');
        $this->insert_remote_grade_item(3);

        // Hide the course 3.
        course_change_visibility(self::$courses[3]->id, false);

        $plugin->create_grade_items();

        // Check that a standard grade item is created.
        if ($gradeitem1 = $this->assert_grade_item_is_created(self::$courses[1]->id, 'gradeitem1')) {
            $this->assertEquals(self::$courses[1]->id, $gradeitem1->courseid);
            $this->assertEquals('gradeitem1', $gradeitem1->idnumber);
            $this->assertEquals(100, $gradeitem1->grademax);
        }

        // Check that a course can have more that one grade item with correct values.
        if ($gradeitem2 = $this->assert_grade_item_is_created(self::$courses[2]->id, 'gradeitem2')) {
            $this->assertEquals(self::$courses[2]->id, $gradeitem2->courseid);
            $this->assertEquals('gradeitem2', $gradeitem2->idnumber);
            $this->assertEquals(30.58679, $gradeitem2->grademax);
        }

        // Make sure that this grade item is not created without error because it contains a bad max grade.
        $this->assert_grade_item_is_not_created(self::$courses[2]->id, 'gradeitem3');

        // Make sure that this grade item is not created without error because it contains an empty bad grade.
        $this->assert_grade_item_is_not_created(self::$courses[2]->id, 'gradeitem4');

        // Make sure that the course of 3 is not created because it is hidden.
        $this->assert_grade_item_is_not_created(self::$courses[3]->id, 'gradeitem1');

        // Allow the creation of grade items in hidden courses and call the method again.
        $plugin->set_config('ignorehiddencourses', 0);
        $plugin->create_grade_items();

        // The grade item should now be here.
        $this->assert_grade_item_is_created(self::$courses[3]->id, 'gradeitem1');

        $this->reset_tool_dbgradeimport();
        $this->cleanup_tool_dbgradeimport();
    }

    /**
     * Test for the sync_grades method.
     */
    public function test_sync_grades() {

        $this->init_tool_dbgradeimport();

        $this->resetAfterTest(false);
        $this->preventResetByRollback();

        $plugin = new tool_dbgradeimport_importer(true);

        $this->insert_remote_grade();
        $plugin->sync_grades();

        // Be sure to do note create grades if the grade item is not present.
        $this->assert_user_is_not_graded(self::$courses[1]->id, 'gradeitem1', self::$users[1]->id);

        // We create the grade item.
        $this->insert_remote_grade_item();
        $plugin->create_grade_items();
        $plugin->sync_grades();

        // The user should now be created.
        if ($grade1 = $this->assert_user_is_graded(self::$courses[1]->id, 'gradeitem1', self::$users[1]->id)) {
            $this->assertEquals('gradeitem1', $grade1->load_grade_item()->idnumber);
            $this->assertEquals(self::$users[1]->id, $grade1->userid);
            $this->assertEquals(80, $grade1->finalgrade);
        }

        // Grade a second user.
        $this->insert_remote_grade(1, 1, 2, '71.756796');
        $plugin->sync_grades();

        // Check if all the infos are correct.
        if ($grade2 = $this->assert_user_is_graded(self::$courses[1]->id, 'gradeitem1', self::$users[2]->id)) {
            $this->assertEquals('gradeitem1', $grade2->load_grade_item()->idnumber);
            $this->assertEquals(self::$users[2]->id, $grade2->userid);
            $this->assertEquals(71.7568, $grade2->finalgrade);
        }

        // Update a grade value in the remote database.
        $this->update_remote_grade(1, 1, 2, '95');
        $plugin->sync_grades();

        // Check if all the infos are correct.
        if ($grade3 = $this->assert_user_is_graded(self::$courses[1]->id, 'gradeitem1', self::$users[2]->id)) {
            $this->assertEquals('gradeitem1', $grade3->load_grade_item()->idnumber);
            $this->assertEquals(self::$users[2]->id, $grade3->userid);
            $this->assertEquals(95, $grade3->finalgrade);
        }

        // Update the grade with an empty value.
        $this->update_remote_grade(1, 1, 2, '');
        $plugin->sync_grades();

        // The grade value should remain to 95.
        if ($grade4 = $this->assert_user_is_graded(self::$courses[1]->id, 'gradeitem1', self::$users[2]->id)) {
            $this->assertEquals('gradeitem1', $grade4->load_grade_item()->idnumber);
            $this->assertEquals(self::$users[2]->id, $grade4->userid);
            $this->assertEquals(95, $grade4->finalgrade);
        }

        // Hide the course 3 and add a grade for this course.
        course_change_visibility(self::$courses[3]->id, false);
        $this->insert_remote_grade_item(3, 1);
        $this->insert_remote_grade(3, 1, 1, '58');
        $plugin->create_grade_items();
        $plugin->sync_grades();

        // Be sure to do note create grades if the course is hidden.
        $this->assert_user_is_not_graded(self::$courses[3]->id, 'gradeitem1', self::$users[1]->id);

        // Show the course 3.
        course_change_visibility(self::$courses[3]->id, true);
        $plugin->create_grade_items();
        $plugin->sync_grades();

        // The grade value should now be here.
        if ($grade5 = $this->assert_user_is_graded(self::$courses[3]->id, 'gradeitem1', self::$users[1]->id)) {
            $gradeitem5 = $grade5->load_grade_item();
            $this->assertEquals('gradeitem1', $gradeitem5->idnumber);
            $this->assertEquals(self::$users[1]->id, $grade5->userid);
            $this->assertEquals(58, $grade5->finalgrade);
        }

        // Lock the grade item and update the grade.
        $gradeitem5->regrading_finished();
        $gradeitem5->set_locked(1);
        $this->update_remote_grade(3, 1, 1, '32.56789');
        $plugin->sync_grades();

        // The grade value should remain to 58.
        if ($grade6 = $this->assert_user_is_graded(self::$courses[3]->id, 'gradeitem1', self::$users[1]->id)) {
            $gradeitem6 = $grade6->load_grade_item();
            $this->assertEquals('gradeitem1', $gradeitem6->idnumber);
            $this->assertEquals(self::$users[1]->id, $grade6->userid);
            $this->assertEquals(58, $grade6->finalgrade);
        }

        // Unlock the grade item.
        $gradeitem6->set_locked(0);
        $plugin->sync_grades();

        // The grade value should now be 32.56789.
        if ($grade7 = $this->assert_user_is_graded(self::$courses[3]->id, 'gradeitem1', self::$users[1]->id)) {
            $gradeitem7 = $grade7->load_grade_item();
            $this->assertEquals('gradeitem1', $gradeitem7->idnumber);
            $this->assertEquals(self::$users[1]->id, $grade7->userid);
            $this->assertEquals(32.56789, $grade7->finalgrade);
        }

        // Lock the grade and update it.
        $gradeitem7->regrading_finished();
        $grade7->set_locked(1);
        $this->update_remote_grade(3, 1, 1, '10');
        $plugin->sync_grades();

        // The grade value should remain to 32.56789.
        if ($grade7 = $this->assert_user_is_graded(self::$courses[3]->id, 'gradeitem1', self::$users[1]->id)) {
            $gradeitem7 = $grade6->load_grade_item();
            $this->assertEquals('gradeitem1', $gradeitem7->idnumber);
            $this->assertEquals(self::$users[1]->id, $grade7->userid);
            $this->assertEquals(32.56789, $grade7->finalgrade);
        }

        // Unlock the grade.
        $grade6->set_locked(0);
        $plugin->sync_grades();

        // The grade value now be 10.
        if ($grade7 = $this->assert_user_is_graded(self::$courses[3]->id, 'gradeitem1', self::$users[1]->id)) {
            $gradeitem7 = $grade7->load_grade_item();
            $this->assertEquals('gradeitem1', $gradeitem7->idnumber);
            $this->assertEquals(self::$users[1]->id, $grade7->userid);
            $this->assertEquals(10, $grade7->finalgrade);
        }

        $this->reset_tool_dbgradeimport();

        // Final cleanup - remove extra tables, fixtures and caches.
        $this->cleanup_tool_dbgradeimport();
    }
}
