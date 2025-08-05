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
global $SESSION;
$admins = get_admins();
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
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
//echo '<pre>';
//print_r($SESSION->list_count);
$html_excel = '';
if(count($SESSION->list_count)>0) { 
$html_excel = ' <table id="customers">
  <tr>
  <th>Course</th>
    <th><?php echo ucfirst($assnum); ?></th>
	<th>Total Marked</th>
	<th>On Time Marked</th>
	<th>Late Marked</th>
	
	
  </tr>';
     
	 

$k=0;
	  
  $color ="#f3eff7";
 foreach($SESSION->list_count as $key=>$val)
  {
	  $k++;

	$ontime = 0;
	$late = 0;
	
	if($val['totalgraded']>0)
	{
		//$ip_array = array();
	foreach($val['submissiondetails'] as $mm=>$ss)
	{
		$d = floor(($ss['timedifference']%2592000)/86400);
		if($d<14)
		{
			$ontime++;
		}
		else
			
		{
			$late++;
		}
		
		//IP DETAILS
		/* $sql_ip = "SELECT `ip` FROM `mdl_logstore_standard_log` 
	WHERE `userid` = '".$stuidpost."' AND `relateduserid` = '".$mm."' AND `contextinstanceid` = '".$val['instanceid']."' AND `action` = 'graded' ORDER BY `id` DESC";
	 $ip_details = $DB->get_record_sql($sql_ip);
	$ip_array[]=$ip_details->ip; */
		
	}
	
	
	
	
	}
	
	
	 $sql_trainers = "SELECT DISTINCT u.id AS userid
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
JOIN mdl_role r ON r.id = ra.roleid AND r.shortname != 'student'
WHERE c.id = '".$val['courseid']."'";
$trainers = $DB->get_records_sql($sql_trainers);

unset($arr_check_tr);
$arr_check_tr = array();
foreach($trainers as $key666=>$val666) {
	
	$arr_check_tr[]=$val666->userid;
}

if(in_array($SESSION->stuidpost,$arr_check_tr)==true)
{
  
  
  $html_excel = $html_excel. 
  "<tr >
  <td>".$val['courseshortname']."
  
 </td>
  <td>".$val['assessmentname']."

  </td>
      
	<td>".$val['totalgraded']."  
  </td>
	<td>".$ontime."</td>
	<td>".$late."</td>
   
	
      </tr>";

   } 
   unset($sql_trainers); unset($ontime); unset($late); unset($d);   
   
   } 
   } 
   
   else 
   
   { 
   
   $html_excel = $html_excel.'<tr><td colspan="8">&nbsp;&nbsp;<div style="color: red!important; font-weight: bold!important;" class="info-msg">No Records are Found!</div></td></tr>'; 
   
   }
         
    
       $html_excel = $html_excel.
     '</table>';
            
$file_name ="report.xls";
$excel_file=$html_excel;
//echo $excel_file;
//die;
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file_name");
echo $excel_file;
die; 