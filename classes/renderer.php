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
 * File containing the general information page.
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
class tool_managecourse_renderer extends plugin_renderer_base {

    public function get_course_count() {

        global $DB;
        $coursecount = $DB->count_records('course', array());

        return $coursecount;

    }

    public function get_course_count_time($time1, $time2) {

        global $DB;
	$coursecounttime = $DB->get_record_sql('SELECT count(id) as c from {course} WHERE timecreated <= ' . $time1 .
            ' AND timecreated >= ' . $time2, array());

        return $coursecounttime->c;

    }


    public function get_course_names() {

        global $DB;
	$names = $DB->get_records('course', array());

        return $names;

    }

    public function show_table() {

        global $CFG;
        $data = array();
        $table = new html_table();

        $table->head = [
            get_string('coursescount', 'tool_managecourse'),
        ];

        $row_top = new html_table_row(array(
            new html_table_cell("all"),
            new html_table_cell("hour"),
            new html_table_cell("day"),
            new html_table_cell("week"),
            new html_table_cell("month"),
        ));

        $time_now = time();
        $time_hour = $time_now - 3600;
        $time_day = $time_now - 86400;
        $time_week = $time_now - 604800;
        $time_month = $time_now - 18144000;
        $row = new html_table_row(array(
            new html_table_cell($this->get_course_count()),
            new html_table_cell($this->get_course_count_time($time_now, $time_hour)),
            new html_table_cell($this->get_course_count_time($time_now, $time_day)),
            new html_table_cell($this->get_course_count_time($time_now, $time_week)),
            new html_table_cell($this->get_course_count_time($time_now, $time_month)),
        ));

        $data[] = $row_top;
        $data[] = $row;
        $table->data = $data;
        $perpage = 1;
        return html_writer::table($table, array('sort' => 'location', 'dir' => 'ASC','perpage' => $perpage));

    }

    public function show_table2_count() {

        global $DB;
        $VIEW_COLUMNS = "c.id as courseid, c.fullname, c.timecreated, u.lastname, u.firstname, r.shortname as roleshortname";
        $FROM_TABLES = "FROM mdl_user_enrolments m, mdl_role_assignments a, mdl_user u, mdl_enrol e, mdl_course c, mdl_role r, mdl_course_categories k";
        $BIND1 = "m.enrolid = e.id";
        $BIND2 = "a.roleid = r.id";
        $BIND3 = "a.userid = u.id";
        $BIND4 = "m.userid = u.id";
        $BIND5 = "e.courseid = c.id";
        $BIND6 = "c.category = k.id";
        $GROUP_BY = "GROUP BY c.id,a.roleid";
        $DESC = "DESC";
        $ASC = "ASC";
        $sql = "select ${VIEW_COLUMNS} ${FROM_TABLES}  where ${BIND1} and ${BIND2} and ${BIND3} and ${BIND4} and ${BIND5} and (a.roleid <= 4) 
                ${GROUP_BY} order by c.timecreated $DESC, c.id $ASC";

        $records = $DB->get_records_sql($sql, array());
        $counts = count($records);
	return $counts;

    }

    public function show_table2($page, $perpage) {

        global $DB;
        $table = new html_table();
        $table->head = [
            get_string('categoryname', 'tool_managecourse'),
            get_string('fullname', 'tool_managecourse'),
            get_string('firstname', 'tool_managecourse'),
            get_string('lastname', 'tool_managecourse'),
            get_string('roleshortname', 'tool_managecourse'),
            get_string('timecreated', 'tool_managecourse'),
        ];
        $table->id = 'courses';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = array();

        $VIEW_COLUMNS = "distinct c.id as courseid, k.name as categoryname, c.fullname, c.timecreated, u.lastname, u.firstname, r.shortname as roleshortname";
        $FROM_TABLES = "FROM mdl_user_enrolments m, mdl_role_assignments a, mdl_user u, mdl_enrol e, mdl_course c, mdl_role r, mdl_course_categories k";
        $BIND1 = "m.enrolid = e.id";
        $BIND2 = "a.roleid = r.id";
        $BIND3 = "a.userid = u.id";
        $BIND4 = "m.userid = u.id";
        $BIND5 = "e.courseid = c.id";
        $BIND6 = "c.category = k.id";
        $GROUP_BY = "GROUP BY c.id,a.roleid";
        $DESC = "DESC";
        $ASC = "ASC";
        $sql = "select ${VIEW_COLUMNS} ${FROM_TABLES}  where ${BIND1} and ${BIND2} and ${BIND3} and ${BIND4} and ${BIND5} and ${BIND6} and (a.roleid <= 4) 
                ${GROUP_BY} order by c.timecreated $DESC, c.id $ASC";

        $rs = $DB->get_recordset_sql($sql, array(), $page*$perpage, $perpage);
        foreach ($rs as $c) {
            $row = array();
            $row[] = $c->categoryname;
            $row[] = $c->fullname;
            $row[] = $c->firstname;
            $row[] = $c->lastname;
            $row[] = $c->roleshortname;
            $row[] = date('Y/m/d H:i:s', $c->timecreated);

            $table->data[] = $row;
        }
        $rs->close();

        return html_writer::table($table);
    }

