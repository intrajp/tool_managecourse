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
class tool_managecourse_renderer extends plugin_renderer_base {

    /** @var tool_managecourse_installer */
    protected $uploader = null;

    /**
     * Sets the tool_managecourse_uploader instance being used.
     *
     * @throws coding_exception if the uploader has been already set
     * @param tool_managecourse_uploader $uploader
     */
    public function set_uploader_instance(tool_managecourse_uploader $uploader) {
        if (is_null($this->uploader)) {
            $this->uploader = $uploader;
        } else {
            throw new coding_exception('Attempting to reset the uploader instance.');
        }
    }

    /**
     * Defines the index page layout (for PDF upload)
     *
     * @return string
     */
    public function index_page() {

        if (is_null($this->uploader)) {
            throw new coding_exception('Uploader instance has not been set.');
        }

        $out = $this->index_page_heading();
        $out .= $this->index_page_upload();
        $out .= $this->output->footer();

        return $out;
    }

    /**
     * Renders the index page heading (for PDF upload)
     *
     * @return string
     */
    protected function index_page_heading() {
        return $this->output->heading(get_string('pluginname', 'tool_managecourse'));
    }

    /**
     * Renders the widget (for uploading PDF)
     *
     * @return string
     */
    protected function index_page_upload() {

        $form = $this->uploader->get_uploadpdf_form();

        ob_start();
        $form->display();
        $out = ob_get_clean();

        $out = $this->box($out, 'generalbox', 'uploadpdfbox');

        return $out;
    }

//// sqls

    private function teacher_enroled_to_course_sql() {

        $VIEW_COLUMNS = "c.id as courseid, k.name as categoryname, c.fullname, c.timecreated,
                             u.lastname, u.firstname, r.shortname as roleshortname";
        $FROM_TABLES = "FROM {user_enrolments} m, {role_assignments} a, {user} u, {enrol} e,
                            {course} c, {role} r, {course_categories} k";
        $BIND1 = "m.enrolid = e.id";
        $BIND2 = "a.roleid = r.id";
        $BIND3 = "a.userid = u.id";
        $BIND4 = "m.userid = u.id";
        $BIND5 = "e.courseid = c.id";
        $BIND6 = "c.category = k.id";
        $GROUP_BY = "GROUP BY c.id, a.roleid, r.id";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY c.timecreated $DESC, c.id $ASC, r.id $ASC";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES}  WHERE ${BIND1} AND ${BIND2} AND ${BIND3}
                    AND ${BIND4} AND ${BIND5} AND ${BIND6} AND (a.roleid <= 4) 
                    ${GROUP_BY} ${ORDER_BY}";

        return $sql;

    }

    private function teacher_enroled_to_course_sql_count() {

        $VIEW_COLUMNS = "DISTINCT c.id as courseid";
        $FROM_TABLES = "FROM {user_enrolments} m, {role_assignments} a, {user} u, {enrol} e,
                            {course} c, {role} r, {course_categories} k";
        $BIND1 = "m.enrolid = e.id";
        $BIND2 = "a.roleid = r.id";
        $BIND3 = "a.userid = u.id";
        $BIND4 = "m.userid = u.id";
        $BIND5 = "e.courseid = c.id";
        $BIND6 = "c.category = k.id";
        $GROUP_BY = "GROUP BY c.id, a.roleid, r.id";
        $DESC = "DESC";
        $ASC = "ASC";
        $ORDER_BY = "ORDER BY c.timecreated $DESC, c.id $ASC, r.id $ASC";

        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} WHERE ${BIND1} AND ${BIND2} AND
                    ${BIND3} AND ${BIND4} AND ${BIND5} AND ${BIND6} AND (a.roleid <= 4) 
                    ${GROUP_BY} ${ORDER_BY}";

