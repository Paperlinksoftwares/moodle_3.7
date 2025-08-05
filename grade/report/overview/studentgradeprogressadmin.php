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

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
$courseid = optional_param('id', SITEID, PARAM_INT);
//$userid   = optional_param('userid', $USER->id, PARAM_INT);
//$userid   = 931;
//$userid = $USER->id;

$progress = array();


global $USER;
global $CFG;

if(isset($_GET['userid']) && $_GET['userid']>0)
{
$userid = $_GET['userid'];
}
else
{
	header("Location: index.php");
	exit();
}

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
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

<script>
$(document).ready(function(){
  $('.fail').on('click',function(){
    $('#fail h1,#fail p,#fail .fail').css({display:'none'});

    $('#fail').animate({
      width:'0',
    },250,function(){
      $('#fail .icon').animate({
        borderRadius:'50%',
      },250,function(){

        $('#fail .icon').animate({
          opacity:0
        },250);
      });
    });
  });
  $('.succ').on('click',function(){
    $('#success h1,#success p,#success .succ').css({display:'none'});

    $('#success').animate({
      width:'0',
    },250,function(){
      $('#success .icon').animate({
        borderRadius:'50%',
      },250,function(){

        $('#success .icon').animate({
          opacity:0
        },250);
      });
    });
  });
  $('button').on('click',function(){
    $('section').css({width:'400px'});
    $('section h1,section p,section i').css({display:'block'});
    $('section .icon').css({
      borderRadius:'0',
      opacity:1
    })
  });
});

