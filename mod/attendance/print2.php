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
//echo '<pre>';
//print_r($att);
//echo $att->cm->id;
//die;
$PAGE->set_url($att->url_take());
$PAGE->set_title('Print');
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(false);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att);
$sesstable = new attendance_take_data($att);
//echo '<pre>'; print_r($sesstable->users);
// Output starts here.
//echo '<pre>';
//print_r($sesstable);

//echo $output->render($sesstable);
?>
<script type="text/javascript">
    function printpage() {
       
        var printButton = document.getElementById("printpagebutton");
      
        printButton.style.visibility = 'hidden';
       
        window.print()
       
       
        printButton.style.visibility = 'visible';
    }
</script>
<style>


.container {
 
  font-family: Verdana, Geneva, sans-serif; font-size: 24px; font-style: normal; font-variant: normal; font-weight: 700;
  @media (min-width: $bp-bart) {
    margin: 2%; 
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif; font-size: 24px; font-style: normal; font-variant: normal; font-weight: 700;
	
  }
  
  @media (min-width: $bp-homer) {
    margin: 2em auto;
    max-width: $bp-homer;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif; font-size: 24px; font-style: normal; font-variant: normal; font-weight: 700;
  }
}

.responsive-table {
  width: 100%;
  margin-bottom: 1.5em;
  border-spacing: 0;
  font-weight: bold;
  
  @media (min-width: $bp-bart) {
    font-size: .9em; 
  }
  
  @media (min-width: $bp-marge) {
    font-size: 1em; 
  }
  
  thead {
    // Accessibly hide <thead> on narrow viewports
    position: absolute;
    clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
    padding: 0;
    border: 0;
    height: 1px; 
    width: 1px; 
    overflow: hidden;
    
    @media (min-width: $bp-bart) {
      // Unhide <thead> on wide viewports
      position: relative;
      clip: auto;
      height: auto;
      width: auto;
      overflow: auto;
    }
    
    th {
      background-color: rgba(29,150,178,1);
      border: 1px solid rgba(29,150,178,1);
      font-weight: normal;
      text-align: center;
      color: white;
      
      &:first-of-type {
        text-align: left; 
      }
    }
  }
  
  // Set these items to display: block for narrow viewports
  tbody,
  tr,
  th,
  td {
    display: block;
    padding: 0;
    text-align: left;
    white-space: normal;
  }
  
  tr {   
    @media (min-width: $bp-bart) {
      // Undo display: block 
      display: table-row; 
    }
  }
  
  th,
  td {
    padding: .5em;
    vertical-align: middle;
    
    @media (min-width: $bp-lisa) {
      padding: .75em .5em; 
    }
    
    @media (min-width: $bp-bart) {
      // Undo display: block 
      display: table-cell;
      padding: .5em;
    }
    
    @media (min-width: $bp-marge) {
      padding: .75em .5em; 
    }
    
    @media (min-width: $bp-homer) {
      padding: .75em; 
    }
  }
  
  caption {
    margin-bottom: 1em;
    font-size: 1em;
    font-weight: bold;
    text-align: center;
    
    @media (min-width: $bp-bart) {
      font-size: 1.5em;
    }
  }
  
  tfoot {
    font-size: .8em;
    font-style: italic;
    
    @media (min-width: $bp-marge) {
      font-size: .9em;
    }
  }
  
  tbody {
    @media (min-width: $bp-bart) {
      // Undo display: block 
      display: table-row-group; 
    }
    
    tr {
      margin-bottom: 1em;
      
      @media (min-width: $bp-bart) {
        // Undo display: block 
        display: table-row;
        border-width: 1px;
      }
      
      &:last-of-type {
        margin-bottom: 0; 
      }
      
      &:nth-of-type(even) {
        @media (min-width: $bp-bart) {
          background-color: rgba(94,93,82,.1);
        }
      }
    }
    
    th[scope="row"] {
      background-color: rgba(29,150,178,1);
      color: white;
      
      @media (min-width: $bp-lisa) {
        border-left: 1px solid  rgba(29,150,178,1);
        border-bottom: 1px solid  rgba(29,150,178,1);
      }
      
      @media (min-width: $bp-bart) {
        background-color: transparent;
        color: rgba(94,93,82,1);
        text-align: left;
      }
    }
    
    td {
      text-align: right;
      
      @media (min-width: $bp-bart) {
        border-left: 1px solid  rgba(29,150,178,1);
        border-bottom: 1px solid  rgba(29,150,178,1);
        text-align: center; 
      }
      
      &:last-of-type {
        @media (min-width: $bp-bart) {
          border-right: 1px solid  rgba(29,150,178,1);
        } 
      }
    }
    
    td[data-type=currency] {
      text-align: right; 
    }
    
    td[data-title]:before {
      content: attr(data-title);
      float: left;
      font-size: .8em;
      color: rgba(94,93,82,.75);
      
      @media (min-width: $bp-lisa) {
        font-size: .9em; 
      }
      
      @media (min-width: $bp-bart) {
        // Don’t show data-title labels 
        content: none; 
      }
    } 
  }
}
</style>
<?php
echo '<style> table { border-color: #ccc!important; } #attendancesfill td { padding-left: 0px!important; } </style>';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';

