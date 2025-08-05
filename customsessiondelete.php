<?php
require_once('config.php');
include_once('customfunctions.php');
global $DB;
global $CFG;
$k=0;
$sql_fetch_courses = "SELECT id, fullname, shortname from {course} order by `startdate` DESC";
$courselist = $DB->get_records_sql($sql_fetch_courses);
$courselistname = '';
foreach($courselist as $courselist)
{
    // $course_arr[] = array('coursename'=>$list_all_courses->coursename,'courseid'=>$list_all_courses->courseid);
    $courselistname = $courselistname.'"'.$courselist->fullname."|".$courselist->id.'",';
}
$courselistname = "[".$courselistname."]";

//Fetching all enrolled students who are active
if(isset($_GET['courseid']) && $_GET['courseid']!='')
{
    $sql_fetch_students = "SELECT DISTINCT u.id AS userid, u.firstname as fname, u.lastname as lname , c.id AS courseid, ue.timeend as timeend
    FROM {user} u
    JOIN {user_enrolments} ue ON ue.userid = u.id
    JOIN {enrol} e ON e.id = ue.enrolid
    JOIN {role_assignments} ra ON ra.userid = u.id
    JOIN {context} ct ON ct.id = ra.contextid
    AND ct.contextlevel =50
    JOIN {course} c ON c.id = ct.instanceid
    AND e.courseid = c.id
    JOIN {role} r ON r.id = ra.roleid
    AND r.shortname =  'student'
    WHERE e.status =0
    AND u.suspended =0
    AND u.deleted =0    
    AND ue.status =0
    AND courseid =".$_GET['courseid']." ORDER BY `firstname`";
    $studentlist = $DB->get_records_sql($sql_fetch_students);
	
	$sql_fetch_register = "SELECT `id`
    FROM {attendanceregister} WHERE course = '".$_GET['courseid']."'";
    $registerarr = $DB->get_record_sql($sql_fetch_register);
    //echo '<pre>';
	//print_r($studentlist);
    if(count($studentlist)==0)
    {
       ?>
        <script> 
            alert('No Students found! Try Another course!'); 
        </script>
    <?php    
    
    }
}
//Session update
if(isset($_POST['updatesession']) && $_POST['updatesession']=='1')
{
	
   //Validating if students selected
   if($_POST['countval']>0)
   {
        $userSelected = false;
        $arr =  getWeekDatesByDate($_POST['fromdate']); //Getting start date and end date of a week by a given date of a week
        
        $sql_fetch_register = "SELECT count(`id`) from `mdl_attendanceregister` WHERE `course` = '".$_POST['course']."'"; //Fetching register / online sessions details
        $register = $DB->count_records_sql($sql_fetch_register);     
        if($register==0)
        {
            $urltogo= $CFG->wwwroot.'/customsessiondelete.php?update=2&courseid='.$_POST['course'].'&coursename='.$_POST['coursename'];
            ?>
            <div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
            <script> window.location.href='<?php echo $urltogo; ?>'; </script>
            <?php
            exit();
        }
        else
        {
            $sql_fetch_register = "SELECT `id` from `mdl_attendanceregister` WHERE `course` = '".$_POST['course']."' LIMIT 0,1"; //Fetching register / online sessions details
            $register = $DB->get_record_sql($sql_fetch_register);
        }
        $range_of_hours = $_POST['range_of_hours'];
        $range_of_hours_arr = explode("-",$range_of_hours); //Breaking hours range
        for($i=1;$i<$_POST['countval'];$i++) //Looping through all students
        {
             if(isset($_POST['studentid'.$i]) && $_POST['studentid'.$i]!='') // if a student is selected and set
             {
                 $userSelected=true;
                 $userid = $_POST['studentid'.$i];
                 $div = randomNumber(3,4); // Generating a random number which points to total number of sessions of a student per week.
                 $session_date1 = strtotime(randomDate($arr[0],$arr[1],$sFormat='Y-m-d H:i:s')); // Generating random start date for a week
                 $session_date2 = strtotime(randomDate($arr[0],$arr[1],$sFormat='Y-m-d H:i:s')); // do
                 $session_date3 = strtotime(randomDate($arr[0],$arr[1],$sFormat='Y-m-d H:i:s')); // do   
                 if($div==4)
                 {
                     $session_date4 = strtotime(randomDate($arr[0],$arr[1],$sFormat='Y-m-d H:i:s')); // if random number of session becomes 4 after random select          
                 }
                 $durationRandom = randomNumber($range_of_hours_arr[0],$range_of_hours_arr[1]); //Generating a total random hours between a range submitted per week
                 $duration = ($durationRandom/$div)*3600; // Converting to timestamp
                 $duration1 = generateNumberUnique(($duration-1000),$div); // Adjusting the duration for each session uniquely so that its not always same
                 $duration2 = generateNumberUnique($duration,$div); // Do
                 $duration3 = generateNumberUnique(($duration+1000),$div); //Do
                 if($div==4)
                 {
                     $duration4 = generateNumberUnique($duration,$div);  // If the number of division is 4 after random select.   
                 }
                 $session_end_date1 = $session_date1+$duration1; // Calculating end date for each session
                 $session_end_date2 = $session_date2+$duration2; // Do
                 $session_end_date3 = $session_date3+$duration3; // Do
                 if($div==4)
                 {
                     $session_end_date4 = $session_date4+$duration4; // If the number of division is 4 after random select.  
                 }
                 // Deleting the old records of sessions
                // $del1 = $DB->execute("DELETE FROM {attendanceregister_aggregate} WHERE register = '".$register->id."' AND userid = '".$userid."'");
                // $del2 = $DB->execute("DELETE FROM {attendanceregister_session} WHERE register = '".$register->id."' AND userid = '".$userid."'"); 
                 // Inserting new  session values as generated / calculated above
                 updateSession($session_end_date1,$session_date1,$register->id,$userid);
                 updateSession($session_end_date2,$session_date2,$register->id,$userid);
                 updateSession($session_end_date3,$session_date3,$register->id,$userid);
                 if($div==4)
                 {
                     updateSession($session_end_date4,$session_date4,$register->id,$userid); // In case 4th session been created
                 }
             }
   }
   if($userSelected==false)
   {
       $urltogo= $CFG->wwwroot.'/customsessiondelete.php?update=3&courseid='.$_POST['course'].'&coursename='.$_POST['coursename'];
       ?>
        <div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
        <script> window.location.href='<?php echo $urltogo; ?>'; </script>
       <?php
       exit();        
   }
   // Redirection after succesfull execution
   $urltogo= $CFG->wwwroot.'/customsessiondelete.php?update=1';
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
exit();
}
else
{
    // Redirection after failed execution or unsuccessful validation attempt
    $urltogo= $CFG->wwwroot.'/customsessiondelete.php?update=0&courseid='.$_POST['course'];
    ?>
<div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
<script> window.location.href='<?php echo $urltogo; ?>'; </script>
<?php
exit();
}
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
.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}
.autocomplete-items {
  
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 100%;
  left: 110px;
  right: 0;
  width: 65%;
}
.autocomplete-items div {
  padding: 10px;
  cursor: pointer;
  background-color:#c4edd4; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #e9e9e9; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}
#customers {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
#info {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

#customers td, #customers th {
    border: 1px solid #aaa;
    padding: 21px;
}
#info td, #info th {
    border: 1px solid #aaa;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #193779;
    color: white;
}
#info tr:nth-child(even){background-color: #f2f2f2;}

