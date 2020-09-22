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

//// methods

    public function get_course_forum($courseid) {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_course_forum_sql($courseid), array());

        $row = array();
        foreach ($rs as $c) {
            $forumid = $c->forumid;
            $forumname = $c->forumname;
            $row += array("$forumid"=>"$forumname");
        }
        $rs->close();

        return $row;

    }

}
