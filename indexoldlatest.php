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

if (!file_exists('./config.php')) {
    header('Location: install.php');
    die;
}

require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');
global $DB;
global $USER;
global $SESSION;
$not_to_check_array = array(867,941,858,1009,980,94,316,335,128,967);

if($USER->id==966)
{
$sql_popup_trainer = "SELECT `date` FROM `mdl_gradingnotification` WHERE `user_id` = '".@$USER->id."'";
$list_pop_trainer = $DB->get_record_sql($sql_popup_trainer); 

$today_date = strtotime(date("Y-m-d"));
$notification_date = strtotime($list_pop_trainer->date);

if($notification_date!=$today_date)
{
	$show_trainer_popup = true;
}
else
{
	$show_trainer_popup = false;
}

}


if(isset($_GET['doitlater']) && $_GET['doitlater']!='')
{
    $u3 = $DB->execute("INSERT INTO `mdl_gradingnotification`(`id`,`user_id`,`date`) VALUES(NULL, '".@$USER->id."' , '".@date("Y-m-d")."')"); 
    
    if($u3)
    {
        ?>
        <script> window.location.href='indexoldlatest.php'; </script>
        <?php
    }
}




///Array of students ID for transition form pop up

//$transition_student_array=array(1105,1097,1085,1086,1099,1101,1056,1084,1098,1068);

///END

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

