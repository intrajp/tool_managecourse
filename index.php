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

namespace  tool_managecourse;

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/renderer.php');

if (isguestuser()) {
    throw new \require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');

admin_externalpage_setup('admins');
if (!is_siteadmin()) {
    die;
}

$url = new \moodle_url('/admin/tool/managecourse/index.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('managecourse', 'tool_managecourse'));
$PAGE->set_heading(get_string('managecourse', 'tool_managecourse'));

$returnurl = new \moodle_url('/admin/tool/managecourse/index.php');
$renderer = $PAGE->get_renderer('tool_managecourse');

echo $OUTPUT->header();
echo $renderer->show_table();
// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'timecreated', PARAM_ALPHA);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);

$coursescount = $DB->count_records('course');

$columns = array('id'    => get_string('id', 'tool_managecourse'),
                 'timemodified' => get_string('timecreated', 'tool_managecourse'),
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
    $hcolumns[$column] = "<a href=\"index.php?sort=$column&amp;dir=$columndir&amp;page=$page&amp;perpage=$perpage\">".$strcolumn."</a>$columnicon";
}

$baseurl = new \moodle_url('index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
$count2_actual = $renderer->show_table2_count();
$count2_redundant = $renderer->show_table2_count_redundant();
echo "Showing ".$count2_redundant." rudundant (".$count2_actual." actual) courses which has enrol as manager, coursecreator, editingteacher or teacher role.";
echo $OUTPUT->paging_bar($count2_redundant, $page, $perpage, $baseurl);
echo $renderer->show_table2($page, $perpage);

echo $OUTPUT->single_button(new \moodle_url('/admin/tool/managecourse/category_list.php'), get_string('categorycourselist', 'tool_managecourse'));
echo $OUTPUT->single_button(new \moodle_url('/admin/tool/managecourse/course_file_size.php'), get_string('coursefilesize', 'tool_managecourse'));
echo $OUTPUT->single_button(new \moodle_url('/admin/tool/managecourse/grade.php'), get_string('coursegrades', 'tool_managecourse'));

echo $OUTPUT->footer();
