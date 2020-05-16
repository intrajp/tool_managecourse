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

    public function show_table2($page, $perpage) {

        global $DB;
        $table = new html_table();
        $table->head = [
            get_string('fullname', 'tool_managecourse'),
            get_string('timecreated', 'tool_managecourse'),
        ];
        $table->id = 'courses';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = array();

        $sql = "SELECT * from {course} ORDER BY timecreated DESC";

        $rs = $DB->get_recordset_sql($sql, array(), $page*$perpage, $perpage);
        foreach ($rs as $c) {
            $row = array();
            $row[] = $c->fullname;
            $row[] = date('Y/m/d H:i:s', $c->timecreated);

            $table->data[] = $row;
        }
        $rs->close();

        return html_writer::table($table);
    }

}
