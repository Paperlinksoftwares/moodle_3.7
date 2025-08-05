<?php
require_once 'config.php';
global $DB;


function checkReport($userid,$courseid)
{
	global $DB;

$sql = "SELECT uen.enrolid , en.courseid from {user_enrolments} as uen LEFT JOIN {enrol} as en ON en.id = uen.enrolid LEFT JOIN {course} as course ON course.id = en.courseid WHERE course.id = '".$courseid."' AND uen.userid = '".@$userid."' AND YEAR(FROM_UNIXTIME(course.startdate)) = '".@date('Y')."'"; 
$list_records = $DB->get_records_sql($sql); 

$row_assign_id=array();
foreach ($list_records as $key=>$val)
{
    $row1=$DB->get_records_sql("SELECT id FROM {assign} as ass WHERE ass.grade != '0' AND ass.course = '".$val->courseid."' AND ass.duedate!='0' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s') >= NOW() AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s')< NOW() + INTERVAL 7 DAY");    
    
    foreach($row1 as $m=>$n)
    {
        if($n->id!='')
        {
            $row_assign_id[$val->courseid][]=$n->id;
        }
    }
    unset($row1); 
}
$row_assign_id2=array();
foreach ($list_records as $key=>$val)
{
    $row2=$DB->get_records_sql("SELECT id FROM {assign} as ass WHERE ass.grade != '0' AND ass.course = '".$val->courseid."' AND ass.duedate!='0' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s') < NOW()");    
    
    foreach($row2 as $m=>$n)
    {
        if($n->id!='')
        {
            $row_assign_id2[$val->courseid][]=$n->id;
        }
    }
    unset($row2); 
}

$row_assign_id3=array();
foreach ($list_records as $key=>$val)
{
    $row3=$DB->get_records_sql("SELECT id FROM {assign} as ass WHERE ass.grade != '0' AND ass.course = '".$val->courseid."' AND ass.duedate!='0' AND FROM_UNIXTIME(ass.duedate,'%Y-%m-%d %h %i %s')< NOW() - INTERVAL 7 DAY");    
    
    foreach($row3 as $m=>$n)
    {
        if($n->id!='')
        {
            $row_assign_id3[$val->courseid][]=$n->id;
        }
    }
    unset($row3); 
}
$list_assign_id = array();
$open_modal = false;
foreach($row_assign_id as $key=>$arrval)
{
    foreach($arrval as $x=>$y)
    {
      //  $rows1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".$_GET['id']."'");
        $rows2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".$userid."' AND ass.status = 'submitted'");

       // if($rows1->count==0 || $rows2->count==0)
        //{
          //  $open_modal = true;
           // $list_assign_id[$key][]=$y;
        //}        
        if($rows2->count==0)
        {
            $open_modal = true;
            $list_assign_id[$key][]=$y;
        }
        else
        {
            //do nothing
        }
      //  unset($rows1);
        unset($rows2); 
    }
    
}

$list_assign_id2 = array();
$open_modal2 = false;

foreach($row_assign_id2 as $key=>$arrval)
{
    foreach($arrval as $x=>$y)
    {
     //   $rows1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".$_GET['id']."'");
        $rows2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".$userid."' AND ass.status = 'submitted'");

      //  if($rows1->count==0 && $rows2->count==0)
      //  {
          //  $open_modal2 = true;
          //  $list_assign_id2[$key][]=$y;
       // }        
        if($rows2->count==0)
        {
            $open_modal2 = true;
            $list_assign_id2[$key][]=$y;
        }
        else
        {
            //do nothing
        }
      //  unset($rows1);
        unset($rows2); 
    }
    
}
//echo '<pre>';
//print_r($list_assign_id2);
$list_assign_id3 = array();
$open_modal3 = false;
foreach($row_assign_id3 as $key=>$arrval)
{
    foreach($arrval as $x=>$y)
    {
        //$rows1 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".$_GET['id']."'");
        $rows2 = $DB->get_record_sql("SELECT count(ass.id) as count FROM {assign_submission} as ass WHERE ass.assignment = '".$y."' AND ass.userid='".$userid."' AND ass.status = 'submitted'");

       // if($rows1->count==0 || $rows2->count==0)
        //{
          //  $open_modal3 = true;
           // $list_assign_id3[$key][]=$y;
        //}        
        if($rows2->count==0)
        {
            $open_modal3 = true;
            $list_assign_id3[$key][]=$y;
        }
        else
        {
            //do nothing
        }
      //  unset($rows1);
        unset($rows2); 
    }
    
}



$returnarr=array();
$returnarr[0] = count($list_assign_id);
$returnarr[1] = count($list_assign_id2);
$returnarr[2] = count($list_assign_id3);
return $returnarr;
}
	


if(isset($_GET['id']))
{
    if(isset($_GET['coursename']) && $_GET['coursename']!='')
    {
        $courseid = $_GET['id'];
    }
    else
    {
        header("Location: assignment_submission_report.php");
        exit();
    }
}
else
{
    $courseid=1;
}
$role = $DB->get_record('role', array('shortname' => 'student'));
$context = get_context_instance(CONTEXT_COURSE, $courseid);
$students = get_role_users($role->id, $context);

