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

require_once '../../../config.php';
require_once($CFG->dirroot.'/course/lib.php');

$courseid = optional_param('id', SITEID, PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
global $USER;
global $CFG;
$admins = get_admins();
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}

if(isset($_GET['categoryid']) && $_GET['categoryid']>0 && isset($_GET['courseid']) && $_GET['courseid']>0)
{
	$categoryid = $_GET['categoryid'];
	$courseid = $_GET['courseid'];

$arrnew = $DB->get_records_sql("SELECT ue.id, c.id AS courseid, c.fullname as course_name, c.idnumber as module, 
c.timemodified as dateshow , ue.timestart as enrol_start , ue.timeend as enrol_end , u.id AS userid , u.phone1 as phone1, u.email as email,
u.phone2 as phone2, 
u.firstname as fname , u.lastname as lname , u.username as studentno FROM mdl_user u 
JOIN mdl_user_enrolments ue ON ue.userid = u.id 
JOIN mdl_enrol e ON e.id = ue.enrolid 
JOIN mdl_role_assignments ra ON ra.userid = u.id 
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50 
JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id 
JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student' 
WHERE e.status = 0 AND ue.status = 0 AND c.category = '".$categoryid."' AND e.courseid = '".$courseid."'");
}




$PAGE->set_url(new moodle_url('/grade/report/overview/gradesbycourse.php', array('categoryid' => null)));

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>';
echo '
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
 
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#accordion" ).accordion({
      collapsible: true,
	  heightStyle: "content"
    });
  } );
  $( function() {
    $( "#accordion2" ).accordion({
      collapsible: true,
	  heightStyle: "content"
    });
  } );
  </script>

<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #85adf2;
  color: white;
}
.ui-accordion-content ui-corner-bottom ui-helper-reset ui-widget-content ui-accordion-content-active
{
	height:auto!important;
}
.testbutton {
  font-family: arial;
  font-weight: bold;
  color: #FFFFFF !important;
  font-size: 16px;
  text-shadow: 1px 1px 1px #7CACDE;
  box-shadow: 1px 1px 1px #BEE2F9;
  padding: 10px 25px;
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
  border: 2px solid #3866A3;
  background: #63B8EE;
  background: linear-gradient(top,  #63B8EE,  #468CCF);
  background: -ms-linear-gradient(top,  #63B8EE,  #468CCF);
  background: -webkit-gradient(linear, left top, left bottom, from(#63B8EE), to(#468CCF));
  background: -moz-linear-gradient(top,  #63B8EE,  #468CCF);
}
.testbutton:hover {
  color: #fff !important;
  background: #468CCF;
  background: linear-gradient(top,  #468CCF,  #63B8EE);
  background: -ms-linear-gradient(top,  #468CCF,  #63B8EE);
  background: -webkit-gradient(linear, left top, left bottom, from(#468CCF), to(#63B8EE));
  background: -moz-linear-gradient(top,  #468CCF,  #63B8EE);
}
</style>
';


redirect_if_major_upgrade_required();

$urlparams = array();
if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 0) {
    $urlparams['redirect'] = 0;
}


// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

require_course_login($SITE);

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();
$main_cat = $DB->get_records_sql("SELECT * FROM mdl_course_categories WHERE parent=0 order by id asc");


if($if_student==0) { 
?>


<div id="accordion" style="height:auto!important;">
  <?php foreach($main_cat as $key=>$val)
  {
	  $sub_cat = $DB->get_records_sql("SELECT * FROM mdl_course_categories WHERE parent=".$val->id);
	  
	  ?>
  <h3><?php echo $val->name; ?></h3>
  <div style="height:auto!important;">
    

	
	<?php foreach($sub_cat as $key2=>$val2) { 
	
	$sub_cat2 = $DB->get_records_sql("SELECT * FROM mdl_course_categories WHERE parent=".$val2->id);
	$search_arr = explode(" ",$val2->name);
	if(in_array("Supervised",$search_arr)==true) {
	?>
	

	<a href="#" ><?php echo $val2->name; ?></a>
	 
	
	  <?php foreach($sub_cat2 as $key3=>$val3) {
		 
//if(in_array("Supervised",$search_arr2)==true) {
		  ?>
		 
	  <div style="background-color: #a2f2c6;" ><a href="<?php echo $CFG->wwwroot; ?>/grade/report/overview/reportbycourse.php?categoryid=<?php echo $val3->id; ?>" ><?php echo $val3->name; ?></a></div>
	
	
<?php
$sub_cat3 = $DB->get_records_sql("SELECT * FROM mdl_course WHERE category=".$val3->id);
	foreach($sub_cat3 as $key4=>$val4) { ?>
	
	<div style="background-color: #cdf7a0;"><a href="<?php echo $CFG->wwwroot; ?>/grade/report/overview/reportbycourse.php?categoryid=<?php echo $val3->id; ?>&courseid=<?php echo $val4->id; ?>" ><?php echo $val4->fullname; ?></a></div>
	
<?php } } ?>
	<?php unset($search_arr); } }  ?>
    
  </div>
  <?php } ?>
</div>

<br/><br/>
<?php
if(isset($_GET['categoryid']) && $_GET['categoryid']>0)
{
	?>
	<div style="float: right;"><a href="#" 
	onclick="javascript: window.open('<?php echo $CFG->wwwroot; ?>/grade/report/overview/reportbycourseexcel.php?categoryid=<?php echo $_GET['categoryid']; ?>&courseid=<?php echo $_GET['courseid']; ?>','_blank');" 
	class="testbutton">Download Excel</a></div><br/>
<?php } ?>
<br/><br/>
<?php 
//echo '<pre>';
//print_r($arrnew);
if(count($arrnew)>0) { ?>

<table id="customers" style="width:100%!important;">
<thead>
<tr>
<th>Student No</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Enrolment Start</th>
<th>Enrolment End</th>
</tr>
</thead>
<tbody>
    <?php $course_name = array(); 
	foreach($arrnew as $key=>$val) 
	{ 
	$course_name[] = $val->course_name;  
	if($val->id>0) 
	{
		
		$course_explode_arr = explode(" ",$val->course_name);
	?>
    <tr>
		<td><?php echo $val->studentno; ?></td>
		<td><?php echo $val->fname." ".$val->lname; ?></td>
		
	    <td><?php echo $val->email; ?></td>
		<td><?php if($val->phone1!='' && $val->phone2!='') { echo $val->phone1." / ".$val->phone2; } 
		else if($val->phone1!='' && $val->phone2=='') { echo $val->phone1; } else 
if($val->phone1=='' && $val->phone2!='') { echo $val->phone2; }	else { echo 'No Phone'; }	?></td>
		<td><?php echo @date('jS F, Y, \a\\t g.i a',$val->enrol_start); ?></td>
		<td><?php if($val->enrol_end>0) { echo @date('jS F,Y, \a\\t g.i a',$val->enrol_end); } else { echo 'NA'; } ?></td>
	</tr>
   <?php 
}

	}
	?></tbody></table>
<?php
} else { ?>No records found
  <?php } }
  
  else {
	  ?>
	  
	  
	  <div class="container">
 
  <div class="alert alert-danger">
    <strong>Unauthorized!</strong> You are not allowed to access !
  </div>
</div>
	  
	  
	  
  <?php } 
echo $OUTPUT->footer();