<?php
session_start();
unset($_SESSION);
error_reporting(0);
include("config.php");

if(isset($_GET['del_id']) && $_GET['del_id']>0)
{
	$sql_user_delete = "DELETE FROM `mdl_users_restricted` WHERE `user_id` = '".$_GET['del_id']."'";
	$del_res = mysqli_query($connection,$sql_user_delete);
	if($del_res)
	{
		header('Location: setting.php');
		exit();

	}
}

if(isset($_POST['form_submit']) && $_POST['form_submit']==1)
{
    $student_id=$_POST['studentid'];
	$restriction_type=$_POST['restriction_type'];
	$sql_check = "SELECT count(`id`) as total FROM `mdl_users_restricted` WHERE `user_id` = '".$student_id."'"; 
	$check_res = mysqli_query($connection,$sql_check);
	$check_row = mysqli_fetch_assoc($check_res);
	if($check_row['total']==0)
	{
		$sql_ins = "INSERT INTO `mdl_users_restricted` (`id`, `user_id`, `type_of_restriction`, `date`) 
		VALUES (NULL, '".$student_id."', '".$restriction_type."', '".@date('Y-m-d h:i:s')."')";
		$res_ins = mysqli_query($connection,$sql_ins);
		?>
		<script>
		alert('Successfully added to the restricted list!');
		window.location.href='setting.php';
		</script>
		<?php
	}
	else
	{
		?>
		<script>
		alert('Already added to the restricted list!');
		window.location.href='setting.php';
		</script>
		<?php
	}
	
}

$limit = 12;  // Number of entries to show in a page. 
// Look for a GET variable page if not found default is 1.   
if (isset($_GET["page"])) {  
    $pn  = $_GET["page"];  
}  
else {  
    $pn=1;  
};   
   
$start_from = ($pn-1) * $limit; 

$sql_trainers_list = "SELECT DISTINCT u.id AS userid, u.firstname as fname , u.lastname as lname 
FROM mdl_user u
JOIN mdl_user_enrolments ue ON ue.userid = u.id
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
JOIN mdl_role r ON r.id = ra.roleid WHERE r.shortname = 'student'";
$trainers_list_res = mysqli_query($connection,$sql_trainers_list);


$arr_check_tr=array();

$st = '';
$ct = mysqli_num_rows($trainers_list_res);
$cc = 0;

while($trainers_list = mysqli_fetch_assoc($trainers_list_res))
{
	
	if(($ct-$cc)>1)
	{
	$st = $st . '"'.$trainers_list['fname'].' '.$trainers_list['lname'].'|'.$trainers_list['userid'].'",';
	}
	else 
	{
		$st = $st . '"'.$trainers_list['fname'].' '.$trainers_list['lname'].'|'.$trainers_list['userid'].'"';
	}
	$cc++;
}
	
$stnew = "[".$st."]"; 


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ACCIT | Tracker Data</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>

