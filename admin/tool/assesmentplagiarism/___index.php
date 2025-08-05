<?php
// Standard GPL and phpdocs

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');
require_once('Plagiarism.class.php');
//admin_externalpage_setup('index');


function read_docx($filename)
	{
		$striped_content = '';
		$content = '';
		if(!$filename || !file_exists($filename)) return false;
		$zip = zip_open($filename);
		if (!$zip || is_numeric($zip)) return false;
		while ($zip_entry = zip_read($zip)) 
		{
			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
			if (zip_entry_name($zip_entry) != "word/document.xml") continue;
			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			zip_entry_close($zip_entry);
		}
		zip_close($zip);      
		$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		$content = str_replace('</w:r></w:p>', "\r\n", $content);
		$striped_content = strip_tags($content);
		return $striped_content;
	}



// Set up the page.
$title = get_string('pluginname', 'tool_assesmentplagiarism');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/assesmentplagiarism/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();
$output = $PAGE->get_renderer('tool_assesmentplagiarism');
 
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
global $SESSION;

global $DB;

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
	
    foreach($_POST['contenthash'] as $key=>$val)
    {
       
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
    

    
    $urltogo= $CFG->wwwroot.'/admin/tool/assesmentplagiarism/index.php?page='.$_POST['page'].'&update=1&sort='.$sort.'&sorttype='.$_POST['sorttype'].'&showallstudent='.$_REQUEST['showallstudent'];
    ?>
<div style="padding-left: 38px;"><img src='<?php echo $CFG->wwwroot; ?>/admin/tool/assesmentplagiarism/templates/images/loader.gif' border='0'></div>
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
if(isset($_GET['sorttype']) && $_GET['sorttype']=="submission")
{
    if($_GET['sort']==1)
    {
       $sortval = 2;
       $sortorder = " order by x.timemodified ASC";
       $SESSION->sortorder=$sortorder;
       @$SESSION->sortval = $sortval;
       @$SESSION->sorttype = $_GET['sorttype'];
    }
    else
    {
        $sortval = 1;
        $sortorder = " order by x.timemodified DESC";
        $SESSION->sortorder=$sortorder;
        @$SESSION->sortval = $sortval;
        @$SESSION->sorttype = $_GET['sorttype'];
    }
}
else if(isset($_GET['sorttype']) && $_GET['sorttype']=="grades")
{
    if($_GET['sort']==1)
    {
        $sortval = 2;
        $sortorder = " order by gtimemodified ASC";
        $SESSION->sortorder=$sortorder;
        @$SESSION->sortval = $sortval;
        @$SESSION->sorttype = $_GET['sorttype'];
    }
    else
    {
        $sortval = 1;
        $sortorder = " order by gtimemodified DESC";
        $SESSION->sortorder=$sortorder;
        @$SESSION->sortval = $sortval;
        @$SESSION->sorttype = $_GET['sorttype'];
    }
}
else if(isset($_GET['sorttype']) && $_GET['sorttype']=="assignment")
{
    if($_GET['sort']==1)
    {
        $sortval = 2;
        $sortorder = " order by z.name ASC";
        $SESSION->sortorder=$sortorder;
        @$SESSION->sortval = $sortval;
        @$SESSION->sorttype = $_GET['sorttype'];
    }
    else
    {
        $sortval = 1;
        $sortorder = " order by z.name DESC";
        $SESSION->sortorder=$sortorder;
        @$SESSION->sortval = $sortval;
        @$SESSION->sorttype = $_GET['sorttype'];
    }
}
else
{
    if(@$SESSION->sortorder=='')
    {
        $sortval = 1;
        $sortorder = " order by x.id DESC";
        $SESSION->sortorder=$sortorder;
        $SESSION->sortval=$sortval;
        @$SESSION->sorttype = '';
    }
    else
    {
        $sortval = @$SESSION->sortval;
        $sortorder = @$SESSION->sortorder;
        @$SESSION->sorttype = '';
    }
}


if(isset($_REQUEST["page"]))
$page = (int)$_REQUEST["page"];
else
$page = 1;
$setLimit = 10;
$pageLimit = ($page * $setLimit) - $setLimit;


$list_all_courses = $DB->get_records_sql('SELECT `id` as courseid , `fullname` as coursename FROM {course}  WHERE 1 order by `id` DESC');
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
AND r.shortname =  'student'
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


$list_all_students_username = $DB->get_records_sql("SELECT u.`username` , u.`id` as studentuserid FROM
{user} u");
$studentsstrusername = '';
foreach($list_all_students_username as $list_all_students_username)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $studentsstrusername = $studentsstrusername.'"'.$list_all_students_username->username."|".$list_all_students_username->studentuserid.'",';
}
$studentsstrusername = "[".$studentsstrusername."]";

$list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
        mdl_assign z");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";
//echo $studentsstr; 
//$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid WHERE 1';
if(isset($_POST['search']) && $_POST['search']==" Search ")
{
    if(isset($_POST['courseid']) && $_POST['courseid']>0 && $_POST['coursename']!='')
    {
        $SESSION->courseid = '';
        $SESSION->courseid = $_POST['courseid'];
        $SESSION->coursename = '';
        $SESSION->coursename = $_POST['coursename'];
    }
    if($_POST['coursename']=='')
    {
        $SESSION->courseid = '';
    }
    if(isset($_POST['studentid']) && $_POST['studentid']>0 && $_POST['studentname']!='')
    {
        $SESSION->studentid = '';
        $SESSION->studentid = $_POST['studentid'];
        $SESSION->studentname = '';
        $SESSION->studentname = $_POST['studentname'];
    }
    if(isset($_POST['assessmentid']) && $_POST['assessmentid']>0 && $_POST['assessmentname']!='')
    {
        $SESSION->assessmentid = '';
        $SESSION->assessmentid = $_POST['assessmentid'];
        $SESSION->assessmentname = '';
        $SESSION->assessmentname = $_POST['assessmentname'];
    }
    if(isset($_POST['studentuserid']) && $_POST['studentuserid']>0 && $_POST['studentusername']!='')
    {
        $SESSION->studentuserid = '';
        $SESSION->studentuserid = $_POST['studentuserid'];
        $SESSION->studentusername = '';
        $SESSION->studentusername = $_POST['studentusername'];
    }
    if($_POST['studentname']=='')
    {
        $SESSION->studentid = '';
    }
    if($_POST['studentusername']=='')
    {
        $SESSION->studentuserid = '';
    }
    if($_POST['assessmentname']=='')
    {
        $SESSION->assessmentid = '';
    }
}
if(isset($_POST['coursename']) && $_POST['coursename']=='')
{
    $SESSION->coursename = '';
}
if(isset($_POST['studentname']) && $_POST['studentname']=='')
{
    $SESSION->studentname = '';
}
if(isset($_POST['studentusername']) && $_POST['studentusername']=='')
{
    $SESSION->studentusername = '';
}
if(isset($_POST['assessmentname']) && $_POST['assessmentname']=='')
{
    $SESSION->assessmentname = '';
}
if(isset($_GET['showall']) && $_GET['showall']=='1')
{
     $SESSION->courseid = '';
     $SESSION->studentid = '';
     $SESSION->studentuserid = '';
     $SESSION->assessmentid = '';
     $SESSION->studentname = '';
     $SESSION->studentusername = '';
     $SESSION->coursename = '';
     $SESSION->assessmentname = '';
     $SESSION->sortorder = " order by x.id DESC";
     $sortval = 1;
}

if(@$SESSION->courseid>0 && @$SESSION->studentid=='' && @$SESSION->assessmentid=='')
{
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x LEFT JOIN {user} as y ON x.userid = y.id '
        . ' LEFT JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.id as gradeitemid, g.userid as uid , g.grader as graderid, g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x LEFT JOIN {user} as y ON x.userid = y.id '
        . ' LEFT JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;

     $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND z.grade!=0");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";

}
else if(@$SESSION->courseid=='' && @$SESSION->studentid>0 && @$SESSION->assessmentid=='')
{
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;


}
else if(@$SESSION->courseid>0 && @$SESSION->studentid=='' && @$SESSION->assessmentid>0)
{
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND z.id ='.$SESSION->assessmentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND z.id ='.$SESSION->assessmentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND z.grade!='0'");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";


}
else if(@$SESSION->courseid>0 && @$SESSION->studentid>0 && @$SESSION->assessmentid=='')
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
      $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND z.grade!='0'");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";

}
else if(@$SESSION->courseid>0 && @$SESSION->studentid>0 && @$SESSION->assessmentid>0)
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.attemptnumber = x.attemptnumber LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid  AND g.attemptnumber = x.attemptnumber LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND z.grade!='0'");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";

}
else if(@$SESSION->courseid=='' && @$SESSION->studentuserid>0 && @$SESSION->assessmentid=='')
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;

}
else if(@$SESSION->courseid>0 && @$SESSION->studentuserid>0 &&  @$SESSION->assessmentid=='')
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND z.grade!='0'");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";

}
else if(@$SESSION->courseid>0 && @$SESSION->studentuserid>0 &&  @$SESSION->assessmentid>0)
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , max(g.timemodified) as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON x.assignment = g.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' AND x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND z.grade!='0'");
$assessmentstr = '';
foreach($list_all_assessments as $list_all_assessments)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $assessmentstr = $assessmentstr.'"'.$list_all_assessments->assignmentname."|".$list_all_assessments->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";

}
else
{
    $sql_all = 'SELECT x.id as rowid , x.status,  x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.status ="submitted" '.$set_query.' '.$SESSION->sortorder;

    $sql = 'SELECT x.id as rowid , x.status , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.status ="submitted" '.$set_query.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;


}
//echo $sql_all;
//echo '<hr>';
//echo $sql;
if(isset($_POST['coursename']) && isset($_POST['assessmentname']))
{
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
	
	if($SESSION->set==1)
	{
		$userid = $list->userid;
		$firstname = $list->firstname;
		$lastname = $list->lastname;
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
	if($SESSION->set==1)
	{
		$file_all = $DB->get_record_sql("SELECT * FROM {files} WHERE `itemid` = '".$list->rowid."' AND `userid` = '".$list->userid."' AND `filesize`!='0'");
		if(@$file_all->contextid>0)
		{
			$download = $CFG->wwwroot.'/pluginfile.php/'.$file_all->contextid.'/assignsubmission_file/submission_files/'.$file_all->itemid.'/'.$file_all->filename.'?forcedownload=1';
		}
		else
		{
			$download = '';
		}
	}
	else
	{
		$file_all = $DB->get_record_sql("SELECT * FROM {files} WHERE `itemid` = '".$list->gradeitemid."' AND `userid` = '".$list->graderuserid."' AND `filesize`!='0' AND `filearea` = 'feedback_files'" );
		
		if(@$file_all->contextid>0)
		{
			$download = $CFG->wwwroot.'/pluginfile.php/'.$file_all->contextid.'/assignfeedback_file/feedback_files/'.$list->gradeitemid.'/'.$file_all->filename.'?forcedownload=1';
		}
		else
		{
			$download='';
		}
	}
	
	$str1 = substr($file_all->contenthash,0,2);
	$str2 = substr($file_all->contenthash,2,2);
	
	$submission_file = 'C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$file_all->contenthash;
//	$comparison_array = array();
	$comparison_array[$list->assignmentid][] = 'C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$file_all->contenthash;
	
	
	//echo '<hr>';
	
	
//echo '<pre>';
	//print_r($file_all); die;
	$arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$userid, 'name'=>$firstname.' '.$lastname, 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>date("F j, Y, g:i a",$list->timemodified),'timemodifiedg'=>$gtimemodified,'gradeexists'=>$grade_exists,'gradername'=>$gradername,'graderid'=>$list->graderid,'gradeitemid'=>$list->gradeitemid, 'itemid'=> $file_all->itemid, 'contextid'=> $file_all->contextid, 'contenthash'=>$file_all->contenthash, 'pathnamehash'=>$file_all->pathnamehash, 'filename'=>$file_all->filename,'source'=>$file_all->source,'download'=>$download);
unset($file_all);
unset($download);
unset($userid);
unset($firstname);
unset($lastname);

unset($str1);
unset($str2);
unset($submission_file);
}
//echo '<pre>';
//print_r($comparison_array);

