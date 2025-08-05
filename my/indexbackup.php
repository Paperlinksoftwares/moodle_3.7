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
 * My Moodle -- a user's personal dashboard
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - only the user can see their own dashboard
 * - users can add any blocks they want
 * - the administrators can define a default site dashboard for users who have
 *   not created their own dashboard
 *
 * This script implements the user's view of the dashboard, and allows editing
 * of the dashboard.
 *
 * @package    moodlecore
 * @subpackage my
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
global $DB;
global $USER;
global $SESSION;
$not_to_check_array = array(867,941,858,1009,980,94,316,335,128,967);
if(isset($_GET['set']) && $_GET['set']==1)
{
    $SESSION->set=$_GET['set'];
    ?>
    <script> window.location.href='<?php echo $CFG->wwwroot; ?>'; </script>
    <?php
}
//echo @$SESSION->enrolmentform_stat; die;
if(!isset($_GET['showlast']))
{
    $sql_popup = "SELECT `status` FROM `mdl_user_popup` WHERE `userid` = '".@$USER->id."'";
    $list_pop_config = $DB->get_record_sql($sql_popup); 
}
if(isset($_GET['popupdisplay']) && $_GET['popupdisplay']==1)
{
    $sql_popup = "SELECT `status` FROM `mdl_user_popup` WHERE `userid` = '".@$USER->id."'";
    $list_pop_setting = $DB->get_record_sql($sql_popup); 
    if($list_pop_setting->status!='')
    {
        $u1 = $DB->execute("UPDATE mdl_user_popup SET status = '0' WHERE `userid` = '".@$USER->id."'"); 
    }
    else
    {
        $u2 = $DB->execute("INSERT INTO `mdl_user_popup`(`id`,`userid`,`status`) VALUES(NULL, '".@$USER->id."' , '0')"); 
    }
    if($u1 || $u2)
    {
        ?>
        <script> window.location.href='index.php?showlast=1'; </script>
        <?php
    }
}
if((!isset($SESSION->set) && $SESSION->set!=1) && !in_array($USER->id,$not_to_check_array))
{
if($list_pop_config->status==1 || $list_pop_config->status=='' || isset($_GET['showlast']))
{
$sql = "SELECT uen.enrolid , en.courseid from {user_enrolments} as uen LEFT JOIN {enrol} as en ON en.id = uen.enrolid LEFT JOIN {course} as course ON course.id = en.courseid WHERE uen.userid = '".@$USER->id."' AND ( MONTH(FROM_UNIXTIME(course.startdate)) = '7' OR MONTH(FROM_UNIXTIME(course.startdate)) = '8' OR MONTH(FROM_UNIXTIME(course.startdate)) = '9') AND YEAR(FROM_UNIXTIME(course.startdate)) = '2020'";
$list_records = $DB->get_records_sql($sql); 
$row_assign_id=array();
foreach ($list_records as $key=>$val)
{
    $row1=$DB->get_records_sql("SELECT id FROM {assign} as ass WHERE ass.grade != '0' AND ass.course = '".$val->courseid."' AND ass.duedate!='0' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s') >= NOW() AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s')< NOW() + INTERVAL 7 DAY");    
    
    foreach($row1 as $m=>$n)
    {
        if($n->id!='')
        {
            $row_assign_id[$val->courseid][]=$n->id;
        }
    }
    unset($row1); 
}
$row_assign_id2=array();
foreach ($list_records as $key=>$val)
{
    $row2=$DB->get_records_sql("SELECT id FROM {assign} as ass WHERE ass.grade != '0' AND ass.course = '".$val->courseid."' AND ass.duedate!='0' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s') < NOW()");    
    
    foreach($row2 as $m=>$n)
    {
        if($n->id!='')
        {
            $row_assign_id2[$val->courseid][]=$n->id;
        }
    }
    unset($row2); 
}

$row_assign_id3=array();
foreach ($list_records as $key=>$val)
{
    $row3=$DB->get_records_sql("SELECT id FROM {assign} as ass WHERE ass.grade != '0' AND ass.course = '".$val->courseid."' AND ass.duedate!='0' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s')< NOW() - INTERVAL 7 DAY");    
    
    foreach($row3 as $m=>$n)
    {
        if($n->id!='')
        {
            $row_assign_id3[$val->courseid][]=$n->id;
        }
    }
    unset($row3); 
}
$list_assign_id = array();
$open_modal = false;
foreach($row_assign_id as $key=>$arrval)
{
    foreach($arrval as $x=>$y)
    {
      //  $rows1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".@$USER->id."'");
        $rows2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".@$USER->id."' AND ass.status = 'submitted'");

      //  if($rows1->count==0 || $rows2->count==0)
       // {
         //   $open_modal = true;
          //  $list_assign_id[$key][]=$y;
        //}        
        if($rows2->count==0)
        {
            $open_modal = true;
            $list_assign_id[$key][]=$y;
        }
        else
        {
            //do nothing
        }
      //  unset($rows1);
        unset($rows2); 
    }
    
}

$list_assign_id2 = array();
$open_modal2 = false;
foreach($row_assign_id2 as $key=>$arrval)
{
    foreach($arrval as $x=>$y)
    {
       // $rows1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".@$USER->id."'");
        $rows2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".@$USER->id."' AND ass.status = 'submitted'");

       // if($rows1->count==0 || $rows2->count==0)
       // {
         //   $open_modal2 = true;
          //  $list_assign_id2[$key][]=$y;
        //}        
        if($rows2->count==0)
        {
            $open_modal2 = true;
            $list_assign_id2[$key][]=$y;
        }
        else
        {
            //do nothing
        }
        //unset($rows1);
        unset($rows2); 
    }
    
}

$list_assign_id3 = array();
$open_modal3 = false;
foreach($row_assign_id3 as $key=>$arrval)
{
    foreach($arrval as $x=>$y)
    {
      //  $rows1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".@$USER->id."'");
        $rows2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".@$USER->id."' AND ass.status = 'submitted'");

     //   if($rows1->count==0 || $rows2->count==0)
      //  {
        //    $open_modal3 = true;
          //  $list_assign_id3[$key][]=$y;
        //}        
        if($rows2->count==0)
        {
            $open_modal3 = true;
            $list_assign_id3[$key][]=$y;
        }
        else
        {
            //do nothing
        }
       // unset($rows1);
        unset($rows2); 
    }
    
}
}
//echo '<pre>';
//print_r($list_assign_id);
//die;

$sql_form_settings = "SELECT `setting` FROM `mdl_form_settings` WHERE `formid` = '1'";
$row_form_settings = $DB->get_record_sql($sql_form_settings);
$modal_open = false;
//echo '<pre>';
//print_r($row_form_settings);
if(@$row_form_settings->setting=='' || @$row_form_settings->setting!=0)
{
//$sql_form_check = "SELECT count(`id`) as countrow FROM `mdl_questionnaire_response` WHERE `questionnaireid` = '408' AND `userid` = '".$USER->id."' AND `complete` = 'Y'";
$sql_form_check = "SELECT count(`id`) as countrow FROM `mdl_questionnaire_response` WHERE `questionnaireid` = '1245' AND `userid` = '".$USER->id."' AND `complete` = 'Y'";

$form_check = $DB->get_record_sql($sql_form_check);



//AVETMISS FORM

$sql_form_check2 = "SELECT count(`id`) as countrow FROM `mdl_questionnaire_response` WHERE `questionnaireid` = '362' AND `userid` = '".$USER->id."' AND `complete` = 'Y'";

$form_check2 = $DB->get_record_sql($sql_form_check2);


//ZOHO ENROLMENT FORM


$sql_form_check3 = "SELECT count(`id`) as countrow FROM `zoho_forms` WHERE `form_name` = 'EnrollmentForm' AND `userid` = '".$USER->id."' AND `term` = '".@date('Y')."|3'";

$form_check3 = $DB->get_record_sql($sql_form_check3);



if($form_check->countrow==0)
{
//$course_array = array('572','573','574','575','576','577','578','579','580','582','583','584','589','596','597','598','599');
//$course_array = array('638','637','635','636','634','633','632');
$course_array = array('638','637','635','636','634','633','632','611','612','613','614','615','616','617','618','619','620','621','622');
///This static mmanipulation of courses needs to be changed later. Its temporary.
$exclude_students = array('1035','1039','1037','1036','1002','952','1038','1034','1044','1043');
$modal_open = false;
$role = $DB->get_record('role', array('shortname' => 'student'));
foreach($course_array as $k=>$v)
{
    $context = get_context_instance(CONTEXT_COURSE, $v);
    $students = get_role_users($role->id, $context);
    foreach($students as $key=>$val)
    {
        if($USER->id==$val->id && !in_array($USER->id,$exclude_students))
        {
            $modal_open=true;
			$open1 = true;
        }
    }
    unset($context);
    unset($students);
}
}


if($form_check2->countrow==0)
{
//$course_array = array('572','573','574','575','576','577','578','579','580','582','583','584','589','596','597','598','599');
//$course_array2 = array('638','637','635','636','634','633','632');
$course_array2 = array('638','637','635','636','634','633','632','611','612','613','614','615','616','617','618','619','620','621','622');
///This static mmanipulation of courses needs to be changed later. Its temporary.
$exclude_students2 = array('1035','1039','1037','1036','1002','952','1038','933','928','1043','1045','1034','184','1044');


$sql_exclude = "SELECT `id`  FROM `mdl_user` WHERE `username` IN ('1385','1350','1431','1327','a001369','1085','1352','1370','1390','1435','1406','1358','1411','1372','a001305','a001164','1374','1394','a001539','1424','1379','1429','1367','1452','1402','1436','1445','1376','1420','a001253','1366','1321','1332','1347','1344','1334','a001254','1449','a001214','1348','a001555','1368','1395','a1086','1427','1371','a001079','1417','a001647','1291','1064','a001548','1354','a001328','a001320','a001333','1065','a001337','a001112','a001310','1441','1052','1353','a001344','a001334','1423','a001247','1382','1373','a001330','a001048','1381','1360','1346','1387','a001352','1338','1444','a001642','a001329','a001338','a001342','1331','1279','1386','a001301','1403','1378','a001747','1362','1322','1428','1355')";
$row_exclude = $DB->get_records_sql($sql_exclude);
foreach($row_exclude as $m=>$n)
{
	$exclude_students2[] = $n->id;	
}


$role = $DB->get_record('role', array('shortname' => 'student'));
foreach($course_array2 as $k=>$v)
{
    $context2 = get_context_instance(CONTEXT_COURSE, $v);
    $students2 = get_role_users($role->id, $context2);
    foreach($students2 as $key=>$val)
    {
        if($USER->id==$val->id && !in_array($USER->id,$exclude_students2))
        {
            $modal_open=true;
			$open2 = true;
        }
    }
    unset($context2);
    unset($students2);
}
}



//ZOHO ENROLENT FORM

if($form_check3->countrow==0)
{
	//$all_students_string = '1493,1499,1498,1431,1485,1331,1362,1419,1379,1291,1355,1346,1376,1420,1256,1480,1338,1395,1460,1045,1390,1413,1445,1421,1378,a001344,1441,1487,a001747,1429,1451,1384,1484,a001342,1354,1433,1438,1334,1450,1399,1456,1410,1496,1396,1453,1442,1352,1349,1388,1357,1465,1363,a001555,1353,1404,a001328,a001329,1406,1425,a001338,a000493,1435,1435,1423,1416,1417,1422,1385,1347,1428,1279,1430,1430,a001647,a001358,1467,1446,a001330,1327,1466,1436,1494,1386,1412,1401,1462,a001079,1459,a001352,1394,a001337,1427,1452,a001539,1411,1360,1495,1392,1389,1402,1448,1477,1426,a001164,1408,1322,a001334,1479,1474';
	$all_students_string = '1485,1370,1488,1499,1331,1419,1379,1355,1376,1376,1420,1256,1480,1338,1395,1460,1045,1390,1413,1445,1378,1441,1487,a001747,1429,1384,1484,a001342,1473,1433,1438,1334,1450,1493,1399,1456,1496,1396,1453,1442,1352,1349,1388,1357,1465,1404,a001328,a001329,1406,1424,1425,a000493,1435,1423,1416,1422,a001412,1385,1347,1428,1279,1430,a001647,a001358,1467,1446,1498,1327,1466,1436,a001413,1489,1494,1386,1462,a001079,1459,1394,1427,1452,a001539,1411,1360,1495,1392,1389,1402,1448,1477,1490,1426,1408,1472,1479,1474';
	$all_students_string_explode = explode(",",$all_students_string);
	$all_students_id = array();
	foreach($all_students_string_explode as $j=>$v)
	{
		$sql_exclude_3 = "SELECT `id`  FROM `mdl_user` WHERE `username` = '".$v."'";
		$row_exclude_3 = $DB->get_record_sql($sql_exclude_3);
		$all_students_id[] = $row_exclude_3->id;
		unset($sql_exclude_3);
		unset($row_exclude_3);
	}
//$exclude_students_3 = array('946','954','985','1007','1046','1049','1053','1054','1042','1056','840','632');
$exclude_students_3 = array();
if(in_array($USER->id,$all_students_id)==true && in_array($USER->id,$exclude_students_3)==false)
{

            $modal_open=true;
			$open3 = true;
       
}

}
}
//$uidcheck = $checkval->uid;

?>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<?php
if($modal_open==true)
{
    ?>
<script>
     
    $(window).load(function()
{
    
    
   $('#myModal').modal('show');
  
});

</script>
<?php } ?>
<?php
if(($open_modal==true || $open_modal2==true) && ( $list_pop_config->status==1 || $list_pop_config->status==''))
{
    ?>
<style>
    .agree
    {
        border: 1px solid #ff0099;
        background-color: red;
    }
</style>
<style>
.button {
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  padding: 3px 2px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 18px;
  margin: 4px 2px;
  -webkit-transition-duration: 0.4s; /* Safari */
  transition-duration: 0.4s;
  cursor: pointer;
  width: 188px;
}

.button1 {
  background-color: white; 
  color: black; 
  border: 2px solid #4CAF50;
}

.button1:hover {
  background-color: #4CAF50;
  color: white;
}

.button2 {
  background-color: white; 
  color: black; 
  border: 2px solid #008CBA;
}

.button2:hover {
  background-color: #008CBA;
  color: white;
}

.button3 {
  background-color: white; 
  color: black; 
  border: 2px solid #f44336;
}

.button3:hover {
  background-color: #f44336;
  color: white;
}

.button4 {
  background-color: white;
  color: black;
  border: 2px solid #e7e7e7;
}

.button4:hover {background-color: #e7e7e7;}

.button5 {
  background-color: white;
  color: black;
  border: 2px solid #555555;
}

.button5:hover {
  background-color: #555555;
  color: white;
}
</style>
<script>
     
    $(window).load(function()
{
    
    
   $('#assignment_check').modal('show');
  
});

</script>
<?php } ?>
<div class="container" style=" pointer-events: none; ">
  <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" style="display: none;"></button>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog" style="display: none;">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
         
            <h4 class="modal-title"><strong>Forms</strong></h4>
        </div>
        <div class="modal-body">
             <?php if(@$USER->id!='' && $open1==true) { ?>
                        
<p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/questionnaire/complete.php?id=28413">Enrolment Form
             </a></p> <?php } ?>
			 <?php if(@$USER->id!='' && $open2==true) { ?>
                        
<p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/questionnaire/complete.php?id=19411">AVETMISS Form
             </a></p> <?php } ?>
			 
			  <?php if(@$USER->id!='' && $open3==true) { ?>
                        
<p><a style="pointer-events: auto!important;" href="https://zfrmz.com/JE2hFhvthjkEG5TJax4t">Enrolment Form
             </a></p> <?php } ?>
        </div>
<!--        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>-->
      </div>
      
    </div>
  </div>
  
  
   <div class="modal fade" id="assignment_check" role="dialog" style="display: none;">
    <div class="modal-dialog">
     
      <!-- Modal content-->
      <div class="modal-content">
          
          
         <?php if(count($list_assign_id)>0) { ?> 
        <div class="modal-header" style="padding: 24px!important; background-color: #ccc!important;">
         
            <h4 class="modal-title" ><strong>Assessments Due </strong></h4>
        </div>
          
        <div class="modal-body">
            <div >
              Dear <?php echo $USER->firstname." ".$USER->lastname; ?> <br/><br/>you have assessments due.
          Please click the link below to directly access your submission page.
          Please get in touch with your trainer for any assistance required.
          The academic team can also help you with any questions related to your assessments.
          <br/>The contact email : <a href="mailto: academic@accit.nsw.edu.au">academic@accit.nsw.edu.au</a>
          If you have submitted and getting the pop up yet please contact academic team and they will work on sorting this out.
         		  <br/><br/><strong><font style="color: red!important;">Please note – Students who are enrolled into SUPERVISED STUDIES will have to pay a fee of $200 per unit. </font></strong>
           </div><br/>
             <?php if(@$USER->id!='' && $open_modal==true && count($list_assign_id)>0) { foreach($list_assign_id as $m=>$n) { for($k=0;$k<count($n); $k++) 
             { 
                 $sql_ass = "SELECT cm.id , assign.name , assign.duedate , assign.cutoffdate from {course_modules} as cm LEFT JOIN {assign} as assign ON assign.id = cm.instance WHERE cm.course = '".$m."' AND cm.instance = '".$n[$k]."' AND assign.grade!='0'";
                 $list_arr = $DB->get_record_sql($sql_ass); 
                 if($list_arr->name!='') {
                 ?>
                        
            <p><a style="pointer-events: auto!important;font-weight: bold;" href="http://localhost/accit-moodle/accit/mod/assign/view.php?id=<?php echo $list_arr->id; ?>" target="_blank">&nbsp;&nbsp;<?php echo $list_arr->name;  ?>
             </a><?php if($list_arr->duedate!='0') { ?> <br/><font style="color: red;"><?php echo "&nbsp;&nbsp;Due date : ".@date("F j, Y, g:i a",$list_arr->duedate); ?></font><?php } ?></p> <?php } } } } ?>
     </div>
          <?php if(count($list_assign_id2)==0) { ?> 
          <div style="padding-left: 150px;
    padding-bottom: 15px;
          padding-top: 1px;"> <button style="background-color: #008CBA!important; color: white!important;" class="button button2" onclick="javascript: window.location.href='<?php echo $CFG->wwwroot; ?>/index.php?set=1';">Okay, I understand</button></div><?php } ?>
             <?php } ?> 
         
          <?php if(count($list_assign_id2)>0 && $open_modal2==true) { ?> 
          <div class="modal-header" style="padding: 24px!important; background-color: #ccc!important;">
        
            <h4 class="modal-title"><strong>Assessments Overdue </strong></h4>
            
        </div>
          <div class="modal-body">
              
             		  <br/><br/><strong><font style="color: red!important;">Please note that any re-assessment requests after two attempts in your  term will charge a fee of $200 per unit.</font></strong>
          <br/>
              
              
             <?php $r=0; if(@$USER->id!='' && $open_modal2==true) { foreach($list_assign_id2 as $m=>$n) { for($k=0;$k<count($n); $k++) 
             { 
                 $sql_ass = "SELECT cm.id , cm.course , assign.name , assign.duedate , assign.cutoffdate from {course_modules} as cm LEFT JOIN {assign} as assign ON assign.id = cm.instance WHERE cm.course = '".$m."' AND cm.instance = '".$n[$k]."'";
                 $list_arr = $DB->get_record_sql($sql_ass); 
                 if($list_arr->name!='') { 
                 ?>
                        
            <p><a style="pointer-events: auto!important; font-weight: bold;" <?php if(isset($_GET['showlast'])) { ?> href="http://localhost/accit-moodle/accit/course/view.php?id=<?php echo $list_arr->course; ?>" <?php } ?> target="_blank">&nbsp;&nbsp;<?php echo $list_arr->name;  ?>
             </a><?php if($list_arr->duedate!='0') { ?><br/><font style="color: red; "><?php echo "&nbsp;&nbsp;Due date : ".@date("F j, Y, g:i a",$list_arr->duedate); ?></font><?php } ?>
             <?php if($list_arr->cutoffdate!='0') { ?><br/><font style="color: green!important;"><i><?php echo "&nbsp;&nbsp;Extended date :".@date("F j, Y, g:i a",$list_arr->cutoffdate); ?></i></font><?php } ?></p> <?php } } } } ?>
          <?php if(!isset($_GET['showlast'])) { ?> <input class="agree" onchange="javascript: window.location.href='index.php?popupdisplay=1&userid=<?php echo @$USER->id; ?>&showlast=1';" type="checkbox" name="ok" id="ok" value="1" /> I understand if I do not submit within the due date the re-assessment policy will be initiated which could lead to additional fees extending of study period.
          <?php } ?>
        <?php if(isset($_GET['showlast'])) { ?>  <div style="padding-left: 32px;
    padding-bottom: 15px;
    padding-top: 1px;"> <button style="background-color: #008CBA!important; color: white!important;" class="button button2" onclick="javascript: window.location.href='<?php echo $CFG->wwwroot; ?>/index.php';">Close</button>&nbsp;&nbsp;&nbsp;<button style="background-color: #008CBA!important; color: white!important;" class="button button2" onclick="javascript: window.location.href='<?php echo $CFG->wwwroot; ?>/grade/report/overview/studentgrades.php';">View your Grades</button></div> <?php } ?>
          </div>
        <?php if(isset($_GET['showlast'])) { ?>  <?php } } ?>
          
         <!-- <?php if(count($list_assign_id3)>0) { ?>
          <div class="modal-header">
         
            <h4 class="modal-title"><strong>Assessments Past Due </strong></h4>
        </div>
        <div class="modal-body">
             <?php if(@$USER->id!='' && $open_modal==true) { foreach($list_assign_id3 as $m=>$n) { for($k=0;$k<count($n); $k++) 
             { 
                 $sql_ass = "SELECT cm.id , assign.name , assign.duedate , assign.cutoffdate from {course_modules} as cm LEFT JOIN {assign} as assign ON assign.id = cm.instance AND assign.grade!='0'  WHERE cm.course = '".$m."' AND cm.instance = '".$n[$k]."' AND assign.grade!='0' ";
                 $list_arr = $DB->get_record_sql($sql_ass); 
                 if($list_arr->name!='') {
                 ?>
                        
            <p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/assign/view.php?id=<?php echo $list_arr->id; ?>" target="_blank">&nbsp;&nbsp;<?php echo $list_arr->name;  ?>
             </a><br/><font style="color: red; font-weight: bold;"><?php if($list_arr->duedate!='') { echo "&nbsp;&nbsp;Due date : ".@date("F j, Y, g:i a",$list_arr->duedate); } ?></font></p> <?php } } } } ?>
          </div> <?php } ?> -->
<!--        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>-->
      </div>
      
    </div>
  </div>
  </div>
  
<?php

}

redirect_if_major_upgrade_required();

// TODO Add sesskey check to edit
$edit   = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off
$reset  = optional_param('reset', null, PARAM_BOOL);

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$strmymoodle = get_string('myhome');

if (isguestuser()) {  // Force them to see system default, no editing allowed
    // If guests are not allowed my moodle, send them to front page.
    if (empty($CFG->allowguestmymoodle)) {
        redirect(new moodle_url('/', array('redirect' => 0)));
    }

    $userid = null;
    $USER->editing = $edit = 0;  // Just in case
    $context = context_system::instance();
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :)
    $header = "$SITE->shortname: $strmymoodle (GUEST)";
    $pagetitle = $header;

} else {        // We are trying to view or edit our own My Moodle page
    $userid = $USER->id;  // Owner of the page
    $context = context_user::instance($USER->id);
    $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
    $header = fullname($USER);
    $pagetitle = $strmymoodle;
}

// Get the My Moodle page info.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PRIVATE)) {
    print_error('mymoodlesetup');
}


$list_all_users = $DB->get_records_sql("SELECT c.id, c.shortname, c.fullname , c.summary, c.idnumber  
FROM mdl_course c 
JOIN mdl_enrol en ON en.courseid = c.id 
JOIN mdl_user_enrolments ue ON ue.enrolid = en.id 
WHERE ue.userid = '".$USER->id."'");
//echo '<pre>';
//print_r($list_all_users);
$studentsstr = '';
foreach($list_all_users as $key=>$val)
{
    $studentsstr = $studentsstr.'"'.$val->fullname."|".$val->id.'",';
}
$studentsstr = "[".$studentsstr."]";



?>
<link rel="stylesheet" media="all" type="text/css" href="https://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="http://localhost/accit-moodle/accit/admin/tool/assesmentresults/templates/css/jquery.modal.min.css" />
<?php
// Start setting up the page
$params = array();
$PAGE->set_context($context);
$PAGE->set_url('/my/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_pagetype('my-index');
$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($header);

if (!isguestuser()) {   // Skip default home page for guests
    if (get_home_page() != HOMEPAGE_MY) {
        if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
            set_user_preference('user_home_page_preference', HOMEPAGE_MY);
        } else if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_USER) {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'), new moodle_url('/my/', array('setdefaulthome' => true)),
                    navigation_node::TYPE_SETTING);
        }
    }
}

