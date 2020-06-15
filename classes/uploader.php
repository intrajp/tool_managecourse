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


defined('MOODLE_INTERNAL') || die();

/**
 * Provides tool_managecourse_uploader class.
 *
 * @package     tool_managecourse
 * @category    admin
 * @copyright   2020 Shintaro Fujiwara <shintaro dot fujiwara at gmail dot com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_managecourse_uploader {

    /** @var tool_managecourse_uploadpdf_form */
    protected $uploadpdfform = null;

    /**
     * Factory method returning an instance of this class.
     *
     * @return tool_managecourse_uploader
     */
    public static function instance() {
        return new static();
    }

    /**
     * Returns the URL to the main page of this admin tool
     *
     * @param array optional parameters
     * @return moodle_url
     */
    public function index_url(array $params = null) {
        return new moodle_url('/admin/tool/managecourse/upload_file.php', $params);
    }

    /**
     * @return tool_managecourse_uploadpdf_form
     */
    public function get_uploadpdf_form() {
        if (!is_null($this->uploadpdfform)) {
            return $this->uploadpdfform;
        }

        $action = $this->index_url();
        $customdata = array('uploader' => $this);

        $this->uploadpdfform = new tool_managecourse_uploadpdf_form($action, $customdata);

        return $this->uploadpdfform;
    }

    /**
     * @see self::instance()
     */
    protected function __construct() {
    }

}
