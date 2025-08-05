<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

global $SESSION;

global $DB;

$sortval = " order by assignmentid ASC";
$sort = " ";
$SESSION->sort=$sort;

if(@$SESSION->studentid!='')
{
    $list_students = $DB->get_record_sql("SELECT u.`firstname` , u.`lastname` , u.`id` as studentid FROM
{user} u WHERE `id` = '".$SESSION->studentid."'");
    $stu_name = $list_students->firstname." ".$list_students->lastname;
}
if(@$SESSION->courseid!='')
{
   $list_courses = $DB->get_record_sql('SELECT `fullname` as coursename FROM {course} WHERE `id` = "'.$SESSION->courseid.'"');
   $coursename = $list_courses->coursename;
}

//echo $studentsstr; 
//$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.userid as uid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid WHERE 1';

if(@$SESSION->courseid>0 && @$SESSION->studentid=='')
{
    $contextid = get_context_instance(CONTEXT_COURSE, $SESSION->courseid);

 
      $sql_all =  'SELECT z.id as rowid , gg.usermodified , z.userid , y.firstname , gg.feedback , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid '
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , gg.usermodified , z.userid , y.firstname , y.lastname , gg.feedback, ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid '
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid '
            .' '.$SESSION->sort;
 

}
else if(@$SESSION->courseid=='' && @$SESSION->studentid>0)
{
    
      $sql_all =   'SELECT z.id as rowid , gg.usermodified , z.userid , gg.feedback , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$SESSION->studentid.' GROUP BY z.assignment , z.userid ';
 
   
    $sql =   'SELECT z.id as rowid , gg.usermodified , z.userid , y.firstname , gg.feedback, y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$SESSION->studentid.' GROUP BY z.assignment , z.userid '
            .' '.$SESSION->sort;
 
}
else if(@$SESSION->courseid>0 && @$SESSION->studentid>0)
{
    $contextid = get_context_instance(CONTEXT_COURSE, $SESSION->courseid);
    $sql_all =   'SELECT z.id as rowid , gg.usermodified , gg.feedback , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '                      
            .' WHERE z.userid = '.$SESSION->studentid.' AND ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , gg.usermodified , z.userid , gg.feedback , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$SESSION->studentid.' AND ax.course = '.$SESSION->courseid.' GROUP BY z.assignment , z.userid '
            .' '.$SESSION->sort;
    
    
}
/*else
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , z.course , gi.scaleid as scaleid, gh.finalgrade AS Recorded_Grade, gi.itemname AS Item_Name FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id JOIN {grade_grades_history} gh ON y.id=gh.userid LEFT JOIN {grade_items} gi ON gh.itemid=gi.id JOIN {course} c ON gi.courseid=c.id WHERE gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL '.$SESSION->sort;
 
     $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , z.course , gi.scaleid as scaleid, gh.finalgrade AS Recorded_Grade, gi.itemname AS Item_Name FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
        . ' JOIN {assign} as z ON x.assignment = z.id JOIN {grade_grades_history} gh ON y.id=gh.userid LEFT JOIN {grade_items} gi ON gh.itemid=gi.id JOIN {course} c ON gi.courseid=c.id WHERE gh.finalgrade IS NOT NULL and gi.itemname IS NOT NULL '.$SESSION->sort.' LIMIT '.$pageLimit.' , '.$setLimit;
 

}*/
else
{
    $sql_all = '';
    $sql = '';
}
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
//echo '<pre>';
//print_r($list_all); 
//$list_all_count = count($list_all);

$list = $DB->get_records_sql($sql);

//echo '<pre>';
//print_r($list);
$arr = array();

foreach($list as $list)
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
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
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
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
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
    }
}
else
{
    
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
}
?>
<html>
    <head>
        <title>Print of Assesment dates & Results</title>
        <style>
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 90%;
}
#info {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

#customers td, #customers th {
    border: 1px solid #aaa;
    padding: 8px;
}
#info td, #info th {
    border: 1px solid #aaa;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
#info tr:nth-child(even){background-color: #f2f2f2;}

#info tr:hover {background-color: #ddd;}

#info th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
<script type="text/javascript">
    function printpage() {
       
        var printButton = document.getElementById("printpagebutton");
      
        printButton.style.visibility = 'hidden';
       
        window.print()
       
       
        printButton.style.visibility = 'visible';
    }
</script>
    </head>
    <center> <br/>
        <img src="http://www.accit.nsw.edu.au/img/logo.png" border="0" />
        <br/><br/><input type="button" name="printpagebutton" id="printpagebutton" value=" Print " onclick="javascript: printpage();" style="height: 45px; width: 105px; font-size:26px; float: left; margin-left: 68px;" />
        <br/><br/><br/><br/>
        <table id="info" style="float:left; margin-left: 68px;">
  <?php if($stu_name!='') { ?>
  <tr>
    <td>Student</td>
    <td><?php echo $stu_name; ?></td>
  </tr>
  <?php } ?>
  <?php if($coursename!='') { ?>
  <tr>
    <td>Course</td>
    <td><?php echo $coursename; ?></td>
  </tr>
  <?php } ?>
        </table><div style="height: 95px;"></div>
      <table id="customers">
  <tr>
    <th>Assignment</th>
    <th>Name</th>
    <th>Username</th>
    <th>Last Updated Date</th>
    <th>Result</th>
  </tr>
  <?php foreach($arr as $key=>$val) { ?>
  <tr>
    <td><?php echo $val['assignmentname']; ?></td>
    <td><?php echo $val['name']; ?></td>
    <td><?php echo $val['username']; ?></td>
    <td><?php echo $val['timemodified']; ?></td>
    <td><?php echo $val['result']; ?></td>
  </tr>
  <?php } ?>
  
  
</table>

    </center>
</html>