if(isset($_GET['confirm']) && $_GET['confirm']!='')
{
    $u2 = $DB->execute("INSERT INTO `mdl_roleplay_confirmation`(`id`,`user_id`,`confirm`,`date`) VALUES(NULL, '".@$USER->id."' , '".$_GET['confirm']."', '".@date("Y-m-d h:i:s")."')"); 
    
    if($u2)
    {
        ?>
        <script> window.location.href='index.php'; </script>
        <?php
    }
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
$sql = "SELECT uen.enrolid , en.courseid from {user_enrolments} as uen LEFT JOIN {enrol} as en ON en.id = uen.enrolid LEFT JOIN {course} as course ON course.id = en.courseid WHERE uen.userid = '".@$USER->id."' AND ( MONTH(FROM_UNIXTIME(course.startdate)) = '1' OR MONTH(FROM_UNIXTIME(course.startdate)) = '2' OR MONTH(FROM_UNIXTIME(course.startdate)) = '3') AND YEAR(FROM_UNIXTIME(course.startdate)) = '2021'";
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


$sql_form_check3 = "SELECT count(`id`) as countrow FROM `zoho_forms` WHERE `form_name` = 'EnrollmentForm' AND `userid` = '".$USER->id."' AND `term` = '".@date('Y')."|4'";

$form_check3 = $DB->get_record_sql($sql_form_check3);


//ZOHO STUDENTS FEEDBACK FORM

$sql_form_check4 = "SELECT count(`id`) as countrow FROM `zoho_forms` WHERE `form_name` = 'FeedbackForm' AND `userid` = '".$USER->id."' AND `term` = '".@date('Y')."|4'";

$form_check4 = $DB->get_record_sql($sql_form_check4);


//ZOHO STUDENTS TRANSITION FORM

$sql_form_check5 = "SELECT count(`id`) as countrow FROM `zoho_forms` WHERE `form_name` = 'TransitionForm' AND `userid` = '".$USER->id."' AND `term` = '".@date('Y')."|4'";

$form_check5 = $DB->get_record_sql($sql_form_check5);

//ZOHO STUDENTS ROLEPLAY FORM

$sql_form_check6 = "SELECT count(`id`) as countrow FROM `mdl_roleplay_confirmation` WHERE `user_id` = '".$USER->id."'";

$form_check6 = $DB->get_record_sql($sql_form_check6);


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
///This static manipulation of courses needs to be changed later. Its temporary.
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
	//$all_students_string = '1430,a001342,1437,1389,1394,a001344,1392,1408,1362,1370,1410,1416,1417,1425,1431,1428,1441,1452,1446,1448,1456,1465,1045,1331,1334,a001539,1346,1347,1349,a001079,1354,1353,1357,1379,a001747,1376,1388,1386,1373,1390,1385,1411,1406,1366,1395,1402,1419,1422,1420,1413,1397,1363,1424,1423,1427,1426,1429,1436,1327,1442,1445,1450,1453,1460,1462,1466,1467,1384,1396,1404,a001342,1338,1435,1438,a001254,a001310,a001320,a001305,1279,1321,a001330,a001334,1052,a001337,a001131,a001333,a001328,a001329,1322,a001338,a001352,a001358,a001247,a001647,1348,a001369,a001164,a001555,1355,1360,1352,1378,1403,a001112,1387,a001548,1421,1291,1449,a000493,1459,1451,1048,1057,1055,1054,1047,1039,1052,1030,184,1034';
	
	
	//$all_students_string = 'a109145,santhiah,1349,1520,1256,1385,1460,1467,1477,1462,1498,1370,1476,1390,1406,1509,1515,1392,1411,1357,1433,1448,1484,a001413,1394,a001539,1379,1429,1510,1419,1452,1402,1436,1508,1376,1420,1479,1416,1471,1446,1473,1493,1465,1408,1347,1482,1334,1489,1519,1504,1494,1468,1480,1395,1427,1472,1426,1511,1490,1470,1506,1464,1503,1354,1456,1425,1499,1441,1512,1430,1399,1353,1442,1463,1423,1507,a001048,1478,1488,1485,1514,1045,1389,1513,1466,a001342,1331,1386,a001412,1496,a001747,1487,1495,1422,1428';
	
	//$all_students_string = '1408,1520,1460,1467,1477,1462,1498,1370,1515,1392,1357,1433,1484,a001413,1394,1379,1429,1510,1419,1452,1436,1376,1420,1479,1416,1446,1473,1465,1408,1334,1489,1519,1504,1413,1388,1427,1472,1426,1506,1503,1354,1456,1425,1499,1441,1430,1399,1353,1442,1423,1478,1488,1485,1514,1045,1513,1466,a001342,a001412,a001747,1487,1495,1422';
	
	//$all_students_string = '1495,1045,a001413,1357,santhiah,1522,1487,1506,1507,1513,1514,1515,1520,1392,1399,a001412,1416,1426,1452,1456,1465,1470,1478,1502,1510,1519,1523,1524,a001342,1425,a001048,1045,1331,1334, 1357,1370,1376,1379,1388,1389,1394,1408,1442,1460,1462,1466,1467,1471,1473,1477,1479,1484,1485,1495,1498,1499,1503,1504';
	
	$all_students_string = '1520,a001413,1524,1467,1477,1462,1498,1370,1522,1515,1392,1448,1484,1394,1525,1379,1510,1376,1420,1479,1416,1471,1473,1465,1408,1334,1489,1519,1516,1504,1388,1472,1426,1506,1503,1456,1425,1499,1441,1399,1507,1478,1502,1485,1514,1045,1389,1513,1466,1331,a001412,1487,1495,1523';
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

$exclude_students_3 = array('');

if(in_array($USER->id,$all_students_id)==true && in_array($USER->id,$exclude_students_3)==false)
{

        $modal_open=true;
		$open3 = true;

}

}

//ZOHO FEEDBACK FORM
if($form_check4->countrow==0 && $form_check3->countrow>0)
{
	
	$all_students_string = '1520,1524,1460,1467,1477,1462,1498,1370,1522,1515,1392,1357,1448,1484,a001413, 1394, 1379, 1510, 1452, 1376, 1420, 1479, 1416, 1471, 1465, 1408, 1334, 1489, 1519, 1504, 1388, 1472, 1426, 1506, 1503, 1456, 1425, 1499, 1399, 1507, 1478, 1502, 1485, 1514, 1045, 1389, 1513, 1466, a001342, 1331, a001412, 1487, 1495, 1523';
	$all_students_string_explode = explode(",",$all_students_string);
	$all_students_id4 = array();
	foreach($all_students_string_explode as $j=>$v)
	{
		$sql_exclude_4 = "SELECT `id`  FROM `mdl_user` WHERE `username` = '".trim($v)."'";
		$row_exclude_4 = $DB->get_record_sql($sql_exclude_4);
		$all_students_id4[] = $row_exclude_4->id;
		unset($sql_exclude_4);
		unset($row_exclude_4);
	}
//$exclude_students_3 = array('946','954','985','1007','1046','1049','1053','1054','1042','1056','840','632');

$exclude_students_4 = array('');

if(in_array($USER->id,$all_students_id4)==true && in_array($USER->id,$exclude_students_4)==false)
{

        $modal_open=true;
		//$open4 = false;
		$open4 = false;
       
}

}


