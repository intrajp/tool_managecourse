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
 * Implements the plugin PDF creation 
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
class tool_managecourse_pdf {

    public function __construct() {
    }

//// sqls

    private function grade_sql($userid, $categoryid, $courseid) {

        $VIEW_COLUMNS="m.id as user_enrolments_id, k.name AS categoryname, u.id AS userid, u.firstname,
                       u.lastname, e.id enrolid, r.shortname AS rolename, c.fullname,
		       FROM_UNIXTIME(c.startdate, '%Y/%m/%d') AS startdate, FROM_UNIXTIME(c.enddate, '%Y/%m/%d') AS enddate,
                       g.finalgrade, g.rawgrademax";
        $FROM_TABLES="FROM {user} u, {user_enrolments} m, {enrol} e, {course} c, {role_assignments} a,
                      {role} r , {grade_items} i, {grade_grades} g, {course_categories} k";
        $WHERE="";
        $SUB_QUERY="";
        $BIND1="u.id = m.userid";
        $BIND2="m.enrolid = e.id";
        $BIND3="c.id = e.courseid";
        $BIND4="a.userid = u.id";
        $BIND5="a.roleid = r.id";
        $BIND6="i.courseid = c.id";
        $BIND7="g.itemid = i.id";
        $BIND8="g.userid = u.id";
        $BIND9 = "c.category = k.id";
        $CONDITION1 = NULL;
        if ($userid) {
            $CONDITION1 = "AND u.id = $userid";
        }
        $CONDITION2 = NULL;
        if ($categoryid) {
            $CONDITION2 = "AND k.id = $categoryid";
        }
        $CONDITION3 = NULL;
        if ($courseid) {
            $CONDITION3 = "AND c.id = $courseid";
        }
        $GROUP_BY="GROUP BY m.id,u.id";
        $ORDER="ORDER BY u.id, c.startdate, m.id, e.id, c.id";
        $DESC="DESC";
        $ASC="ASC";
        $FORMAT_NORMAL=";";
        $FORMAT_ROW="\G";
        $FORMAT="${FORMAT_NORMAL}";
        $OUTPUTFILE="";

        $sql="SELECT ${VIEW_COLUMNS} ${FROM_TABLES} WHERE ${BIND1} AND ${BIND2}
                  AND ${BIND3} AND ${BIND4} AND ${BIND5} AND ${BIND6}
		  AND ${BIND7} AND ${BIND8} AND ${BIND9} ${CONDITION1} ${CONDITION2}
                  ${CONDITION3} ${GROUP_BY} ${ORDER}";

        return $sql;
    }

//// methods

    public function show_grade_count($page, $perpage, $userid, $categoryid, $courseid) {

        global $DB;

        $records = $DB->get_records_sql($this->grade_sql($userid, $categoryid, $courseid), array());
        $counts = count($records);

	return $counts;

    }

    public function render_grade_pdf($userid, $categoryid, $courseid) {

        global $CFG;
        global $DB;
        $data = array();

        $rs = $DB->get_recordset_sql($this->grade_sql($userid, $categoryid, $courseid), array());
        foreach ($rs as $c) {
            $fullname = $c->fullname;
            $firstname = $c->firstname;
            $lastname = $c->lastname;
            $startdate = $c->startdate;
            $enddate = $c->enddate;
            $finalgrade = $c->finalgrade;
            $rawgrademax = $c->rawgrademax;

            $data[] = $fullname."%".$firstname."%".$lastname."%".$startdate."%".
                          $enddate."%".$finalgrade."%".$rawgrademax;
        }
        $rs->close();

        return $data;

    }

}
