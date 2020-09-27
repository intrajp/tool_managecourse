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
 * Returning course informations.
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
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/simple.php');

if (isguestuser()) {
    throw new \require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');
if (!is_siteadmin()) {
    die;
}

$simple = new \intrajp_simple();

// Get the parameter
$courseid  = NULL;
$modulename_str = NULL;
$enrol_str = NULL;

$courseid  = optional_param('courseid', $courseid, PARAM_INT);

$rs_b = $simple->get_course_basic($courseid);

foreach($rs_b as $key=>$value) {
    echo "$key: $value";
    echo "\n";
}

$rs_g = $simple->get_course_groupings($courseid);

foreach($rs_g as $key=>$value) {
    echo "$key: $value";
    echo "\n";
}

$rs_g = $simple->get_course_groups($courseid);

foreach($rs_g as $key=>$value) {
    echo "$key: $value";
    echo "\n";
}

$rs_ra = $simple->get_course_role_assignments($courseid);
$role_assignments_str = NULL;

$occurences = array_count_values($rs_ra);
$keys = array_keys($occurences);
$counted = count($keys);

echo "Role assignments:";
for($i = 0; $i < $counted; $i++){
    echo " ".$keys[$i]." ".$occurences[$keys[$i]];
}
echo "\n";

$rs_e = $simple->get_course_enrol_methods($courseid);
$enrol_str = NULL;
foreach($rs_e as $id => $enrol) {
    $enrol_str .= " ".$enrol;
}
echo "Enrolment methods: $enrol_str";
echo "\n";

$rs_f = $simple->get_course_format($courseid);
$format = NULL;
foreach($rs_f as $key => $value) {
    $format = $value;
    if ($format == "singleactivity") {
        $format = "Single activity format";
    } else if ($format == "social") {
        $format = "Social format";
    } else if ($format == "topics") {
        $format = "Topics format";
    } else if ($format == "weeks") {
        $format = "Weekly format";
    }
}
echo "Format: $format";
echo "\n";

if ($format) {
    $rs_s = $simple->get_course_sections($courseid);
    $count_s = count($rs_s);
    echo "Sections:";
    echo "\n";
    for($i=0; $i<$count_s; $i++){
        foreach($rs_s[$i] as $key => $value) {
            if ($format == "Topics format") {
                if ($key == "name") {
                    if ($value == "") {
                        if ($i == 0) {
                            echo "General";
                        } else {
                            echo "Topic ".$i;
                        }
                    } else {
                        echo "$value";
                    }
                    echo "\n";
                }
            } else if ($format == "Weekly format") {
                if ($key == "name") {
                    if ($value == "") {
                        if ($i == 0) {
                            echo "General";
                        } else {
                            // change it to MM/DD - MM/DD 
                            $rs_w = $simple->get_course_weeks($courseid, $i);
                            foreach($rs_w as $key => $value) {
                                if ($key == "weekstart") {
                                    echo gmdate("m/d", $value + date("Z"))." - ";
                                } else if ($key == "weekend") {
                                    echo gmdate("m/d", $value + date("Z"));
                                }
                            }
                        }
                    } else {
                        echo "$name";
                    }
                    echo "\n";
                }
            }
        }
    }
}

$rs_m = $simple->get_course_module_names($courseid);
$rs_m = array_unique($rs_m);
foreach($rs_m as $id => $modulename) {
    $modulename_str .= " ".$modulename;
}

echo "Modules used: $modulename_str";
