<?php

/**
 * Attendance Register view page
 *
 * @package    mod
 * @subpackage attendanceregister
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Disable output buffering
define('NO_OUTPUT_BUFFERING', true);


require('../../config.php');
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');
global $CFG;
global $DB;
global $USER;
if(user_has_role_assignment($USER->id,5))
{
    $if_student = TRUE;
}
else
{
    $if_student = FALSE;
}
//echo $if_student;
// Main parameters
global $if_student;
$userId = optional_param('userid', 0, PARAM_INT);   // if $userId = 0 you'll see all logs
$id = optional_param('id', 0, PARAM_INT);           // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT);             // register ID
if($a!='')
{
    $registerid = $a;
}
else
{
    $registerid = $_GET['registerid'];
}
$groupId = optional_param('groupid', 0, PARAM_INT);             // Group ID
// Other parameters
$inputAction = optional_param('action', '', PARAM_ALPHA);   // Available actions are defined as ATTENDANCEREGISTER_ACTION_*
// Parameter for deleting offline session
$inputSessionId = optional_param('session', null, PARAM_INT);

// =========================
// Retrieve objects
// =========================

if ($id!='') {
    $cm = get_coursemodule_from_id('attendanceregister', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $register = $DB->get_record('attendanceregister', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $register = $DB->get_record('attendanceregister', array('id' => $a), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('attendanceregister', $register->id, $register->course, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $id = $cm->id;
}

// Retrive session to delete
$sessionToDelete = null;
if ($inputSessionId) {
    $sessionToDelete = attendanceregister_get_session($inputSessionId);
}

// ================================
// Basic security checks
// ================================
// Requires login
require_course_login($course, false, $cm);
if(isset($_POST['addsession']) && $_POST['addsession']==1)
{
    $arr = array();
    $duration = (strtotime($_POST['logout']) - strtotime($_POST['login']));
    $u1 = $DB->get_records_sql("SELECT `duration` FROM {attendanceregister_aggregate} WHERE register = '".$_POST['registerid']."' AND userid = '".$_POST['userid']."' limit 0 , 1");
 foreach($u1 as  $u1)
 {
     $arr[] = $u1->duration;
 }
   $total_duration = $duration + @$arr[0];
    $u2 = $DB->execute("INSERT INTO {attendanceregister_session} (`id`, `register`, `login`, `logout`, `duration`, `userid`, `onlinesess`, `refcourse`, `comments`, `addedbyuserid`) VALUES (NULL, '".$_POST['registerid']."', '".strtotime($_POST['login'])."', '".strtotime($_POST['logout'])."', '".$duration."', '".$_POST['userid']."', '1', NULL, '".$_POST['comments']."', NULL)");
    if($arr[0]=='')
    {
        $u3 = $DB->execute("INSERT INTO {attendanceregister_aggregate} (`id`, `register`, `userid`, `duration`, `onlinesess`, `total`, `grandtotal`, `refcourse`,`lastsessionlogout`) VALUES (NULL, '".$_POST['registerid']."', '".$_POST['userid']."', '".$total_duration."' , '1', '1', '0', NULL, '0')");
    
        $u4 = $DB->execute("INSERT INTO {attendanceregister_aggregate} (`id`, `register`, `userid`, `duration`, `onlinesess`, `total`, `grandtotal`, `refcourse`,`lastsessionlogout`) VALUES (NULL, '".$_POST['registerid']."', '".$_POST['userid']."', '".$total_duration."' , NULL, '0', '1', NULL, '".strtotime($_POST['logout'])."')");
    
    }
    else
    {
        $u3 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `duration` = '".$total_duration."' WHERE `register` = '".$_POST['registerid']."' AND `userid` = '".$_POST['userid']."'");
        $u5 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `lastsessionlogout` = '".strtotime($_POST['logout'])."' WHERE `register` = '".$_POST['registerid']."' AND `userid` = '".$_POST['userid']."' AND `lastsessionlogout`!='0'");
   }
    if($u3==true)
    {
    $urltogo= $CFG->wwwroot.'/mod/attendanceregister/view.php?update=1&'.$_SERVER['QUERY_STRING'];
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>

<?php
} 
else
{
    $urltogo= $CFG->wwwroot.'/mod/attendanceregister/view.php?id='.$_GET['id'].'&update=0';
    ?>
<div style="padding-left: 38px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
}
}
if(isset($_POST['updatesession']) && $_POST['updatesession']==1)
{
   // echo '<pre>';
   // print_r($_POST); die;
    if($_POST['logout_date']=='')
    {
        $logout_date = $_POST['logout_date_old'];
    }
    else
    {
        $logout_date = strtotime($_POST['logout_date']);
    }
    if($_POST['login_date']=='')
    {
        $login_date = $_POST['login_date_old'];
    }
    else
    {
        $login_date = strtotime($_POST['login_date']);
    }
   
    $arr = array();
    $duration = $logout_date - $login_date;
    $u1 = $DB->execute("UPDATE {attendanceregister_session} SET `login` = '".$login_date."', `logout` = '".$logout_date."', `duration` = '".$duration."' WHERE `id` = '".$_POST['session_id']."'");
    $u2 = $DB->get_records_sql("SELECT SUM(`duration`) as durationtotal FROM {attendanceregister_session} WHERE `register` = '".$_POST['registerid']."' AND `userid` = '".$_POST['userid']."'");
    foreach($u2 as  $u2)
    {
        $arr[] = $u2->durationtotal;
    }
    $u3 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `duration` = '".$arr[0]."' WHERE `register` = '".$_POST['registerid']."' AND `userid` = '".$_POST['userid']."'");
    $u4 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `lastsessionlogout` = '".$logout_date."' WHERE `register` = '".$_POST['registerid']."' AND `userid` = '".$_POST['userid']."' AND `lastsessionlogout`!='0'");
   
    if($u1==true && $u2==true && $u3==true && $u4==true)
    {
    $urltogo= $CFG->wwwroot.'/mod/attendanceregister/update.php?update=1&'.$_SERVER['QUERY_STRING'];
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>

<?php
} 
else
{
    $urltogo= $CFG->wwwroot.'/mod/attendanceregister/view.php?id='.$_GET['id'].'&update=0';
    ?>
<div style="padding-left: 38px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
}
}

// Retrieve Context
if (!($context = context_module::instance($cm->id))) {
    print_error('badcontext');
}

// Preload User's Capabilities
$userCapabilities = new attendanceregister_user_capablities($context);

// If user is not defined AND the user has NOT the capability to view other's Register
// force $userId to User's own ID
if ( !$userId && !$userCapabilities->canViewOtherRegisters) {
    $userId = $USER->id;
}
// (beyond this point, if $userId is specified means you are working on one User's Register
//  if not you are viewing all users Sessions)
/*if(!empty($userId)) {
$progressbar = new progress_bar('recalcbar', 500, true);
        attendanceregister_force_recalc_user_sessions($register, $userId, $progressbar);

        // Reload User's Sessions
        $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
}
*/
// ==================================================
// Determine Action and checks specific permissions
// ==================================================
/// These capabilities checks block the page execution if failed

