<?php
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');
//admin_externalpage_setup('index');
 
// Set up the page.
$title = get_string('pluginname', 'tool_assesmentresults');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/assesmentresults/courseindex.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();
$output = $PAGE->get_renderer('tool_assesmentresults');
 
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
global $SESSION;

global $DB;
if(isset($_GET['showallstudent']) && $_GET['showallstudent']==1)
{
    $showallstudent = $_GET['showallstudent'];
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
{user} u WHERE u.deleted!='1'");
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
        mdl_assign z WHERE z.grade!= 0");
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
    
     $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission  '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' JOIN {assign} as z ON x.assignment = z.id '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '                       
             . ' WHERE z.course ='.$SESSION->courseid.' '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' JOIN {assign} as z ON x.assignment = z.id '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE z.course ='.$SESSION->courseid.' '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
         $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND `grade`!= 0");
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
    
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '         
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    // $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' '.$SESSION->sortorder;
 
   //  $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
     //   . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;


}
else if(@$SESSION->courseid>0 && @$SESSION->studentid=='' && @$SESSION->assessmentid>0)
{
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = x.assignment AND `userid` = x.userid) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '         
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE z.course ='.$SESSION->courseid.' AND z.grade!=0 '
            . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = x.assignment AND `userid` = x.userid) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE z.course ='.$SESSION->courseid.' AND z.grade!=0 '
              . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    
    
    
    
    
    
    
    
    
    // $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND z.id ='.$SESSION->assessmentid.' '.$SESSION->sortorder;
 
   //  $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
     //   . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE z.course ='.$SESSION->courseid.' AND z.id ='.$SESSION->assessmentid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND `grade`!= 0");
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
    
    
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '         
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.'  '
              . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    //$sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     //$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
      $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND `grade`!= 0");
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
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '         
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
            . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.'  '
              . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
              . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    //$sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     //$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
      $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND `grade`!= 0");
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
    
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '         
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentuserid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentuserid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    
    
    
    
    
    
    //$sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' '.$SESSION->sortorder;
 
     //$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;

}
else if(@$SESSION->courseid>0 && @$SESSION->studentuserid>0 &&  @$SESSION->assessmentid=='')
{
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '  
                 .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '             
             . ' WHERE x.userid ='.$SESSION->studentuserid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentuserid.'  '
              . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    
    
 //   $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
   //     . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     //$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentuserid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND `grade`!= 0");
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
   $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '         
           .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentuserid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
            . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentuserid.'  '
              . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
              . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    //$sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     //$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
      $list_all_assessments = $DB->get_records_sql("SELECT z.id as assignmentid , z.name as assignmentname , z.course FROM 
         mdl_assign as z WHERE z.course =".$SESSION->courseid." AND `grade`!= 0");
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
    $sql_all = '';
     
      $sql = '';
 
    
    
    
    

}

//echo $sql_all;
//echo '<hr>';
//echo $sql;
if(@$SESSION->courseid!='' || @$SESSION->studentuserid!='' ||  @$SESSION->assessmentid!='' || @$SESSION->studentid!='')
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
if($sql!='' && $sql_all!='')
{
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
$arr = array();
foreach($list as $list)
{
    $contextid = get_context_instance(CONTEXT_COURSE, $list->courseid);
     $sql_module = "SELECT id FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
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
    }
    
}
else
{
    
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
    $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>date("F j, Y, g:i a",$list->timemodified),'timemodifiedg'=>$gtimemodified,'gradeexists'=>$grade_exists,'gradername'=>$gradername,'graderid'=>$list->graderid,'gradeitemid'=>$list->gradeitemid,"result"=>$scale_text,"countsubmission"=>$list->countsubmission);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);    
            
    }
}
//echo '<pre>';
//print_r($arr);
if($SESSION->sortval==1)
{
    $sortvalold = 2;
}
else
{
    $sortvalold = 1;
}
}
$renderable = new \tool_timestamp\output\index_page($arr,$coursestr,$assessmentstr,$studentsstr,$studentsstrusername,$page,@count($list_all),@$SESSION->studentname,@$SESSION->coursename,@$SESSION->assessmentname,@$SESSION->studentusername,@$SESSION->courseid,@$SESSION->assessmentid,@$SESSION->studentid,@$SESSION->studentuserid,@$SESSION->sortval,$sortvalold,@$SESSION->sorttype,@$SESSION->showallstudent);
echo $output->render($renderable); 
if(!isset($_GET['showallstudent']) || @$_GET['showallstudent']!=1)
{
    echo '<br/><br/>';
    echo displayPaginationHere(count($list_all),$setLimit,$page); // Call the Pagination Function to display Pagination.
}
echo $OUTPUT->footer();