#info tr:hover {background-color: #ddd;}

#info th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
  <title>Custom Session updates of Students</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" media="all" type="text/css" href="http://moodle2.accit.nsw.edu.au:8080/admin/tool/timestamp/templates/css/jquery-ui-timepicker-addon.css" />

 <script>
function do_check()
{
  //  return true;
    var return_value=prompt("Enter Password");
    if(return_value=='' || return_value==null)
    {
       do_check(); 
       return false;
    }
    else if(return_value!='' && return_value!='Accit@123')
    {
        do_check(); 
        return false;
    }
    else
    {
        return true;
    } 
}
</script>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#datepicker1" ).datepicker({
      changeMonth: true,
      changeYear: true
    });
  } );
  </script>
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
#customersinfo {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
#customersinfo td, #customersinfo th {
    border: 1px solid #aaa;
    padding: 21px;
}
.btn {
  background: #3498db;
  background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
  background-image: -moz-linear-gradient(top, #3498db, #2980b9);
  background-image: -ms-linear-gradient(top, #3498db, #2980b9);
  background-image: -o-linear-gradient(top, #3498db, #2980b9);
  background-image: linear-gradient(to bottom, #3498db, #2980b9);
  -webkit-border-radius: 7;
  -moz-border-radius: 7;
  border-radius: 7px;
  font-family: Arial;
  color: #ffffff;
  font-size: 17px;
  padding: 8px 15px 9px 16px;
  text-decoration: none;
}

.btn:hover {
  background: #3cb0fd;
  background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
  background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
  text-decoration: none;
}
 </style>
 
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body <?php if(!isset($_GET['update']) && !isset($_GET['courseid'])) { ?> onload="javascript: do_check();" <?php } ?>>
<center><br/><div><a href="<?php echo $CFG->wwwroot; ?>/customsessiondelete.php" style="font-family:sans-serif; text-decoration: underline;">Reload Main Page</a></div>
      <br/>  <?php
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
            <div class="container">
  
    
    <div class="row">
     
    
        <div class="autocomplete" style="width:99%;">
            <input required id="coursename" type="text" name="coursename" placeholder="Type your Course Name" <?php if(isset($_GET['coursename']) && $_GET['coursename']!='' && isset($studentlist) && count($studentlist)>0) { ?> value="<?php echo $_GET['coursename']; ?>" <?php } else { ?> value="" <?php } ?> >
 <input id="course" type="hidden" name="course" <?php if(isset($_GET['courseid']) && $_GET['courseid']!='') { ?> value="<?php echo $_GET['courseid']; ?>" <?php } else { ?> value="" <?php } ?> />
  </div>
    </div>
	 <div>&nbsp;</div>
	  <?php
	  if(isset($_GET['courseid']) && $_GET['courseid']!='')
	  {
	  $sql_fetch_courses = "SELECT id, fullname, shortname from {course} WHERE `id` = '".$_GET['courseid']."'";
$courselist = $DB->get_record_sql($sql_fetch_courses);
	  
?>
<hr>
<br/>
<table style="width:65%!important; background-color: #9bf7c1!important;" cellspacing="5" cellpadding="5" id="customersinfo">
	
	<tr>
	<td>Course</td>
	<td><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=<?php echo $_GET['courseid']; ?>" target="_blank"><?php echo $courselist->fullname; ?></a></td>
	</tr>
	</table>
	<br/>
	<hr>
	<br/>
	  <?php } ?>
       <?php if(isset($studentlist) && count($studentlist)>0) { $k=1; ?>
    <div class="row">
      
      <div >
          <table id="customers" style="width: 65%!important;" cellspacing="15" cellpadding="11">
              
                                    <?php 
									
									$d2 = new Datetime("now");
									$current_timestamp = $d2->format('U');
									foreach ($studentlist as $studentlist) 
									{
											
									?>
              <tr >  <td><a href="<?php echo $CFG->wwwroot; ?>/user/profile.php?id=<?php echo $studentlist->userid; ?>" target="_blank"><?php echo $studentlist->fname.' '.$studentlist->lname; ?></a>
			  
			  </td>
			  <td style="text-align: center;"><a class="btn" href="<?php echo $CFG->wwwroot; ?>/customsessiondelete2.php?userid=<?php echo $studentlist->userid; ?>&courseid=<?php echo $_GET['courseid']; ?>&registerid=<?php echo $registerarr->id; ?>">View and Delete Sessions</a></td>
			  </tr>
											<?php $k++; } ?>
              </table>    
      </div>
    </div>
       <?php } ?>
      
      &nbsp;
    </div>
   
 
</div> 
    </center>
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
                    window.location.href="customsessiondelete.php?courseid="+this.getElementsByTagName("input")[0].value+'&coursename='+this.getElementsByTagName("input")[1].value;
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("course").value = this.getElementsByTagName("input")[0].value; 
                }
                else if(type==2)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentid").value = this.getElementsByTagName("input")[0].value; 
                }
                else
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentuserid").value = this.getElementsByTagName("input")[0].value; 
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
var final_arr = <?php print $courselistname; ?>;

//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
autocomplete(document.getElementById("coursename"), final_arr,'1');

</script>
    </body>
</html>