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
 * Take Attendance
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir.'/adminlib.php');
$pageparams = new mod_attendance_take_page_params();

$id                     = required_param('id', PARAM_INT);
$pageparams->sessionid  = required_param('sessionid', PARAM_INT);
$pageparams->grouptype  = required_param('grouptype', PARAM_INT);
$pageparams->sort       = optional_param('sort', ATT_SORT_DEFAULT, PARAM_INT);
$pageparams->copyfrom   = optional_param('copyfrom', null, PARAM_INT);
$pageparams->viewmode   = optional_param('viewmode', null, PARAM_INT);
$pageparams->gridcols   = optional_param('gridcols', null, PARAM_INT);
$pageparams->page       = optional_param('page', 1, PARAM_INT);
$pageparams->perpage    = optional_param('perpage', get_config('attendance', 'resultsperpage'), PARAM_INT);

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
$sessiondetails = $DB->get_record('attendance_sessions', array('id' => $pageparams->sessionid), 'sessdate', MUST_EXIST);
//echo $sessiondetails->sessdate;
$att_id_val = $att->id;
$select = "attendanceid = '".$att_id_val."' AND acronym!='L' && acronym!='E'"; 
$att_id_status_set = $DB->get_records_select('attendance_statuses',$select);
$att_id_status_set_val = '';
$att_status_arr = array();
foreach($att_id_status_set as $att_id_status_set)
{
    $att_id_status_set_val = $att_id_status_set_val.$att_id_status_set->id.",";
    $att_status_arr["'".$att_id_status_set->acronym."'"] = $att_id_status_set->id;
}
$att_id_status_set_val = rtrim($att_id_status_set_val,',');

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:takeattendances', $context);
global $SESSION;
global $USER;
global $DB;
$SESSION->att_status_arr = $att_status_arr;
if(isset($_POST['updateatt']) && $_POST['updateatt']==1)
{
    //echo '<pre>';
	//print_r($_POST['userid']); die;
    if(isset($_POST['allset']) && $_POST['allset']==1)
    {
		
    $att_id_status_set_val = $_POST['att_id_status_set'];
    
   // $att_status_arr = $_POST['att_status_arr'];
    $atthrnew = '';
    $u1 = $DB->execute("UPDATE {attendance_sessions} set `lasttaken` = '".time()."' , `lasttakenby` = '".$USER->id."' WHERE `id` = '".$_GET['sessionid']."'");
	$id_arr_add = explode(",",$_POST['id_arr_add']);
//	echo $_POST['att_id']; 
	

	foreach($id_arr_add as $p=>$d)
	{
		if($d>0)
		{
		//	echo "INSERT INTO {attendance_extra_students} (`id`, `att_id`, `session_id`, `student_id`) VALUES (NULL, '".$_POST['att_id']."', '".$_GET['sessionid']."', '".$d."')"; echo '<hr>';
		
			$u_add_arr = $DB->execute("INSERT INTO {attendance_extra_students} (`id`, `att_id`, `session_id`, `student_id`) VALUES (NULL, '".$_POST['att_id']."', '".$_GET['sessionid']."', '".$d."')");
		$u_add_arr2 = $DB->execute("INSERT INTO {user_attendance_extra} (`id`, `user_id`, `course_id`, `attendance_id`,`session_id`) VALUES (NULL, '".$d."' , '".$_POST['courseid']."' , '".$_POST['att_id']."', '".$_GET['sessionid']."')");
		
		}
	}
    foreach($_POST['userid'] as $userid)
   {
        
        if(isset($_POST['full_'.$userid]) && $_POST['full_'.$userid]=='0')
        {
            $full_stat = $_POST['full_'.$userid];
        }
        $atthrstr = '';
        $comm = $_POST['comment'.$userid]; 
        $abshrval = $_POST['abshrval'.$userid];
        for($j=1;$j<($_POST['duration']+1);$j++)
        {
            $atthrstr = 'atthr'.$userid.'-'.$j;
            if(isset($_POST[$atthrstr]) && $_POST[$atthrstr]!='') { $atthr = $_POST[$atthrstr]; } else { $atthr = ''; }
            if($atthr!='')
            {
                $atthrnew = $atthrnew.$j.",";
            }
            
         $atthr = '';
         $atthrstr = '';
         }
		
         //$hrs_array = $DB->get_record_sql('SELECT * FROM {attendance_log_hours} WHERE sessionid = :sessionid AND studentid = :studentid',
                       //array('sessionid'=>$_GET['sessionid'], 'studentid'=>$userid));
         $select = "sessionid = '".$_GET['sessionid']."' AND studentid='".$userid."'"; 
//$hrs_array = $DB->get_records_select_menu('attendance_log_hours',$select,array(), '',$fields='*', $limitfrom=0, $limitnum=1); 
    if($DB->record_exists_select('attendance_log', $select)==true) {
    

    $DB->execute("DELETE FROM {attendance_log} WHERE `sessionid` = '".$_GET['sessionid']."' AND `studentid` = '".$userid."'"); 
    $DB->execute("DELETE FROM {attendance_log_hours} WHERE `sessionid` = '".$_GET['sessionid']."' AND `studentid` = '".$userid."'"); 
    }
    
    if(isset($_POST['extra'.$userid]) && $_POST['extra'.$userid]!='' && isset($_POST['atthr'.$userid.'-'.$j]) && $_POST['atthr'.$userid.'-'.$j]==1) { $atthrnew = $atthrnew.$_POST['extra'.$userid]; }
   // echo @$full_stat.$_POST['extra'.$userid].$atthrnew; die;
           if($atthrnew!='') {
               
         $atthrnew = @rtrim($atthrnew,',');
         $stat = "P";
        
         $stat_id = $SESSION->att_status_arr["'".$stat."'"];
         
    
        $u2 = $DB->execute("INSERT INTO {attendance_log} (`id`, `sessionid`, `studentid`, `statusid`, `statusset`, `timetaken`, `takenby`, `remarks`,`hrsonline`) VALUES (NULL, '".$_GET['sessionid']."', '".$userid."', '".$stat_id."' , '".$att_id_status_set_val."', '".time()."', '".$USER->id."','".$comm."','')");
           
        $u3 = $DB->execute("INSERT INTO {attendance_log_hours} (`id`, `sessionid`, `studentid`, `hours`, `absenthours` , `timetaken`, `takenby`, `comments`) VALUES (NULL, '".$_GET['sessionid']."', '".$userid."', '".$atthrnew."' , '".$abshrval."' , '".time()."', '".$USER->id."','".$comm."')");
   }
  
   if(@$full_stat==0 && $atthrnew=='')
   {
       $stat = "A";
         $stat_id = $SESSION->att_status_arr["'".$stat."'"];
         
        $u2 = $DB->execute("INSERT INTO {attendance_log} (`id`, `sessionid`, `studentid`, `statusid`, `statusset`, `timetaken`, `takenby`, `remarks`,`hrsonline`) VALUES (NULL, '".$_GET['sessionid']."', '".$userid."', '".$stat_id."' , '".$att_id_status_set_val."', '".time()."', '".$USER->id."','".$comm."','')");
           
       $u3 = $DB->execute("INSERT INTO {attendance_log_hours} (`id`, `sessionid`, `studentid`, `hours`, `absenthours` , `timetaken`, `takenby`, `comments`) VALUES (NULL, '".$_GET['sessionid']."', '".$userid."', '' , '".$abshrval."' , '".time()."', '".$USER->id."','".$comm."')");
   
  }
   @$full_stat = '';
   $stat ='';
   $stat_id = '';
   $atthrnew = '';
        $abshrval = '';
        $comm = '';    
        
   }
       
             
    
if($u1==true)
{
    $urltogo= $CFG->wwwroot.'/mod/attendance/take.php?id='.$_GET['id'].'&update=1&sessionid='.$_GET['sessionid'].'&grouptype='.$_GET['grouptype'];
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendance/pix/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>

<?php
} 
else
{
    $urltogo= $CFG->wwwroot.'/mod/attendance/take.php?id='.$_GET['id'].'&update=0&sessionid='.$_GET['sessionid'].'&grouptype='.$_GET['grouptype'];
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendance/pix/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
}
}
}
$pageparams->group = groups_get_activity_group($cm, true);

