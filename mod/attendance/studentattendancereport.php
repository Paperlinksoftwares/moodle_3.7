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
 * The gradebook overview report
 *
 * @package   gradereport_overview
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//echo '<pre>';
//print_r($_POST); die;
require_once '../../config.php';


global $USER;
global $CFG;
global $DB;
global $SESSION;
if (isset($_POST['type']) && $_POST['type']!='')
{
    $userdetails = $_SESSION['userdetails'];
if($_POST['type']==1)
{
//
} 
else  if($_POST['type']==3)
{
    ?>
<script> window.location.href="http://localhost/accit-moodle/accit/sms/sendsms.php?phone=<?php echo $_POST['phone']; ?>&name=<?php echo $_POST['name']; ?>"; </script>
<?php
}
else if($_POST['type']==2)
{
	$userdetails = $DB->get_record('user', array('id' => $_POST['userid']), '*', MUST_EXIST);

	
    $html_excel = '<table>';
	$html_excel = $html_excel.'<tr><td style="color:blue;font-size:15px!important;font-weight:bold!important;">Attendances of '.@$userdetails->firstname.' '.@$userdetails->lastname.'</td></tr><tr><td>&nbsp;</td></tr>';
    
     $course_name = array(); 
	 foreach($SESSION->data_all as $key=>$val) 
	 { 
	 $course_name[] = $val[0]; 
	 $startdate = $val[2]->startdate; 
	if($val[2]->category!=93 && $val[2]->category!=64) 
	{
	
    
     $html_excel = $html_excel.'<tr><td style="color:blue;font-size:15px!important;font-weight:bold!important;">'.$val[0].'</td></tr><tr><td>';
    
         

  //NO LEFT JOIN ONLY TO STUDENTS SPECIFIC RECORDS- ADDING LEFT IN JOIN IN LOG OURS SHOW ENTIRE DATA with student as well.
       $session_details = $DB->get_records_sql('SELECT session.* , session.id as sessionid, attendance.id as aid, attendance.course as a_course , loghours.hours as hours , loghours.absenthours as absenthours , loghours.comments as comments FROM mdl_attendance_sessions as session LEFT JOIN mdl_attendance as attendance
ON session.attendanceid = attendance.id JOIN mdl_attendance_log_hours as loghours ON loghours.sessionid = session.id AND loghours.studentid = '.$_POST["userid"].' WHERE attendance.course = '.$val[2]->id);
   $session_time_max = $DB->get_record_sql('SELECT max(session.duration) as sess_duration FROM mdl_attendance_sessions as session JOIN mdl_attendance as attendance
ON session.attendanceid = attendance.id WHERE attendance.course = '.$val[2]->id);
$multiple_hours = ($session_time_max->sess_duration/3600);
$if_fraction=0;
if(fmod($multiple_hours, 1) !== 0.00){ $if_fraction=1; }

   $decimal = $multiple_hours - floor($multiple_hours);
   if(count($session_details)>0) {
   
		 
		 
	$html_excel = $html_excel.'<table>
						<thead>
							<tr >
							<th ></th>';
							 for($k=1;$k<(intval($multiple_hours)+1);$k++)
							{
								
							$html_excel = $html_excel.'	<th>Hour '.$k.'</th>';
							 }
							if($if_fraction==1) { 
								$html_excel = $html_excel.'<th>Hour '.$decimal.'</th>';
							 }
						$html_excel = $html_excel.'	<th>Absent Hours</th>
							<th>Remark</th>
							
							</tr>
						</thead>
						<tbody>';
						 foreach($session_details as $key=>$sess) { 
						
						$count_row = $DB->count_records_sql("SELECT COUNT(`id`) FROM {attendance_log_hours} WHERE `sessionid`='".$sess->sessionid."'");

						
						$duration_hours = ($sess->duration/3600); 
						$if_fraction_show = 0;
						if(fmod($duration_hours, 1) !== 0.00){ $if_fraction_show=1; }

   $decimal_show = $duration_hours - floor($duration_hours);
						
						$html_excel = $html_excel.'<tr >
								<td >
								<font style="color: #000!important; font-weight: bold!important;font-size:20px!important;">'.@gmdate("jS F Y", $sess->sessdate).'</font>

							</td>';
							
							$abs_count=0;
							
							for($k=1;$k<(intval($multiple_hours)+1);$k++)
							{
								$show='';
								$show_decimal='';
								$hour_arr = array();
								if($sess->hours!='')
								{
									$hour_arr=explode(",",@$sess->hours);
								}
								
								if($k<intval($duration_hours) || $k==intval($duration_hours))
								{
								if($count_row>0)
								{
								
								if(in_array($k,$hour_arr)==true)
								{
									$show ="<font style='color: green;'>Present</font>";
								}
								else
								{
									$show ="<font style='color: red;'>Absent</font>";
									$abs_count++;
								}
								}
								else
								{
									$show ="Unmarked";
								}
								}
								else
								{
									$show ="NA";
								}
								
								$html_excel = $html_excel.'<td>'.$show.'</td>';
							 }
							if($if_fraction_show==1) { 
							if($count_row>0)
								{ if(in_array($decimal_show,$hour_arr)==true) { $show_decimal = "<font style='color: green;'>Present</font>" ; } else { $show_decimal = "<font style='color: red;'>Absent</font>"; 
							$abs_count = ($abs_count+$decimal_show);  } } else { $show_decimal = "Unmarked"; }
							
							$html_excel	= $html_excel.'<td>'.$show_decimal.'</td>';
							 } else { $html_excel = $html_excel . '<td>NA</td>'; } 
							$html_excel = $html_excel.'<td style="padding-left:50%!important;">'.$abs_count.'</td>
							<td>'.$sess->comments.'</td>
								</tr>';
						 } 
							
								
						$html_excel = $html_excel.
						'</tbody>
					</table>';


    } else { $html_excel = $html_excel.'<table><tr><td style="color: green!important;">No attendances are found!</td></tr></table>';
    } 
		 
     
     
  $html_excel = $html_excel.'</td></tr><tr><td>&nbsp;</td></tr>';
} 
} 

$html_excel = $html_excel.	
'</table>';

       
            
$file_name ="attendances_".@$userdetails->firstname." ".@$userdetails->lastname.".xls";
$excel_file=$html_excel;
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file_name");
echo $excel_file;
	
	
	die; 
	
}
else
{
	///
}
}

?>