echo '<link rel="stylesheet" href="'.$CFG->wwwroot.'/mod/attendanceregister/css/custom.css">';
//echo $output->render($sesstable);
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
AND courseid ='".$att->cm->course."' AND r.id ='5' AND ue.userid NOT IN (".$userlist.")";
if($pageparams->grouptype>0) {
    $sql_enrol = $sql_enrol." AND mg.groupid = '".$pageparams->grouptype."'";
}
$sql_search_record = $DB->get_records_sql($sql_enrol);
if(count($sql_search_record)>0)
{
foreach($sql_search_record as $u=>$val)
{
    //$sesstable->users[$val->userid]=$val;
    array_push($sesstable->users,$val);
}
}
$cc = 1;
$duration = $sesstable->sessioninfo->duration;
$duration_hours = intval($duration/3600);
$duration_hours_extra = (@round(@fmod($duration,3600),2)/60);
$duration_show = @sprintf("%.1f",$duration/3600);
$duration_show_fraction =  ($duration_show - $duration_hours);

$sql_get_record_add = $DB->get_records_sql("SELECT `student_id` FROM {attendance_extra_students} WHERE `att_id` = '".$_GET['id']."' AND `session_id` = '".$_GET['sessionid']."'");
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

//echo sprintf("%.1f",'45.233');
//if($duration_hours_extra!='')
//{
  //  $duration_hours++;
//}

echo '<input type="button" name="printpagebutton" id="printpagebutton" value=" Print " onclick=\'javascript: printpage();\' style="height: 55px; width: 105px; font-size:26px;" /><br/><br/>';
echo '<div style="text-align: center;"><img src="http://accit.nsw.edu.au/img/logo.png" /></div><br/>';
echo '<div class="container"><div style="width:94%;
margin-left:40px;
padding: 0;
overflow-y: scroll;"><table id="attendances" class="responsive-table" style="width: 100%;" border="1">
  <tr id="caption">
    <th colspan="'.($duration_hours+5).'" style="background-color: blue; height: 32px!important; border: 1px solid #ccc; color: white;">Details</th>
  </tr> 
  <tr>
    <td style="width: 20%;"><h5>Module:</h5></td>
    <td><div style="font-size: 15px;">'.$att->cm->name.'</div></td>
  </tr>
  <tr>
    <td style="width: 20%;"><h5>Date & Day:</h5></td>
    <td><div style="font-size: 15px;">'.@date("F j, Y, g:i a", $sessiondetails->sessdate).'</div></td>
  </tr>
    <tr>
    <td><h5>Format:</h5></td>
    <td><div style="font-size: 15px;">'.$att->course->format.'</div></td>
  </tr>
   <tr>
    <td><h5>Course:</h5></td>
    <td><div style="font-size: 15px;">'.$att->course->fullname.' | '.$att->course->shortname.' | '.$att->course->idnumber.'</div></td>
  </tr>
 
</table></div>';


echo '<div class="container"><div style="width:100%;
margin: 0;
padding: 0;
overflow-y: scroll;"><table id="attendancesfill" style="width: 100%; margin-left: 22px; " border="1" class="responsive-table" >
<tr id="caption">
    <th colspan="'.($duration_hours+5).'" style="background-color: blue; height: 32px!important; border: 1px solid #ccc; color: white;">Attendences</th>
  </tr> 
<tr style="background-color: #b3c5f2;">
<td rowspan="2" >
Username
</td>
<td rowspan="2" >
Name
</td>
<td colspan="2">
Attendance
</td>


