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

if (!file_exists('./config.php')) {
    header('Location: install.php');
    die;
}

require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');
global $DB;
global $USER;
global $SESSION;
//echo @$SESSION->enrolmentform_stat; die;



$sql = "SELECT uen.enrolid , en.courseid from {user_enrolments} as uen LEFT JOIN {enrol} as en ON en.id = uen.enrolid WHERE uen.userid = '810'";
$list_records = $DB->get_records_sql($sql); 
$row_assign_id=array();
foreach ($list_records as $key=>$val)
{
    $row=$DB->get_record_sql("SELECT id FROM {assign} as ass WHERE ass.course = '".$val->courseid."' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s') >= NOW() AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s')< NOW() + INTERVAL 7 DAY");    
    if($row->id!='')
    {
        $row_assign_id[]=$row->id;
    }
    unset($row); 
}
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



$sql_form_settings = "SELECT `setting` FROM `mdl_form_settings` WHERE `formid` = '1'";
$row_form_settings = $DB->get_record_sql($sql_form_settings);
//echo '<pre>';
//print_r($row_form_settings);
if(@$row_form_settings->setting=='' || @$row_form_settings->setting!=0)
{
$sql_form_check = "SELECT count(`id`) as countrow FROM `mdl_questionnaire_response` WHERE `questionnaireid` = '408' AND `userid` = '".$USER->id."' AND `complete` = 'Y'";
$form_check = $DB->get_record_sql($sql_form_check);
if($form_check->countrow==0)
{
$course_array = array('572','573','574','575','576','577','578','579','580','582','583','584','589','596','597','598','599');
$modal_open = false;
$role = $DB->get_record('role', array('shortname' => 'student'));
foreach($course_array as $k=>$v)
{
    $context = get_context_instance(CONTEXT_COURSE, $v);
    $students = get_role_users($role->id, $context);
    foreach($students as $key=>$val)
    {
        if($USER->id==$val->id)
        {
            $modal_open=true;
        }
    }
    unset($context);
    unset($students);
}
}
}
//$uidcheck = $checkval->uid;

?>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<?php
if($modal_open==true)
{
    ?>
<script>
     
    $(window).load(function()
{
    
    
   $('#myModal').modal('show');
  
});

</script>
<?php } ?>
<?php
if(count($list_assign_id)>0)
{
    ?>
<script>
     
    $(window).load(function()
{
    
    
   $('#assignment_check').modal('show');
  
});

</script>
<?php } ?>
<div class="container" style=" pointer-events: none; ">
  <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" style="display: none;"></button>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog" style="display: none;">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
         
            <h4 class="modal-title"><strong>Forms</strong></h4>
        </div>
        <div class="modal-body">
             <?php if(@$USER->id!='') { ?>
                        
<p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/questionnaire/complete.php?id=19679">Enrolment Form
             </a></p> <?php } ?>
        </div>
<!--        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>-->
      </div>
      
    </div>
  </div>
  
  
   <div class="modal fade" id="assignment_check" role="dialog" style="display: none;">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
         
            <h4 class="modal-title"><strong>Forms</strong></h4>
        </div>
        <div class="modal-body">
             <?php if(@$USER->id!='') { ?>
                        
<p><a style="pointer-events: auto!important;" href="http://localhost/accit-moodle/accit/mod/questionnaire/complete.php?id=19679">Enrolment Form
             </a></p> <?php } ?>
        </div>
<!--        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>-->
      </div>
      
    </div>
  </div>
  
</div>
<?php

redirect_if_major_upgrade_required();

$urlparams = array();
if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && optional_param('redirect', 1, PARAM_BOOL) === 0) {
    $urlparams['redirect'] = 0;
}
$PAGE->set_url('/', $urlparams);
$PAGE->set_pagelayout('frontpage');
$PAGE->set_other_editing_capability('moodle/course:update');
$PAGE->set_other_editing_capability('moodle/course:manageactivities');
$PAGE->set_other_editing_capability('moodle/course:activityvisibility');

// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

require_course_login($SITE);

?>


<?php
$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

// If site registration needs updating, redirect.
\core\hub\registration::registration_reminder('/index.php');

if (get_home_page() != HOMEPAGE_SITE) {
    // Redirect logged-in users to My Moodle overview if required.
    $redirect = optional_param('redirect', 1, PARAM_BOOL);
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/');
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
        $frontpagenode = $PAGE->settingsnav->find('frontpage', null);
        if ($frontpagenode) {
            $frontpagenode->add(
                get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        } else {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        }
    }
}

// Trigger event.
course_view(context_course::instance(SITEID));

// If the hub plugin is installed then we let it take over the homepage here.
if (file_exists($CFG->dirroot.'/local/hub/lib.php') and get_config('local_hub', 'hubenabled')) {
    require_once($CFG->dirroot.'/local/hub/lib.php');
    $hub = new local_hub();
    $continue = $hub->display_homepage();
    // Function display_homepage() returns true if the hub home page is not displayed
    // ...mostly when search form is not displayed for not logged users.
    if (empty($continue)) {
        exit;
    }
}

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$editing = $PAGE->user_is_editing();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();

$siteformatoptions = course_get_format($SITE)->get_format_options();
$modinfo = get_fast_modinfo($SITE);
$modnamesused = $modinfo->get_used_module_names();

// Print Section or custom info.
if (!empty($CFG->customfrontpageinclude)) {
    // Pre-fill some variables that custom front page might use.
    $modnames = get_module_types_names();
    $modnamesplural = get_module_types_names(true);
    $mods = $modinfo->get_cms();

    include($CFG->customfrontpageinclude);

} else if ($siteformatoptions['numsections'] > 0) {
    echo $courserenderer->frontpage_section1();
}
// Include course AJAX.
include_course_ajax($SITE, $modnamesused);

echo $courserenderer->frontpage();

if ($editing && has_capability('moodle/course:create', context_system::instance())) {
    echo $courserenderer->add_new_course_button();
}
echo $OUTPUT->footer();