$pageparams->init($course->id);
$att = new mod_attendance_structure($att, $cm, $course, $PAGE->context, $pageparams);

$allowedgroups = groups_get_activity_allowed_groups($cm);
if (!empty($pageparams->grouptype) && !array_key_exists($pageparams->grouptype, $allowedgroups)) {
     $group = groups_get_group($pageparams->grouptype);
     throw new moodle_exception('cannottakeforgroup', 'attendance', '', $group->name);
}

//if (($formdata = data_submitted()) && confirm_sesskey()) { 
 //   $att->take_from_form_data($formdata);
//}

//echo sesskey();

//echo $att->cm->id;
//die;
$PAGE->set_url($att->url_take());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(false);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att);
$sesstable = new attendance_take_data($att);

$sql_get_record_add = $DB->get_records_sql("SELECT `student_id` FROM {attendance_extra_students} WHERE `att_id` = '".$_GET['id']."' AND `session_id` = '".$_GET['sessionid']."'");

//echo '<pre>';
//print_r($sql_get_record_add);

//echo '<pre>'; print_r($sesstable->users);
// Output starts here.
//echo '<pre>';
//print_r($att);
if(!isset($_POST['search']) || $_POST['studentid']=='' || $_GET['delete_added_user_id']>0) 
{
	//echo '<pre>';
	//print_r($SESSION->sesstable_users);
	if(!isset($_GET['delete_added_user_id']) && !isset($_GET['resetval']))
	{
		if(count($SESSION->sesstable_users)>0)
		{
			unset($SESSION->sesstable_users);
		}
		if(count($SESSION->id_arr_add)>0)
		{
			unset($SESSION->id_arr_add);
		}
	}
	
	
	
	
	
	if(isset($_GET['delete_added_user_id']))
	{
	if($_GET['delete_added_user_id']>0)
	{
		foreach($SESSION->sesstable_users as $key=>$obj_val)
		{
			if($obj_val->id==$_GET['delete_added_user_id'])
			{
				unset($SESSION->sesstable_users[$key]);
				$u_delete_arr = $DB->execute("DELETE FROM {attendance_extra_students} WHERE `student_id` = '".$_GET['delete_added_user_id']."' AND `session_id` = '".$_GET['sessionid']."'");
	
			}
		}
	}
	header("Location: take.php?sessionid=".$_GET['sessionid']."&id=".$_GET['id']."&grouptype=".$_GET['grouptype']."&resetval=0");
	exit();
	}
$userlist = '';
//echo '<pre>';
//print_r($sesstable->users);
foreach($sesstable->users as $users)
{
    if($users->id!='')
    {
        $userlist = $userlist."'".$users->id."',";
    }
}
$userlist = @rtrim($userlist,',');
//echo $userlist; die;
$sql_enrol = "SELECT DISTINCT u.id AS userid , u.*
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid
AND ct.contextlevel =50";
if($pageparams->grouptype>0) {
$sql_enrol = $sql_enrol." JOIN mdl_groups_members mg ON mg.userid = ue.userid";
}
$sql_enrol = $sql_enrol." JOIN mdl_course c ON c.id = ct.instanceid
AND e.courseid = c.id
JOIN mdl_role r ON r.id = ra.roleid
AND r.shortname =  'student'
WHERE e.status =0
AND u.suspended =0
AND u.deleted =0
AND (
ue.timeend =0
OR ue.timeend > NOW( )
)
AND ue.status =0
AND courseid ='".$att->cm->course."' AND r.id ='5' ";

if($userlist!='')
{
    $sql_enrol = $sql_enrol." AND ue.userid NOT IN (".$userlist.")";
}

if($pageparams->grouptype>0) {
    $sql_enrol = $sql_enrol." AND mg.groupid = '".$pageparams->grouptype."'";
}
$sql_search_record = $DB->get_records_sql($sql_enrol." ORDER BY u.firstname ASC");
}
//echo '<pre>';
//print_r($sql_search_record);
echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'attendance').' :: ' .format_string($course->fullname));
echo $output->render($tabs);
//echo $output->render($sesstable);
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>';
echo '<style> table { border-color: #ccc!important; } #attendancesfill td { padding-left: 0px!important; } </style>';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
echo '<link rel="stylesheet" href="'.$CFG->wwwroot.'/mod/attendance/css/custom.css">';
echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">';