</tr>
<tr><td style="background-color: #b3c5f2;"><table border="0" style="width: 100%;"><tbody><tr>';
for($kk=1; $kk<($duration_hours+1); $kk++) { 
    echo '<td style="border-right: 1px solid #ccc; font-weight: bold; width: 90px; 
    
    
    background: -webkit-linear-gradient(left, #bef4bf 100%, white 0%);
    background: -moz-linear-gradient(left, #bef4bf 100%, white 0%);
    background: -ms-linear-gradient(left, #bef4bf 100%, white 0%);
    background: linear-gradient(left, #bef4bf 100%, white 0%);">
Start Time &ndash; Finish Time ('.$kk.' hr)
</td>';
    if($kk==$duration_hours)
    {
        if($duration_hours_extra!='') { 
      echo '<td style="border-right: 1px solid #ccc; font-weight: bold; width: 90px; background: -webkit-linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);
    background: -moz-linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);
    background: -ms-linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);
    background: linear-gradient(left, #bef4bf '.(($duration_hours_extra/60)*100).'%, white '.(($duration_hours_extra/60)*100).'%);">
Start Time &ndash; Finish Time ('.$duration_hours_extra.' Mint.)
</td>'; }
       
         echo '<td style="width: 90px; font-weight: bold;">Total absent hours
</td>';
    }
}
echo '</tr></tbody></table></td><td style="background-color: #b3c5f2; font-weight: bold;">
Remarks
</td>
</tr>
';

foreach($sesstable->users as $users)
{
    $checked = '';
    $check_full = '';
    $dis = '';
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

//if($hrs_array[$_GET['sessionid']]->absenthours >0)
//{
    //$duration_hours = $hrs_array[$_GET['sessionid']]->absenthours;
//}
//echo $hrs_array[$_GET['sessionid']]->hours;
//echo '<pre>'; print_r($hrs_array);
echo '<tr>
<td >
'.$users->username.'
</td>
<td >
'.$users->firstname.'&nbsp;'.$users->lastname.'<input type="hidden" name="userid[]" id="userid[]" value = "'.$users->id.'" />
</td><td><table style="width: 100%; height: 70px;"><tbody><tr>';
//echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;"><input type="radio" name="full_'.$users->id.'" id="full_'.$users->id.'" value="1" onclick=\'javascript: setAllChecked('.$users->id.','.$duration_hours.','.$duration_show_fraction.');\'  /></td>';
//echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;"><input type="radio" name="full_'.$users->id.'" id="full_'.$users->id.'" value="0" onclick=\'javascript: setAllChecked('.$users->id.','.$duration_hours.','.$duration_show_fraction.');\' '.$check_full.'  /></td>';
for($k=1; $k<($duration_hours+1); $k++) { if(in_array($k,explode(",",@$hrs_array->hours))==true) { $checked = "checked"; } else { $checked = ''; }
echo '<td style="border-right: 1px solid #ccc; width: 90px; text-align: center;   font-weight: bold;">'; if($checked=="checked") { echo '<img src="pix/tick.png" border="0" style="width: 20px;height:20px;" />'; } else { echo '<img src="pix/cross.png" border="0" style="width: 20px;height:20px;" />'; } echo '</td>'; 

 if($k==$duration_hours)
    {
      if($duration_hours_extra!='') { if(in_array($duration_show_fraction,explode(",",@$hrs_array->hours))==true) { $checked2 = "checked"; } else { $checked2 = ''; }
      echo '<td style="border-right: 1px solid #ccc;   font-weight: bold; width: 90px; text-align: center;">'; if($checked2=="checked") { echo '<img src="pix/tick.png" border="0" style="width: 20px;height:20px;" />'; } else { echo '<img src="pix/cross.png" border="0" style="width: 20px;height:20px;" />'; } echo '</td>'; }
        if(@$hrs_array->absenthours !='') { echo '<td style="width: 90px;   font-weight: bold;" id="abshr'.$users->id.'">'.@$hrs_array->absenthours.'
        </td>'; } else { echo '<td style="width: 90px;   font-weight: bold;" id="abshr'.$users->id.'">'.$duration_show.'
        </td>'; }
    }
}
echo '</tr></tbody></table></td>
<td align="valign" style="padding-top: 11px;">'.@$hrs_array->comments.'</td>
</tr>';
$cc++;
unset($hrs_array);
unset($check_full);
unset($checked);
unset($checked2);
unset($dis);
}
echo '</table></div>';