//ZOHO COURSE TRANSITION FORM

/*if($form_check5->countrow==0 && $form_check3->countrow>0)
{
	
	$all_students_string = '1105,1097,1085,1086,1099,1101,1056,1084,1098,1068';
	
	$all_students_string_explode = explode(",",$all_students_string);
	$all_students_id5 = array();
	foreach($all_students_string_explode as $j=>$v)
	{
		$all_students_id5[] = $v;
	}

$exclude_students_5 = array('');

if(in_array($USER->id,$all_students_id5)==true && in_array($USER->id,$exclude_students_5)==false)
{

        $modal_open=true;
		$open5 = true;
       
}

}*/

//ROLE PLAY CONFIRMATION

if($form_check6->countrow==0 && $form_check3->countrow>0)
{
	
	$roleplay_student_array = '1376,1399,1416,1413,1452,1446,1456,1465,1426,1473,1478,1389,1503,1504,1510,1519,1392';
	
	$all_students_string_explode = explode(",",$roleplay_student_array);
	$all_students_id6 = array();
	foreach($all_students_string_explode as $j=>$v)
	{
		
		$sql_exclude_6 = "SELECT `id`  FROM `mdl_user` WHERE `username` = '".$v."'";
		$row_exclude_6 = $DB->get_record_sql($sql_exclude_6);
		$all_students_id6[] = $row_exclude_6->id;
		unset($sql_exclude_6);
		unset($row_exclude_6);
		
		
		$all_students_id6[] = $v;
	}

$exclude_students_6 = array('');

if(in_array($USER->id,$all_students_id6)==true && in_array($USER->id,$exclude_students_6)==false)
{

        $modal_open=true;
		$open6 = true;
       
}

}

}
//$uidcheck = $checkval->uid;

?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
  .modal-backdrop {
background-color: #275394!important;
  }
  </style>
<?php
if($modal_open==true && ($open3==true || $open4==true || $open5==true || $open6==true))
{
    ?>
<script>
     
    $(document).ready(function() {
    
    
   $('#myModal').modal('show');
  
});

</script>
<?php 
} 


if((($open_modal==true || $open_modal2==true) && ( $list_pop_config->status==1 || $list_pop_config->status=='')) || $USER->id==966)
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
    
    
  // $('#assignment_check').modal('show');
   //alert('ok');
  
   //$('#popup_alert_trainer').modal('show');
  
});

</script>
<?php } ?>
<?php
if($show_trainer_popup==true)
{
	?>
<script>
     
    $(document).ready(function() {
    
    
   $('#popup_alert_trainer').modal('show');
  
});

</script>
<?php } ?>
<div class="container" style="pointer-events: none; ">
  <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" style="display: none;"></button>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog" style="display: none; top:60px;">