// Requires capabilities to view own or others' register
if ( attendanceregister__isCurrentUser($userId) ) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_VIEW_OWN_REGISTERS, $context);
} else {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_VIEW_OTHER_REGISTERS, $context);
}

// Require capability to recalculate
$doRecalculate = false;
$doScheduleRecalc = false;
if ($inputAction == ATTENDANCEREGISTER_ACTION_RECALCULATE ) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_RECALC_SESSIONS, $context);
    $doRecalculate = true;
}
if ($inputAction == ATTENDANCEREGISTER_ACTION_SCHEDULERECALC ) {
    require_capability(ATTENDANCEREGISTER_CAPABILITY_RECALC_SESSIONS, $context);
    $doScheduleRecalc = true;
}


// Printable version?
$doShowPrintableVersion = false;
if ($inputAction == ATTENDANCEREGISTER_ACTION_PRINTABLE) {
    $doShowPrintableVersion = true;
}

/// Check permissions and ownership for showing offline session form or saving them
$doShowOfflineSessionForm = false;
$doSaveOfflineSession = false;
// Only if Offline Sessions are enabled (and No printable-version action)
if ( $register->offlinesessions &&  !$doShowPrintableVersion  ) {
    // Only if User is NOT logged-in-as, or ATTENDANCEREGISTER_ALLOW_LOGINAS_OFFLINE_SESSIONS is enabled
    if ( !(\core\session\manager::is_loggedinas()) || ATTENDANCEREGISTER_ALLOW_LOGINAS_OFFLINE_SESSIONS ) {
        // If user is on his own Register and may save own Sessions
        // or is on other's Register and may save other's Sessions..
        if ( $userCapabilities->canAddThisUserOfflineSession($register, $userId) ) {
            // Do show Offline Sessions Form
            $doShowOfflineSessionForm = true;

            // If action is saving Offline Session...
            if ( $inputAction == ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION  ) {
                // Check Capabilities, to show an error if a security violation attempt occurs
                if ( attendanceregister__isCurrentUser($userId) ) {
                    require_capability(ATTENDANCEREGISTER_CAPABILITY_ADD_OWN_OFFLINE_SESSIONS, $context);
                } else {
                    require_capability(ATTENDANCEREGISTER_CAPABILITY_ADD_OTHER_OFFLINE_SESSIONS, $context);
                }

                // Do save Offline Session
                $doSaveOfflineSession = true;
            }
        }
    }
}


