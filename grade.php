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
 * Serves grade feature.
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
require_once($CFG->libdir . '/pagelib.php');
global $PAGE;

// including custom js file
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/admin/tool/managecourse/js/jQuery-3.5.1.min.js'));

if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');

$url = new moodle_url('/admin/tool/managecourse/grade.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('managecourse', 'tool_managecourse'));
$PAGE->set_heading(get_string('managecourse', 'tool_managecourse'));

$renderer = $PAGE->get_renderer('tool_managecourse');

echo $OUTPUT->header();

$mform = NULL;
if (!$mform) {
    $mform = new select_form();
}

$userid = NULL;
$categoryid = NULL;
$courseid = NULL;

// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'userid', PARAM_ALPHA);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);
$userid  = optional_param('userid', $userid, PARAM_INT);
$categoryid  = optional_param('categoryid', $categoryid, PARAM_INT);
$courseid  = optional_param('courseid', $courseid, PARAM_INT);

echo "
<script>
window.onload = init;
function init() {
    // first we get the value of each option 
    var useridval = $('#id_type').val();
    var categoryval = $('#id_type2').val();
    var courseidval = $('#id_type3').val();
    // if cancel button is clicked set default
    $('#id_cancel').click(function() {
        $('#id_type option[value=-1]').prop('selected', true);
        $('#id_type2 option[value=-1]').prop('selected', true);
        $('#id_type3 option[value=-1]').prop('selected', true);
    });
    //  we show user and category hiding courses if user is not selected
    if (useridval == -1) {
        $(function () {
            $('#id_type3').find('option').remove().end();
        });
    }
    // set value to each option if any
    $(function () {
        $('#id_type option[value=$userid]').prop('selected', true);
        $('#id_type2 option[value=$categoryid]').prop('selected', true);
        $('#id_type3 option[value=$courseid]').prop('selected', true);
    });
    // we want to disable submit button when user is not selected.
    $(function () {
        var useridval = $('#id_type').val();
        var categoryidval = $('#id_type2').val();
        var courseidval = $('#id_type3').val();
        if (useridval == -1) {
            $('#id_submitbutton').prop('disabled', true);
        }
    });
    // When user option has changed, get list of categories. 
    $('#id_type').change(function() {
        var useridchanged = $(this).val();
        var categoryidval = $('#id_type2').val();
        var courseidval = $('#id_type3').val();
        if ((useridchanged != -1) && (categoryidval != -1) && (courseidval != -1)) {
            $('#id_submitbutton').prop('disabled', false);
        }
        if (useridchanged == -1) {
            $.ajax({
                type: 'post',
                url: 'show_result.php',
                data: {userid:useridchanged},
                //dataType: 'json',
            })
                .done( function (responseText) {           
                    $(function () {
                        $('#id_type2').append(responseText);
                    })
                    $('#id_type2').find('option').remove().end();
                    $('#id_type3').find('option').remove().end();
                    $('#id_submitbutton').prop('disabled', true);
                })
                .fail( function (jqXHR, status, error) {
                    // Triggered if response status code is NOT 200 (OK)
                    alert(jqXHR.responseText);
                })
                .always( function() {
                    // Always run after .done() or .fail()
                    //$('p:first').after('<p>Thank you.</p>');
               })
        }
    });
    // When category option has changed, get list of courses. 
    $('#id_type2').change(function() {
        var categoryidchanged = $(this).val();
        var useridval = $('#id_type').val();
        var courseidval = $('#id_type3').val();
        if (categoryidchanged == -1) {
            $('#id_type3 option[value=-1]').prop('selected', true);
        }
        $.ajax({
            type: 'post',
            url: 'show_result.php',
            data: {categoryid:categoryidchanged},
            //dataType: 'json',
        })
            .done( function (responseText) {           
                $('#id_type3').find('option').remove().end();
                $(function () {
                    $('#id_type3')
                    .append(responseText);
                })
                $(function () {
                    $('#id_type3 option[value=-1]').remove();
                    // which is a top page
                    $('#id_type3 option[value=1]').remove();
                })
                if ((useridval != -1) && (categoryidchanged != -1)){
                    $('#id_submitbutton').prop('disabled', false);
                }
            })
            .fail( function (jqXHR, status, error) {
                // Triggered if response status code is NOT 200 (OK)
                alert(jqXHR.responseText);
            })
            .always( function() {
                // Always run after .done() or .fail()
                //$('p:first').after('<p>Thank you.</p>');
           })
    });
}
</script>
";

$fromform = $mform->get_data();
if ($fromform) {
    $userid = $fromform->type;
    if ($userid == "-1") {
        $show_queryresult = FALSE;
    } else {
        $show_queryresult = TRUE;
    }
    $categoryid = $fromform->type2;
    $courseid = $fromform->type3;
    $mform->set_userid($userid);
    $mform->set_categoryid($categoryid);
    $mform->set_courseid($courseid);
} else {
    $userid = NULL;
    $categoryid = NULL;
    $courseid = NULL;
}

// rebase is needed shomehow here
$baseurl = new moodle_url('grade.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage,
       	                      'userid' => $userid, 'courseid' => $courseid, 'categoryid' => $categoryid));
