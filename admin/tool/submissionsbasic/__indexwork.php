<?php
// Standard GPL and phpdocs

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');
//admin_externalpage_setup('index');


// Set up the page.
$title = get_string('pluginname', 'tool_submissionsbasic');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/submissionsbasic/indexwork.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();
$output = $PAGE->get_renderer('tool_submissionsbasic');
 
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
global $SESSION;

global $DB;

if(isset($_REQUEST['studentid']))
{
	$grader_id = $_REQUEST['studentid'];
}

if(isset($_REQUEST['year']))
{
	$year = $_REQUEST['year'];
}
else
{
	$year = @date("Y");
}

if(isset($_REQUEST['term']))
{
	$term = $_REQUEST['term'];
}
else
{
	$term = 1;
}

if($term==1)
{
	$term_query = "(MONTH(FROM_UNIXTIME(course.startdate)) = '1' OR MONTH(FROM_UNIXTIME(course.startdate)) = '2' OR MONTH(FROM_UNIXTIME(course.startdate)) = '3')";
}
else if($term==2)
{
	$term_query = "(MONTH(FROM_UNIXTIME(course.startdate)) = '4' OR MONTH(FROM_UNIXTIME(course.startdate)) = '5' OR MONTH(FROM_UNIXTIME(course.startdate)) = '6')";
}
else if($term==3)
{
	$term_query = "(MONTH(FROM_UNIXTIME(course.startdate)) = '7' OR MONTH(FROM_UNIXTIME(course.startdate)) = '8' OR MONTH(FROM_UNIXTIME(course.startdate)) = '9')";
}
else if($term==4)
{
	$term_query = "(MONTH(FROM_UNIXTIME(course.startdate)) = '10' OR MONTH(FROM_UNIXTIME(course.startdate)) = '11' OR MONTH(FROM_UNIXTIME(course.startdate)) = '12')";
}
else
{
	$term_query = " course.fullname LIKE '%SUPERVISED%' OR course.fullname LIKE '%SCV%'";
}

if(isset($_REQUEST['set']))
{
	if(isset($SESSION->set))
	{
		unset($SESSION->set);
	}
    $SESSION->set=$_REQUEST['set'];
}
if(!isset($SESSION->set) && !isset($_REQUEST['set']))
{
	$SESSION->set=1;
}
if($SESSION->set==1)
{
	$set_query = ' ';
	$set_query2 = " r.shortname =  'student' ";
}
else
{
	$set_query = ' AND g.grader > 0 ';
	$set_query2 = " r.shortname !=  'student' ";
}
if(isset($_GET['showallstudent']) && $_GET['showallstudent']==1)
{
    $showallstudent = $_GET['showallstudent'];
}
if(isset($_POST['updateform']) && $_POST['updateform']!='' )
{
    $useridarray = $_POST['userid'];
    $graderidarray = $_POST['graderid'];
    $assidarray = $_POST['assid'];
    $itemidarray = $_POST['itemid'];
	$filenamearray = $_POST['filename'];
    $gradeitemid = $_POST['gradeitemid'];
	$updateidarray = $_POST['updateidarray'];
    $c = 0;
	unset($SESSION->stringmsg);
	$stringmsg=array();
	
	//echo '<pre>';
	//print_r($_POST['contenthash']);
	
    foreach($_POST['contenthash'] as $key=>$val)
    {
		//echo '<pre>';
		//print_r($_FILES);
		//die;
       
       if($_FILES['new_file']['name'][$key]!='')
	   {
		    if($_FILES['new_file']['name'][$key]==$filenamearray[$c])
			{
			$tmp_name = $_FILES['new_file']['tmp_name'][$key];
			$contenthash = sha1_file($tmp_name);
			
			//$pathnamehash = sha1("/36689/assignsubmission_file/submission_files/32600/CIT05 BSBMKG416 Assessment 2 of 2.docx");
			//echo $contenthash;
			
			
		   $str1 = substr($contenthash,0,2);
		   $str2 = substr($contenthash,2,2);
		   if(is_dir('C:/xampp/moodledata/filedir/'.$str1.'/'.$str2)==false)
		   {
		     @mkdir('C:/xampp/moodledata/filedir/'.$str1.'/'.$str2, 0777, true);
			 
		   }
		   
		   
		   $destination = 'C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$_FILES['new_file']['name'][$key];
		   @move_uploaded_file($tmp_name, $destination);
		   
		   rename('C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$_FILES['new_file']['name'][$key],'C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$contenthash);
		   
		   $DB->execute("UPDATE {files} SET `contenthash` = '".$contenthash."' WHERE `itemid` = '".$itemidarray[$c]."' AND `contenthash` = '".$val."' ");
		  // echo "UPDATE {files} SET `contenthash` = '".$contenthash."' WHERE `itemid` = '".$itemidarray[$c]."' AND `contenthash` = '".$val."' ";
		   
		   unset($str1);
		   unset($str2);
		   unset($tmp_name);
		   unset($destination);
		   $SESSION->stringmsg[$c]="Row ID #".$updateidarray[$c]." successfully updated!|success";
	
		   
		   }
		   else
		   {
			   $SESSION->stringmsg[$c]="Row ID #".$updateidarray[$c]." must have same name of file already submitted!|error";
		   }
		  
	   }		   
		
       $c++;
        
    }

    if($_POST['sort']=='') { $sort = 1; } else {  $sort = $_POST['sort']; } 
    

    
    $urltogo= $CFG->wwwroot.'/admin/tool/submissionsbasic/indexwork.php?update=1&sort='.$sort.'&sorttype='.$_POST['sorttype'].'&showallstudent='.$_REQUEST['showallstudent'].'&studentid='.$_POST['graderid'].'&year='.$_POST['yearselect'];
    ?>
<div style="padding-left: 38px;"><img src='<?php echo $CFG->wwwroot; ?>/admin/tool/submissionsbasic/templates/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>

<?php

}




