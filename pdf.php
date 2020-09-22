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
 * Create PDF for the grade.
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
require_once($CFG->dirroot.'/admin/tool/managecourse/classes/pdf_core.php');

if (isguestuser()) {
    throw new \require_login_exception('Guests are not allowed here.');
}

// This is a system level page that operates on other contexts.
require_login();

admin_externalpage_setup('tool_managecourse');

$userid = NULL;
$categoryid = NULL;
$courseid = NULL;

$userid = $_POST['userid'];
$categoryid = $_POST['categoryid'];
$courseid = $_POST['courseid'];

if ((!$userid) && (!$categoryid) && (!$courseid)) {
    throw new \moodle_exception('Please select a user');
}

if (!$userid) {
    throw new \moodle_exception('Please select a user');
}

// Include the main TCPDF library (search for installation path).
//retrieved from https://tcpdf.org/examples/example_001/
require_once($CFG->libdir.'/tcpdf/tcpdf.php');

// create new PDF document
$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
//$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('GRADE');
//$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
//$pdf->SetFont('dejavusans', '', 14, '', true);
$pdf->SetFont('kozgopromedium', '', 10, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

$pdf_core = new \tool_managecourse_pdf();
$grade_result = $pdf_core->render_grade_pdf($userid, $categoryid, $courseid);

$record = explode("%",$grade_result[0]);
$firstname = $record[1];
$lastname = $record[2];

$pdf->writeHTML("GRADE: $firstname $lastname", true, false, true, false, 'J');
$pdf->writeHTML("<br />", true, false, true, false, 'J');

for ($i = 0; $i < count($grade_result); $i++) {
    $record = explode("%",$grade_result[$i]);
    $fullname = $record[0];
    $firstname = $record[1];
    $lastname = $record[2];
    $startdate = $record[3];
    $enddate = $record[4];
    $grade = $record[5];
    if (!$grade) {
        $grade = 0;
    }
    $rawgrademax = $record[6];

    $html = $startdate." - ".$enddate;
    $pdf->writeHTML($html, true, false, true, false, 'J');
    $html = $fullname;
    $pdf->writeHTML($html, true, false, true, false, 'J');
    $html = $grade."/".$rawgrademax;
    $pdf->writeHTML($html, true, false, true, false, 'J');
    $html = "<br />";
    $pdf->writeHTML($html, true, false, true, false, 'J');
}

// Set some content to print
// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('grade_example_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
