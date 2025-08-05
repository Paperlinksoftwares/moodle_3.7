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

require_once '../../config.php';

global $USER;
global $CFG;
global $PAGE;
global $DB;







  			$course_info = $DB->get_records_sql("SELECT * FROM mdl_course WHERE `id` = '".$_GET['courseid']."'");
  
				//echo '<pre>';
				//print_r($course_extra);
				
				
				$sessions = $DB->get_records_sql("SELECT session.* , session.id as sessionid, attendance.id as aid, attendance.course as a_course 
				FROM mdl_attendance_sessions as session 
				LEFT JOIN mdl_attendance as attendance
ON session.attendanceid = attendance.id WHERE attendance.course = '".$_GET['courseid']."'" );
   
  // echo '<pre>';
	//			print_r($session_details);
				
   
   		
				
		
              //  echo '<pre>';
              //  print_r($data_all);
			  $html_excel='';
			  if(count($sessions)>0) {
                 $html_excel = $html_excel.'<div>Attendances for <font style="color: black!important; font-weight:bold;">'.$_GET['coursename'].'</font></div><br/>';
      foreach($sessions as $key=>$val) {  
	
	
    
     $html_excel = $html_excel.'<br/><div><font style="color: green!important;font-weight: bold;">'.@gmdate("jS F Y", $val->sessdate).'</font></div><br/>';
    
	
	//NO LEFT JOIN ONLY TO STUDENTS SPECIFIC RECORDS- ADDING LEFT IN JOIN IN LOG OURS SHOW ENTIRE DATA with student as well.
       $session_details = $DB->get_records_sql('SELECT loghours.* , session.id as sessionid, session.duration as duration, users.* , loghours.hours as hours , 
	   loghours.absenthours as absenthours , loghours.comments as comments FROM mdl_attendance_log_hours as loghours LEFT JOIN mdl_attendance_sessions as session 
	   ON loghours.sessionid = session.id 
	   LEFT JOIN mdl_user as users ON loghours.studentid = users.id 
WHERE session.id = '.$val->sessionid);
   $session_time_max = $DB->get_record_sql('SELECT max(session.duration) as sess_duration FROM mdl_attendance_sessions as session JOIN mdl_attendance as attendance
ON session.attendanceid = attendance.id WHERE session.id = '.$val->sessionid);
$multiple_hours = ($session_time_max->sess_duration/3600);
$if_fraction=0;
if(fmod($multiple_hours, 1) !== 0.00){ $if_fraction=1; }

   $decimal = $multiple_hours - floor($multiple_hours);
	
	
$html_excel = $html_excel.'<table style="border: 1px solid #aaa!important;">
						<thead>
							<tr >
							<th ></th>
							
							<th style="text-align: center!important;">Absent Hours</th>
							<th style="text-align: center!important;">Present Hours</th>
							
							<th style="text-align: center!important;">Online Presence</th>
							<th style="text-align: center!important;">Online Absence</th>
							
							<th style="text-align: center!important;">Online Presence Net</th>
							<th style="text-align: center!important;">Star Entry</th>
							
							<th style="text-align: center!important;">Remark</th>
							
							</tr>
						</thead>
						<tbody>';
						 foreach($session_details as $key=>$sess) { 
						
						$count_row = $DB->count_records_sql("SELECT COUNT(`id`) FROM {attendance_log_hours} WHERE `sessionid`='".$sess->sessionid."'");
						
						//online attendnace details 
						
						$dates_arr = array();
						$dates_arr['start'] = strtotime('Last Monday', $val->sessdate);
						$dates_arr['end'] = strtotime('Next Sunday', $val->sessdate);
						
						
						$online_att = $DB->get_record_sql("select ar.course, ar.id as ar_id, SUM(ass.duration) as duration FROM mdl_attendanceregister as ar 
						LEFT JOIN mdl_attendanceregister_session as ass ON ass.register = ar.id AND ass.userid = '".$sess->studentid."'
						WHERE ( ass.login BETWEEN '".$dates_arr['start']."' AND '".$dates_arr['end']."' ) 
						AND ( ass.logout BETWEEN '".$dates_arr['start']."' AND '".$dates_arr['end']."' ) AND ar.course = '".$_GET['courseid']."'");


						$online_att = (array)$online_att;
						if(@$online_att['duration']>0) { 
						$init = $online_att['duration'];
						$online_hours = floor($init / 3600);
						$online_minutes = floor(($init / 60) % 60);
						$online_seconds = $init % 60;
						$online_attendnace = $online_hours ." hours ".$online_minutes." Minutes ".$online_seconds." Seconds";
						$online_attendnace_net = $online_hours + round(($online_minutes/60),2);
						$online_diff = 18000 - $init;
						if($online_diff<0)
						{
							$online_absence = 0;
						}
						else
						{
							$online_absence = floor($online_diff / 3600) ." hours ".(floor(($online_diff / 60) % 60))." Minutes ".($online_diff % 60)." Seconds";
						
						}
						
						} else { $online_attendnace = 0; $online_absence = "5 hours"; }

						$duration_hours = ($sess->duration/3600); 
						$if_fraction_show = 0;
						if(fmod($duration_hours, 1) !== 0.00){ $if_fraction_show=1; }

   $decimal_show = $duration_hours - floor($duration_hours);
						$html_excel=$html_excel.'<tr >
								<td >
								<font style="color: #000!important; font-weight: bold!important;">
								
								'.$sess->firstname." ".$sess->lastname.'</font>

							</td>';
							 
							//echo '<pre>';
							//print_r($session_details);
							
							//echo '<pre>';
							//print_r($session_details);
							$abs_count=0;
							$pres_count = 0;
							for($k=1;$k<(intval($multiple_hours)+1);$k++)
							{
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
									
									$pres_count++;
								}
								else
								{
									
									$abs_count++;
								}
								}
								
								}
								
								}
							if($if_fraction_show==1) 
							{ 
							if($count_row>0)
								
								{ 
								if(in_array($decimal_show,$hour_arr)==true) 
								{  
							$pres_count = $pres_count+$decimal_show; 
							} 
							else 
							{ 
						 $abs_count = ($abs_count+$decimal_show);  
						 } 
						 } 
						 
							 } 
							
							
							
							
							
							$html_excel = $html_excel.'
							<td style="text-align: center!important;">'.$abs_count.'</td>
							<td style="text-align: center!important;">'.$pres_count.'</td>
							
							<td style="text-align: center!important;">'.$online_attendnace.'</td>
							<td style="text-align: center!important;">'.$online_absence.'</td>
							
							<td style="text-align: center!important;">'; 
							if($online_attendnace_net>0) { $html_excel = $html_excel.$online_attendnace_net; } else { $html_excel = $html_excel.'0'; } 
							
							$html_excel = $html_excel.'</td>
							<td style="text-align: center!important;">'.($online_attendnace_net+$pres_count).'</td>
							
							<td>'.$sess->comments.'</td>
								</tr>';
						 unset($online_hours); 
						unset($online_minutes);
						unset($online_seconds);
						unset($init);
						unset($online_attendnace);
						unset($online_attendnace_net);
						} 
							
						$html_excel = $html_excel.'		
						
						</tbody>
					</table>';
			


			   } 
     
  $html_excel = $html_excel.'<br/><br/>';

                
            }
			
			
			  else { $html_excel=$html_excel.'<div style="height:9px;"></div>
<div class="info-msg">No attendances are found under <font style="color: black!important; font-weight:bold;">'.$_GET['coursename'].'</font></div>
		 <div style="height:3px;"></div>';
	 } 

	// $file_name =$_GET['coursename']."_attendances.xls";
	$file_name_1 = str_replace(" ","_",$_GET['coursename']);
	
	$file_name = "_attendances.xls";
$excel_file=$html_excel;
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file_name");
echo $excel_file;
die; 
?>
