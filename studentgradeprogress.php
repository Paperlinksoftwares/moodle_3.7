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

//require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
$courseid = optional_param('id', SITEID, PARAM_INT);
//$userid   = optional_param('userid', $USER->id, PARAM_INT);
$userid   = 931;

$progress = array();


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
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}


$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;


  
        
        $report = new grade_report_overview($userid, $gpr, $context);
        
        
		   
	
            if ($report->generate_table_data()) {
                //echo '<br />'.$report->print_table(true);
                $data_all = $report->generate_table_data();
             
                //echo '<pre>';
                //print_r($data_all);
				
				foreach($data_all as $kk=>$vv)
				{
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",trim($vv[0]));
					
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
				}
	
	
	$course_name = array(); 
	foreach($data_all_new as $mm=>$pp) 
	{ 
	
	foreach($pp as $key=>$val) 
	{ 
	
	

$context = context_course::instance($val[2]->id);


         
         $contextid = context_course::instance($val[2]->id);
    $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '   
            .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id AND app.userid = '.$userid                   
            .' WHERE z.userid = '.$userid.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = '.$userid.') GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' , count(app.id) as countsubmission FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.id = (SELECT max(`id`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 .' LEFT JOIN {assign_submission} as app ON app.assignment = ax.id AND app.userid = '.$userid                   
            .' WHERE z.userid = '.$userid.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = '.$userid.') GROUP BY z.assignment , z.userid ';
    
    
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}
//echo '<pre>';
//print_r($scale_array);
//echo $sql_all;
//echo '<hr>';
//echo $sql;
//$list_all = $DB->get_records_sql($sql_all);

//$list_all_count = count($list_all);

$list = $DB->get_records_sql($sql);

$arr = array();
//echo '<pre>';
//print_r($list); 



foreach($list as $list)
{
	$unit_name = $list->assignmentname;
    $sql_module = "SELECT id , deletioninprogress FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
//	echo '<pre>';
//	print_r($list_module);
	if($list_module->deletioninprogress==0)
	{
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'unit_id'=>$list->courseid,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>@$list->feedbackposted1,"feedbackposted2"=>@$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'unit_id'=>$list->courseid,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'unit_id'=>$list->courseid,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'unit_id'=>$list->courseid, 'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate,'countsubmission'=>$list->countsubmission);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
}
            
         
      //   echo '<hr>';
      //   echo '<pre>';
      //  print_r($arr);
if($list_all_count>0) {
   //echo '<pre>';
   // print_r($arr);
    
         ?>
        

        
  <?php $cn = 0; $ct = 0; 
  
  foreach($arr as $key=>$val) 
  { 

	  //$unit_name = $val[]
	  
	  if(strtolower($val['result'])=='not satisfactory' || strtolower($val['result'])=='na / not yet graded')
	  {
		$progress[$mm][$unit_name][] = 0;
	  }
	  else
	  {
		  $progress[$mm][$unit_name][] = 1;
	  } 
	  
	  ?>
      
    
<?php } } else { 

//$unit_id = $val['unit_id'];
$progress[$mm][$unit_name][] = 0;

    
         unset($context);  }  }  } ?>
  

<?php
                
            }
   

//echo '<pre>';
//print_r($progress);

$progress_new = array();
$progress_final = array();

foreach($progress as $q=>$a)
{
	foreach($a as $z=>$x)
	{
		
		if(in_array('0',$x)==true)
		{
			$progress_new[$q][$z]['progress']='incomplete';
		}
		else
		{
			$progress_new[$q][$z]['progress']='complete';
		}
	}
}
//echo '<pre>';
//print_r($progress_new);

foreach($progress_new as $v=>$n)
{
	$fail=0;
	$pass=0;
	foreach($n as $key=>$val)
	{
	if($val['progress']=='incomplete')
	{
		$fail++;
	}
	else if($val['progress']=='complete')
	{
		$pass++;
	}
	else
	{
		// do nothing
	}
	}
	$progress_final[$v]['total']=$pass+$fail;
	$progress_final[$v]['complete']=$pass;
	$progress_final[$v]['incomplete']=$fail;
	$progress_final[$v]['progress_rate']=round(($pass/($pass+$fail))*100,2);
	unset($pass);
	unset($fail);
	
}
//echo '<pre>';
//print_r($progress_final);

//$event = \gradereport_overview\event\grade_report_viewed::create(
//    array(
//        'context' => $context,
//        'courseid' => $courseid,
//        'relateduserid' => $userid,
//    )
//);
//$event->trigger();

		
		
?>



<!-- PI CHART CODE -->

<?php
$dataPoints = array();
$strArr = array();
$indexCount = 0;
foreach($progress_final as $key=>$val)
{
	$strArr = array("label"=>$key,"y"=>$val['progress_rate']);
	$dataPoints[$indexCount] = $strArr;
	unset($strArr);
	$indexCount++;
 
}




?>
<script>
window.onload = function() {
 
 CanvasJS.addColorSet("customColorSet1",
     [//colorSet Array
     "red",
     "green",
     "blue",
     "purple",
     "violet"
     
    ]);
	
	CanvasJS.addColorSet("customColorSet2",
     [//colorSet Array
     "orange",
     "cyan",
     "yellow",
     "pink",
     "bronze",
	 "blue"
    ]);
	
	
var chart = new CanvasJS.Chart("chartContainer", {
	theme: "light1",
	animationEnabled: true,
	colorSet:  "customColorSet1",
	title: {
		text: "Overview of Qualification Completion"
	},
	data: [{
		type: "pie",
		indexLabel: "{y}",
		
		yValueFormatString: "#,##0.00\"%\"",
		indexLabelPlacement: "inside",
		indexLabelFontColor: "#000",
		indexLabelFontSize: 14,
		indexLabelFontWeight: "bold",
		showInLegend: false,
		legendText: "{label}",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();


var chart2 = new CanvasJS.Chart("chartContainer2", {
	theme: "light1",
	colorSet:  "customColorSet2",
	animationEnabled: true,
	title: {
		text: "Overview of Total Academic Progress"
	},
	data: [{
		type: "pie",
		indexLabel: "{y}",
		yValueFormatString: "#,##0.00\"%\"",
		indexLabelPlacement: "inside",
		indexLabelFontColor: "#000",
		indexLabelFontSize: 14,
		indexLabelFontWeight: "bold",
		showInLegend: true,
		legendText: "{label}",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart2.render();
 
}
</script>
<table>
<tr>
<td>
<div id="chartContainer" style="height: 270px; width: 390px;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</td>
<td>
<div id="chartContainer2" style="height: 260px; width: 390px;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</td>
</tr>
</table>


<!-- END -->