?>
<style>
.zui-table {
    border: solid 1px #DDEEEE;
    border-collapse: collapse;
    border-spacing: 0;
    font: normal 14px Arial, sans-serif;
	width: 100%;
}
.zui-table thead th {
    background-color: #DDEFEF;
    border: solid 1px #DDEEEE;
    color: #336B6B;
    padding: 10px;
    text-align: left;
    text-shadow: 1px 1px 1px #fff;
}
.zui-table tbody td {
    border: solid 1px #DDEEEE;
    color: #333;
    padding: 10px;
    text-shadow: 1px 1px 1px #fff;
}
.zui-table-rounded {
    border: none;
}
.zui-table-rounded thead th {
    background-color: #CFAD70;
    border: none;
    text-shadow: 1px 1px 1px #ccc;
    color: #333;
}
.zui-table-rounded thead th:first-child {
    border-radius: 10px 0 0 0;
}
.zui-table-rounded thead th:last-child {
    border-radius: 0 10px 0 0;
}
.zui-table-rounded tbody td {
    border: none;
    border-top: solid 1px #957030;
    background-color: #EED592;
}
.zui-table-rounded tbody tr:last-child td:first-child {
    border-radius: 0 0 0 10px;
}
.zui-table-rounded tbody tr:last-child td:last-child {
    border-radius: 0 0 10px 0;
}

/* The container */
.container {
  display: block;
  position: relative;
  padding-left: 0px;
  margin-bottom: 5px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  left:20px;
}

/* Hide the browser's default checkbox */
.container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
  
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #db7a7a;
  border: 1px solid #000;
}

/* Create a custom radio */
.checkmark2 {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #db7a7a;
  border: 1px solid #000;
   border-radius: 50%;
}

/* On mouse-over, add a grey background color */
.container:hover input ~ .checkmark {
  background-color: white;
}