        return $sql;

    }

    private function show_table3_sql($component, $contextlevel) {

        // Alert, first column should be unique with get_records_sql.
        $VIEW_COLUMNS = "DISTINCT x.instanceid, f.component, x.contextlevel, u.firstname,
                             u.lastname, c.fullname, c.shortname, f.timecreated, f.timemodified,
                             sum(f.filesize) as size_in_bytes, sum(f.filesize/1024) as size_in_kbytes,
                             sum(f.filesize/1048576) as size_in_mbytes, 
                             sum(f.filesize/1073741824) as size_in_gbytes,
                             sum(case when (f.filesize > 0) then 1 else 0 end) as number_of_files";
        $FROM_TABLES = "FROM {files} f, {course} c, {context} x, {user} u";
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
        $sql = "SELECT ${VIEW_COLUMNS} ${FROM_TABLES} WHERE ${BIND1} AND ${component}
                    AND ${contextlevel} AND ${BIND2} AND ${BIND3} ${GROUP_BY} 
                    ORDER BY ${ORDER_FILESIZE} ${DESC}, ${ORDER_TIMECREATED} ${DESC}";

        return $sql;

    }

    private function grade_sql($userid, $categoryid, $courseid) {

        $VIEW_COLUMNS="m.id as user_enrolments_id, k.name AS categoryname, u.id AS userid, u.firstname,
                       u.lastname, e.id enrolid, r.shortname AS rolename, c.fullname,
		       FROM_UNIXTIME(c.startdate) AS startdate, FROM_UNIXTIME(c.enddate) AS enddate,
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

    protected function get_course_count() {

        global $DB;
        $coursecount = $DB->count_records('course', array());

        return $coursecount;

    }

    protected function get_course_names() {

        global $DB;
	$names = $DB->get_records('course', array());

        return $names;

    }

    protected function get_course_count_time($time1, $time2) {

        global $DB;
        $coursecounttime = $DB->get_record_sql('SELECT count(id) as c from {course}
                                                WHERE timecreated <= ' . $time1 .
                                                ' AND timecreated >= ' . $time2, array());

        return $coursecounttime->c;

    }

    public function show_table2_count() {

        global $DB;

        $rs = $DB->get_records_sql($this->teacher_enroled_to_course_sql_count(), array());
        $counts = count($rs);

	return $counts;

    }

    public function show_table2_count_redundant() {

        global $DB;

        $rs = $DB->get_recordset_sql($this->teacher_enroled_to_course_sql(), array());
        $cnt=0;
        foreach ($rs as $c) {
            $cnt = $cnt + 1;
        }
        $rs->close();

	return $cnt;

    }

    public function show_table3_count($page, $perpage, $component, $contextlevel) {

        global $DB;

        $records = $DB->get_records_sql($this->show_table3_sql($component, $contextlevel),
                                            array());
        $counts = count($records);

	return $counts;

    }

    public function show_grade_count($page, $perpage, $userid, $categoryid, $courseid) {

        global $DB;

        $records = $DB->get_records_sql($this->grade_sql($userid, $categoryid, $courseid), array());
        $counts = count($records);

	return $counts;

    }

//// tables

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

        return html_writer::table($table, array('sort' => 'location', 'dir' => 'ASC',
                                      'perpage' => $perpage));

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
        $table->id = 'courses2';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data = array();

        $rs = $DB->get_recordset_sql($this->teacher_enroled_to_course_sql(), array(),
                                         $page*$perpage, $perpage);

        $timecreated_pre = NULL;
        $fullname_pre = NULL;
        $firstname_pre = NULL;
        $lastname_pre = NULL;
        $roleshortname_pre = NULL;

        foreach ($rs as $c) {
            $row = array();
            if ($c->timecreated == $timecreated_pre && strcmp($c->fullname, $fullname_pre) == 0
                                   && strcmp($c->firstname, $firstname_pre) == 0 &&
		    strcmp($c->lastname, $lastname_pre) == 0
                    && strcmp($c->roleshortname, $roleshortname_pre) != 0) {
                    $row[] = "\"";
                    $row[] = "\"";
                    $row[] = "\"";
                    $row[] = "\"";
                    $row[] = $c->roleshortname;
                    $row[] = "\"";
            } else {
                $row[] = $c->categoryname;
                $row[] = $c->fullname;
                $row[] = $c->firstname;
                $row[] = $c->lastname;
                $row[] = $c->roleshortname;
                $row[] = date('Y/m/d H:i:s', $c->timecreated);
            }
            $timecreated_pre = $c->timecreated;
            $fullname_pre = $c->fullname;
            $firstname_pre = $c->firstname;
            $lastname_pre = $c->lastname;
            $roleshortname_pre = $c->roleshortname;

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
        $table->id = 'courses3';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = array();

        $rs = $DB->get_recordset_sql($this->show_table3_sql($component, $contextlevel),
                  array(), $page*$perpage, $perpage);
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

    public function show_grade_table1($page, $perpage, $userid, $categoryid, $courseid) {

        global $CFG;
        global $DB;
        $data = array();
        $table = new html_table();
        $table->head = [
            get_string('categoryname', 'tool_managecourse'),
            get_string('fullname', 'tool_managecourse'),
            get_string('firstname', 'tool_managecourse'),
            get_string('lastname', 'tool_managecourse'),
            get_string('startdate', 'tool_managecourse'),
            get_string('enddate', 'tool_managecourse'),
            get_string('finalgrade', 'tool_managecourse'),
            get_string('rawgrademax', 'tool_managecourse'),
        ];
        $table->id = 'courses4';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = array();

        $rs = $DB->get_recordset_sql($this->grade_sql($userid, $categoryid, $courseid), array(), $page*$perpage, $perpage);
        foreach ($rs as $c) {
            $row = array();
            $row[] = $c->categoryname;
            $row[] = $c->fullname;
            $row[] = $c->firstname;
            $row[] = $c->lastname;
            $row[] = $c->startdate;
            $row[] = $c->enddate;
            $row[] = $c->finalgrade;
            $row[] = $c->rawgrademax;

            $table->data[] = $row;
        }

        $rs->close();

        return html_writer::table($table);
    }

    public function render_grade_pdf($userid, $categoryid, $courseid) {

        global $CFG;
        global $DB;
        $data = array();
        $table = new html_table();
        $table->head = [
            get_string('categoryname', 'tool_managecourse'),
            get_string('fullname', 'tool_managecourse'),
            get_string('firstname', 'tool_managecourse'),
            get_string('lastname', 'tool_managecourse'),
            get_string('startdate', 'tool_managecourse'),
            get_string('enddate', 'tool_managecourse'),
            get_string('finalgrade', 'tool_managecourse'),
            get_string('rawgrademax', 'tool_managecourse'),
        ];

        $rs = $DB->get_recordset_sql($this->grade_sql($userid, $categoryid, $courseid), array());
        foreach ($rs as $c) {
            $row = array();
            $row[] = $c->categoryname;
            $row[] = $c->fullname;
            $row[] = $c->firstname;
            $row[] = $c->lastname;
            $row[] = $c->startdate;
            $row[] = $c->enddate;
            $row[] = $c->finalgrade;
            $row[] = $c->rawgrademax;
        }
        $rs->close();

        return $row;

    }

}
