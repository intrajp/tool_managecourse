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
 * Implements the plugin uploading PDF form 
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Defines a simple form for uploading the add-on ZIP package
 *
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_managecourse_uploadpdf_form extends moodleform {

    /**
     * Defines the form elements
     */
    public function definition() {

        $mform = $this->_form;
        $uploader = $this->_customdata['uploader'];

        $mform->addElement('header', 'general', get_string('uploadpdf', 'tool_managecourse'));
        $mform->addHelpButton('general', 'uploadpdf', 'tool_managecourse');
        $mform->addElement('filepicker', 'pdffile', get_string('uploadpdffile', 'tool_managecourse'),
            null, array('accepted_types' => '.pdf'));
        $mform->addHelpButton('pdffile', 'uploadpdffile', 'tool_managecourse');
        $mform->addRule('pdffile', null, 'required', null, 'client');
        $mform->addElement('static', 'permcheck', '',
            html_writer::span(get_string('permcheck', 'tool_managecourse'), '',
                array('id' => 'tool_managecourse_uploadpdf_permcheck')));
        $mform->setAdvanced('permcheck');

        $this->add_action_buttons(false, get_string('uploadpdfsubmit', 'tool_managecourse'));

    }

}
