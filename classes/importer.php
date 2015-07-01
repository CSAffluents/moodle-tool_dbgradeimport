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
 * Database grade import plugin.
 *
 * This plugin create grade items and synchronise user grades from external database table.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/import/lib.php');

/**
 * Database grade import importer class.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_dbgradeimport_importer extends tool_dbgradeimport_base {

    /**
     * @var tool_dbgradeimport_database The database object used for creating grades.
     */
    private $database = null;

    /**
     * @var string The last skipped course identifier.
     */
    private $lastskippedcourse = null;

    /**
     * @var stdClass The last skipped gradeitem. We store the course id and the grade item idnumber.
     */
    private $lastskippedgradeitem = null;

    /**
     * The constructor of the class.
     *
     * @param boolean $verbose If we output information when calling the methods.
     */
    public function __construct($verbose = false) {
        $this->database = new tool_dbgradeimport_database();

        if (!empty($verbose)) {
            $this->trace = new text_progress_trace();
        } else {
            $this->trace = new null_progress_trace();
        }
    }

    /**
     * Create all grade items from external database.
     *
     * @return int 0 means success, 1 db connect failure, 2 db read failure.
     */
    public function create_grade_items() {

        $trace = $this->trace;
        $db = $this->database;

        $this->reset_skipped_elements();

        $trace->output('Beginning: Grade items creation');

        $table = $this->get_config('remotegradeitemstable');

        // Lowercased versions - necessary because we normalize the resultset with array_change_key_case().
        $coursefield = $this->get_normalized_field_name('remotegradeitemscoursefield');
        $idnumberfield = $this->get_normalized_field_name('remotegradeitemsfield');
        $namefield = $this->get_normalized_field_name('remotegradeitemsnamefield');
        $grademaxfield = $this->get_normalized_field_name('remotemaxgradefield');

        $realcoursefield = $this->get_config('remotegradeitemscoursefield');

        // If can not connect to database.
        if (!$extdb = $db->init()) {
            $trace->output('Error: Can not connect to database.');
            $trace->finished();
            return 1;
        }

        // Read remote grade items and create instances.
        $sql = $db->get_sql($table, array(), array(), true, $realcoursefield . " ASC");

        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = $this->get_normalized_fields($fields);

                    $courseidentifier = $fields[$coursefield];
                    $idnumber = $fields[$idnumberfield];
                    $gradeitemname = $fields[$namefield];
                    $grademax = $fields[$grademaxfield];

                    // If the course is valid.
                    if (!$course = $this->get_valid_course($coursefield, $courseidentifier)) {
                        continue;
                    }

                    // Missing grade item idnumber.
                    if ($this->field_value_is_empty($idnumberfield, $idnumber)) {
                        continue;
                    }

                    // Missing grade item name.
                    if ($this->field_value_is_empty($namefield, $gradeitemname)) {
                        continue;
                    }

                    // Missing grade max.
                    if ($this->field_value_is_empty($grademaxfield, $grademax)) {
                        continue;
                    }

                    // If the max grade value is valid.
                    if (!$value = $this->get_valid_grade_value($grademax, $course->id, $idnumber)) {
                        continue;
                    }

                    // Finally, we create the grade item.
                    $this->create_grade_item($course->id, $idnumber, $gradeitemname, $value, $this->trace);

                }
            }
            // Close db connection.
            $rs->Close();
            $extdb->Close();
            $trace->output('End: Grade items creation finished.');
            $trace->finished();
            return 0;
        } else {
            // Bad luck, something is wrong with the db connection.
            $extdb->Close();
            $trace->output('Error: The database cannot be read or the table containing the grade items is not specified.');
            $trace->finished();
            return 2;
        }
    }

    /**
     * Performs a full sync of student grades from external database.
     *
     * @return int 0 means success, 1 db connect failure, 4 db read failure.
     */
    public function sync_grades() {
        global $DB, $USER;

        $trace = $this->trace;
        $db = $this->database;

        $this->reset_skipped_elements();

        $trace->output('Beginning: User grades synchronisation');

        $table = $this->get_config('remotegradestable');

        // Lowercased versions - necessary because we normalize the resultset with array_change_key_case().
        $coursefield = $this->get_normalized_field_name('remotegradescoursefield');
        $idnumberfield = $this->get_normalized_field_name('remotegradesfield');
        $userfield = $this->get_normalized_field_name('remotegradesuserfield');
        $valuefield = $this->get_normalized_field_name('remotegradesvaluefield');

        $realidnumberfield = $this->get_config('remotegradesfield');
        $realcoursefield = $this->get_config('remotegradescoursefield');

        // If can not connect to database.
        if (!$extdb = $db->init()) {
            $trace->output('Error: Can not connect to database.');
            $trace->finished();
            return 1;
        }

        // Read remote user grades and sync it. Order by course and grade items.
        $sql = $db->get_sql($table, array(), array(), true, $realcoursefield . ","  . $realidnumberfield. " ASC");

        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {

                $oldcourseid = null;
                $importcode = get_new_importcode();

                while ($fields = $rs->FetchRow()) {
                    $fields = $this->get_normalized_fields($fields);

                    $courseidentifier = $fields[$coursefield];
                    $idnumber = $fields[$idnumberfield];
                    $useridentifier = $fields[$userfield];
                    $gradevalue = $fields[$valuefield];

                    // If the course is valid.
                    if (!$course = $this->get_valid_course($coursefield, $courseidentifier)) {
                        continue;
                    }

                    // If the gradeitem is valid.
                    if (!$gradeitem = $this->get_valid_gradeitem($course->id, $idnumberfield, $idnumber)) {
                        continue;
                    }

                    // If the user is valid.
                    if (!$user = $this->get_valid_user($userfield, $useridentifier)) {
                        continue;
                    }

                    // Missing grade value.
                    if ($this->field_value_is_empty($valuefield, $gradevalue)) {
                        continue;
                    }

                    // If the grade value is valid.
                    if (!$validvalue = $this->get_valid_grade_value($gradevalue, $course->id, $idnumber, $user->id)) {
                        continue;
                    }

                    // Individual grade locked.
                    $grade = new grade_grade(array('itemid' => $gradeitem->id, 'userid' => $user->id));
                    if ($grade->is_locked()) {
                        $trace->output('Skip: Course ' . $course->id . ' grade item ' .
                                $gradeitem->id . ' user ' . $user->id . ' grade value is locked.');
                        continue;
                    }

                    // Finally, we grade this student.
                    $newgrade = new stdClass();
                    $newgrade->itemid = $gradeitem->id;
                    $newgrade->finalgrade = $validvalue;
                    $newgrade->importcode = $importcode;
                    $newgrade->userid     = $user->id;
                    $newgrade->importer   = $USER->id;
                    $DB->insert_record('grade_import_values', $newgrade);
                    $trace->output('Success: Course ' . $course->id . ' grade item ' . $gradeitem->id . ' user ' . $user->id .
                            ' grade committed with value ' . $validvalue . '.');

                    grade_import_commit($course->id, $importcode, false, false);

                    // If the last course is not the same that this one.
                    if ($course->id != $oldcourseid) {
                        $oldcourseid = $course->id;
                        $importcode = get_new_importcode();
                    }
                }
            }
            grade_import_commit($oldcourseid, $importcode, false, false);
            // Close db connection.
            $rs->Close();
            $extdb->Close();
            $trace->output('End: User grades synchronisation finished.');
            $trace->finished();
            return 0;
        } else {
            // Bad luck, something is wrong with the db connection.
            $extdb->Close();
            $trace->output('Error: The database cannot be read or the table containing the user grades is not specified.');
            $trace->finished();
            return 4;
        }
    }

    /**
     * Test plugin settings, print info to output.
     */
    public function test_settings() {
        global $CFG, $OUTPUT;

        raise_memory_limit(MEMORY_HUGE);

        $gradeitemstable = $this->get_config('remotegradeitemstable');
        $gradestable = $this->get_config('remotegradestable');

        $gradeitems = $this->get_string('gradeitems');
        $usergrades = $this->get_string('usergrades');

        $db = $this->database;

        if (empty($gradeitemstable)) {
            echo $OUTPUT->notification($this->get_string('externaltablenotspecified', $gradeitems), 'notifyproblem');
        }

        if (empty($gradestable)) {
            echo $OUTPUT->notification($this->get_string('externaltablenotspecified', $usergrades), 'notifyproblem');
        }

        if (empty($gradestable) and empty($gradeitemstable)) {
            return;
        }

        $olddebug = $CFG->debug;
        $olddisplay = ini_get('display_errors');
        ini_set('display_errors', '1');
        $CFG->debug = DEBUG_DEVELOPER;
        $olddebugdb = $this->get_config('debugdb');
        $this->set_config('debugdb', 1);
        error_reporting($CFG->debug);

        $adodb = $db->init();

        if (!$adodb or !$adodb->IsConnected()) {
            $this->set_config('debugdb', $olddebugdb);
            $CFG->debug = $olddebug;
            ini_set('display_errors', $olddisplay);
            error_reporting($CFG->debug);
            ob_end_flush();

            echo $OUTPUT->notification($this->get_string('cannotconnectdatabase'), 'notifyproblem');
            return;
        }

        if (!empty($gradeitemstable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $gradeitemstable");
            if (!$rs) {
                echo $OUTPUT->notification($this->get_string('cannotreadexternaltable', $gradeitems),
                        'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification($this->get_string('externaltableempty', $gradeitems), 'notifyproblem');
                $rs->Close();

            } else {
                $fieldsobj = $rs->FetchObj();
                $columns = array_keys((array)$fieldsobj);

                echo $OUTPUT->notification($this->get_string('externaltablecontains', $gradeitems) . '<br />' .
                        implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        if (!empty($gradestable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $gradestable");
            if (!$rs) {
                echo $OUTPUT->notification($this->get_string('cannotreadexternaltable', $usergrades),
                        'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification($this->get_string('externaltableempty', $usergrades), 'notifyproblem');
                $rs->Close();

            } else {
                $fieldsobj = $rs->FetchObj();
                $columns = array_keys((array)$fieldsobj);

                echo $OUTPUT->notification($this->get_string('externaltablecontains', $usergrades) . '<br />' .
                        implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        $adodb->Close();

        $this->set_config('debugdb', $olddebugdb);
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
    }

    /**
     * Get a valid course based on an unique identifier.
     *
     * @param string $field The field used to store the course identifier in external database.
     * @param string $identifier The course identifier.
     * @return stdClass | boolean The course object or false if not valid.
     */
    private function get_valid_course($field, $identifier) {
        global $DB;

        // Check if the course has been already be skipped.
        if ($this->course_is_skipped($identifier)) {
            return false;
        }

        // Missing course info.
        if ($this->field_value_is_empty($field, $identifier)) {
            $this->mark_course_as_skipped($identifier, 'is empty');
            return false;
        }

        // The course do not exist.
        $localcoursefield = $this->get_config('localcoursefield');
        if (!$course = $DB->get_record('course', array($localcoursefield => $identifier), 'id,visible')) {
            $this->mark_course_as_skipped($identifier, 'do not exist');
            return false;
        }

        // The course is not visible and we want to ignore hidden course.
        $ignorehidden = $this->get_config('ignorehiddencourses');
        if (!$course->visible && $ignorehidden) {
            $this->mark_course_as_skipped($identifier, 'is hidden');
            return false;
        }
        return $course;
    }

    /**
     * Get a valid grade item based on a unique idnumber.
     *
     * @param int $courseid The course id.
     * @param string $fieldname The name of the field used to store the grade item idnumber in external database.
     * @param string $idnumber The course identifier.
     * @return stdClass|boolean The course object or false if not valid.
     */
    private function get_valid_gradeitem($courseid, $fieldname, $idnumber) {

        // Check if the grade item has been already be skipped.
        if ($this->gradeitem_is_skipped($courseid, $idnumber)) {
            return false;
        }

        // Missing grade item info.
        if ($this->field_value_is_empty($fieldname, $idnumber)) {
            $this->mark_gradeitem_as_skipped($courseid, $idnumber, 'is empty');
            return false;
        }

        $params = new stdClass();
        $params->courseid = $courseid;
        $params->idnumber = $idnumber;

        // The grade item do not exist in the course.
        if (!$gradeitem = grade_item::fetch($params)) {
            $this->mark_gradeitem_as_skipped($courseid, $idnumber, 'do not exist');
            return false;
        }

        // The grade item is locked.
        if ($gradeitem->is_locked()) {
            $this->mark_gradeitem_as_skipped($courseid, $idnumber, 'is locked');
            return false;
        }

        // The grade item grade type is invalid.
        if ($gradeitem->gradetype != GRADE_TYPE_VALUE) {
            $this->mark_gradeitem_as_skipped($courseid, $idnumber, 'grade type is invalid');
            return false;
        }

        return $gradeitem;
    }

    /**
     * Get a valid user based on an unique identifier.
     *
     * @param string $fieldname The name of the field used to store the grade item idnumber in external database.
     * @param string $identifier The user identifier.
     * @return stdClass|boolean The user object or false if not valid.
     */
    private function get_valid_user($fieldname, $identifier) {
        global $DB;

        // Missing course info.
        if ($this->field_value_is_empty($fieldname, $identifier)) {
            return false;
        }

        // The user do not exist.
        $localuserfield = $this->get_config('localuserfield');
        if (!$user = $DB->get_record('user', array($localuserfield => $identifier), 'id')) {
            $this->trace->output('Skip: User ' . $identifier . ' do not exist.');
            return false;
        }

        return $user;
    }

    /**
     * Get a valid grade value
     *
     * @param string $value The value to validate.
     * @param int $courseid The course id of the grade element.
     * @param string $idnumber The grade item idnumber of the grade element.
     * @param int $userid The user id of the grade element.
     * @return float|boolean $validvalue A valid grade value or false if invalid.
     */
    private function get_valid_grade_value($value, $courseid, $idnumber, $userid = null) {
        $validvalue = unformat_float($value, true);
        if ($validvalue !== false) {
            $value = $validvalue;
        } else {
            $message = 'Skip: Grade value ' . $value . ' for course ' . $courseid . ' grade item ' . $idnumber;
            if (isset($userid)) {
                $message .= ' user ' . $userid;
            }
            $this->trace->output( $message . ' is not valid.');
            return false;
        }
        return $validvalue;
    }

    /**
     * Check if a value is empty for a specific field in the external database.
     *
     * @param string $fieldname The name of the field
     * @param string $value The value to check.
     * @return boolean If the value is empty or not.
     */
    private function field_value_is_empty($fieldname, $value) {
        if (empty($value) || $value == '-') {
            $this->trace->output('Skip: The value of the field named "' . $fieldname . '" is empty.');
            return true;
        }
        return false;
    }

    /**
     * Check if course is the same that the last skipped one.
     * Note: The courses must be called in order to work.
     *
     * @param string $identifier The identifier of the course.
     * @return boolean If the course can be skipped or not.
     */
    private function course_is_skipped($identifier) {
        if ($this->lastskippedcourse == $identifier) {
            $this->trace->output('Skip: Course with identifier ' . $identifier . '.');
            return true;
        }
        return false;
    }

    /**
     * Check if grade item is the same that the last skipped one.
     * Note: The grade items must be called in order to work.
     *
     * @param int $courseid The id of the course containing the grade item.
     * @param string $idnumber The idnumber of the grade item in the course.
     * @return boolean If the grade item can be skipped or not.
     */
    private function gradeitem_is_skipped($courseid, $idnumber) {
        $identifier = new stdClass();
        $identifier->courseid = $courseid;
        $identifier->idnumber = $idnumber;
        if ($this->lastskippedgradeitem == $identifier) {
            $this->trace->output('Skip: Grade item with the idnumber ' . $idnumber . ' in course ' . $courseid . '.');
            return true;
        }
        return false;
    }

    /**
     * Mark a course as skipped.
     *
     * @param string $identifier The identifier of the course.
     * @param string $message A message prefix to show in the output as the reason of the skip.
     */
    private function mark_course_as_skipped($identifier, $message) {
        $this->lastskippedcourse = $identifier;
        if (isset($message)) {
            $this->trace->output('Skip: Course identified by ' . $identifier . ' ' . $message . '.');
        }
    }

    /**
     * Mark a grade item as skipped.
     *
     * @param int $courseid The id of the course containing the grade item.
     * @param string $idnumber The idnumber of the grade item in the course.
     * @param string $message A message prefix to show in the output as the reason of the skip.
     */
    private function mark_gradeitem_as_skipped($courseid, $idnumber, $message = null) {
        $this->lastskippedgradeitem = new stdClass();
        $this->lastskippedgradeitem->courseid = $courseid;
        $this->lastskippedgradeitem->idnumber = $idnumber;
        if (isset($message)) {
            $this->trace->output('Skip: Grade item with the idnumber ' . $idnumber . ' in course ' . $courseid . ' ' . $message .
                    '.');
        }
    }

    /**
     * Reset the skipped elements flags.
     */
    private function reset_skipped_elements() {
        $this->lastskippedgradeitem = null;
        $this->lastskippedcourse = null;
    }

    /**
     * Normalize the name of a field.
     *
     * @param string $fieldname The field name.
     * @return string The normalized field name.
     */
    private function get_normalized_field_name($fieldname) {
        $field = trim($this->get_config($fieldname));
        return strtolower($field);
    }

    /**
     * Normalize a list of fields.
     *
     * @param array $fields The field to be normalized.
     * @return array The normalized fields.
     */
    private function get_normalized_fields($fields) {
        $fields = array_change_key_case($fields, CASE_LOWER);
        return $this->database->decode($fields);
    }

    /**
     * Create a grade item.
     * 
     * @param int $courseid The id of the course containing the grade item.
     * @param string $idnumber The idnumber of the grade item in the course.
     * @param string $name The name of the grade item.
     * @param float $grademax The maximum grade of the grade item.
     */
    private function create_grade_item($courseid, $idnumber, $name, $grademax) {

        $params = new stdClass();
        $params->courseid = $courseid;
        $params->idnumber = $idnumber;

        // Do not create if the grade item already exist.
        if (grade_item::fetch($params)) {
            $this->trace->output('Skip: Grade item ' . $idnumber . ' already exist.');
            return;
        }

        // We complete the grade items params.
        $params->itemname = $name;
        $params->grademax = $grademax;
        $params->itemtype = 'manual';
        $params->hidden = true;

        // We create and insert the grade item in the course.
        $gradeitem = new grade_item($params, false);
        $gradeitem->insert('import');
        $this->trace->output('Success: Grade item ' . $idnumber . ' created in course ' . $courseid . '.');
    }
}
