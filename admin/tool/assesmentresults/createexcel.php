<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
global $SESSION;
global $DB;
require_once($CFG->libdir."/pdfclass/PHPExcel.php");
require_once($CFG->libdir."/pdfclass/PHPExcel/Writer/Excel5.php");
$allowedstatus = ['satisfactory','notsatisfactory','notyetgraded','nosubmission','openattempt'];
$selectedstatus = isset($SESSION->statusfilter) ? $SESSION->statusfilter : '';
if(@$SESSION->courseid>0 && @$SESSION->studentid=='' && @$SESSION->assessmentid=='')
{
    
      $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission  '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '       
             .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '  
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '                           
             . ' WHERE z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;     

}
else if(@$SESSION->courseid=='' && @$SESSION->studentid>0 && @$SESSION->assessmentid=='')
{
    
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
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
              .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '  
               .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '                
             . ' WHERE x.userid ='.$SESSION->studentid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      
 
   

}
else if(@$SESSION->courseid>0 && @$SESSION->studentid=='' && @$SESSION->assessmentid>0)
{
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
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
             .' LEFT JOIN {user} as us ON g.grader = us.id   '
             .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '           
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE z.course ='.$SESSION->courseid.' AND z.grade!=0 '
            . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
     
 
    
    
}
else if(@$SESSION->courseid>0 && @$SESSION->studentid>0 && @$SESSION->assessmentid=='')
{
    
    
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
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
               .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '       
             . ' WHERE x.userid ='.$SESSION->studentid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      
    
    
    
    
   
}
else if(@$SESSION->courseid>0 && @$SESSION->studentid>0 && @$SESSION->assessmentid>0)
{
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
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
             .' LEFT JOIN {user} as us ON g.grader = us.id  '
               .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '  
             .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE x.userid ='.$SESSION->studentid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
            . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
     
 
    

}
else if(@$SESSION->courseid=='' && @$SESSION->studentuserid>0 && @$SESSION->assessmentid=='')
{
    
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
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
                     .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '   
                              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid ' 
             . ' WHERE x.userid ='.$SESSION->studentuserid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      
}
else if(@$SESSION->courseid>0 && @$SESSION->studentuserid>0 &&  @$SESSION->assessmentid=='')
{
    
    
    $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid ,'
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
             .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '             
            .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid ' 
             . ' WHERE x.userid ='.$SESSION->studentuserid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
     
 

}
else if(@$SESSION->courseid>0 && @$SESSION->studentuserid>0 &&  @$SESSION->assessmentid>0)
{
   $sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , cm.id as cmid , '
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
             .' LEFT JOIN {course_modules} as cm ON cm.course = gi.courseid AND cm.instance = x.assignment '         
           .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid ' 
             . ' WHERE x.userid ='.$SESSION->studentuserid.'  '
            . ' AND z.course ='.$SESSION->courseid.' AND z.grade!=0 '
            . ' AND x.assignment ='.$SESSION->assessmentid.'  '
             . 'and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . 'GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      
   

}