// Toggle the editing state and switches
if (empty($CFG->forcedefaultmymoodle) && $PAGE->user_allowed_editing()) {
    if ($reset !== null) {
        if (!is_null($userid)) {
            require_sesskey();
            if (!$currentpage = my_reset_page($userid, MY_PAGE_PRIVATE)) {
                print_error('reseterror', 'my');
            }
            redirect(new moodle_url('/my'));
        }
    } else if ($edit !== null) {             // Editing state was specified
        $USER->editing = $edit;       // Change editing state
    } else {                          // Editing state is in session
        if ($currentpage->userid) {   // It's a page we can edit, so load from session
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            // For the page to display properly with the user context header the page blocks need to
            // be copied over to the user context.
            if (!$currentpage = my_copy_page($USER->id, MY_PAGE_PRIVATE)) {
                print_error('mymoodlesetup');
            }
            $context = context_user::instance($USER->id);
            $PAGE->set_context($context);
            $PAGE->set_subpage($currentpage->id);
            // It's a system page and they are not allowed to edit system pages
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe
        }
    }

    // Add button for editing page
    $params = array('edit' => !$edit);

    $resetbutton = '';
    $resetstring = get_string('resetpage', 'my');
    $reseturl = new moodle_url("$CFG->wwwroot/my/index.php", array('edit' => 1, 'reset' => 1));

    if (!$currentpage->userid) {
        // viewing a system page -- let the user customise it
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
    } else {
        $editstring = get_string('updatemymoodleoff');
        $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    }

    $url = new moodle_url("$CFG->wwwroot/my/index.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($resetbutton . $button);

} else {
    $USER->editing = $edit = 0;
}

echo $OUTPUT->header();
echo '<form target="_blank" autocomplete="off" action="http://localhost/accit-moodle/accit/course/view.php" method="get"><div class="autocomplete" style="width:452px;">
    <input size="50" id="coursename" type="text" name="coursename" placeholder="Type Your Course" value="" >
 <input id="id" type="hidden" name="id" value="<?php echo @$agent_id; ?>" />
 &nbsp;<input type="submit" name="search_course" id="search_course" value=" View " />
        </div></form>';
?>
<style>
    .autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}