    public function show_table3($page, $perpage, $component, $contextlevel) {

        global $CFG;
        global $DB;
        $data = array();
        $table = new html_table();
        $table->head = [
            get_string('fullname', 'tool_managecourse'),
            get_string('sizeinkbytes', 'tool_managecourse'),
            get_string('sizeinmbytes', 'tool_managecourse'),
            get_string('firstname', 'tool_managecourse'),
            get_string('lastname', 'tool_managecourse'),
        ];
        $table->id = 'courses';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = array();

        $VIEW_COLUMNS = "x.instanceid, f.component, x.contextlevel, u.firstname, u.lastname, c.fullname, c.shortname, f.timecreated, f.timemodified,
                        sum(f.filesize) as size_in_bytes, sum(f.filesize/1024) as size_in_kbytes, sum(f.filesize/1048576) as size_in_mbytes,
                        sum(f.filesize/1073741824) as size_in_gbytes, sum(case when (f.filesize > 0) then 1 else 0 end) as number_of_files";
        $FROM_TABLES = "FROM mdl_files f, mdl_course c, mdl_context x, mdl_user u";
        $BIND1 = "f.contextid = x.id";
        $BIND2 = "c.id = x.instanceid";
        $BIND3 = "u.id = f.userid";
        $GROUP_BY = "GROUP BY f.contextid, x.instanceid";
        $ORDER_FILESIZE = "sum(f.filesize)";
        $ORDER_TIMECREATED = "f.timecreated";
        $ORDER_MODIFIED = "f.timemodified";
        $DESC = "DESC";
        $ASC = "ASC";
        $FORMAT_NORMAL = ";";
        $FORMAT_ROW = "\G";
        //Uncomment if you want to echo date string  
        $FORMAT = $FORMAT_NORMAL;
        //sql (you can tweek order with above variable)
        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} WHERE ${BIND1} and ${component} and ${contextlevel} and ${BIND2} and ${BIND3} ${GROUP_BY} 
                ORDER BY ${ORDER_FILESIZE} ${DESC}, ${ORDER_TIMECREATED} ${DESC}";
        $rs = $DB->get_recordset_sql($sql, array(), $page*$perpage, $perpage);
        foreach ($rs as $c) {
            $row = array();
            $row[] = $c->fullname;
            $row[] = $c->size_in_kbytes;
            $row[] = $c->size_in_mbytes;
            $row[] = $c->firstname;
            $row[] = $c->lastname;

            $table->data[] = $row;
        }
        $rs->close();

        return html_writer::table($table);
    }

    public function show_table3_count($page, $perpage, $component, $contextlevel) {

        global $DB;
        // Alert, first column should be unique with get_records_sql.
        $VIEW_COLUMNS = "x.instanceid, f.component, x.contextlevel, x.instanceid, u.username,c.fullname, c.shortname, f.timecreated,
                        f.timemodified, sum(f.filesize) as size_in_bytes, sum(case when (f.filesize > 0) then 1 else 0 end) as number_of_files";
        $FROM_TABLES = "FROM mdl_files f, mdl_course c, mdl_context x, mdl_user u";
        $BIND1 = "f.contextid = x.id";
        $BIND2 = "c.id = x.instanceid";
        $BIND3 = "u.id = f.userid";
        $GROUP_BY = "GROUP BY f.contextid, x.instanceid";
        $ORDER_FILESIZE = "sum(f.filesize)";
        $ORDER_TIMECREATED = "f.timecreated";
        $ORDER_MODIFIED = "f.timemodified";
        $DESC = "DESC";
        $ASC = "ASC";
        //sql (you can tweek order with above variable)
        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} WHERE ${BIND1} and ${component} and ${contextlevel} and ${BIND2} and ${BIND3} ${GROUP_BY} 
                ORDER BY ${ORDER_TIMECREATED} ${DESC}, ${ORDER_FILESIZE} ${ASC}";
        $records = $DB->get_records_sql($sql, array());
        $counts = count($records);
	return $counts;
    }

}