/* On mouse-over, add a grey background color */
.container:hover input ~ .checkmark2 {
  background-color: white;
}

/* When the checkbox is checked, add a blue background */
.container input:checked ~ .checkmark {
  background-color: #73ce65;
}

/* When the checkbox is checked, add a blue background */
.container input:checked ~ .checkmark2 {
  background-color: #73ce65;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.checkmark2:after {
  content: "";
  position: absolute;
  display: none;
}



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
  padding: 10px;
  cursor: pointer;
  background-color: #cef9dd; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #fff; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
input[type=text] {
    width: 100%!important;
    padding: 4px 7px!important;
    margin: 3px 0!important;
    box-sizing: border-box!important;
    border: 1px solid #555!important;
    outline: none!important;
    height:35px!important;
}

input[type=text]:focus {
    background-color: #d9f1fc!important;
}
.accordion_container {
  width: 100%;
}

.accordion_head {
 background-color: #dae2ef;
    color: #020201;
    cursor: pointer;
    font-family: arial;
    font-size: 16px;
    margin: 0 0 1px 0;
    padding: 7px 11px;
    border-radius: 12px;
    border: 1px solid #6f7aca;
    /* padding: 20px; */
    width: 100%;
    height: 50px;
    /* padding-bottom: 11px; */
    padding-top: 11px;
    margin-top: 11px;
  padding-top: 15px;
}

.accordion_body {
  background: #fff;
}

.accordion_body p {
  padding: 18px 5px;
  margin: 0px;
}

.plusminus {
  float: right;
  font-size: 22px;
}
 .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white!important;
            border-radius: 4px!important;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2)!important;
            padding: 8px 13px!important;
            text-decoration: none!important;
        }

        .button-success {
            background: rgb(28, 184, 65)!important; 
        }
        .styled select {
   background: transparent;
   width: 150px;
   font-size: 16px;
   border: 1px solid #ccc;
   height: 34px; 
} 


.info-msg,
.success-msg,
.warning-msg,
.error-msg {
  margin: 11px 0;
  padding: 10px;
  border-radius: 4px 4px 4px 4px;
}
.info-msg {
  color: #059;
  background-color: #BEF;
}
.success-msg {
  color: #270;
  background-color: #DFF2BF;
}
.warning-msg {
  color: #9F6000;
  background-color: #FEEFB3;
}
.error-msg {
  color: #D8000C;
  background-color: #FFBABA;
}

</style>
<script> 
    function countAbsHr(val1,val2,extra) 
    { 
	//alert(val1+'--'+val2+'---'+extra);
	//return false;
        document.getElementById('atthr'+val1+'-'+val2).disabled=true;
		if(extra>0)
		{
			document.getElementById('atthr'+val1+'-'+(val2+1)).disabled=true;
		}
        
        
        if(extra=='0')
        {
            //alert('You have clicked '+val2+' hours!');
        }
        else
        {
            //alert('You have clicked '+extra+' hours!');
        }
        if(extra=='0')
        {
            if(document.getElementById('atthr'+val1+'-'+val2).checked==true)
            {            
                document.getElementById('abshr'+val1).innerHTML = (parseFloat(document.getElementById('abshr'+val1).innerHTML) - 1).toFixed(1);            
            }
            else
            {
                document.getElementById('abshr'+val1).innerHTML = (parseFloat(document.getElementById('abshr'+val1).innerHTML) + 1).toFixed(1);
            }
        }
        if(extra!='' && extra!='0')
        {
           if(document.getElementById('atthr'+val1+'-'+(val2+1)).checked==true)
            {            
                document.getElementById('abshr'+val1).innerHTML = (parseFloat(document.getElementById('abshr'+val1).innerHTML) - extra).toFixed(1);            
            }
            else
            {
                document.getElementById('abshr'+val1).innerHTML = (parseFloat(document.getElementById('abshr'+val1).innerHTML) + extra).toFixed(1);
            }
           
        }
        document.getElementById('abshrval'+val1).value = document.getElementById('abshr'+val1).innerHTML ;
        document.getElementById('allset').value=1;
        document.getElementById('atthr'+val1+'-'+val2).removeAttribute('disabled');
        document.getElementById('atthr'+val1+'-'+(val2+1)).removeAttribute('disabled');
    } 
    function setAllChecked(userid,duration,extra)
    {      
        if(extra!='') 
        { 
            var dur = Number(duration)+2; 
            var duration = (parseFloat(duration)+extra).toFixed(1); 
        } 
        else 
        { 
            var dur = Number(duration)+1; 
            var duration = (parseFloat(duration)).toFixed(1); 
        }
        //alert(duration);
            if(document.querySelector('input[name="full_'+userid+'"]:checked').value=="1")
            {
                for(var t=1; t<dur; t++)
                {
                     document.getElementById('atthr'+userid+'-'+t).removeAttribute('checked');
                    document.getElementById('atthr'+userid+'-'+t).removeAttribute('disabled');
                   // document.getElementById('atthr'+userid+'-'+t).setAttribute('checked', 'checked');
                   document.getElementById('atthr'+userid+'-'+t).checked=true;
                    document.getElementById('abshrval'+userid).value = '0';
                    document.getElementById('abshr'+userid).innerHTML = '0';
                               
                }
                //document.getElementById('atthr'+userid+'-'+Number(t+1)).removeAttribute('checked');
                  //  document.getElementById('atthr'+userid+'-'+Number(t+1)).removeAttribute('disabled');
            }
            if(document.querySelector('input[name="full_'+userid+'"]:checked').value=="0")
            { 
                for(var t=1; t<dur; t++)
                {
                    document.getElementById('atthr'+userid+'-'+t).removeAttribute('disabled');
                    //document.getElementById('atthr'+userid+'-'+t).removeAttribute('checked');
                    document.getElementById('atthr'+userid+'-'+t).checked=false;
                    document.getElementById('atthr'+userid+'-'+t).setAttribute('disabled', 'disabled');
                    document.getElementById('abshrval'+userid).value = duration;
                    document.getElementById('abshr'+userid).innerHTML = duration;
                         
                }
              //  document.getElementById('atthr'+userid+'-'+Number(t+1)).removeAttribute('disabled');
            }
            document.getElementById('allset').value=1;
        } 
         function downloadFile(val)
        {
            window.open('print.php?<?php  echo $_SERVER['QUERY_STRING']; ?>', '_blank');
           // window.opem.href="print.php?<?php  //echo $_SERVER['QUERY_STRING']; ?>";
        }
