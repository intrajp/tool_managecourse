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
 * Implements the plugin form 
 *
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");
 
class select_form extends moodleform {

    // properties
    public $userid;

    // setter and getter
    public function set_userid($userid) {

        $this->userid = $userid;

    }

    public function get_userid() {

        return $this->userid;

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

    // methods

    public function get_users_list() {

        global $DB;

        $rs = $DB->get_recordset_sql($this->get_users_sql(), array());

        $row = array();
        $row += array(NULL=>get_string('allusers', 'tool_managecourse'));
        foreach ($rs as $c) {
            $userid = $c->userid;
            $firstname = $c->firstname;
            $lastname = $c->lastname;
            $row += array("$userid"=>"$firstname $lastname");
        }
        $rs->close();

        return $row;

    }

    //Add elements to form
    public function definition() {

        global $CFG;
 
        $mform = $this->_form;

        $options = $this->get_users_list();
        $attributes = NULL;
        $mform->addElement('select', 'type', '', $options, $attributes);
        $this->add_action_buttons(true, get_string('selectuser', 'tool_managecourse'));
 
    }

    //Custom validation should be added here
    function validation($data, $files) {

        return array();

    }
}
