<?php
require_once('config.php');
include_once('customfunctions.php');
global $DB;
global $CFG;

//Fetching all enrolled students who are active
if(isset($_REQUEST['searchstudent']) && $_REQUEST['searchstudent']==1)
{
	$term = $_REQUEST['term'];
	$year = $_REQUEST['year'];
	$form_type = $_REQUEST['form_type'];
    $sql_fetch_students = "SELECT DISTINCT u.id AS userid, u.suspended , u.firstname, u.lastname, u.username , u.lastaccess, c.fullname , c.id AS courseid
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student'
WHERE e.status = 0 AND u.deleted = 0
  AND (ue.timeend = 0 OR ue.timeend > UNIX_TIMESTAMP(NOW())) AND ue.status = 0 AND ( c.fullname LIKE '%T".$term."/".$year."%' OR 
  (( MONTH(FROM_UNIXTIME(c.startdate)) = '7' OR MONTH(FROM_UNIXTIME(c.startdate)) = '8' 
  OR MONTH(FROM_UNIXTIME(c.startdate)) = '9') AND YEAR(FROM_UNIXTIME(c.startdate)) = '".@date('Y')."')) group by u.id";
    $studentlist = $DB->get_records_sql($sql_fetch_students);
    //echo '<pre>';
	//print_r($studentlist);
    if(count($studentlist)==0)
    {
       ?>
        <script> 
            alert('No Students found! Try Another course!'); 
			window.location.href="formstudentinclusion1.php";
        </script>
    <?php    
    
    }
}
//Session update
if(isset($_POST['updateaccess']) && $_POST['updateaccess']=='1')
{
	$userSelected = false;
   //Validating if students selected

	   if(count($_POST['studentid'])>0)
	   {
			$userSelected = true;
	   }
         
        $sql_fetch_access = "SELECT count(`id`) from `mdl_forms_students_access` WHERE `term` = '".$_POST['term']."' AND `year` = '".$_POST['year']."'"; 
        $access = $DB->count_records_sql($sql_fetch_access);     
        if($access>0)
        {
           $del = $DB->execute("DELETE from `mdl_forms_students_access` WHERE `term` = '".$_POST['term']."' AND `year` = '".$_POST['year']."'");
        }
        
        for($i=0;$i<count($_POST['studentid']);$i++) //Looping through all students
        {
			if($_POST['studentid'][$i]>0)
			{
				$studentid = $_POST['studentid'][$i];
				$username = $_POST['user_name'][$i];
				$sql_insert = "INSERT INTO `mdl_forms_students_access` (`id`, `form_type`, `user_id`, `user_name` , `year`, `term`) VALUES (NULL, '".$_POST['form_type']."', '".$studentid."', '".$username."' , '".$_POST['year']."', '".$_POST['term']."')"; 
				$insert_result = $DB->execute($sql_insert); 
				unset($studentid);
				unset($sql_insert);
				unset($insert_result);
				unset($username);
			}	
		}			
       
   if($userSelected==false)
   {
       $urltogo= $CFG->wwwroot.'/formstudentinclusion2.php?update=3&term='.$_REQUEST['term'].'&year='.$_REQUEST['year'].'&form_type='.$_REQUEST['form_type'].'&searchstudent=1';
       ?>
        <div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
        <script> window.location.href='<?php echo $urltogo; ?>'; </script>
       <?php
       exit();        
   }
   // Redirection after succesfull execution
   $urltogo= $CFG->wwwroot.'/formstudentinclusion2.php?update=1&term='.$_REQUEST['term'].'&year='.$_REQUEST['year'].'&form_type='.$_REQUEST['form_type'].'&searchstudent=1';
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
* {
    box-sizing: border-box;
}

input[type=text], select, textarea {
    width: 65%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
input[type=number] {
    width: 65%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical;
}
label {
    padding: 12px 12px 12px 0;
    display: inline-block;
    font-family: sans-serif;
}

input[type=submit] {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    float: none;
    margin-top: 15px;
}

input[type=submit]:hover {
    background-color: #45a049;
}

.container {
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 20px;
}

.col-25 {
    float: left;
    width: 25%;
    margin-top: 6px;
}

.col-75 {
    float: left;
    width: 75%;
    margin-top: 6px;
}

/* Clear floats after the columns */
.row:after {
    content: "";
    display: table;
    clear: both;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
    .col-25, .col-75, input[type=submit] {
        width: 100%;
        margin-top: 0;
    }
}


</style>
  <title>Custom Students Inclusion/Exclusion of Students for Forms</title>
  
  <script>
   function checkAll(ele) {
     var checkboxes = document.getElementsByTagName('input');
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
 </script>

 <style>
     .info-msg,
.success-msg,
.warning-msg,
.error-msg {
  margin: 10px 0;
  padding: 10px;
  border-radius: 3px 3px 3px 3px;
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
 

</head>
<!-- <body <?php // if(!isset($_GET['update']) && !isset($_GET['courseid'])) { ?> onload="javascript: do_check();" <?php // } ?>>  -->
<body >
<center><br/><div><a href="<?php echo $CFG->wwwroot; ?>/formstudentinclusion1.php" style="font-family:sans-serif; text-decoration: underline;">Reload Main Page</a></div>
        <?php
        if(isset($_GET['update'])) {
if($_GET['update']==1)
   { 
       echo '<div class="success-msg">
  <i class="fa fa-check"></i>
  The data has been successfully updated!
   </div>'; }
   else if($_GET['update']==2)
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  No Online session register module is installed / activated for this course! Please contact the Administrator.
</div>';
   }
   else if($_GET['update']==3)
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  No student selected! Please select student.
</div>';
   }
   else 
       {
       echo '<div class="error-msg">
  <i class="fa fa-times-circle"></i>
  Some error occured or no student selected! Please try again later or contact to a technical assistant.
</div>';
   }
}
        ?>
    <form action="formstudentinclusion2.php" name="f1" id="f1" method="POST" autocomplete="off">
            <div class="container">
   
      <?php if(isset($studentlist) && count($studentlist)>0) { $k=1; ?>
    <div class="row">
      <div class="col-25">
        <label for="subject">Select Student</label>
      </div>
      <div class="col-75">
          <table style="width: 66%;">
              <tr>  <td><input type="checkbox" name="checkall" id="checkall" onchange="javascript: checkAll(this);" name="chk[]" >&nbsp;Check All</td></tr>
                                    <?php 
									
									$d2 = new Datetime("now");
									$current_timestamp = $d2->format('U');
									foreach ($studentlist as $studentlist) 
									{
										$sql_fetch_access2 = "SELECT count(`id`) from `mdl_forms_students_access` WHERE `user_id` = '".$studentlist->userid."' AND
										`term` = '".$term."' AND `year` = '".$year."' AND `form_type` = '".$form_type."'"; 
										$access2 = $DB->count_records_sql($sql_fetch_access2);     
										if($access2>0)
										{
										   $checked = "checked";
										}
										else
										{
											$checked = "";
										}
        	
									?>
              <tr <?php if($studentlist->timeend < $current_timestamp && $studentlist->timeend>0) { ?>
			  style="background-color: yellow!important;" <?php } ?>>  <td><input <?php if($studentlist->timeend < $current_timestamp && $studentlist->timeend>0) { ?>
			  disabled <?php } ?> <?php echo $checked; ?> type="checkbox" name="studentid[]" id="studentid[]" value="<?php echo $studentlist->userid; ?>" />&nbsp;<?php echo $studentlist->firstname.' '.$studentlist->lastname; ?>
			  <?php if($studentlist->timeend < $current_timestamp && $studentlist->timeend>0) { ?>[ <span style="color: red; font-weight: bold; font-size: 12px;">
			  Not Current</span> ]<?php } ?>
			  <?php if($studentlist->suspended==1) { ?>
			  [ <span style="color: red; font-weight: bold; font-size: 14px;">
			  Suspended</span> ]
			  <?php } ?>
			  
			  <span style="color: green!important; font-weight: bold; font-size: 16px!important;">[ Last Access : <?php echo @date("F d, Y h:i:s A",$studentlist->lastaccess); ?> ]</span></td></tr>
							<input type="hidden" name="user_name[]" id="user_name[]" value="<?php echo $studentlist->username; ?>" />				<?php  } ?>

              </table>    
      </div>
    </div>
       <?php } ?>
      
      
       <div class="row">
      &nbsp;
    </div>
    <div class="row">
        <input type="submit" value=" Submit " name="submit" id="submit" <?php if(isset($studentlist) && count($studentlist)==0) { ?> onclick="javascript: alert('No enrolled students found! Try another course'); return false;" <?php } ?> />
    </div>
 
</div><input type="hidden" value="1" name="updateaccess" id="updateaccess" />
<input type="hidden" value="<?php echo $term; ?>" name="term" id="term" /> 
<input type="hidden" value="<?php echo $year; ?>" name="year" id="year" /> 
<input type="hidden" value="<?php echo $form_type; ?>" name="form_type" id="form_type" /> 
</form>
    </center>
   </body>
</html>