</script>



<?php

//echo '<link rel="stylesheet" href="'.$CFG->wwwroot.'/mod/attendance/css/custom.css">';
//echo $output->render($sesstable);
if(isset($_GET['update']) && !isset($_POST['search'])) {
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
if(!isset($_POST['search'])) 
{
if(count($sql_search_record)>0)
{
foreach($sql_search_record as $u=>$val)
{
    //$sesstable->users[$val->userid]=$val;
    array_push($sesstable->users,$val);
}
}
}
$id_extra=array();
foreach($sql_get_record_add as $q=>$l)
{
    //$sesstable->users[$val->userid]=$val;
	$sql_extra = "SELECT * FROM mdl_user WHERE `id` = '".$q."'";
	$sql_extra_record = $DB->get_records_sql($sql_extra);
	foreach($sql_extra_record as $u55=>$val55)
	{
		//$sesstable->users[$val->userid]=$val;
		$id_extra[] = $u55;
		array_unshift($sesstable->users,$val55);
	}
}
$_SESSION['id_extra'] = $id_extra;
$added=false;
if(count($SESSION->sesstable_users)==0)
{
	$SESSION->sesstable_users = $sesstable->users;
}
else
{
	$sesstable->users = $SESSION->sesstable_users;
}
$add = false;
if(count($SESSION->id_arr_add)==0)
{
	$id_arr_add = array();
}
else
{
	$id_arr_add = $SESSION->id_arr_add;
}
if(isset($_POST['search']))
{
foreach($SESSION->sesstable_users as $key=>$val)
{
	if($val->id==$_POST['studentid'])
	{
		$added=true;
		
	}
}
if($added==false && (in_array($_POST['studentid'],$id_arr_add)==false || in_array($_POST['studentid'],$_SESSION['id_extra'])==false))
	{
		$id_arr_add[] = $_POST['studentid'];
		$sql_add = "SELECT * FROM mdl_user WHERE `id` = '".$_POST['studentid']."'";
		$sql_add_record = $DB->get_records_sql($sql_add);
		foreach($sql_add_record as $u1=>$val1)
		{
			//$sesstable->users[$val->userid]=$val;
			if(array_unshift($sesstable->users,$val1)) { $add = true;  }
		}
		$SESSION->sesstable_users = $sesstable->users;
		$SESSION->id_arr_add=$id_arr_add;
	}

}
//echo '<pre>';
//print_r($SESSION->id_arr_add);

//array_merge($sesstable->users,$sql_search_record);
//$sesstable->users[]=$sql_search_record;
//echo '<pre>';
//print_r($sesstable);

$cc = 1;
$duration = $sesstable->sessioninfo->duration;
$duration_hours = intval($duration/3600);
$duration_hours_extra = (@round(@fmod($duration,3600),2)/60);
$duration_show = @sprintf("%.1f",$duration/3600);
$duration_show_fraction =  ($duration_show - $duration_hours);

//echo sprintf("%.1f",'45.233');
//if($duration_hours_extra!='')
//{
  //  $duration_hours++;
//}
//echo $sesstable;

echo '<table class="zui-table zui-table-rounded">
    <thead>
        <tr>
            <th>Details</th>
            <th></th>
           
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Date:</strong></td>
            <td>'.@date("F j, Y, g:i a", $sessiondetails->sessdate).'</td>
           
        </tr>
        <tr>
            <td><strong>Format:</strong></td>
            <td>'.$att->course->format.'</td>
           
        </tr>
        <tr>
            <td><strong>Course:</strong></td>
            <td>'.$att->course->fullname.' | '.$att->course->shortname.' | '.$att->course->idnumber.'</td>
           
        </tr>
       
    </tbody>
</table><br/><br/>';
$renderer = $PAGE->get_renderer('gradereport_overview');
            //echo $renderer->graded_users_selector_autosuggest('overview', $course, $userid, $currentgroup, false);
            $studentsstr = $renderer->graded_users_selector_autosuggest('overview', $course, $userid, $currentgroup, false);
			
           ?><form name="search" autocomplete="off" action="" method="POST">
		   <input type="hidden" name="courseid" id="courseid" value="<?php echo $att->cm->course; ?>"  />
   <table ><tr><td><div class="autocomplete" style="width:220px;">
   <input id="studentname" type="text" name="studentname" placeholder="Type your Student Name" value="" />
 <input id="studentid" type="hidden" name="studentid" value="" />


            </div></td><td>&nbsp;&nbsp;&nbsp;</td><td><input style="box-shadow: 0px 11px 14px -7px #276873;
	background:linear-gradient(to bottom, #599bb3 5%, #1d7785 100%);
	background-color:#599bb3;
	border-radius:8px;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:Arial;
	font-size:16px;
	font-weight:bold;
	padding:5px 19px;
	text-decoration:none;
	text-shadow:0px 1px 0px #3d768a;" type="submit" name="search" value=" Add Record " style="margin: 0 0 0px 0px!important;" />&nbsp;&nbsp;<input style="box-shadow: 0px 11px 14px -7px #276873;
	background:linear-gradient(to bottom, #599bb3 5%, #1d7785 100%);
	background-color:#599bb3;
	border-radius:8px;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:Arial;
	font-size:16px;
	font-weight:bold;
	padding:5px 19px;
	text-decoration:none;
	text-shadow:0px 1px 0px #3d768a;" type="submit" name="search" value=" Reset " onclick="javascript: document.getElementById('studentname').value=''; document.getElementById('resetnow').value='1';" style="margin: 0 0 0px 0px!important;" /></td></tr></table></form> 
<?php

//echo '<form action="" method="GET" name="f3" id="f3" autocomplete="off">';
//echo '<br/><input type="text" class="edit_session_login_datetime" name="from_date" id="from_date" value="" style="width: 146px;" placeholder="Enter From Date"  />';
//echo '&nbsp;&nbsp;<input type="text" class="edit_session_login_datetime" name="to_date" id="to_date" value="" style="width: 146px;" placeholder="Enter To Date"  />';
//echo '&nbsp;&nbsp;<input type="submit" name="search" id="search" value=" Search "  />';
//echo '&nbsp;&nbsp;<input type="submit" name="showall" id="showall" value=" Show All " onclick=\'javascript: document.getElementById("to_date").value=""; document.getElementById("from_date").value="";\' />';
//echo '<input type="hidden" value="1" name="searchbydate" id="searchbydate"  />'; 
//echo '<input type="hidden" value="'.@$_GET['id'].'" name="id" id="id"  />'; 
//echo '</form><br/>'; 
echo '<div align="left"></div><form action="" method="post" name="f11" id="f11" autocomplete="of">';

?>
  <input id="id_arr_add" type="hidden" name="id_arr_add" value="<?php echo implode(',',$SESSION->id_arr_add); ?>" />
  <input id="att_id" type="hidden" name="att_id" value="<?php echo  $_GET['id']; ?>" />
  <input id="courseid" type="hidden" name="courseid" value="<?php echo $att->cm->course; ?>" />
  <input type="hidden" name="resetnow" id="resetnow" value="" />
  <?php
echo '<div align="right"><input type="submit" name="saveatt" id="saveatt" value= " Save attendance " style="box-shadow: 0px 11px 14px -7px #276873;
	background:linear-gradient(to bottom, #599bb3 5%, #1d7785 100%);
	background-color:#599bb3;
	border-radius:8px;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:Arial;
	font-size:16px;
	font-weight:bold;
	padding:5px 19px;
	text-decoration:none;
	text-shadow:0px 1px 0px #3d768a;" />&nbsp;&nbsp;<input style="box-shadow: 0px 11px 14px -7px #276873;
	background:linear-gradient(to bottom, #599bb3 5%, #1d7785 100%);
	background-color:#599bb3;
	border-radius:8px;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:Arial;
	font-size:16px;
	font-weight:bold;
	padding:5px 19px;
	text-decoration:none;
	text-shadow:0px 1px 0px #3d768a;" type="button" name="download" id="download" value= " Print a Copy " onclick=\'javascript: downloadFile('.$_GET['sessionid'].');\' /></div>';


 if(isset($_POST['search'])) 
 { 
	if($added==true)
	{
	?>
	<div class="error-msg">The student already added in the list!</div>
	<?php	
	}
	else
	{
		?>
		<div class="success-msg">
  <i class="fa fa-check"></i>
  The data has been successfully updated!
   </div>
		<?php
	}
}
//echo '<pre>'; print_r($_POST); 


echo '<br/><div style="width:100%;
margin: 0;
padding: 0;
overflow-y: scroll;"><table id="attendancesfill" style="width: 100%; font-weight: bold;" border="1" >
<tr id="caption">
    <th colspan="'.($duration_hours+5).'" style="background-color: #1C4E75; height: 32px!important; border: 1px solid #ccc;"></th>
  </tr> 
<tr>
<td rowspan="2"  style="text-align: center; background-color: #ccc;" >
Username
</td>
<td rowspan="2" style="text-align: center; background-color: #ccc;">
Name
</td>
<td colspan="2" style="text-align: center; background-color: #ccc;">
Attendance
</td>


</tr>
<tr><td ><table border="0" style="width: 100%;"><tbody><tr>
<td style="border-right: 1px solid #ccc; width: 90px;">Full day Present</td><td style="border-right: 1px solid #ccc; width: 90px;">Full day Absent</td>';
for($kk=1; $kk<($duration_hours+1); $kk++) { 
    echo '<td style="border-right: 1px solid #ccc; width: 90px; 
    
    
    background: -webkit-linear-gradient(left, #bef4bf 100%, white 0%);
    background: -moz-linear-gradient(left, #bef4bf 100%, white 0%);
    background: -ms-linear-gradient(left, #bef4bf 100%, white 0%);
    background: linear-gradient(left, #bef4bf 100%, white 0%);">
Start Time &ndash; Finish Time ('.$kk.' hr)
</td>';
    if($kk==$duration_hours)
    {
        if($duration_hours_extra!='') { 
      echo '<td style="border-right: 1px solid #ccc; width: 90px; background: -webkit-linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);
    background: -moz-linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);
    background: -ms-linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);
    background: linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);">
Start Time &ndash; Finish Time ('.$duration_hours_extra.' Mint.)
</td>'; }
       
         echo '<td style="width: 90px; background-color: #ccc;">Total absent hours
</td>';
    }
}
echo '</tr></tbody></table></td><td  style="text-align: center; background-color: #ccc;">
Remarks
</td>
</tr>
';
  
foreach($SESSION->sesstable_users as $users)
{
    $checked = '';
    $check_full = '';
    $dis = '';
	$check_full2 = '';
    $dis2 = '';
$select = "sessionid = '".$_GET['sessionid']."' AND studentid='".$users->id."'"; 
//$hrs_array = $DB->get_records_select_menu('attendance_log_hours',$select,array(), '',$fields='*', $limitfrom=0, $limitnum=1); 
    if($DB->record_exists_select('attendance_log_hours', $select)==true)
    {
$hrs_array = $DB->get_record_sql('SELECT * FROM {attendance_log_hours} WHERE sessionid = :sessionid AND studentid = :studentid',
    array('sessionid'=>$_GET['sessionid'], 'studentid'=>$users->id)); }

if(@$hrs_array->hours=='' && $DB->record_exists_select('attendance_log_hours', $select)==true)
{
   
    $check_full = 'checked';
    $dis = "disabled";
}
else
{
    $check_full = '';
    $dis = "";
}
if(in_array($users->id,$SESSION->id_arr_add)==true || in_array($users->id,$_SESSION['id_extra'])==true)
{
	$bg = "yellow";
	$delete_button = '<a href="take.php?delete_added_user_id='.$users->id.'&sessionid='.$_GET['sessionid'].'&grouptype='.$_GET['grouptype'].'&id='.$_GET['id'].'">Delete</a>';
}
else
{
	$bg = "white";
	$delete_button = '';
}
if($hrs_array->absenthours==0)
{
	$check_full2 = 'checked';
    $dis2 = "";
}
//if($hrs_array[$_GET['sessionid']]->absenthours >0)
//{
    //$duration_hours = $hrs_array[$_GET['sessionid']]->absenthours;
//}
//echo $hrs_array[$_GET['sessionid']]->hours;
//echo '<pre>'; print_r($hrs_array);
echo '<tr style="background-color: '.$bg.'">
<td >
&nbsp;'.$delete_button.'&nbsp;&nbsp;'.$users->username.'
</td>
<td >
'.$users->firstname.'&nbsp;'.$users->lastname.'<input type="hidden" name="userid[]" id="userid[]" value = "'.$users->id.'" />
</td><td><table style="width: 100%; height: 70px;"><tbody><tr>';
echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;">



<label class="container"><input type="radio" name="full_'.$users->id.'" id="full_'.$users->id.'" value="1" onchange=\'javascript: setAllChecked('.$users->id.','.$duration_hours.','.$duration_show_fraction.');\' '.$check_full2.'  /><span class="checkmark2"></span>
</label></td>';
echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;"><label class="container"><input type="radio" name="full_'.$users->id.'" id="full_'.$users->id.'" value="0" onchange=\'javascript: setAllChecked('.$users->id.','.$duration_hours.','.$duration_show_fraction.');\' '.$check_full.'  /><span class="checkmark2"></span></td>';
for($k=1; $k<($duration_hours+1); $k++) { if(in_array($k,explode(",",@$hrs_array->hours))==true) { $checked = "checked"; } else { $checked = ''; }
echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;">
<label class="container">
<input type="checkbox" name="atthr'.$users->id.'-'.$k.'" id="atthr'.$users->id.'-'.$k.'" value="1" onchange=\'javascript: countAbsHr('.$users->id.','.$k.',0);\' '.$checked.' '.$dis.'   /><span class="checkmark"></span>
</label></td>'; 

 if($k==$duration_hours)
    {
      if($duration_hours_extra!='') { if(in_array($duration_show_fraction,explode(",",@$hrs_array->hours))==true) { $checked2 = "checked"; } else { $checked2 = ''; }
      echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;">
	  
	  
	
	  
	  
	 <label class="container"><input type="checkbox" name="atthr'.$users->id.'-'.($k+1).'" id="atthr'.$users->id.'-'.($k+1).'" value="1" onchange=\'javascript: countAbsHr('.$users->id.','.$k.','.$duration_show_fraction.');\' '.$checked2.' '.$dis.' /><span class="checkmark"></span>
</label></td>'; }
        if(@$hrs_array->absenthours !='') { echo '<td style="width: 90px;" id="abshr'.$users->id.'">'.@$hrs_array->absenthours.'
        </td>'; } else { echo '<td style="width: 90px;" id="abshr'.$users->id.'">'.$duration_show.'
        </td>'; }
    }
}
echo '</tr></tbody></table></td>
<td align="valign" style="padding-top: 11px;"><textarea name="comment'.$users->id.'" id="comment'.$users->id.'" style="width: 92px!important;" >'.@$hrs_array->comments.'</textarea>
<input type="hidden" name="abshrval'.$users->id.'" id="abshrval'.$users->id.'" value="'.@$hrs_array->absenthours.'" />
    <input type="hidden" name="extra'.$users->id.'" id="extra'.$users->id.'" value="'.$duration_show_fraction.'" /></td>
</tr>';
$cc++;
unset($hrs_array);
unset($check_full);
unset($checked);
unset($checked2);
unset($dis);
}
echo '</table></div><br/><div align="right"><input type="submit" name="saveatt" id="saveatt" style="box-shadow: 0px 11px 14px -7px #276873;
	background:linear-gradient(to bottom, #599bb3 5%, #1d7785 100%);
	background-color:#599bb3;
	border-radius:8px;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:Arial;
	font-size:16px;
	font-weight:bold;
	padding:5px 19px;
	text-decoration:none;
	text-shadow:0px 1px 0px #3d768a;" value= " Save attendance " /></div><input type="hidden" name="updateatt" id="updateatt" value="1" />'
. '<input type="hidden" name="duration" id="duration" value="'.$duration_hours.'" /><input type="hidden" name="att_id_status_set" id="att_id_status_set" value="'.$att_id_status_set_val.'" /><input type="hidden" name="allset" id="allset" value="1" /></form>';
?>
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
                    document.getElementById("studentid").value = this.getElementsByTagName("input")[0].value; 
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
var studentname = '';
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
//var final_arr = {{{coursestr}}}; 
var final_arr_stu = <?php echo $studentsstr; ?> ; 
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
//autocomplete(document.getElementById("coursename"), final_arr,'1');
autocomplete(document.getElementById("studentname"), final_arr_stu,'2');
</script>
<script>
$(document).ready(function() {
  //toggle the component with class accordion_body
  $(".accordion_head").click(function() {
    if ($('.accordion_body').is(':visible')) {
      $(".accordion_body").slideUp(300);
      $(".plusminus").text('+');
    }
    if ($(this).next(".accordion_body").is(':visible')) {
      $(this).next(".accordion_body").slideUp(300);
      $(this).children(".plusminus").text('+');
    } else {
      $(this).next(".accordion_body").slideDown(300);
      $(this).children(".plusminus").text('-');
    }
  });
});
</script>
<?php
echo $output->footer();
?>
