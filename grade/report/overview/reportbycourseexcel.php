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
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once($CFG->dirroot.'/course/lib.php');

$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
global $USER;
global $CFG;
$admins = get_admins();
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}



if(isset($_GET['categoryid']) && $_GET['categoryid']>0 && isset($_GET['courseid']) && $_GET['courseid']>0)
{
	$categoryid = $_GET['categoryid'];
	$courseid = $_GET['courseid'];
$arrnew = $DB->get_records_sql("SELECT ue.id, c.id AS courseid, c.fullname as course_name, c.idnumber as module, 
c.timemodified as dateshow , ue.timestart as enrol_start , ue.timeend as enrol_end , u.id AS userid , u.phone1 as phone1, u.email as email,
u.phone2 as phone2, 
u.firstname as fname , u.lastname as lname , u.username as studentno FROM mdl_user u 
JOIN mdl_user_enrolments ue ON ue.userid = u.id 
JOIN mdl_enrol e ON e.id = ue.enrolid 
JOIN mdl_role_assignments ra ON ra.userid = u.id 
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50 
JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id 
JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student' 
WHERE e.status = 0 AND ue.status = 0 AND c.category = '".$categoryid."' AND e.courseid = '".$courseid."'");
}


echo '<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #85adf2;
  color: white;
}
</style>';


// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

?>

<br/><br/>
<?php
if(count($arrnew)>0) { 
$html_excel = '<table id="customers" style="width:100%!important;">
<thead>
<tr style="width:100%!important; background-color: blue; color: white;">
<th>Student No</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Enrolment Start</th>
<th>Enrolment End</th>
</tr>
</thead>
<tbody>';
     $course_name = array(); 
	foreach($arrnew as $key=>$val) 
	{ 
	$course_name[] = $val->course_name;  
	if($val->id>0) 
	{
		
		$course_explode_arr = explode(" ",$val->course_name);
$html_excel = $html_excel.'<tr>
		<td>'.$val->studentno.'</td>
		<td>'.$val->fname." ".$val->lname.'</td>
		
	    <td>'.$val->email.'</td><td>';
		if($val->phone1!='' && $val->phone2!='') { $html_excel = $html_excel.$val->phone1." / ".$val->phone2; } 
		else if($val->phone1!='' && $val->phone2=='') { $html_excel = $html_excel.$val->phone1; } else 
if($val->phone1=='' && $val->phone2!='') { $html_excel = $html_excel.$val->phone2; }	else { $html_excel = $html_excel.'No Phone'; }

$html_excel = $html_excel.'</td>';
		$html_excel = $html_excel.'<td>'.@date('jS F, Y, \a\\t g.i a',$val->enrol_start).'</td>';
		$html_excel = $html_excel.'<td>'.@date('jS F,Y, \a\\t g.i a',$val->enrol_end).'</td>';
$html_excel = $html_excel.'</tr>';
   
}

	}
	$html_excel = $html_excel.'</tbody></table>';
} else { $html_excel = $html_excel.'No records found';
   } 
       
            
$file_name ="report.xls";
$excel_file=$html_excel;
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file_name");
echo $excel_file;
die; 