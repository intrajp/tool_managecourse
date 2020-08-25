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
 * Implements the plugin form 
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
 
class select_form extends moodleform {

    // properties
    private $userid;
    private $categoryid;
    private $courseid;

    // setter and getter
    public function set_userid($userid) {

        $this->userid = $userid;

    }

    public function set_categoryid($categoryid) {

        $this->categoryid = $categoryid;

    }

    public function set_courseid($courseid) {

        $this->courseid = $courseid;

    }

    public function get_userid() {

        return $this->userid;

    }

    public function get_categoryid() {

        return $this->categoryid;

    }

    public function get_courseid() {

        return $this->courseid;

    }

    // sqls

    private function get_users_sql() {

        $VIEW_COLUMNS = "u.id as userid, u.firstname, u.lastname";
        $FROM_TABLES = "FROM {user} u";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY u.id $ASC";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${ORDER_BY}";

        return $sql;

    }

    private function get_course_name_sql($courseid) {

        $sql = "SELECT id AS courseid, fullname FROM {course} WHERE id = $courseid";

        return $sql;

    }

    private function get_course_category_sql() {

        $sql = "WITH RECURSIVE category_path (id, parent, name, path) AS
                (SELECT id, parent, name, CAST(name AS TEXT) as path FROM {course_categories} WHERE parent=0 UNION ALL
                SELECT k.id, k.parent, k.name, CONCAT(cp.path, '>', k.name) FROM category_path AS cp
                JOIN {course_categories} AS k ON cp.id = k.parent) SELECT * FROM category_path ORDER BY path";

        return $sql;

    }

    private function get_courses_sql($categoryid) {

        $VIEW_COLUMNS = "c.id as courseid, c.fullname";
        $FROM_TABLES = "FROM {course} c";
        if ($categoryid != NULL) {
            $WHERE = "WHERE c.category = $categoryid";
        } else {
            $WHERE = "";
        }
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY c.id $ASC";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE} ${ORDER_BY}";

        return $sql;

    }

    private function is_course_exists_in_category_sql($categoryid, $courseid) {

        $VIEW_COLUMNS = "c.id as courseid, k.id as categoryid";
        $FROM_TABLES = "FROM {course} c, {course_categories} k";
        $BIND1 = "c.category = k.id";
        $BIND2 = "k.id = $categoryid";
        $BIND3 = "c.id = $courseid";
        $DESC = "DESC";
        $ASC = "ASC";

	$sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} WHERE ${BIND1} AND ${BIND2}
                AND ${BIND3}";

        return $sql;

    }

    // methods

    public function get_users_list() {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_users_sql(), array());

        $row = array();
        $row += array("-1"=>get_string('selectuser', 'tool_managecourse'));
        $row += array("0"=>get_string('allusers', 'tool_managecourse'));
        foreach ($rs as $c) {
            $userid = $c->userid;
            $firstname = $c->firstname;
            $lastname = $c->lastname;
            $row += array("$userid"=>"$firstname $lastname");
        }
        $rs->close();

        return $row;

    }

    public function get_course_category_list() {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_category_sql(), array());

        $row = array();
        $row += array("-1"=>get_string('selectcategories', 'tool_managecourse'));
        $row += array("0"=>get_string('allcategories', 'tool_managecourse'));
        foreach ($rs as $c) {
            $categoryid = $c->id;
            $parentid = $c->parent;
            $categoryname = $c->name;
            if ($parentid == 0) {
                $twoequals = get_string('twoequals', 'tool_managecourse');
                $categoryname = $twoequals.$categoryname.$twoequals;
            }
            $row += array("$categoryid"=>"$categoryname");
        }
        $rs->close();

        return $row;

    }

    public function get_courses_list($categoryid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_courses_sql($categoryid), array());

        $row = array();
        $row += array("-1"=>get_string('selectcourses', 'tool_managecourse'));
        $row += array("0"=>get_string('allcourses', 'tool_managecourse'));
        foreach ($rs as $c) {
            $courseid = $c->courseid;
            $fullname = $c->fullname;
            $row += array("$courseid"=>"$fullname");
        }
        $rs->close();

        return $row;

    }

    public function get_course_name($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_name_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $courseid = $c->courseid;
            $fullname = $c->fullname;
            $row += array("$courseid"=>"$fullname");
        }
        $rs->close();

        return $row;

    }

    public function is_course_exists_in_category($categoryid, $courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->is_course_exists_in_category_sql($categoryid, $courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $categoryid = $c->categoryid;
            $courseid = $c->courseid;
            $row += array("$categoryid"=>"$courseid");
        }
        $rs->close();

        return $row;

    }

    //Add elements to form
    public function definition() {

        global $CFG;
 
        $mform = $this->_form;
        $categoryid = 0;
        $options = $this->get_users_list();
        $options2 = $this->get_course_category_list();
        $options3 = $this->get_courses_list(NULL);
        $attributes = NULL;
        $mform->addElement('select', 'type', '', $options, $attributes);
        $mform->addElement('select', 'type2', '', $options2, $attributes);
        $mform->addElement('select', 'type3', '', $options3, $attributes);
        $this->add_action_buttons(true, get_string('selectuserorcourse', 'tool_managecourse'));
 
    }

    //Custom validation should be added here
    function validation($data, $files) {

        return array();

    }
}