<style>
BODY, TD {
	font-family:Arial, Helvetica, sans-serif;
	font-size:13px;
	margin-left: 40px;
    margin-right: 40px;
    margin-top: 35px;
}
</style>
<style>
    .button {
    display: inline-block;
    text-align: center;
    vertical-align: middle;
    padding: 9px 20px;
    border: 1px solid #2c4a7e;
    border-radius: 8px;
    background: #538bed;
    background: -webkit-gradient(linear, left top, left bottom, from(#538bed), to(#2c4a7e));
    background: -moz-linear-gradient(top, #538bed, #2c4a7e);
    background: linear-gradient(to bottom, #538bed, #2c4a7e);
    -webkit-box-shadow: #64a7ff 0px 0px 40px 0px;
    -moz-box-shadow: #64a7ff 0px 0px 40px 0px;
    box-shadow: #64a7ff 0px 0px 40px 0px;
    text-shadow: #1a2c4a 1px 1px 1px;
    font: normal normal bold 17px arial;
    color: #ffffff;
    text-decoration: none;
}
.button:hover,
.button:focus {
    border: 1px solid #345794;
    background: #64a7ff;
    background: -webkit-gradient(linear, left top, left bottom, from(#64a7ff), to(#355997));
    background: -moz-linear-gradient(top, #64a7ff, #355997);
    background: linear-gradient(to bottom, #64a7ff, #355997);
    color: #ffffff;
    text-decoration: none;
}
.button:active {
    background: #2c4a7e;
    background: -webkit-gradient(linear, left top, left bottom, from(#2c4a7e), to(#2c4a7e));
    background: -moz-linear-gradient(top, #2c4a7e, #2c4a7e);
    background: linear-gradient(to bottom, #2c4a7e, #2c4a7e);
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
  padding: 10px;
  cursor: pointer;
  background-color: #cef9dd; 
  border-bottom: 1px solid #d4d4d4; 
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #fff; 
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important; 
  color: #ffffff; 
}


</style>
</head>


<body>
    <br/>
     <br/>
<form id="form1" name="form1" method="post" action="setting.php" autocomplete="off">

			<label>Name:</label>
<div class="autocomplete" style="width:310px;">
   <input style="width:310px;" id="studentname" type="text" name="studentname" placeholder="Type your Student's Name" value="" />
 <input id="studentid" type="hidden" name="studentid" value="" />
            </div>&nbsp;
			<label>&nbsp;&nbsp;</label>
			<label>Restriction Type:</label>
			<label>
			<select name="restriction_type" id="restriction_type">
			<option value="1">Notified of Restriction</option>
			<option value="2">Fully Restricted</option>
			</select>
			</label>
			<label>&nbsp;&nbsp;
<input type="submit" name="button" id="button" value=" Restrict The Access " /></label>
  <input type="button" name="reset" id="reset" value=" Show All " onclick="javascript: window.location.href='setting.php?showall=1';" />
  <input type="hidden" name="form_submit" id="form_submit" value="1" />
</form>
<br /><br />

<?php
$sql_begin = "SELECT d.id , d.type_of_restriction , d.date , u.firstname, u.lastname , u.email, u.id as userid FROM `mdl_users_restricted` 
as d LEFT JOIN `mdl_user` as u ON d.user_id = u.id";
if((isset($_GET['showall']) && $_GET['showall']==1))
{
    $sql = $sql_begin." WHERE d.id>0";
    unset($_SESSION['sql']);
    $_SESSION['sql']=$sql;
}
if(isset($_SESSION['sql']) && $_SESSION['sql']!='')
{
    $sql = $_SESSION['sql']." GROUP BY d.id ";
}
else
{
    $sql = $sql_begin." GROUP BY d.id ";
}
$_SESSION['sql']=$sql;
$sql_param =base64_encode($sql);
$res_total = mysqli_query($connection,$sql);
$total = mysqli_num_rows($res_total);
$pagetotal = ceil($total/$limit);
$sql= $sql." LIMIT ".$start_from." , ".$limit; 
$sql_result = mysqli_query ($connection ,$sql ) or die ('request "Could not execute SQL query" '.$sql);
?>
<br/>
<div>Total <?php echo $total; ?> Records found! 
<?php if($total>0) { ?><a href="download.php?sql_param=<?php echo $sql_param; ?>" style="background-color: green;
  color: white;
  padding: 10px;
  text-decoration: none;
  text-transform: uppercase; height:20px; margin-left:15px;"><strong>DOWNLOAD EXCEL</strong></a><?php } ?></div><br/><br/>
<table width="100%" border="1" cellspacing="0" cellpadding="4">
  <tr>
    <td bgcolor="#CCCCCC"><strong>SL No.</strong></td>
    <td bgcolor="#CCCCCC"><strong>Name | Email</strong></td>
	<td bgcolor="#CCCCCC"><strong>Type of Restriction</strong></td>  
    <td bgcolor="#CCCCCC"><strong>Date when Restricted</strong></td>
	 <td bgcolor="#CCCCCC"><strong>Action</strong></td>
  </tr>
  <?php

if (mysqli_num_rows($sql_result)>0) {
	$cc=0;
	while ($row = mysqli_fetch_assoc($sql_result)) {
	  
$u_id = $row["userid"];
$cc++;

?>
  <tr>
      <td><?php echo $cc; ?></td>
   <td><?php echo $row["firstname"]." ".$row["lastname"]." | ".$row["email"]; ?></td>
   <td><?php if($row["type_of_restriction"]==1) { echo 'Notified of Restriction'; } else { echo 'Fully Restricted'; }; ?></td>
   
    <td><?php echo @date("l jS \of F Y h:i A", strtotime($row["date"])); ?></td>
	<td><input type="button" name="del" id="del" onclick="javascript: 
	var p = confirm('Are you sure you want to remove <?php echo $row["firstname"]." ".$row["lastname"]; ?>?');
	if(p==true)
	{
	window.location.href='setting.php?del_id=<?php echo $u_id; ?>';
	}
	else
	{
		return false;
	}" value="Remove" /></td>
      </tr>
<?php

unset($result_count);
unset($row_count);
unset($count);
unset($u_id);
	}
} else {
?>
<tr><td colspan="6">Sorry! No results found.</td>
<?php	
}
?>
</table><br/>
<?php if (mysqli_num_rows($sql_result)>0) { ?>
<table><tr><td colspan="3"></td></tr></td></tr><tr><td><?php if($pn<$pagetotal) { ?><a href="setting.php?page=<?php echo ($pn+1); ?>">NEXT</a><?php } ?></td><td></td><td><?php if($pn>1) { ?><a href="setting.php?page=<?php echo ($pn-1); ?>">PREVIOUS</a><?php } ?></td></tr></table>
<?php } ?>


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
                    document.getElementById("stuid").value = this.getElementsByTagName("input")[0].value; 
                }
                else if(type==2)
                {
                    inp.value = this.getElementsByTagName("input")[1].value; 
                    document.getElementById("studentid").value = this.getElementsByTagName("input")[0].value; 
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
//var final_arr = {{{coursestr}}}; 
var final_arr_stu = <?php echo $stnew; ?> ; 
; 
//var course_arr = ["Afgha sdewwew 456456 nistan-20","Albania-21","Malaysia-33",];
//var arr = '';
//{{#course_arr}}
//var arr = arr+'"{{{coursename}}}*{{{courseid}}}"'+",";
//{{/course_arr}}
//var final_arr = "["+arr+"]";
//alert(final_arr);
//autocomplete(document.getElementById("coursename"), final_arr,'1');
autocomplete(document.getElementById("studentname"), final_arr_stu,'2');
</script>
<script>
	$(function() {
		var dates = $( "#from, #to" ).datepicker({
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 2,
			dateFormat: 'yy-mm-dd',
			onSelect: function( selectedDate ) {
				var option = this.id == "from" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	});
	</script>
</body>
</html>