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
 * Show uploading page for PDF file.
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/renderer.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/uploader.php');
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/uploadpdf_form.php');
require_once($CFG->libdir . '/pagelib.php');
global $PAGE;

if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');

$uploader = tool_managecourse_uploader::instance();

$url = new moodle_url('/admin/tool/managecourse/upload_file.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('managecourse', 'tool_managecourse'));
$PAGE->set_heading(get_string('managecourse', 'tool_managecourse'));

$returnurl = new moodle_url('/admin/tool/managecourse/index.php');
$renderer = $PAGE->get_renderer('tool_managecourse');

echo $OUTPUT->header();
$renderer = $PAGE->get_renderer('tool_managecourse');
$renderer->set_uploader_instance($uploader);

$PAGE->set_popup_notification_allowed(false);
$form = $uploader->get_uploadpdf_form();
$success = FALSE;

if ($form->is_cancelled()) {
    redirect($PAGE->url);
} else if ($data = $form->get_data()) {
    make_temp_directory("tool_managecourse");
    $storage = $CFG->dataroot.'/temp/tool_managecourse';
    $form->save_file('pdffile', $storage.'/plugin.pdf');
    $success = TRUE;
}

if ($success) {
    // redirect to grade.php.
    redirect("/admin/tool/managecourse/grade.php");
} else {
    echo $renderer->index_page();
}