</script>
<?php
echo "<style>
@import url(https://fonts.googleapis.com/css?family=Lato);

.wrap {
  padding-left: 1.4em;
  padding-right: 1.4em;
  margin:0;
  padding: 0;
  box-sizing: border-box;
  
  max-width: 900px;
  margin: 1em auto 2em;
  font-family: Lato, Arial;
}
h1 {
  text-align: center;
  border: 2px solid #ddd;
  padding: 1em;
  margin-bottom: 1em;
  
}

h2 {
  border-bottom: 1px solid #ddd;
}

a {
  text-decoration: none;
  color: #2ecc71;
}
a:hover {
  color: #27ae60;
}


/* Alert Box CSS */

/* Base Alert Style */
.alert {
    position: relative;
    display: block;
    padding: 1em 1.8em;
    font-size: 18px;;
    font-weight: 300;
    line-height: 1.2;
    text-align: left;
    margin-top: 0.4em;
    margin-bottom: 0.4em;
    background: transparent;
    color: white;
}

/* Error */ 
.alert-error { 
  background: #e74c3c; 
  border: 1px solid #c0392b; 
}

/* Success */ 
.alert-success { 
  background: #2ecc71; 
  border: 1px solid #27ae60; 
}

/* Info */ 
.alert-info { 
  background: #3498db; 
  border: 1px solid #2980b9; 
}

/* Radius */
.radius {
  border-radius: 3px;
}

</style>";
$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;


  
        
        $report = new grade_report_overview($userid, $gpr, $context);
        
        
		   
	
            if ($report->generate_table_data()) {
                //echo '<br />'.$report->print_table(true);
                $data_all = $report->generate_table_data();
             
               
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
					
					/*if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					*/
					
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
	
	$unit_name = $val[2]->fullname;

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




foreach($list as $list)
{
	
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
        
if($list_all_count>0) {

	
    
         ?>
        

        
  <?php $cn = 0; $ct = 0; 

  foreach($arr as $key=>$val) 
  { 

	  //$unit_name = $val[]
	// echo $unit_name;
	// echo '<br/>';
	  
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
   



$progress_new = array();
$progress_final = array();
$progress_final2 = array();

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

//ADDITION

$progress_new_extra = array();

foreach($progress_new as $p2=>$ss2)
{
	foreach($ss2 as $zz2=>$xx2)
	{
		if(strpos($zz2,'SUPERVISED')==true || strpos($zz2,'supervised')==true || strpos($zz2,'Supervised')==true)
		{
			$ex = explode("-",$zz2);
			$progress_new_extra['supervised'][trim($ex[0])]['progress']=$xx2['progress'];
			unset($ex);
		}
		if(strpos($zz2,'SCV')==true)
		{
			$ex = explode("-",$zz2);
			$progress_new_extra['scv'][trim($ex[0])]['progress']=$xx2['progress'];
			unset($ex);
		}
	}
}

/*
echo '<pre>';
print_r($progress_new);

echo '<hr>';

echo '<pre>';
print_r($progress_new_extra);

echo '<hr>';
*/
$progress_new_revised = array();
foreach($progress_new as $p=>$ss)
{
	if($p!='ICT50220 - Diploma of Information Technology')
	{
	foreach($ss as $zz=>$xx)
	{
		$zz=trim($zz);
		if(strpos($zz,'SCV')==false && strpos($zz,'SUPERVISED')==false && strpos($zz,'supervised')==false && strpos($zz,'Supervised')==false)
		{
			
			$progress_new_revised[$p][$zz]['progress']=$xx['progress'];
			if($xx['progress']=='incomplete')
			{
				
				foreach($progress_new_extra['supervised'] as $f=>$t)
				{
					
					$f = trim($f);
					$f_code = explode(" ",$f);
					$zz_code = explode(" ",$zz);

					if ($zz_code[0] == $f_code[0] && trim($t['progress'])=='complete') 
					{					
						$progress_new_revised[$p][$zz]['progress']='complete';
						
						
					}
					unset($f);
					unset($f_code);
					unset($zz_code);
					
				}
				
				foreach($progress_new_extra['scv'] as $f2=>$t2)
				{
					$f2 = trim($f2);
					$f2_code = explode(" ",$f2);
					$zz2_code = explode(" ",$zz);
					if ($zz2_code[0] == $f2_code[0] && trim($t2['progress'])=='complete') 
					{
						$progress_new_revised[$p][$zz]['progress']='complete';
						
					}
					unset($f2);
					unset($f2_code);
					unset($zz2_code);
					
				}
				
			
				
				
				
				
			}
			else
			{
				$progress_new_revised[$p][$zz]['progress']=$xx['progress'];
			}
			
		}
	}
	}
}



///END///



foreach($progress_new_revised as $v=>$n)
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

	
	if($v=='BSB30220 Certificate III in Entrepreneurship and New Business')
	{
		$total = 10;
	}
	else if($v=='BSB40120 Certificate IV in Business')
	{
		$total = 12;
	}
	else if($v=='BSB50120 Diploma of Business (Business Development)')
	{
		$total = 12;
	}
	else if($v=='BSB60120 Advanced Diploma of Business')
	{
		$total = 10;
	}
	else if($v=='CPC30620 Certificate III in Painting and Decorating')
	{
		$total = 29;
	}
	else if($v=='BSB50815 Diploma of International Business')
	{
		$total = 8;
	}
	else if($v=='BSB41115 Certificate IV in International Trade')
	{
		$total = 10;
	}
	else if($v=='BSB30315 - Certificate III in Micro Business Operations')
	{
		$total = 10;
	}
	/*else if($v=='ICT50220 - Diploma of Information Technology')
	{
		$total = 10;
	}*/
	else
	{
		//do nothing
	}
	
	
	$progress_final[$v]['total']=$total;
	$progress_final[$v]['complete']=$pass;
	$progress_final[$v]['incomplete']=$fail;
	$progress_final[$v]['progress_rate']=round(($pass/($pass+$fail))*100,2);
	unset($pass);
	unset($fail);
	
}

foreach($progress_new_revised as $v2=>$n2)
{
	$fail=0;
	$pass=0;
	foreach($n2 as $key=>$val)
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
	
	if($v2=='BSB30220 Certificate III in Entrepreneurship and New Business')
	{
		$total = 10;
	}
	else if($v2=='BSB40120 Certificate IV in Business')
	{
		$total = 12;
	}
	else if($v2=='BSB50120 Diploma of Business (Business Development)')
	{
		$total = 12;
	}
	else if($v2=='BSB60120 Advanced Diploma of Business')
	{
		$total = 10;
	}
	else if($v2=='CPC30620 Certificate III in Painting and Decorating')
	{
		$total = 29;
	}
	else if($v2=='BSB50815 Diploma of International Business')
	{
		$total = 8;
	}
	else if($v2=='BSB41115 Certificate IV in International Trade')
	{
		$total = 10;
	}
	else if($v2=='BSB30315 - Certificate III in Micro Business Operations')
	{
		$total = 10;
	}
	/*else if($v2=='ICT50220 - Diploma of Information Technology')
	{
		$total = 10;
	}*/
	else
	{
		//do nothing
	}
	
	
	$progress_final2[$v2]['total']=$total;
	$progress_final2[$v2]['complete']=$pass;
	$progress_final2[$v2]['incomplete']=$fail;
	$progress_final2[$v2]['progress_rate']=round(($pass/$total)*100,2);
	unset($pass);
	unset($fail);
	unset($total);
	
	
}


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

$dataPoints2 = array();
$strArr2 = array();
$indexCount2 = 0;
foreach($progress_final as $key=>$val)
{
	if($key!='NA')
	{
	$strArr = array("label"=>$key,"y"=>$val['progress_rate']);
	$dataPoints[$indexCount] = $strArr;
	unset($strArr);
	$indexCount++;
	}
 
}

foreach($progress_final2 as $key2=>$val2)
{
	if($key2!='NA')
	{
	$strArr2 = array("label"=>$key2,"y"=>$val2['progress_rate']);
	$dataPoints2[$indexCount2] = $strArr2;
	unset($strArr2);
	$indexCount2++;
	}
 
}
/*
echo '<pre>';
print_r($progress_final);

echo '<pre>';
print_r($progress_final2);
*/

if (count($dataPoints2)==1 && $dataPoints2[0]['y']==0)
{
	?>
	<br/>
	<br/>
	<br/>
<div class="wrap">
  
  <h2>Notification Message</h2>
  <!-- Red Color -->
  <div class="alert alert-error radius">
  No Academic Progress is Found! Please contact Academic Department for more information.
  </div>

</div>


	<?php
	exit();
}

$userdetails = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
$other_details1 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('15')");
            $other_details2 = $DB->get_record_sql("SELECT `data`  FROM `mdl_user_info_data` WHERE `userid` = '".$userdetails->id."' AND `fieldid` IN('1')");
            

?>
<script>
window.onload = function() {
 
 CanvasJS.addColorSet("customColorSet1",
     [//colorSet Array
     "#03a2b7",
     "#6ad89d",
     "#c28bf9",
     "#91c968",
     "#dbdb41",
	 "#eaaa4f",
	 "#FAC87E",
	 "#F5F583",
	 "#A8D08D"
     
    ]);
	
	CanvasJS.addColorSet("customColorSet2",
     [//colorSet Array
     "#03a2b7",
     "#6ad89d",
     "#c28bf9",
     "#91c968",
     "#dbdb41",
	 "#eaaa4f",
	 "#FAC87E",
	 "#F5F583",
	 "#A8D08D"
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
		indexLabelFontSize: 13,
		indexLabelFontWeight: "bold",
		showInLegend: true,
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
		indexLabelFontSize: 13,
		indexLabelFontWeight: "bold",
		showInLegend: true,
		legendText: "{label}",
		dataPoints: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
	}]
});
chart2.render();
 
}
</script>
<table style="width: 100%;">

<tr>
<td width="10%">&nbsp;</td>
<td width="80%" colspan="4">&nbsp;<?php

echo '<div style="border-radius: 17px;
  border: 2px solid #73AD21;
  padding: 14px; 
  margin-top:13px;
  margin-bottom:18px;
  width: 80%;
  height: 101px;">
   <strong>Name</strong> : '.$userdetails->firstname.' '.$userdetails->lastname.'<br><div style="height:6px;"></div>
 
  <strong>Email</strong> : <a href="mailto: '.$userdetails->email.'">'.$userdetails->email.'</a><br><div style="height:6px;"></div>'.
                    '<strong>Gender</strong>: '; if($other_details1->data!='') { echo $other_details1->data; } else { echo 'NA'; } echo '<br><div style="height:6px;"></div>'.
        '<strong>Phone</strong>: '; if($other_details2->data!='') { echo $other_details2->data; } else { echo 'NA'; } echo '</div>';
		
		?></td>
		<td width="10%">&nbsp;</td>
		</tr>
<tr>
<td width="10%">&nbsp;</td>
<td width="40%">
<div id="chartContainer" style="height: 490px; width: 480;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</td>
<td>&nbsp;</td>
<td width="40%">
<div id="chartContainer2" style="height: 490px; width: 480px;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</td>
<td width="10%">&nbsp;</td>
</tr>
<tr>
<td colspan="2" height="29">&nbsp;</td>
</tr>
<tr>
<td width="10%">&nbsp;</td>
<td width="40%"><p style="color: blue; font-family: 'Lucida Sans', Arial, sans-serif; font-size: 14px; line-height: 20px; text-indent: 0px; margin: 0; font-weight: bold;">
Tracks progress based on enrolment records, completed and remaining courses to ensure timely completion. Pass rate calculated based on total units.</p></td>
<td>&nbsp;</td>
<td width="40%"><p style="color: blue; font-family: 'Lucida Sans', Arial, sans-serif; font-size: 14px; line-height: 20px; text-indent: 0px; margin: 0; font-weight: bold;">
A thorough review of academic performance including completed qualifications and ongoing studies. Pass rate calculated based on enrolled units.</p></td>
<td width="10%">&nbsp;</td>
</tr>
<tr>
<td colspan="2" height="29">&nbsp;</td>
</tr>
</table>


<!-- END -->
