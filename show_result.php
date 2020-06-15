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
 * Returning category list or course list from categoryid.
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/form.php');

if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');

$mform = NULL;
if (!$mform) {
    $mform = new select_form();
}

// Get the parameter
$userid  = optional_param('userid', $userid, PARAM_INT);
$categoryid  = optional_param('categoryid', $categoryid, PARAM_INT);
$courseid  = optional_param('courseid', $courseid, PARAM_INT);

if($userid >= "-1") {
    $row = $mform->get_course_category_list();
}

if($categoryid >= "-1") {
    $row = $mform->get_courses_list($categoryid);
}

foreach ($row as $courseid => $course_name) {
    echo "<option value=".$courseid.">" . $course_name . "</option>";
}