else if($SESSION->termclause!='' && $SESSION->yearclause!='')
{
	
	
	$sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , '
             . ' x.assignment as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
             .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission , course.startdate as startdate, course.id as courseid '
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
             . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
           .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
             . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
             . 'LEFT JOIN {user} as y ON x.userid = y.id '   
             .' LEFT JOIN {user} as us ON g.grader = us.id '        
				 .' LEFT JOIN {course} as course ON course.id = z.course '        
           .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
            
            . ' WHERE z.grade!=0 '
			
			. ' AND '. $SESSION->termclause .' AND '. $SESSION->yearclause 
             . ' and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . ' GROUP BY x.assignment , x.userid '.$SESSION->sortorder;
     
      $sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment as assignmentid, y.firstname , y.lastname, y.id , z.id as assignmentid , z.name as assignmentname , g.grade as recorded_grade , '
             . 'z.course , g.id as gradeitemid , g.userid as uid , g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified , '
             . 'gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , '
              .' us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid , count(app.id) as countsubmission , course.startdate as startdate , course.id as courseid ' 
             . 'FROM {assign_submission} as x '
             . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = x.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
              . 'LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) '
          .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = x.userid  '
              . ' LEFT JOIN {assign} as z ON x.assignment = z.id AND z.grade!=0 '
              . ' LEFT JOIN {user} as y ON x.userid = y.id '
               .' LEFT JOIN {user} as us ON g.grader = us.id '         
			    .' LEFT JOIN {course} as course ON course.id = z.course '    
              .' LEFT JOIN {assign_submission} as app ON app.assignment = x.assignment AND app.userid = x.userid '          
             . ' WHERE z.grade!=0 '
			
			. ' AND '. $SESSION->termclause . ' AND '. $SESSION->yearclause 
             . ' and x.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = x.assignment AND `userid` = gg.userid) '
             . ' GROUP BY x.assignment , x.userid '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;
 
    
    
    
    
    //$sql_all = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id , us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
      //  . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder;
 
     //$sql = 'SELECT x.id as rowid , x.userid, x.timemodified, x.assignment, y.firstname , y.lastname, y.id ,  us.firstname as graderfirstname , us.lastname as graderlastname , us.id as graderuserid, z.id as assignmentid , z.name as assignmentname , z.course , g.userid as uid , g.id as gradeitemid, g.grader as graderid , g.assignment as ass , g.timemodified as gtimemodified FROM {assign_submission} as x JOIN {user} as y ON x.userid = y.id '
       // . ' JOIN {assign} as z ON x.assignment = z.id LEFT JOIN {assign_grades} as g ON g.assignment = x.assignment AND g.userid = x.userid AND g.id = (SELECT max(`id`) as maxid FROM {assign_grades} agr WHERE agr.userid = x.userid AND agr.assignment = x.assignment) LEFT JOIN {user} as us ON g.grader = us.id WHERE x.userid ='.$SESSION->studentid.' AND z.course = '.$SESSION->courseid.' '.$SESSION->sortorder.' LIMIT '.$pageLimit.' , '.$setLimit;



	
}




else
{
    $sql_all = '';
     
     
 
    
    
    
    

}

//echo $sql_all;
//echo '<hr>';
//echo $sql;

if(@$SESSION->courseid!='' || @$SESSION->studentuserid!='' ||  @$SESSION->assessmentid!='' || @$SESSION->studentid!='' || ($SESSION->termclause!='' && $SESSION->yearclause!=''))
{
$list = $DB->get_records_sql($sql_all);

if($sql_all!='')
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
	
	//OBS CHECKLIST

 $sql_checklist2 = "SELECT mcl.`id` as assignid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` LIKE '%".addslashes($list->assignmentname)." | Observation Checklist%'";
	//echo '<br/>';

        $list_all_deb2 = $DB->get_record_sql($sql_checklist2);
        if($list_all_deb2->checklistid>0)
        {
        $obs_url = $CFG->wwwroot.'/mod/checklist/view.php?id='.$list_all_deb2->checklistid;
        }
        else
        {
                $obs_url = '';
        }

    $cmid = $list->cmid;
    $scl = strtolower(trim($scale_text));
    if ($list->countsubmission == 0) {
        $statuscode = 'nosubmission';
        $statuslabel = 'No Submission';
    } else if ($scl === 'satisfactory') {
        $statuscode = 'satisfactory';
        $statuslabel = 'Satisfactory';
    } else if ($scl === 'not satisfactory') {
        $statuscode = 'notsatisfactory';
        $statuslabel = 'Not Satisfactory';
    } else if (strpos($scl, 'not yet graded') !== false) {
        $statuscode = 'notyetgraded';
        $statuslabel = 'Not Yet Graded';
    } else {
        $statuscode = 'openattempt';
        $statuslabel = 'Open Attempt';
    }
    $submissionurl = new moodle_url('/mod/assign/view.php', ['id' => $cmid]);
    $viewurl  = new moodle_url('/mod/assign/view.php', ['id' => $cmid, 'action' => 'grading']);
    $gradeurl = new moodle_url('/mod/assign/view.php', ['id' => $cmid, 'rownum' => 0, 'action' => 'grader', 'userid' => $list->userid]);

    $arr[] = array('name'=>$list->firstname.' '.$list->lastname, 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'gradeexists'=>$grade_exists,'gradername'=>$gradername,"result"=>$scale_text,'userid'=>$list->userid,'rowid'=>$list->rowid,'cmid'=>$cmid,"countsubmission"=>$list->countsubmission,'obs_url'=>$obs_url,'statuscode'=>$statuscode,'statuslabel'=>$statuslabel,'studenturl'=>$submissionurl->out(false),'viewurl'=>$viewurl->out(false),'gradeurl'=>$gradeurl->out(false));
    unset($grade_val);
    unset($scale_text);
    unset($timemodified);
    unset($list_all_stu);
    unset($activitydate);    
        unset($obs_url);
                        unset($sql_checklist2);
                        unset($list_all_deb2);

        }
    }
}