<div class="modal-dialog" <?php if(@$USER->id!='' && $open6==true) { ?> style="max-width: 1000px!important;" <?php } ?>>
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
         
            <h4 class="modal-title"><strong>Forms</strong></h4>
        </div>
       <div class="modal-body">
	   <?php // echo '1-'.$open1.' 2-'.$open2.' 3-'.$open3.' 4-'.$open4.' -5-'.$open5.' 6-'.$open6; ?>
             <?php if(@$USER->id!='' && $open1==true) { ?>
                        
<p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/questionnaire/complete.php?id=28413"><strong>Enrolment Form</strong>
             </a></p> <?php } ?>
			 <?php if(@$USER->id!='' && $open2==true) { ?>
                        
<p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/questionnaire/complete.php?id=19411">AVETMISS Form
             </a></p> <?php } ?>
			 
			  <?php if(@$USER->id!='' && $open3==true) { ?>
                        
<p><a style="pointer-events: auto!important;" href="https://zfrmz.com/JE2hFhvthjkEG5TJax4t">Enrolment Form
             </a></p> <?php } ?>
			 
			 
			 <?php //if(@$USER->id!='' && $open5==true) { ?>
                        
<!--<p><a style="pointer-events: auto!important;" href="https://zfrmz.com/LcXSx0I8I2vx49FZgZXn">
Course transition letter to BSB30220 - Certificate III in Entrepreneurship and New Business
             </a></p> --> <?php //} ?>
			 
			 
			 
			 
			 <?php if(@$USER->id!='' && $open6==true) { ?>
               
<br/>
<div class="modal-header" style="padding: 25px!important;">
        
           
            
        </div>
          <div class="modal-body">
              <div class="modal-header" style="padding: 0px!important; padding-left:1px!important;">
         
            <h4 class="modal-title" ><strong>Roleplay Confirmation</strong></h4>
        </div><br/>
             <p>ACCIT has decided to take bookings for role-play activities till the end of each unit end date. 
			 You will be offered one-week free extension for role-play activities for each unit.</p>
			 <p><i><strong>NOTE</strong> - Those who missed the chance to book for CIT08 BSBWHS401 role-play activities required to pay AUD 20 for extension 
			 and then can book for CIT09 BSBRSK401 and CIT10 BSBMGT401 role-play activities. If you miss to book for all the three units by the deadline, 
			 you will be liable to pay AUD 60 for Term 2/2021 pending role-plays. </i></p>

			 <table cellspacing="2" cellpadding="5" style="border: 1px solid #000!important; width: 100%!important;
  border-collapse: collapse!important;">
			 <thead>
			 <tr style="border: 1px solid #000!important; background-color: #ddd!important;">
			 <td style="border: 1px solid #000!important; ">Unit</td>
			 <td style="border: 1px solid #000!important; ">End Date</td>
			 <td style="border: 1px solid #000!important; ">Last Date for Free Role Play Booking</td>
			 </tr>
			 </thead>
			 <tbody>
			 <tr style="border: 1px solid #000!important;">
			 <td style="border: 1px solid #000!important;">CIT08 BSBWHS401 Implement and monitor WHS policies, procedures and programs to meet legislative requirements </td>
			 <td style="border: 1px solid #000!important;">16th of May</td>
			 <td style="border: 1px solid #000!important;">21st of May 2021</td>
			 </tr>
			 <tr style="border: 1px solid #000!important;">
			 <td style="border: 1px solid #000!important;">CIT09 BSBRSK401 Identify risk and apply risk management processes</td>
			 <td style="border: 1px solid #000!important;">30th of May</td>
			 <td style="border: 1px solid #000!important;">4th of June 2021 </td>
			 </tr>
			 <tr style="border: 1px solid #000!important; ">
			 <td style="border: 1px solid #000!important;">CIT10 BSBMGT401 Show leadership in the workplace</td>
			 <td style="border: 1px solid #000!important;">27th of June</td>
			 <td style="border: 1px solid #000!important;">2nd of July 2021</td>
			 </tr>
			 </tbody>
			 </table>

              <br/>
              
          
          <input class="agree" onchange="javascript: var p=confirm('Are you sure?'); if(p==true) { 
		  window.location.href='index.php?userid=<?php echo @$USER->id; ?>&confirm=1'; } else { this.checked=false; return false; }" type="radio" name="confirm" id="confirm" value="1" /> <span style="font-size:16px;"><strong>Yes, I am ready to pay for my role-play extension.</strong></span>
         
		
		
              <br/><br/>
              
          
          <input class="agree" onchange="javascript: var p=confirm('Are you sure?'); if(p==true) { 
		  window.location.href='index.php?userid=<?php echo @$USER->id; ?>&confirm=0'; } else { this.checked=false; return false; }" type="radio" name="confirm" id="confirm" value="0" /> <span style="font-size:16px;"><strong>No, I would like to book appointment now (to book your slot please mail at <a href="mailto: academic@accit.nsw.edu.au;">academic@accit.nsw.edu.au</a> before the &nbsp;&nbsp;&nbsp;deadline).</strong></span>
         
           </div>

			   