if(isset($_GET['update'])) {

   
if(count($SESSION->stringmsg)>0) 
{ 
foreach($SESSION->stringmsg as $b=>$msg) 
{ 
$msg_arr = explode("|",$msg);
if($msg_arr[1]=="error")
{
echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  '.$msg_arr[0].'
</div>'; 
}
else if($msg_arr[1]=="success")
{
	echo '<div class="success-msg">
  <i class="fa fa-check"></i>
  '.$msg_arr[0].'
   </div>';
}
else { }
unset($msg_arr);
} 
}
   }

//echo $DB->count_records('mdl_assign',array('course'=>'118'));
//echo '<pre>';
//print_r($_POST);

if(isset($_REQUEST["page"]))
$page = (int)$_REQUEST["page"];
else
$page = 1;
$setLimit = 1000000;
$pageLimit = ($page * $setLimit) - $setLimit;


$list_all_courses = $DB->get_records_sql('SELECT `id` as courseid , `fullname` as coursename FROM {course} WHERE 1');
$coursestr = '';
foreach($list_all_courses as $list_all_courses)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $coursestr = $coursestr.'"'.$list_all_courses->coursename."|".$list_all_courses->courseid.'",';
}
$coursestr = "[".$coursestr."]";

$list_all_students = $DB->get_records_sql("SELECT DISTINCT u.id AS studentid, u.firstname as firstname , u.lastname as lastname 
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid
AND ct.contextlevel =50
JOIN mdl_role r ON r.id = ra.roleid
AND r.shortname !=  'student'
WHERE e.status =0
AND u.suspended =0
AND u.deleted =0
AND ue.status =0");
$studentsstr = '';
foreach($list_all_students as $list_all_students)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $studentsstr = $studentsstr.'"'.$list_all_students->firstname." ".$list_all_students->lastname."|".$list_all_students->studentid.'",';
}
$studentsstr = "[".$studentsstr."]";



//echo $studentsstr; 
//$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid WHERE 1';

