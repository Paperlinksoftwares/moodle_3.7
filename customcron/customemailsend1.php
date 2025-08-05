<?php
require_once('../config.php');
$sql = "SELECT uen.enrolid , en.courseid from {user_enrolments} as uen LEFT JOIN {enrol} as en ON en.id = uen.enrolid WHERE uen.userid = '810'";
$list_records = $DB->get_records_sql($sql); 
$row_assign_id=array();
foreach ($list_records as $key=>$val)
{
    $row=$DB->get_record_sql("SELECT id FROM {assign} as ass WHERE ass.course = '".$val->courseid."' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s') >= NOW() AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s')< NOW() + INTERVAL 7 DAY");    
    if($row->id!='')
    {
        $row_assign_id[$val->courseid]=$row->id;
    }
    unset($row); 
}
echo '<pre>';
print_r($row_assign_id);
$list_assign_id = array();
foreach($row_assign_id as $key=>$arrval)
{
    $row1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$arrval."' AND ass.userid='810'");
    $row2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$arrval."' AND ass.userid='810' AND ass.status != 'submitted'");

    if($row1->count==0 || $row2->count==0)
    {
        $list_assign_id[]=$arrval;
    }        
    else if($row1->count==1 || $row2->count==0)
    {
        $list_assign_id[]=$arrval;
    }
    else
    {
        //do nothing
    }
    unset($row1);
    unset($row2);
    
}
echo '<pre>';
print_r($list_assign_id);
die;

