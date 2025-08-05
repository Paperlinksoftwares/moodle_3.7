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
$if_student = 1;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $if_student = 0;
        break;
    }
    
}

//$uidcheck = $checkval->uid;


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

?>
<div class="card-text content mt-3" id="yui_3_17_2_1_1568871366354_108">
            <ul class="unlist" id="yui_3_17_2_1_1568871366354_107">
			
<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/9z1zlIxGgvkuZOmUePcA', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/9z1zlIxGgvkuZOmUePcA"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Counselling Intake Form<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>



<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26631&amp;redirect=1', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26631&amp;redirect=1"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Internal Appeal Form<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>


<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26633&amp;redirect=1', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26633&amp;redirect=1"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Application for Re-Assessment<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>



<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/f5FEhqmoG5HtGwh5v7Ts', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/f5FEhqmoG5HtGwh5v7Ts"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Late Reassessment Request Appeal Form<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>




<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/3nQt9Uqcw7GuG5fF1pqM', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/3nQt9Uqcw7GuG5fF1pqM"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Additional Information for Extension Approval Form (for Finishing-off Students)

<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>



<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/BghDWSbVeYWOXhqfQhfe', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/BghDWSbVeYWOXhqfQhfe"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Additional Information for Reassessment Approval
</span></a></div></div></div><div style="height: 12px;"></div></li>



<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/G2lTD1QS9zdxpGDw6DUV', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/G2lTD1QS9zdxpGDw6DUV"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student's Progress Declaration Form
</span></a></div></div></div><div style="height: 12px;"></div></li>


<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/j6BICy8QwHai3PHre0Ct', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/j6BICy8QwHai3PHre0Ct"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student's Service Request Form
</span></a></div></div></div><div style="height: 12px;"></div></li>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26634&amp;redirect=1', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26634&amp;redirect=1"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Certificate Transcript Request Form
</span></a></div></div></div><div style="height: 12px;"></div></li>



<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/h3Am8qjo9RSUoSIWC91p', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/h3Am8qjo9RSUoSIWC91p"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Certificate Collection Form
</span></a></div></div></div><div style="height: 12px;"></div></li>

<!--

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/g7foRqT5zJTAb0UPjBVf', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/g7foRqT5zJTAb0UPjBVf"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student's Feedback on ACCIT Services and Facilities
</span></a></div></div></div><div style="height: 12px;"></div></li>


<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/FvQFdvoq5DfG2G6MtXPi', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/FvQFdvoq5DfG2G6MtXPi"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Feedback on Trainer's Performance


</span></a></div></div></div><div style="height: 12px;"></div></li>

-->

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/VGPKBdy9x4DSAKsnP2bB', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/BghDWSbVeYWOXhqfQhfe"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Agents Evaluation


</span></a></div></div></div><div style="height: 12px;"></div></li>


<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26632&amp;redirect=1', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/BghDWSbVeYWOXhqfQhfe"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Leave and Deferral Form
</span></a></div></div></div><div style="height: 12px;"></div></li>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/DBZiq76QDKdOAndrPWUJ', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/DBZiq76QDKdOAndrPWUJ"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Job Safety Analysis Template
</span></a></div></div></div><div style="height: 12px;"></div></li>




<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/BU8W45ST5Cqe1Eul06VW', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/BU8W45ST5Cqe1Eul06VW"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Feedback Form
</span></a></div></div></div><div style="height: 12px;"></div></li>






<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/Qt7KcBJA9yphu1RhJgSh', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/Qt7KcBJA9yphu1RhJgSh"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">ACCIT - White Card Confirmation Form for CPC30620 Certificate III in Painting and Decorating Students
</span></a></div></div></div><div style="height: 12px;"></div></li>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/f5FEhqmoG5HtGwh5v7Ts', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/f5FEhqmoG5HtGwh5v7Ts"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">ACCIT - Late Reassessment Request Appeal Form
</span></a></div></div></div><div style="height: 12px;"></div></li>




<?php if($if_student==0) { ?>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity">
			<a class="" onclick="window.open('https://zfrmz.com/El6SMqXU48FVzewXY7sy', '', 'width=900,height=980,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/El6SMqXU48FVzewXY7sy"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">CPC30620 Certificate III in Painting and Decorating - Resource Requirements
</span></a></div></div></div><div style="height: 12px;"></div></li>
<?php } ?>
<!--
<li class="r1">
<div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26632&amp;redirect=1', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26632"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Leave and Deferral Form<span class="accesshide "> URL</span></span></a></div></div></div>
<div style="height: 12px;"></div></li>
<li class="r0"><div class="column c1">
<div class="main-menu-content">
<div class="activity"><a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26633&amp;redirect=1', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26633"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Application for Re-Assessment<span class="accesshide "> URL</span></span></a></div></div></div>

<div style="height: 12px;"></div></li>
<li class="r1">
<div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/G2lTD1QS9zdxpGDw6DUV', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="https://zfrmz.com/G2lTD1QS9zdxpGDw6DUV"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student's Progress Declaration Form
<span class="accesshide "> URL</span></span></a></div></div></div>
<div style="height: 12px;"></div></li>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('http://localhost/accit-moodle/accit/mod/url/view.php?id=26634&amp;redirect=1', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26634"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Certificate transcript request<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>
   
   <li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/h3Am8qjo9RSUoSIWC91p', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="http://localhost/accit-moodle/accit/mod/url/view.php?id=26634"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Certificate collection form<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>
     

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/g7foRqT5zJTAb0UPjBVf', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="#"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student's Feedback on ACCIT Services and Facilities <span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>
<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/FvQFdvoq5DfG2G6MtXPi', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="#"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student Feedback On Trainer's Performance<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>
<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/VGPKBdy9x4DSAKsnP2bB', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="#"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Agents Evaluation<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>
   
<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/j6BICy8QwHai3PHre0Ct', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="#"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Student's Service Request <span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/9z1zlIxGgvkuZOmUePcA', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="#"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">Councelling Intake Form<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>

<li class="r0"><div class="column c1"><div class="main-menu-content"><div class="activity"><a class="" onclick="window.open('https://zfrmz.com/3nQt9Uqcw7GuG5fF1pqM', '', 'width=1000,height=1000,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes'); return false;" href="#"><img src="http://localhost/accit-moodle/accit/formicon.png" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true"><span class="instancename">ACCIT - Additional Information for Extension Approval Form (for Finishing-off Students)<span class="accesshide "> URL</span></span></a></div></div></div><div style="height: 12px;"></div></li>

-->

	 <div class="footer"></div>
            
        </div>
<?php

echo $OUTPUT->footer();