.autocomplete-items {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 0;
  right: 0;
}
.autocomplete-items div {
  padding: 7px;
  cursor: pointer;
  background-color: #fff; 
  width: 449px;
  border-bottom: 1px solid #d4d4d4; 
  background-color: #b3f1af;
  word-break: break-word;
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #e9e9e9; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
</style>
       
<script>
function autocomplete(inp, arr, type) { 


  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
var res = arr[i].split("|"); 
var idval = res[1];
var coursename = res[0];
//document.getElementById('coursename').value=coursename;
        /*check if the item starts with the same letters as the text field value:*/
        if (coursename.substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + coursename.substr(0, val.length) + "</strong>";
          b.innerHTML += coursename.substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input name='stuid' id='stuid' type='hidden' value='" + idval + "'>";
          b.innerHTML += "<input name='stuname' id='stuname' type='hidden' value='" + coursename + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) { 
              /*insert the value for the autocomplete text field:*/
             // inp.value = this.getElementsByTagName("input")[0].value; 
                if(type==1)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("courseid").value = this.getElementsByTagName("input")[0].value; 
                }
                else if(type==2)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("id").value = this.getElementsByTagName("input")[0].value; 
                }
                else
                {
                }
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
var res='';
var coursename = '';
var idval = '';
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});
}
var final_arr_stu = <?php echo $studentsstr; ?>; 
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
autocomplete(document.getElementById("coursename"), final_arr_stu,'2');
</script>
<?php
echo $OUTPUT->custom_block_region('content');

echo $OUTPUT->footer();

// Trigger dashboard has been viewed event.
$eventparams = array('context' => $context);
$event = \core\event\dashboard_viewed::create($eventparams);
$event->trigger();