$returnurl = new moodle_url('/admin/tool/managecourse/grade.php');

echo $OUTPUT->single_button(new moodle_url('/admin/tool/managecourse/upload_file.php'), get_string('uploadpdffile', 'tool_managecourse'));

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    $mform->display();

} else if ($fromform = $mform->get_data()) {

    $userid = $fromform->type;
    $categoryid = $fromform->type2;
    $courseid = $fromform->type3;

    $mform->display();

    echo "
    <script>
        $(function () {
            var categoryidval = $('#id_type2').val();
            $.ajax({
                type: 'post',
                url: 'show_result.php',
                data: {categoryid:categoryidval},
                //dataType: 'json',
            })
                .done( function (responseText) {           
                    $('#id_type3').find('option').remove().end();
                    $(function () {
                        $('#id_type3')
                        .append(responseText);
                    })
                    $(function () {
                        $('#id_type3 option[value=-1]').remove();
                        // which is a top page
                        $('#id_type3 option[value=1]').remove();
                    })
                    $(function () {
                        $('#id_type3 option[value=$courseid]').prop('selected', true);
                    })
                })
                .fail( function (jqXHR, status, error) {
                    // Triggered if response status code is NOT 200 (OK)
                    alert(jqXHR.responseText);
                })
                .always( function() {
                    // Always run after .done() or .fail()
                    //$('p:first').after('<p>Thank you.</p>');
               })
        });
    </script>
    ";

} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    // here, we are using this branch as pagenation goes.

    $userid = $_GET['userid'];
    $categoryid = $_GET['categoryid'];
    $courseid = $_GET['courseid'];
    $perpage = $_GET['perpage'];
    $page = $_GET['page'];

    $baseurl = new moodle_url('grade.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage,
                                  'userid' => $userid, 'courseid' => $courseid, 'categoryid' => $categoryid));

    //displays the form
    $mform->display();

    echo "
    <script>
        $(function () {
            $.ajax({
                type: 'post',
                url: 'show_result.php',
                data: {categoryid:$categoryid},
                //dataType: 'json',
            })
                .done( function (responseText) {           
                    $('#id_type3').find('option').remove().end();
                    $(function () {
                        $('#id_type3')
                        .append(responseText);
                    })
                    $(function () {
                        $('#id_type3 option[value=-1]').remove();
                        // which is a top page
                        $('#id_type3 option[value=1]').remove();
                    })
                    $(function () {
                        $('#id_type3 option[value=$courseid]').prop('selected', true);
                    })
                })
                .fail( function (jqXHR, status, error) {
                    // Triggered if response status code is NOT 200 (OK)
                    alert(jqXHR.responseText);
                })
                .always( function() {
                    // Always run after .done() or .fail()
                    //$('p:first').after('<p>Thank you.</p>');
               })
        });
    </script>
    ";

}

$gradecount = $renderer->show_grade_count($page, $perpage, $userid, $categoryid, $courseid);
if (($userid != "") && ($categoryid != "") && ($courseid != "")) {
    echo "There are $gradecount data.";
    echo $OUTPUT->paging_bar($gradecount, $page, $perpage, $baseurl);
    echo $renderer->show_grade_table1($page, $perpage, $userid, $categoryid, $courseid);
    echo $OUTPUT->single_button(new moodle_url('/admin/tool/managecourse/pdf.php',
            array('userid'=>$userid, 'categoryid'=>$categoryid, 'courseid'=>$courseid)),
            get_string('createpdffromthisresult', 'tool_managecourse'));
}
echo $OUTPUT->single_button(new moodle_url('/admin/tool/managecourse/index.php'), get_string('backtoindex', 'tool_managecourse'));
echo $OUTPUT->footer();
