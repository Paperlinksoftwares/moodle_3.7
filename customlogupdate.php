<?php
require_once('config.php');
include_once('customfunctions.php');
global $DB;
global $CFG;
$sql_fetch_courses = "SELECT id, fullname, shortname from {course}";
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
    $sql_fetch_teachers = "SELECT c.id, c.fullname,u.firstname,u.id,u.lastname,r.name            
    FROM {course} c
    JOIN {context} ct ON c.id = ct.instanceid
    JOIN {role_assignments} ra ON ra.contextid = ct.id
    JOIN {user} u ON u.id = ra.userid
    JOIN {role} r ON r.id = ra.roleid
    WHERE name = 'Teacher' AND c.id = '".$_GET['courseid']."'"; 
    $teacherlist = $DB->get_records_sql($sql_fetch_teachers);    
//    echo '<pre>';
//    print_r($teacherlist);
    if(count($teacherlist)==0)
    {
       ?>
        <script> 
            alert('No teachers found! Try Another course!'); 
        </script>
    <?php        
    }
    $sql_fetch_groups = "SELECT g.id, g.name            
    FROM {groups} g
    WHERE g.courseid = '".$_GET['courseid']."'";
    $grouplist = $DB->get_records_sql($sql_fetch_groups);    
}
//Logs update
if(isset($_POST['updatelog']) && $_POST['updatelog']=='1')
{
   $post_array = $_POST;
   $return = updateLogs($post_array);
   if($return==true)
   {
       $urltogo= $CFG->wwwroot.'/customlogupdate.php?update=1';
       ?>
        <div style="padding-left: 412px; padding-top: 181px;"><img src='<?php echo $CFG->wwwroot; ?>/mod/attendanceregister/images/loader.gif' border='0'></div>
        <script> window.location.href='<?php echo $urltogo; ?>'; </script>
       <?php
       exit(); 
   }
   else
   {
       $urltogo= $CFG->wwwroot.'/customlogupdate.php?update=0';
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
  width: 73%;
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

</style>
  <title>Custom Log updates of Students</title>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" media="all" type="text/css" href="http://moodle2.accit.nsw.edu.au:8080/admin/tool/timestamp/templates/css/jquery-ui-timepicker-addon.css" />

 <script>
function do_check()
{
    
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
  $( function() {
    $( "#datepicker2" ).datepicker({
      changeMonth: true,
      changeYear: true
    });
  } );
  function setCourse(val)
  {
      window.location.href="customlogupdate.php?courseid="+val;
  }
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
 </style>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body <?php if(!isset($_GET['update']) && !isset($_GET['courseid'])) { ?> onload="javascript: do_check();" <?php } ?>>
<center><br/><div><a href="<?php echo $CFG->wwwroot; ?>/customlogupdate.php" style="font-family:sans-serif; text-decoration: underline;">Reload Main Page</a></div>
        <?php
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
  Some error occured or no student selected! Please try again later or contact to a technical assistant.
</div>';
   }
}
        ?>
    <form action="" name="f1" id="f1" method="POST" autocomplete="off">
            <div class="container">
  
    
    <div class="row">
      <div class="col-25">
        <label for="country">Select Course</label>
      </div>
     <div class="autocomplete" style="width:75%;">
    <input id="coursename" type="text" name="coursename" placeholder="Type your Course Name" <?php if(isset($_GET['coursename']) && $_GET['coursename']!='' && isset($teacherlist) && count($teacherlist)>0) { ?> value="<?php echo $_GET['coursename']; ?>" <?php } else { ?> value="" <?php } ?> >
 <input id="course" type="hidden" name="course" <?php if(isset($_GET['courseid']) && $_GET['courseid']!='') { ?> value="<?php echo $_GET['courseid']; ?>" <?php } else { ?> value="" <?php } ?> />
  </div>
    </div>
       <?php if(isset($teacherlist) && count($teacherlist)>0) { $k=1; ?>
    <div class="row">
      <div class="col-25">
        <label for="subject">Select Teacher</label>
      </div>
      <div class="col-75">
           <select name="teacherid" id="teacherid" required>
                                    <option value="">Select Teacher/Trainer</option>
                                   <?php foreach ($teacherlist as $teacherlist) { ?>
                                    <option value="<?php echo $teacherlist->id; ?>">
                                       &nbsp;<?php echo $teacherlist->firstname.' '.$teacherlist->lastname; ?></option>
                                   <?php } ?>
                                </select>
          
      </div>
    </div>
       <?php } ?>
    <?php if(isset($grouplist) && count($grouplist)>0) { ?>
    <div class="row">
      <div class="col-25">
        <label for="subject">Select Group to Update</label>
      </div>
      <div class="col-75">
          <select name="groupid" id="groupid">
                                    <option value="">Select Group</option>
                                   <?php foreach ($grouplist as $grouplist) { ?>
                                    <option value="<?php echo $grouplist->id; ?>">
                                       <?php echo $grouplist->name; ?></option>
                                   <?php } ?>
                                </select>
      </div>
    </div>
   <?php } ?>
    <div class="row">
      <div class="col-25">
        <label for="subject">Select Module to Update</label>
      </div>
      <div class="col-75">
           <select name="module" id="module" required>
                                    <option value="">Select Module</option>
                                   
                                    <option value="mod_attendance">
                                       Module Attendance</option>
                                    <option value="mod_attendanceregister">
                                       Module Attendance Register</option>
                                </select>
      </div>
    </div>
       <div class="row">
      <div class="col-25">
        <label for="fname">Select From Date</label>
      </div>
      <div class="col-75">
          <input type="text" name="fromdate" id="datepicker1" placeholder="From" />
      </div>
    </div>
    <div class="row">
      <div class="col-25">
        <label for="fname">Select To Date</label>
      </div>
      <div class="col-75">
          <input type="text" name="todate" id="datepicker2" placeholder="To" />
      </div>
    </div>
       <div class="row">
      &nbsp;
    </div>
    <div class="row">
        <input type="submit" value=" Submit " name="submit" id="submit" <?php if(isset($teacherlist) && count($teacherlist)==0) { ?> onclick="javascript: alert('No teachers/trainers found! Try another course'); return false;" <?php } ?> />
    </div>
 
</div><input type="hidden" value="1" name="updatelog" id="updatelog" /></form>
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
                    window.location.href="customlogupdate.php?courseid="+this.getElementsByTagName("input")[0].value+'&coursename='+this.getElementsByTagName("input")[1].value;
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