if(isset($grader_id) && $grader_id>0)
{
    $sql_all = 'SELECT x.id as rowid , x.status,  x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , 
	us.lastname as graderlastname , us.id as graderuserid , z.id as assignmentid , z.name as assignmentname , cm.id as assid ,
	g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass ,
	g.timemodified as gtimemodified , course.startdate as startdate FROM {assign_submission} as x 
	JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid 
		JOIN {course} as course ON z.course=course.id
		LEFT JOIN {course_modules} as cm ON cm.instance = z.id AND cm.module = "29"
		LEFT JOIN {user} as us ON g.grader = us.id WHERE x.status ="submitted" and g.grader ='.$grader_id.' AND YEAR(FROM_UNIXTIME(x.timemodified)) = '.$year.' AND '.$term_query.' '.$set_query.' '.$SESSION->sortorder;

    $sql = 'SELECT x.id as rowid , x.status , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, 
	y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , z.id as assignmentid , cm.id as assid,
	z.name as assignmentname , g.userid as uid , g.id as gradeitemid, g.grader as graderid , 
	g.assignment as ass , g.timemodified as gtimemodified , course.startdate as startdate FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid 
		JOIN {course} as course ON z.course=course.id
		LEFT JOIN {course_modules} as cm ON cm.instance = z.id AND cm.module = "29"
		LEFT JOIN {user} as us ON g.grader = us.id WHERE x.status ="submitted" and g.grader ='.$grader_id.'  AND YEAR(FROM_UNIXTIME(x.timemodified)) = '.$year.' AND '.$term_query.' '.$set_query.' '.$SESSION->sortorder.' 
		LIMIT '.$pageLimit.' , '.$setLimit;



//echo $sql_all;
//echo '<hr>';
//echo $sql;
$list_all = $DB->get_records_sql($sql_all);
//echo '<pre>';
//print_r($list_all);
if(isset($_GET['showallstudent']) && $_GET['showallstudent']==1)
{
    $SESSION->showallstudent = 1;
    $list = $DB->get_records_sql($sql_all);
}
else
{
    $SESSION->showallstudent = 0;
    $list = $DB->get_records_sql($sql);
}

$arr = array();
foreach($list as $list)
{
	//if($list->graderid==966)
	//{
	
	if($SESSION->set==1)
	{
		//$userid = $list->userid;
		$firstname = $list->firstname;
		$lastname = $list->lastname;
		$student_id = $list->userid;
	}
	else
	{
		$userid = $list->graderid;
		$firstname = $list->graderfirstname;
		$lastname = $list->graderlastname;
	}
    if($list->gtimemodified!='')
    {
        $gtimemodified = date("F j, Y, g:i a",$list->gtimemodified);
        $grade_exists = '';
    }
    else
    {
        $gtimemodified = 'NA';
        $grade_exists = 'disabled';
    }
    if($list->graderid=='')
    {
        $gradername = 'NA';
    }
    else
    {
        $gradername = $list->graderfirstname." ".$list->graderlastname;
    }
	$student_name = $list->firstname." ".$list->lastname;
	
		$file_all = $DB->get_records_sql("SELECT * FROM {files} WHERE `itemid` = '".$list->gradeitemid."' AND `userid` = '".$list->graderuserid."' AND `filesize`!='0' AND `filearea` = 'feedback_files'" );
		
		foreach($file_all as $key=>$valrow)
		{
		if(@$valrow->contextid>0)
		{
			$download = $CFG->wwwroot.'/pluginfile.php/'.$valrow->contextid.'/assignfeedback_file/feedback_files/'.$list->gradeitemid.'/'.$valrow->filename.'?forcedownload=1';
		$arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$userid, 'name'=>$firstname.' '.$lastname, 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>date("F j, Y, g:i a",$list->timemodified),'timemodifiedg'=>$gtimemodified,'gradeexists'=>$grade_exists,'gradername'=>$gradername,'graderid'=>$list->graderid,'gradeitemid'=>$list->gradeitemid, 'itemid'=> $valrow->itemid, 'contextid'=> $valrow->contextid, 'contenthash'=>$valrow->contenthash, 'pathnamehash'=>$valrow->pathnamehash, 'filename'=>$valrow->filename,'source'=>$valrow->source,'download'=>$download,'studentname'=>$student_name,'student_id'=>$student_id,'assid'=>$list->assid,'yearselect'=>$year,'termselect'=>$term);

		}
		else
		{
			$download='';
		}
		}
		unset($file_all);
unset($download);
unset($student_name);
unset($student_id);
	
//echo '<pre>';
	//print_r($file_all); die;
	//$arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$userid, 'name'=>$firstname.' '.$lastname, 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>date("F j, Y, g:i a",$list->timemodified),'timemodifiedg'=>$gtimemodified,'gradeexists'=>$grade_exists,'gradername'=>$gradername,'graderid'=>$list->graderid,'gradeitemid'=>$list->gradeitemid, 'itemid'=> $file_all->itemid, 'contextid'=> $file_all->contextid, 'contenthash'=>$file_all->contenthash, 'pathnamehash'=>$file_all->pathnamehash, 'filename'=>$file_all->filename,'source'=>$file_all->source,'download'=>$download);
unset($file_all);
unset($download);
unset($userid);
unset($firstname);
unset($lastname);
}
}
//}
//echo '<pre>';
//print_r($arr);
//die;
if($SESSION->sortval==1)
{
    $sortvalold = 2;
}
else
{
    $sortvalold = 1;
}
$renderable = new \tool_submissionsbasic\output\index_page($arr,$coursestr,$assessmentstr,$studentsstr,$studentsstrusername,$page,@count($list_all),@$SESSION->studentname,@$SESSION->coursename,@$SESSION->assessmentname,@$SESSION->studentusername,@$SESSION->courseid,@$SESSION->assessmentid,@$SESSION->studentid,@$SESSION->studentuserid,@$SESSION->sortval,$sortvalold,@$SESSION->sorttype,@$SESSION->showallstudent,$SESSION->set);
echo $output->render($renderable); 


echo $OUTPUT->footer();