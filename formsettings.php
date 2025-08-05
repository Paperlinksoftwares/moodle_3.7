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

$admins = get_admins();
$isadmin = false;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}
if($isadmin==false)
{
	?>
	<script> alert('Sorry! You dont have permission to do it!'); window.location.href="index.php"; </script>
	<?php
}
$sql_form_settings = "SELECT `setting` FROM `mdl_form_settings` WHERE `formid` = '1'";
$row_form_settings = $DB->get_record_sql($sql_form_settings);
if(@$row_form_settings->setting=='')
{
    $val = 0;
}
else
{
    $val = @$row_form_settings->setting;
}
if(isset($_GET['stat']) && $_GET['stat']!='')
{
    if($_GET['stat']!=2)
    {
        $u1 = $DB->execute("UPDATE {form_settings} set `setting` = '".$_GET['stat']."' WHERE `formid` = '1'");
    }
    ?>
<script> window.location.href="index.php"; </script>
<?php
}

//$uidcheck = $checkval->uid;

?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <style>
  .modal-backdrop {
background-color: #275394!important;
  }
  </style>
<script>
     
    $(document).ready(function() {
    
    
   $('#myModal').modal('show');
  
});
function doUpdate(val)
{
    window.location.href="formsettings.php?stat="+val;
}
</script>
<div class="container" style=" pointer-events: none; ">
  <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" style="display: none;"></button>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog" style="display: none; top:50px;">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
         
            <h4 class="modal-title"><strong>Forms</strong></h4>
        </div>
        <div class="modal-body">
             <?php if(@$USER->id!='') { ?>
                        
            <form action="formsettings.php" name="f22" id="f22" method="post">
                <table>
                    <tr>
                    <td>Enable</td>
                    <td><input type="radio" name="status[]" id="status[]" value="1" <?php if($val==1 || $val==2) { ?> checked <?php } ?> onchange="javascript: doUpdate(this.value);" /></td>
                    <td>&nbsp;&nbsp;&nbsp;</td>
                    <td>Disable</td>
                    <td><input type="radio" name="status[]" id="status[]" value="0"  <?php if($val==0) { ?> checked <?php } ?> onchange="javascript: doUpdate(this.value);" /></td>
                    <td>&nbsp;&nbsp;&nbsp;</td>
                    <td>No, leave as it is</td>
                    <td><input type="radio" name="status[]" id="status[]" value="2" onchange="javascript: doUpdate(this.value);" /></td>
                    </tr>
                </table></form> <?php } ?>
        </div>

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

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$editing = $PAGE->user_is_editing();
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$courserenderer = $PAGE->get_renderer('core', 'course');
echo $OUTPUT->header();



echo $OUTPUT->footer();