if ($selectedstatus !== '') {
    $arr = array_values(array_filter($arr, function($row) use ($selectedstatus) {
        return $row['statuscode'] === $selectedstatus;
    }));
}

$objPHPExcel = new PHPExcel();
// Set document properties
$objPHPExcel->getProperties()->setCreator("ACCIT Administrator")
                             ->setLastModifiedBy("ACCIT Administrator")
                             ->setTitle("Office 2007 XLSX Test Document")
                             ->setSubject("Office 2007 XLSX Test Document")
                             ->setDescription("Generated EXCEL Report for ACCIT Administrator")
                             ->setKeywords("ACCIT Administrator")
                             ->setCategory("Generated EXCEL report");

// Add some data
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(46);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(41);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(28);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ASSESSMENT')
            ->setCellValue('B1', 'STUDENT NAME')
            ->setCellValue('C1', 'STATUS')
            ->setCellValue('D1', 'TRAINER/GRADER')
            ->setCellValue('E1', 'ACTION FOR TRAINER & ACADEMIC')
            ->setCellValue('F1', 'STUDENT SUBMISSION LINK')
            ->setCellValue('G1', 'OBSERVATION CHECKLIST');


$objPHPExcel->getActiveSheet()
        ->getStyle('A1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');
      
      $objPHPExcel->getActiveSheet()
        ->getStyle('B1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');
      
      $objPHPExcel->getActiveSheet()
        ->getStyle('C1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');
      
      $objPHPExcel->getActiveSheet()
        ->getStyle('D1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');
      
      $objPHPExcel->getActiveSheet()
        ->getStyle('E1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');
      $objPHPExcel->getActiveSheet()
        ->getStyle('F1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');
      $objPHPExcel->getActiveSheet()
        ->getStyle('G1')
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('82ce73');

// Miscellaneous glyphs, UTF-8
$rowCount=0;
for($i=2;$i<(count($arr)+2);$i++)
{
      $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $arr[$rowCount]['assignmentname']);
      $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $arr[$rowCount]['name']);
      $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $arr[$rowCount]['statuslabel']);
      $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $arr[$rowCount]['gradername']);
      $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $arr[$rowCount]['viewurl'].' | '.$arr[$rowCount]['gradeurl']);
      $objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $arr[$rowCount]['studenturl']);
          if($arr[$rowCount]['obs_url']!='')
          {
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $arr[$rowCount]['obs_url']);
          }
          else
          {
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, 'NA');
          }
      if($arr[$rowCount]['countsubmission']>1)
      {
      $objPHPExcel->getActiveSheet()
        ->getStyle('A'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');

      $objPHPExcel->getActiveSheet()
        ->getStyle('B'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');

      $objPHPExcel->getActiveSheet()
        ->getStyle('C'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');

      $objPHPExcel->getActiveSheet()
        ->getStyle('D'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');

      $objPHPExcel->getActiveSheet()
        ->getStyle('E'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');

      $objPHPExcel->getActiveSheet()
        ->getStyle('F'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');

      $objPHPExcel->getActiveSheet()
        ->getStyle('G'.$i)
        ->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('e2b3b3');
      }

      $rowCount++;

}

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('ACCIT-Result');
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a clients web browser (Excel5)
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment;filename="Result-'.@date("F j, Y, g:i a",strtotime("now")).'.xls"');


$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');


