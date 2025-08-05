<?php
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');
//admin_externalpage_setup('index');
 
// Set up the page.
$title = get_string('pluginname', 'tool_timestamp');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/timestamp/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();
$output = $PAGE->get_renderer('tool_timestamp');
 
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
global $SESSION;

global $DB;
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
    $c = 0;
    foreach($_POST['updateidarray'] as $key=>$val)
    {
        $newdate1 = "newdate1_".$val;
        $newdate2 = "newdate2_".$val;
        if(@$_POST[$newdate1]!='')
        {
            $u1 = $DB->execute("UPDATE {assign_submission} set `timemodified` = '".strtotime($_POST[$newdate1])."' WHERE `id` = '".$val."'");
            $ufile_1 = $DB->execute("UPDATE {files} set `timecreated` = '".strtotime($_POST[$newdate1])."' WHERE `component` = 'assignsubmission_file' AND `filearea` = 'submission_files' AND `itemid` = '".$val."' AND `userid` = '".$useridarray[$c]."'");
        }
        if(@$_POST[$newdate2]!='')
        {
            $u2 = $DB->execute("UPDATE {assign_grades} set `timemodified` = '".strtotime($_POST[$newdate2])."' WHERE `userid` = '".$useridarray[$c]."' AND `assignment` = '".$assidarray[$c]."'");
            $ufile_2 = $DB->execute("UPDATE {files} set `timecreated` = '".strtotime($_POST[$newdate2])."' WHERE `component` = 'assignfeedback_file' AND `filearea` = 'feedback_files' AND `itemid` = '".$val."' AND `userid` = '".$graderidarray[$c]."'");
        
            
        }
       
        unset($ufile_1);
       
        unset($ufile_2);
        unset($newdate2);
        unset($newdate1);
        $c++;
    }
    if($_POST['sort']=='') { $sort = 1; } else {  $sort = $_POST['sort']; } 
    
if($u1==true || $u2==true)
{
    
    $urltogo= $CFG->wwwroot.'/admin/tool/timestamp/index.php?page='.$_POST['page'].'&update=1&sort='.$sort.'&sorttype='.$_POST['sorttype'].'&showallstudent='.$_REQUEST['showallstudent'];
    ?>
<div style="padding-left: 38px;"><img src='<?php echo $CFG->wwwroot; ?>/admin/tool/timestamp/templates/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>

<?php
} 
else
{
    $urltogo= $CFG->wwwroot.'/admin/tool/timestamp/index.php?page='.$_POST['page'].'&update=0&sort='.$sort.'&sorttype='.$_POST['sorttype'].'&showallstudent='.$_REQUEST['showallstudent'];
    ?>
<div style="padding-left: 38px;"><img src='<?php echo $CFG->wwwroot; ?>/admin/tool/timestamp/templates/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
}
}
if(isset($_GET['update'])) {
if($_GET['update']==1)
   { 
       echo '<div class="success-msg">
  <i class="fa fa-check"></i>
  The data has been successfully updated!
   </div>'; }
   else 
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  Some error occured! Please try again later or contact to a technical assistant.
</div>';
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


$list_all_courses = $DB->get_records_sql('SELECT `id` as courseid , `fullname` as coursename FROM {course} WHERE 1');
$coursestr = '';
foreach($list_all_courses as $list_all_courses)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $coursestr = $coursestr.'"'.$list_all_courses->coursename."|".$list_all_courses->courseid.'",';
}
$coursestr = "[".$coursestr."]";

$list_all_students = $DB->get_records_sql("SELECT u.`firstname` , u.`lastname` , u.`id` as studentid FROM
{user} u");
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
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid, g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;

     $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid);
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
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;


}
else if(@$SESSION->courseid>0 && @$SESSION->studentid=='' && @$SESSION->assessmentid>0)
{
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND z.id ='.$SESSION->assessmentid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND z.id ='.$SESSION->assessmentid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid);
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
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
      $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid);
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
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid  LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid);
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
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;

}
else if(@$SESSION->courseid>0 && @$SESSION->studentuserid>0 &&  @$SESSION->assessmentid=='')
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid);
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
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' '.$SESSION->sortorder;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' AND z.id = '.$SESSION->assessmentid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid);
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
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE 1 '.$SESSION->sortorder;

    $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE 1 '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;


}
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
    $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>date("F j, Y, g:i a",$list->timemodified),'timemodifiedg'=>$gtimemodified,'gradeexists'=>$grade_exists,'gradername'=>$gradername,'graderid'=>$list->graderid);
}

//die;
if($SESSION->sortval==1)
{
    $sortvalold = 2;
}
else
{
    $sortvalold = 1;
}
$renderable = new \tool_timestamp\output\index_page($arr,$coursestr,$assessmentstr,$studentsstr,$studentsstrusername,$page,@count($list_all),@$SESSION->studentname,@$SESSION->coursename,@$SESSION->assessmentname,@$SESSION->studentusername,@$SESSION->courseid,@$SESSION->assessmentid,@$SESSION->studentid,@$SESSION->studentuserid,@$SESSION->sortval,$sortvalold,@$SESSION->sorttype,@$SESSION->showallstudent);
echo $output->render($renderable); 
if(!isset($_GET['showallstudent']) || @$_GET['showallstudent']!=1)
{
    echo '<br/><br/>';
    echo displayPaginationHere(count($list_all),$setLimit,$page); // Call the Pagination Function to display Pagination.
}
echo $OUTPUT->footer();