/// Check capabilities to delete self cert
// (in the meanwhile retrieve the record to delete)
$doDeleteOfflineSession = false;
if ($sessionToDelete) {
    // Check if logged-in-as Session Delete
    if (\core\session\manager::is_loggedinas() && !ATTENDANCEREGISTER_ACTION_SAVE_OFFLINE_SESSION) {
        print_error('onlyrealusercandeleteofflinesessions', 'attendanceregister');
    } else if ( attendanceregister__isCurrentUser($userId) ) {
        require_capability(ATTENDANCEREGISTER_CAPABILITY_DELETE_OWN_OFFLINE_SESSIONS, $context);
        $doDeleteOfflineSession = true;
    } else {
        require_capability(ATTENDANCEREGISTER_CAPABILITY_DELETE_OTHER_OFFLINE_SESSIONS, $context);
        $doDeleteOfflineSession = true;
    }
}

// ===========================
// Retrieve data to be shown
// ===========================

// Retrieve Course Completion info object
$completion = new completion_info($course);


// If viewing/updating one User's Register, load the user into $userToProcess
// and retireve User's Sessions or retrieve the Register's Tracked Users
// If viewing all Users load tracked user list
$userToProcess = null;
$userSessions = null;
$trackedUsers = null;
if ( $userId ) {
    $userToProcess = attendanceregister__getUser($userId);
    $userToProcessFullname = fullname($userToProcess);
    $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
} else {
    $trackedUsers = new attendanceregister_tracked_users($register, $userCapabilities);
}


// ===========================
// Pepare PAGE for rendering
// ===========================
// Setup PAGE
$url = attendanceregister_makeUrl($register, null, $userId, $groupId, $inputAction);
$PAGE->set_url($url->out());
$PAGE->set_context($context);
$titleStr = $course->shortname . ': ' . $register->name . ( ($userId) ? ( ': ' . $userToProcessFullname ) : ('') );
$PAGE->set_title(format_string($titleStr));

