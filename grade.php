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
 * Web interface for generating plugins.
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/renderer.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/form.php');

if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');
$url = new moodle_url('/admin/tool/managecourse/index.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('managecourse', 'tool_managecourse'));
$PAGE->set_heading(get_string('managecourse', 'tool_managecourse'));

$renderer = $PAGE->get_renderer('tool_managecourse');

echo $OUTPUT->header();
echo $renderer->show_table();


$mform = NULL;
if (!$mform) {
    $mform = new select_form();
}

$fromform = $mform->get_data();
if ($fromform) {
    $userid = $fromform->type;
    $mform->set_userid($userid);
} else {
    $userid = NULL;
}

// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'userid', PARAM_ALPHA);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);
$userid  = optional_param('userid', $userid, PARAM_INT);

$baseurl = new moodle_url('grade.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'userid' => $userid));
$returnurl = new moodle_url('/admin/tool/managecourse/grade.php');

$columns = array('id'    => get_string('id', 'tool_managecourse'),
                 'timecreated' => get_string('timecreated', 'tool_managecourse'),
                );
$hcolumns = array();

if (!isset($columns[$sort])) {
    $sort = 'timecreated';
}

foreach ($columns as $column=>$strcolumn) {
    if ($sort != $column) {
        $columnicon = '';
        if ($column == 'lastaccess') {
            $columndir = 'DESC';
        } else {
            $columndir = 'ASC';
        }
    } else {
        $columndir = $dir == 'ASC' ? 'DESC':'ASC';
        if ($column == 'lastaccess') {
            $columnicon = $dir == 'ASC' ? 'up':'down';
        } else {
            $columnicon = $dir == 'ASC' ? 'down':'up';
        }
        $columnicon = $OUTPUT->pix_icon('t/' . $columnicon, '');

    }
    $hcolumns[$column] = "<a href=\"grade.php?sort=$column&amp;dir=$columndir&amp;page=$page&amp;perpage=$perpage\">".$strcolumn."</a>$columnicon";
}


//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    $mform->cleanup(true);
    redirect($returnurl);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $userid = $fromform->type;
    $mform->set_data($userid);
    $mform->set_userid($userid);
    $mform->display();
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
 
    //Set default data (if any)
    $mform->set_userid($userid);
    $mform->set_data($userid);
    //displays the form
    $mform->display();
    echo "
    <script type=\"text/javascript\">
        document.getElementById(\"id_type\").options[$userid].selected = true;
    </script>
    ";
}

$gradecount = $renderer->show_grade_count($page, $perpage, $userid);
echo $OUTPUT->paging_bar($gradecount, $page, $perpage, $baseurl);
echo $renderer->show_grade_table1($page, $perpage, $userid);

echo "<a href=\"index.php\">back to index</a>";
echo $OUTPUT->footer();
