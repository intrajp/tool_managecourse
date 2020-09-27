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
 * Implements the plugin rendering page 
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Implements the plugin renderer
 *
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class intrajp_simple {

    private function get_course_forum_sql($courseid) {

        $VIEW_COLUMNS = "f.id as forumid, f.name as forumname";
        $FROM_TABLES = "FROM {forum} f";
        $WHERE = "WHERE f.course = $courseid";
        $CONDITION1 = "type='news'";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY f.id $ASC";
        $LIMIT = "limit 5";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE} AND ${CONDITION1} ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_basic_sql($courseid) {

        $VIEW_COLUMNS = "co.fullname AS fullname, co.shortname AS shortname, co.idnumber AS idnumber, coc.name AS categoryname";
        $FROM_TABLES = "FROM {course} co, {course_categories} coc";
        $WHERE = "WHERE co.id = $courseid";
        $CONDITION1 = "co.category = coc.id";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE} AND
                ${CONDITION1} ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_groupings_sql($courseid) {

        $VIEW_COLUMNS = "count(courseid) as courseid_count";
        $FROM_TABLES = "FROM {groupings}";
        $WHERE = "WHERE courseid = $courseid";
        $CONDITION1 = "";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE}
                ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_groups_sql($courseid) {

        $VIEW_COLUMNS = "count(courseid) as courseid_count";
        $FROM_TABLES = "FROM {groups}";
        $WHERE = "WHERE courseid = $courseid";
        $CONDITION1 = "";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE}
                ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_role_assignments_sql($courseid) {

        $VIEW_COLUMNS = "ra.id as id, c.id as contextid, r.id as roleid, r.shortname";
        $FROM_TABLES = "FROM {role} r, {role_assignments} ra, {context} c, {course} co";
        $WHERE = "WHERE co.id = $courseid";
        $CONDITION1 = "ra.roleid = r.id";
        $CONDITION2 = "ra.contextid = c.id";
        $CONDITION3 = "co.id = c.instanceid";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY id, roleid";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE} AND
                ${CONDITION1} AND ${CONDITION2} AND ${CONDITION3} ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_enrol_methods_sql($courseid) {

        $VIEW_COLUMNS = "id, enrol";
        $FROM_TABLES = "FROM {enrol}";
        $WHERE = "WHERE courseid = $courseid";
        $CONDITION1 = "status = 0";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE} AND
                ${CONDITION1} ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_format_sql($courseid) {

        $VIEW_COLUMNS = "co.format";
        $FROM_TABLES = "FROM {course} co";
        $WHERE = "WHERE co.id = $courseid";
        $CONDITION1 = "";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE}
                ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_sections_sql($courseid) {

        $VIEW_COLUMNS = "cos.id, cos.section, cos.name";
        $FROM_TABLES = "FROM {course_sections} cos";
        $WHERE = "WHERE cos.course = $courseid";
        $CONDITION1 = "";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY cos.id";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE}
                ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_weeks_sql($courseid, $cnt) {

        global $CFG;
        $one_week = (($cnt - 1 ) * 7) * 86400;
        $VIEW_COLUMNS="co.startdate + $one_week AS startdate";
        $FROM_TABLES = "FROM {course} co";
        $WHERE = "WHERE co.id = $courseid";
        $CONDITION1 = "";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE}
                ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

    private function get_course_module_names_sql($courseid) {

        $VIEW_COLUMNS = "DISTINCT cm.id AS id, m.name AS modulename";
        $FROM_TABLES = "FROM {course} c, {course_modules} cm, {modules} m";
        $WHERE = "WHERE c.id = $courseid";
        $CONDITION1 = "c.id = cm.course";
        $CONDITION2 = "cm.module = m.id";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "";
        $LIMIT = "";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} ${WHERE} AND
                ${CONDITION1} AND ${CONDITION2} ${ORDER_BY} ${LIMIT}";

        return $sql;

    }

//// methods

    public function get_course_basic($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_basic_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $fullname = $c->fullname;
            $shortname = $c->shortname;
            $idnumber = $c->idnumber;
            $categoryname = $c->categoryname;
            $row += array(
                "Full name" => "$fullname",
                "Short name" => "$shortname",
                "ID number" => "$idnumber",
                "Category" => "$categoryname",
            );
        }
        $rs->close();

        return $row;

    }

    public function get_course_groupings($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_groupings_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $courseid_count = $c->courseid_count;
            $row += array(
                "Groupings" => "$courseid_count",
            );
        }
        $rs->close();

        return $row;

    }

    public function get_course_groups($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_groups_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $courseid_count = $c->courseid_count;
            $row += array(
                "Groups" => "$courseid_count",
            );
        }
        $rs->close();

        return $row;

    }

    public function get_course_role_assignments($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_role_assignments_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $id = $c->id;
            $shortname = $c->shortname;
            $row += array("$id"=>"$shortname");
        }
        $rs->close();

        return $row;

    }

    public function get_course_enrol_methods($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_enrol_methods_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $id = $c->id;
            $enrol = $c->enrol;
            $row += array("$id"=>"$enrol");
        }
        $rs->close();

        return $row;

    }

    public function get_course_format($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_format_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $format = $c->format;
            $row += array("format"=>"$format");
        }
        $rs->close();

        return $row;

    }

    public function get_course_sections($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_sections_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $row[] = array("id"=>$c->id,"section"=>$c->section,"name"=>$c->name);
        }
        $rs->close();

        return $row;

    }

    public function get_course_weeks($courseid, $cnt) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_weeks_sql($courseid, $cnt), array());

        $startdate = NULL;

        $row = array();
        foreach ($rs as $c) {
            $startdate = $c->startdate;
            $weekend = $startdate + (86400 * 6);
            $row += array("weekstart"=>"$startdate", "weekend"=>"$weekend");
        }
        $rs->close();

        return $row;

    }

    public function get_course_module_names($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_module_names_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $id = $c->id;
            $modulename = $c->modulename;
            $row += array("$id"=>"$modulename");
        }
        $rs->close();

        return $row;

    }

}