$PAGE->set_heading($course->fullname);
if ($doShowPrintableVersion) {
    $PAGE->set_pagelayout('print');
}
// Add User's Register Navigation node
if ( $userToProcess ) {
    $registerNavNode = $PAGE->navigation->find($cm->id, navigation_node::TYPE_ACTIVITY);
    $userNavNode = $registerNavNode->add( $userToProcessFullname, $url );
    $userNavNode->make_active();
}


// ==================================================
// Logs User's action and update completion-by-view
// ==================================================

attendanceregister_add_to_log($register, $cm->id, $inputAction, $userId, $groupId);

/// On View Completion [fixed with isse #52]        
// If current user is the selected user (and completion is enabled) mark module as viewed
if ( $userId == $USER->id && $completion->is_enabled($cm) ) {
    $completion->set_module_viewed($cm, $userId);
}    
        

// ==============================================
// Start Page Rendering
// ==============================================
echo $OUTPUT->header();
$headingStr = $register->name . ( ( $userId ) ? (': ' . $userToProcessFullname ) : ('') );
echo $OUTPUT->heading(format_string($headingStr));

//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/attendanceregister/javascripts/datepicker.js'));
echo '<link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" media="all" type="text/css" href="'.$CFG->wwwroot.'/mod/attendanceregister/css/jquery-ui-timepicker-addon.css" />';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
echo '<link rel="stylesheet" href="'.$CFG->wwwroot.'/mod/attendanceregister/css/custom.css">';
echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  
  <style>
    label, input { display:block; }
    input.text { margin-bottom:12px; width:95%; padding: .4em; }
    fieldset { padding:0; border:0; margin-top:25px; }
    h1 { font-size: 1.2em; margin: .6em 0; }
    div#users-contain { width: 350px; margin: 20px 0; }
    div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
    .ui-dialog .ui-state-error { padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
    #ui-datepicker-div { z-index: 9999!important; }
  </style>
    <style>
input[type=button], input[type=submit], input[type=reset] {
      background-color: #4CAF50;
    border: none;
    color: white;
    padding: 4px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
    width: auto;
    border-radius: 6px;
}

</style>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>';
//echo '<script src="'.$CFG->wwwroot.'js/jquery.validationEngine-en.js"  type="text/javascript" charset="utf-8"></script>';
// ==============================================
// Pepare Offline Session insert form, if needed
// ==============================================
// If a userID is defined, offline sessions are enabled and the user may insert Self.certificatins...
// ...prepare the Form for Self.Cert.
// Process the form (if submitted)
// Note that the User is always the CURRENT User (no UserId param is passed by the form)
$doShowContents = true;
$mform = null;
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
echo '<input type="button" class="create-session" value="Add session" onclick=\'javascript: document.getElementById("userid").value='.$_GET['userid'].';\'>';
echo '<form action="" method="GET" name="f3" id="f3" autocomplete="off">';
echo '<br/><input type="text" class="edit_session_login_datetime" name="from_date" id="from_date" value="" style="width: 146px;" placeholder="Enter From Date"  />';
echo '&nbsp;&nbsp;<input type="text" class="edit_session_login_datetime" name="to_date" id="to_date" value="" style="width: 146px;" placeholder="Enter To Date"  />';
echo '&nbsp;&nbsp;<input type="submit" name="search" id="search" value=" Search "  />';
echo '&nbsp;&nbsp;<input type="submit" name="showall" id="showall" value=" Show All " onclick=\'javascript: document.getElementById("to_date").value=""; document.getElementById("from_date").value="";\' />';
echo '<input type="hidden" value="1" name="searchbydate" id="searchbydate"  />'; 
echo '<input type="hidden" value="'.@$_GET['registerid'].'" name="registerid" id="registerid"  />'; 
echo '<input type="hidden" value="'.@$_GET['userid'].'" name="userid" id="userid"  />'; 
echo '<input type="hidden" value="'.@$_GET['id'].'" name="id" id="id"  />'; 
echo '</form>';     
//echo '<br/><div align="left"><input type="button" name="download" id="download" value= " Printable Version " onclick=\'javascript: printPage();\' /></div></br>';

if ($userId && $doShowOfflineSessionForm && !$doShowPrintableVersion ) {
    

    // Prepare Form
    $customFormData = array('register' => $register,'courses' => $userSessions->trackedCourses->courses);
    // Also pass userId only if is saving for another user
    if (!attendanceregister__isCurrentUser($userId)) {
        $customFormData['userId'] = $userId;
    }
    $mform = new mod_attendanceregister_selfcertification_edit_form(null, $customFormData);


    // Process Self.Cert Form submission
    if ($mform->is_cancelled()) {
        // Cancel
        redirect($PAGE->url);
    } else if ($doSaveOfflineSession && ($formData = $mform->get_data())) {
        // Save Session
        attendanceregister_save_offline_session($register, $formData);

        // Notification & Continue button
        echo $OUTPUT->notification(get_string('offline_session_saved', 'attendanceregister'), 'notifysuccess');
        echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, null, $userId));
        $doShowContents = false;
    }
}
if(isset($_GET['from_date']) && $_GET['from_date']!='' && isset($_GET['to_date']) && $_GET['to_date']!='')
{
    echo '<div style="height: 20px; background-color: #bcdbda; padding:9px 9px 9px 9px;">Search Results for <b>'.date_format(date_create($_GET['from_date']),'jS F , Y').'</b> - <b>'.date_format(date_create($_GET['to_date']),'jS F , Y').'</b></div><br/>';
}
else if(isset($_GET['from_date']) && $_GET['from_date']!='' && isset($_GET['to_date']) && $_GET['to_date']=='')
{
    echo '<div style="height: 20px; background-color: #bcdbda; padding:9px 9px 9px 9px;">Search Results from <b>'.date_format(date_create($_GET['from_date']),'jS F , Y').'</b></div><br/>';
}
else if(isset($_GET['from_date']) && $_GET['from_date']=='' && isset($_GET['to_date']) && $_GET['to_date']!='')
{
   echo '<div style="height: 20px; background-color: #bcdbda; padding:9px 9px 9px 9px;">Search Results till <b>'.date_format(date_create($_GET['to_date']),'jS F , Y').'</b></div><br/>'; 
}
else
{
    
}
//// Process Recalculate
if ($doShowContents && ($doRecalculate||$doScheduleRecalc)) {

    //// Recalculate Session for one User
    if ($userToProcess) {
        $progressbar = new progress_bar('recalcbar', 500, true);
        attendanceregister_force_recalc_user_sessions($register, $userId, $progressbar);

        // Reload User's Sessions
        $userSessions = new attendanceregister_user_sessions($register, $userId, $userCapabilities);
    }

    //// Recalculate (or schedule recalculation) of all User's Sessions
    else {

        //// Schedule Recalculation?
        if ( $doScheduleRecalc ) {
            // Set peding recalc, if set
            if ( !$register->pendingrecalc ) {
                attendanceregister_set_pending_recalc($register, true);
            }
        }

        //// Recalculate Session for all User
        if ( $doRecalculate ) {
            // Reset peding recalc, if set
            if ( $register->pendingrecalc ) {
                attendanceregister_set_pending_recalc($register, false);
            }

            // Turn off time limit: recalculation can be slow
            set_time_limit(0);

            // Cleanup all online Sessions & Aggregates before recalculating [issue #14]
            attendanceregister_delete_all_users_online_sessions_and_aggregates($register);

            // Reload tracked Users list before Recalculating [issue #14]
            $newTrackedUsers = attendanceregister_get_tracked_users($register);

            // Iterate each user and recalculate Sessions
            foreach ($newTrackedUsers as $user) {

                // Recalculate Session for one User
                $progressbar = new progress_bar('recalcbar_' . $user->id, 500, true);
                attendanceregister_force_recalc_user_sessions($register, $user->id, $progressbar, false); // No delete needed, having done before [issue #14]
            }
            // Reload All Users Sessions
            $trackedUsers = new attendanceregister_tracked_users($register, $userCapabilities);
        }
    }

    // Notification & Continue button
    if ( $doRecalculate || $doScheduleRecalc ) {
        $notificationStr = get_string( ($doRecalculate)?'recalc_complete':'recalc_scheduled', 'attendanceregister');
        echo $OUTPUT->notification($notificationStr, 'notifysuccess');
    }
    echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, null, $userId));
    $doShowContents = false;
}
//// Process Delete Offline Session Action
else if ($doShowContents && $doDeleteOfflineSession) {
    // Delete Offline Session
    attendanceregister_delete_offline_session($register, $sessionToDelete->userid, $sessionToDelete->id);

    // Notification & Continue button
    echo $OUTPUT->notification(get_string('offline_session_deleted', 'attendanceregister'), 'notifysuccess');
    echo $OUTPUT->continue_button(attendanceregister_makeUrl($register, null, $userId));
    $doShowContents = false;
}
//// Show Contents: User's Sesions (if $userID) or Tracked Users summary
else if ($doShowContents) {

    //// Show User's Sessions
    if ($userId) {

        /// Button bar

        echo $OUTPUT->container_start('attendanceregister_buttonbar btn-group');
        
        // Printable version button or Back to normal version
        $linkUrl = attendanceregister_makeUrl($register, $id, $userId, null, ( ($doShowPrintableVersion) ? (null) : (ATTENDANCEREGISTER_ACTION_PRINTABLE)));
        //echo $OUTPUT->single_button($linkUrl, (($doShowPrintableVersion) ? (get_string('back_to_normal', 'attendanceregister')) : (get_string('show_printable', 'attendanceregister'))), 'get');
        // Back to Users List Button (if allowed & !printable)
      echo '<input type="button" name="download" id="download" value= " Printable Version " onclick=\'javascript: printPage();\' />';
  if ($userCapabilities->canViewOtherRegisters && !$doShowPrintableVersion) {
            echo $OUTPUT->single_button(attendanceregister_makeUrl($register, $id), get_string('back_to_tracked_user_list', 'attendanceregister'), 'get');
        }
        echo $OUTPUT->container_end();  // Button Bar
        echo '<br />';


        /// Offline Session Form
        // Show Offline Session Self-Certifiation Form (not in printable)
        if ($mform && $register->offlinesessions && !$doShowPrintableVersion) {
            echo "<br />";
            echo $OUTPUT->box_start('generalbox attendanceregister_offlinesessionform');
            $mform->display();
            echo $OUTPUT->box_end();
        }

//    // Show tracked Courses
//    echo '<div class="table-responsive">';
//    echo html_writer::table( $userSessions->trackedCourses->html_table()  );
//    echo '</div>';    

        // Show User's Sessions summary
        echo '<div class="table-responsive">';
        echo html_writer::table($userSessions->userAggregates->html_table());
        echo '</div>';
        
        echo '<div class="table-responsive">';       
        echo html_writer::table($userSessions->html_table());
        echo '</div>';
    }

    //// Show list of Tracked Users summary
    else {

        /// Button bar
	$manager = get_log_manager();
	$allreaders = $manager->get_readers();
	if (isset($allreaders['logstore_standard'])) {
    		$standardreader = $allreaders['logstore_standard'];
    		if ($standardreader->is_logging()) {
        		// OK
    		} else {
        		// Standard log non scrive
			echo $OUTPUT->notification( get_string('standardlog_readonly', 'attendanceregister')  );
    		}
	} else {
    		// Standard log disabilitato 
		echo $OUTPUT->notification( get_string('standardlog_disabled', 'attendanceregister')  );
	}
        // Show Recalc pending warning
        if ( $register->pendingrecalc && $userCapabilities->canRecalcSessions && !$doShowPrintableVersion ) {
            echo $OUTPUT->notification( get_string('recalc_scheduled_on_next_cron', 'attendanceregister')  );
        }
        // Show cron not yet run on this instance
        else if ( !attendanceregister__didCronRanAfterInstanceCreation($cm) ) {
            echo $OUTPUT->notification( get_string('first_calc_at_next_cron_run', 'attendanceregister')  );
        }

        echo $OUTPUT->container_start('attendanceregister_buttonbar btn-group');
        
        // If current user is tracked, show view-my-sessions button [feature #28]
        if ( $userCapabilities->isTracked ) {
            $linkUrl = attendanceregister_makeUrl($register, null, $USER->id);
            echo $OUTPUT->single_button($linkUrl, get_string('show_my_sessions' ,'attendanceregister'), 'get' );
        }
        
        // Printable version button or Back to normal version
        $linkUrl = attendanceregister_makeUrl($register, null, null, ( ($doShowPrintableVersion) ? (null) : (ATTENDANCEREGISTER_ACTION_PRINTABLE)));
        echo $OUTPUT->single_button($linkUrl, (($doShowPrintableVersion) ? (get_string('back_to_normal', 'attendanceregister')) : (get_string('show_printable', 'attendanceregister'))), 'get');
        
        echo $OUTPUT->container_end();  // Button Bar
        echo '<br />';

        // Show list of tracked courses
        echo '<div class="table-responsive">'; 
        echo html_writer::table($trackedUsers->trackedCourses->html_table());
        echo '</div>';

        // Show tracked Users list
        echo '<div class="table-responsive">'; 
        echo html_writer::table($trackedUsers->html_table());
        echo '</div>';
    }
}
?>
<div id="dialog-form" title="Create new session" style="display: none;">
  <p class="validateTips">All form fields are required.</p>
 
  <form action="" name="f1" id="f1" method="post" autocomplete="off">
    <fieldset>
      <label for="logindatetime">Login Date/time</label>
      <input type="text" name="login" id="login" value="" class="text ui-widget-content ui-corner-all" placeholder="Login Date/Time">
      <label for="logoutdatetime">Logout Date/time</label>
      <input type="text" name="logout" id="logout" value="" class="text ui-widget-content ui-corner-all" placeholder="Logout Date/Time">
      <label for="comments">Comments</label>
      <textarea name="comments" id="comments" value="" class="text ui-widget-content ui-corner-all" style="width: 290px!important;"></textarea>
 
      <!-- Allow form submission with keyboard without duplicating the dialog button -->
      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
      <input type="hidden" name="userid"  id="userid"  value="<?php echo $_GET['userid']; ?>"  />
      <input type="hidden" name="registerid"  id="registerid"  value="<?php echo $cm->instance; ?>"  />
      <input type="hidden" name="addsession"  id="addsession"  value="1"  />
    </fieldset>
      
  </form>