$plag_score_array = array();
foreach($comparison_array as $key=>$val)
{
for($k=0;$k<count($val);$k++)
{
	for($j=0;$j<count($val);$j++)
	{
		
		$dest1 = "C:/xampp/htdocs/admin/tool/assesmentplagiarism/copied/".mt_rand().".docx";
		copy($val[$k],$dest1);
		
		
			//$content1 = read_docx($dest1);
			$myfile1 = fopen($dest1, "r");
			$content1 = fgets($myfile1);
			
			
			unlink($dest1);
			unset($dest1);
		
		
		
		$dest2 = "C:/xampp/htdocs/admin/tool/assesmentplagiarism/copied/".mt_rand().".docx";
		copy($val[$j],$dest2);
		//print $val[$j].".docx";
		
			$myfile2 = fopen($dest2, "r");
			$content2 = fgets($myfile2);
			
			
			unlink($dest2);
			unset($dest2);
			
			$objPlag = new Plagiarism();
			$score = $objPlag->process($content1,$content2);
			$plag_score_array[$key][$val[$k]][]=$score;
		//	echo '<br/>';
			unset($score);
	}
}
}
//echo '<pre>';
//print_r($plag_score_array);
foreach($plag_score_array as $key=>$val)
{
	foreach($val as $key1=>$val1)
	
	{
		$sum = array_sum($val1);
		$score_array_mean[] = $sum/count($val1);
		unset($sum);
	}
}
//echo '<pre>';
//print_r($score_array_mean);
if($SESSION->sortval==1)
{
    $sortvalold = 2;
}
else
{
    $sortvalold = 1;
}

for($k=0;$k<count($arr);$k++)
{
	$arr[$k]['plagiarism_mean_score'] = round($score_array_mean[$k],2);
}
}
else
{
	$arr=array();
}
//echo '<pre>';
//print_r($arr);
$renderable = new \tool_assesmentplagiarism\output\index_page($arr,$coursestr,$assessmentstr,$studentsstr,$studentsstrusername,$page,@count($list_all),@$SESSION->studentname,@$SESSION->coursename,@$SESSION->assessmentname,@$SESSION->studentusername,@$SESSION->courseid,@$SESSION->assessmentid,@$SESSION->studentid,@$SESSION->studentuserid,@$SESSION->sortval,$sortvalold,@$SESSION->sorttype,@$SESSION->showallstudent,$SESSION->set,$score_array_mean,$plag_score_array);
echo $output->render($renderable); 
if(!isset($_GET['showallstudent']) || @$_GET['showallstudent']!=1)
{
    echo '<br/><br/>';
    echo displayPaginationHere(count($list_all),$setLimit,$page); // Call the Pagination Function to display Pagination.
}
echo $OUTPUT->footer();