$list_all_users = $DB->get_records_sql("SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE 1");
//echo '<pre>';
//print_r($list_all_users);
$studentsstr = '';
foreach($list_all_users as $key=>$val)
{
    $studentsstr = $studentsstr.'"'.$val->fullname."|".$val->id.'",';
}
$studentsstr = "[".$studentsstr."]";
?>
<html>    
    <head>
        <title>Submission Report Analysis - ACCIT</title>
        <link rel="stylesheet" media="all" type="text/css" href="https://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://accit.edu.mm/moodle/admin/tool/assesmentresults/templates/css/jquery.modal.min.css" />
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 86%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: center;
  padding: 8px;
  color: white;
}

tr:nth-child(even) {
  background-color: #dddddd;
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
  padding: 7px;
  cursor: pointer;
  background-color: #fff; 
  width: 449px;
  border-bottom: 1px solid #d4d4d4; 
  background-color: #b3f1af;
  word-break: break-word;
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
.blink_text {

    animation:1s blinker linear infinite;
    -webkit-animation:1s blinker linear infinite;
    -moz-animation:1s blinker linear infinite;

     color: red;
    }

    @-moz-keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }

    @-webkit-keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }

    @keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }
	 .blink_text2 {

    animation:1s blinker linear infinite;
    -webkit-animation:1s blinker linear infinite;
    -moz-animation:1s blinker linear infinite;

     color: green;
    }
	.blink_text3 {

    animation:1s blinker linear infinite;
    -webkit-animation:1s blinker linear infinite;
    -moz-animation:1s blinker linear infinite;

     color: black;
    }
	
	.blink_text4 {

   

     color: black;
    }

    @-moz-keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }

    @-webkit-keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }

    @keyframes blinker {  
     0% { opacity: 1.0; }
     50% { opacity: 0.0; }
     100% { opacity: 1.0; }
     }
</style>      
    </head>
    <center>
	<!-- <audio controls>
	<source src="song.mp3" type="audio/mpeg">
	
	Your browser does not support the audio tag.
</audio> -->

        <body>
            <br><br> 
                <?php
            echo '<form autocomplete="off" action="http://localhost/accit-moodle/accit/assignment_submission_report.php" method="get"><div class="autocomplete" style="width:452px;">
    <input size="50" id="coursename" type="text" name="coursename" placeholder="Type Course" value="" >
 <input id="id" type="hidden" name="id" value="<?php echo @$agent_id; ?>" /><br/><br/>
 &nbsp;<input style="height: 30px; width: 309px; font-size: 14px; background-color:#7774d3; color: #fff; border: 1px solid #000;" type="submit" name="search_course" id="search_course" value=" View Enrolled Students & Submission Status" />
        </div></form>';
            
            if(isset($_GET['id']))
            {
            echo '<p style="font-weight:bold;color:green;">Total '.count($students).' Students Found under <span style="font-weight:bold;color:black;">'.$_GET['coursename'].'</span></p>';
            echo '<br>';
            }
			if(count($students)>0)
			{
			echo '<table><tr style="background-color: #047c26;"><td>SL No.</td><td>Name</td><td>Email</td><td>Submission Status</td><td>Student Details</td></tr>';
			$cnt=1;
			
            foreach($students as $key=>$val)
            { 
				$stat = checkReport($val->id,$_GET['id']);
				//echo '<pre>';
				//print_r($stat);
				$statval1 = $stat[0];
				$statval2 = $stat[1];
				$statval3 = $stat[2];
				if($statval3>0)
				{
					$class = "blink_text";
				}
				else if($statval1>0)
				{
					$class = "blink_text2";
				}
				
				else
				{
					$class = "blink_text4";
				}
				
                echo "<tr><td><div style='color: black!important;'>".$cnt."</div></td><td><div class='".$class."'><strong>".$val->firstname." ".$val->lastname."</strong></div></td><td> [ <a href='mailto: ".$val->email."'>".$val->email."</a> ]</td><td>"; ?>
				
			<?php if($statval1>0 || $statval3>0) { ?>	<input style="height: 30px; width: 76px; font-size: 14px; background-color:#7774d3; color: #fff; border: 1px solid #000;" type="button" name="show" id="show" value=" VIEW " 
				onclick="javascript: window.open('indexother.php?id=<?php echo $val->id; ?>&courseid=<?php echo $_GET['id']; ?>','assignmentreport','width=900,height=999,scrollbar=yes');" /> 
			<?php            }  else { echo '<div class="blink_text4"><strong>NA</strong></div>'; }  echo '</td><td>'; ?><input style="height: 30px; width: 76px; font-size: 14px; background-color:#7774d3; color: #fff; border: 1px solid #000;" type="button" name="show" id="show" value=" VIEW " 
				onclick="javascript: window.open('http://localhost/accit-moodle/accit/user/profile.php?id=<?php echo $val->id; ?>','_blank')"; /> </td><?php echo '</td></tr>';      
                
				unset($stat);
				unset($statval1);
				unset($statval2);
				unset($statval3);
				unset($class);
				$cnt++;
            }
			echo '</table><br><br><br><br>'; } 
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
                    document.getElementById("id").value = this.getElementsByTagName("input")[0].value; 
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
var coursename = '';
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
var final_arr_stu = <?php echo $studentsstr; ?>; 
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
autocomplete(document.getElementById("coursename"), final_arr_stu,'2');
</script>
        </body>
    </center>
</html>