</div>
<form action="" method="post" name="f2" id="f2">
    <input type="hidden" name="session_id" id="session_id" value="" />
    <input type="hidden" name="login_date" id="login_date" value="" />
    <input type="hidden" name="logout_date" id="logout_date" value="" />
    <input type="hidden" name="login_date_old" id="login_date_old" value="" />
    <input type="hidden" name="logout_date_old" id="logout_date_old" value="" />
    <input type="hidden" name="registerid" id="registerid" value="<?php echo $registerid; ?>" />
    <input type="hidden" name="userid" id="userid" value="<?php echo $_GET['userid']; ?>" />
     <input type="hidden" name="updatesession" id="updatesession" value="1" />
</form>
<?php


// Output page footer
if (!$doShowPrintableVersion) {
    
    ?>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>
		<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/js/jquery-ui-timepicker-addon.js"></script>
		<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/i18n/jquery-ui-timepicker-addon-i18n.min.js"></script>
		<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/js/jquery-ui-sliderAccess.js"></script>

		<script type="text/javascript">
			
			$(function(){
				$('#tabs').tabs();
		
				$('.example-container > pre').each(function(i){
					eval($(this).text());
				});
			});
			
		</script>
<script>
$('#login').datetimepicker({
	timeInput: true,
	timeFormat: "hh:mm tt",
        changeMonth: true,
        changeYear: true
});
$('#logout').datetimepicker({
	timeInput: true,
	timeFormat: "hh:mm tt",
        changeMonth: true,
        changeYear: true
});
$('.edit_session_login_datetime').datetimepicker({
	timeInput: true,
	timeFormat: "hh:mm tt",
        changeMonth: true,
        changeYear: true
});
$('.edit_session_logout_datetime').datetimepicker({
	timeInput: true,
	timeFormat: "hh:mm tt",
        changeMonth: true,
        changeYear: true
});


function redirectSession(userid)
{
//        var d = days*24*60*60;
//        var h = hours*60*60;
//        var m = minutes*60;
//        var timestamp = d+h+m+seconds;
//       // alert(timestamp);
//alert("update.php?<?php //echo $_SERVER['QUERY_STRING']; ?>&userid='+userid+'&registerid=<?php //echo $cm->instance; ?>"); return false;
        window.location.href='update.php?<?php echo $_SERVER['QUERY_STRING']; ?>&userid='+userid+'&registerid=<?php echo $cm->instance; ?>';
    
}
</script>
<script>
  $( function() {
    var dialog, form,
 
      // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
      name = $( "#login" ),
      email = $( "#logout" ),
      password = $( "#comments" ),
      allFields = $( [] ).add( name ).add( email ).add( password ),
      tips = $( ".validateTips" );
 
    function updateTips( t ) {
      tips
        .text( t )
        .addClass( "ui-state-highlight" );
      setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
    }
 
    function checkLength( o , name ) {
      if ( o.val().length ==0 ) {
        o.addClass( "ui-state-error" );
        updateTips( "Please enter "+name+"!" );
        return false;
      } else {
        return true;
      }
    }
 
    function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        updateTips( n );
        return false;
      } else {
        return true;
      }
    }
 
    function addUser() {
      var valid = true;
      allFields.removeClass( "ui-state-error" );
 
      valid = valid && checkLength( name , 'Login date time' );
      valid = valid && checkLength( email, "Logout date time" );
      //valid = valid && checkLength( password, "comments", 5, 16 );
 
//      valid = valid && checkRegexp( name, /^[a-z]([0-9a-z_\s])+$/i, "Username may consist of a-z, 0-9, underscores, spaces and must begin with a letter." );
//      valid = valid && checkRegexp( email, emailRegex, "eg. ui@jquery.com" );
//      valid = valid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
 
      if ( valid ) {
        $( "#users tbody" ).append( "<tr>" +
          "<td>" + name.val() + "</td>" +
          "<td>" + email.val() + "</td>" +
          "<td>" + password.val() + "</td>" +
        "</tr>" );
        document.getElementById('f1').submit();
        dialog.dialog( "close" );
      }
      return valid;
    }
 
    dialog = $( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 400,
      width: 350,
      modal: true,
      buttons: {
        " Add Sesssion ": addUser,
        Cancel: function() {
          dialog.dialog( "close" );
        }
      },
      close: function() {
        form[ 0 ].reset();
        allFields.removeClass( "ui-state-error" );
      }
    });
 
    form = dialog.find( "form" ).on( "submit", function( event ) {
      event.preventDefault();
      addUser();
    });
 
    $( ".create-session" ).button().on( "click", function() { 
      dialog.dialog( "open" );
    });
  } );
  function printPage()
        {
            window.open('print2.php?<?php  echo $_SERVER['QUERY_STRING']; ?>', '_blank');
           // window.opem.href="print.php?<?php  //echo $_SERVER['QUERY_STRING']; ?>";
        }
  </script>
<?php
echo $OUTPUT->footer();
}
?>