<?php } ?>
			 
			 		 
			 
			 <?php  if(@$USER->id!='' && $open4==true && $open5!=true) { ?>
                        
 <p><a style="pointer-events: auto!important;" href="https://zfrmz.com/BU8W45ST5Cqe1Eul06VW">Feedback Form
             </a></p> <?php  } ?>
        </div>
<!--        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>-->
      </div>
      
    </div>
  </div>
  
  
  <?php
if($show_trainer_popup==true)
{
	?>
   <div class="modal fade" id="popup_alert_trainer" role="dialog" style="display: none;  top:80px;">
    
    <div class="modal-dialog">
     
      <!-- Modal content-->
      <div class="modal-content" style="overflow-y: scroll!important; height:auto!important;">
          
          
        
      <!--  <div class="modal-header" style="padding: 24px!important; background-color: #ccc!important; padding: 0!important;">
         
            <h4 class="modal-title" ><strong>Marking Notice </strong></h4>
        </div> -->
          
        <div class="modal-body">
            <div  >
			<br/><br/>
              Dear <?php echo $USER->firstname." ".$USER->lastname; ?><br/><br/>
			  As always thank-you for being such an amazing trainer who does all these cool things. 
			  Students are waiting for your valuable feedback on their submission.
			  <br/>
			  <br/>
			<button style="background-color: #008CBA!important; color: white!important;" class="button button2" 
			onclick="javascript: window.location.href='<?php echo $CFG->wwwroot; ?>/grade/report/overview/ungraded2.php';">Okay, will Grade</button>&nbsp;&nbsp;&nbsp;
			<button style="background-color: #008CBA!important; color: white!important;" class="button button2" 
			onclick="javascript: window.location.href='<?php echo $CFG->wwwroot; ?>/indexoldlatest.php?doitlater=1';">May be Later</button></div>
          <br/>
		  </div>
		  </div>
		  </div>
		  </div>
<?php } ?>
  
  
  
  
   <div class="modal fade" id="assignment_check" role="dialog" style="display: none;">
    <div class="modal-dialog">
     
      <!-- Modal content-->
      <div class="modal-content" style="overflow-y: scroll!important;">
          
          
         <?php if(count($list_assign_id)>0) { ?> 
        <div class="modal-header" style="padding: 24px!important; background-color: #ccc!important;">
         
            <h4 class="modal-title" ><strong>Assessments Due </strong></h4>
        </div>
          
        <div class="modal-body">
            <div >
              Dear <?php echo $USER->firstname." ".$USER->lastname; ?> <br/><br/>You have assessments due.
          Please click the link below to directly access your submission page.
          Please get in touch with your trainer for any assistance required.
          The academic team can also help you with any questions related to your assessments.
          <br/>The contact email : <a href="mailto: academic@accit.nsw.edu.au">academic@accit.nsw.edu.au</a>
		  
          <br/>If you have submitted and getting the pop up yet please contact academic team and they will work on sorting this out.
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
          padding-top: 1px;"> <button style="background-color: #008CBA!important; color: white!important;" class="button button2" onclick="javascript: window.location.href='<?php echo $CFG->wwwroot; ?>/index.php?set=1';">Okay, I'll start working on it</button></div><?php } ?>
             <?php } ?> 
         
          <?php if(count($list_assign_id2)>0 && $open_modal2==true) { ?> 
          <div class="modal-header" style="padding: 24px!important; background-color: #ccc!important;">
        
            <h4 class="modal-title"><strong>Assessments Overdue </strong></h4>
            
        </div>
          <div class="modal-body">
              
             <strong>Please note that any re-assessment requests after two attempts in your term will charge a fee of $200 per unit.</strong>
              <br/><br/>
              
             <?php $r=0; if(@$USER->id!='' && $open_modal2==true) { foreach($list_assign_id2 as $m=>$n) { for($k=0;$k<count($n); $k++) 
             { 
                 $sql_ass = "SELECT cm.id , assign.name , assign.duedate , assign.cutoffdate from {course_modules} as cm LEFT JOIN {assign} as assign ON assign.id = cm.instance WHERE cm.course = '".$m."' AND cm.instance = '".$n[$k]."'";
                 $list_arr = $DB->get_record_sql($sql_ass); 
                 if($list_arr->name!='') { 
                 ?>
                        
            <p><a style="pointer-events: auto!important; font-weight: bold;" <?php if(isset($_GET['showlast'])) { ?> href="http://localhost/accit-moodle/accit/mod/assign/view.php?id=<?php echo $list_arr->id; ?>" <?php } ?> target="_blank">&nbsp;&nbsp;<?php echo $list_arr->name;  ?>
             </a><?php if($list_arr->duedate!='0') { ?><br/><font style="color: red; "><?php echo "&nbsp;&nbsp;Due date : ".@date("F j, Y, g:i a",$list_arr->duedate); ?></font><?php } ?>
             <?php if($list_arr->cutoffdate!='0') { ?><?php } ?></p> <?php } } } } ?>
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

$urlparams = array();
if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 0) {
    $urlparams['redirect'] = 0;
}
$PAGE->set_url('/', $urlparams);
$PAGE->set_pagelayout('frontpage');
$PAGE->set_other_editing_capability('moodle/course:update');
$PAGE->set_other_editing_capability('moodle/course:manageactivities');
$PAGE->set_other_editing_capability('moodle/course:activityvisibility');

// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

require_course_login($SITE);

?>


<?php
$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

// If site registration needs updating, redirect.
\core\hub\registration::registration_reminder('/index.php');

if (get_home_page() != HOMEPAGE_SITE) {
    // Redirect logged-in users to My Moodle overview if required.
    $redirect = optional_param('redirect', 1, PARAM_BOOL);
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/');
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
        $frontpagenode = $PAGE->settingsnav->find('frontpage', null);
        if ($frontpagenode) {
            $frontpagenode->add(
                get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        } else {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        }
    }
}

// Trigger event.
course_view(context_course::instance(SITEID));

// If the hub plugin is installed then we let it take over the homepage here.
if (file_exists($CFG->dirroot.'/local/hub/lib.php') and get_config('local_hub', 'hubenabled')) {
    require_once($CFG->dirroot.'/local/hub/lib.php');
    $hub = new local_hub();
    $continue = $hub->display_homepage();
    // Function display_homepage() returns true if the hub home page is not displayed
    // ...mostly when search form is not displayed for not logged users.
    if (empty($continue)) {
        exit;
    }
}

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$editing = $PAGE->user_is_editing();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();

$siteformatoptions = course_get_format($SITE)->get_format_options();
$modinfo = get_fast_modinfo($SITE);
$modnamesused = $modinfo->get_used_module_names();

// Print Section or custom info.
if (!empty($CFG->customfrontpageinclude)) {
    // Pre-fill some variables that custom front page might use.
    $modnames = get_module_types_names();
    $modnamesplural = get_module_types_names(true);
    $mods = $modinfo->get_cms();

    include($CFG->customfrontpageinclude);

} else if ($siteformatoptions['numsections'] > 0) {
    echo $courserenderer->frontpage_section1();
}
// Include course AJAX.
include_course_ajax($SITE, $modnamesused);

echo $courserenderer->frontpage();

if ($editing && has_capability('moodle/course:create', context_system::instance())) {
    echo $courserenderer->add_new_course_button();
}
echo $OUTPUT->footer();
