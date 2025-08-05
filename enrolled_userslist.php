<?php
require_once 'config.php';
global $DB;
if(isset($_POST['search_unit']))
{	
	$sql1 = '';
	$sql2= '';
	//echo '<pre>';
	//print_r($_POST['year']);
	$cn = count($_POST['year']);
	$cm = count($_POST['term']);
	$c1=0;
	$c2=0;
	foreach($_POST['year'] as $h=>$r)
	{
		if($r>0)
		{
			if(($cn-$c1)!=1)
			{
				$sql1 = $sql1." c.fullname LIKE '%".$r."%' OR ";
			}
			else
			{
				$sql1 = $sql1." c.fullname LIKE '%".$r."%' ";
			}
		}
		$c1++;
	}
	foreach($_POST['term'] as $i=>$k)
	{
		if($k>0)
		{
			if(($cm-$c2)!=1)
			{
				$sql2 = $sql2."  c.fullname LIKE '%Term ".$k."%' OR c.fullname LIKE '%T".$k."/%'  OR ";
			}
			else
			{
				$sql2 = $sql2."  c.fullname LIKE '%Term ".$k."%' OR c.fullname LIKE '%T".$k."/%'  ";
			}
		}
		$c2++;
	}
	if($sql1!='' && $sql2!='')
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE ".$sql1." AND ( ".$sql2." ) AND c.fullname !='' ";
	}
	else if($sql1!='' && $sql2=='')
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE ".$sql1." AND c.fullname !='' ";
	}
	else if($sql1=='' && $sql2!='')
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE ".$sql2." AND c.fullname !='' ";
	}
	else
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE c.fullname !='' ";
	}
	//echo $sql_final; 
	//die;
	
	$list_all_users = $DB->get_records_sql($sql_final);
	//echo '<pre>';
	//print_r($list_all_users);
	$studentsstr = '';
	foreach($list_all_users as $key=>$val)
	{
		$studentsstr = $studentsstr.'"'.$val->fullname."|".$val->id.'",';
	}
	$studentsstr = "[".$studentsstr."]";
}
if(isset($_POST['id']))
{
	
	
	$sql1 = '';
	$sql2= '';
	//echo '<pre>';
	//print_r($_POST['year']);
	$cn = count($_POST['year']);
	$cm = count($_POST['term']);
	$c1=0;
	$c2=0;
	foreach($_POST['year'] as $h=>$r)
	{
		if($r>0)
		{
			if(($cn-$c1)!=1)
			{
				$sql1 = $sql1." c.fullname LIKE '%".$r."%' OR ";
			}
			else
			{
				$sql1 = $sql1." c.fullname LIKE '%".$r."%' ";
			}
		}
		$c1++;
	}
	foreach($_POST['term'] as $i=>$k)
	{
		if($k>0)
		{
			if(($cm-$c2)!=1)
			{
				$sql2 = $sql2."  c.fullname LIKE '%Term ".$k."%' OR c.fullname LIKE '%T".$k."%'  OR ";
			}
			else
			{
				$sql2 = $sql2."  c.fullname LIKE '%Term ".$k."%' OR c.fullname LIKE '%T".$k."%'  ";
			}
		}
		$c2++;
	}
	if($sql1!='' && $sql2!='')
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE ".$sql1." AND ( ".$sql2." ) AND c.fullname !='' ";
	}
	else if($sql1!='' && $sql2=='')
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE ".$sql1." AND c.fullname !='' ";
	}
	else if($sql1=='' && $sql2!='')
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE ".$sql2." AND c.fullname !='' ";
	}
	else
	{
		$sql_final = "SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE c.fullname !='' ";
	}
	//echo $sql_final; 
//die;
	
	$list_all_users = $DB->get_records_sql($sql_final);
	//echo '<pre>';
	//print_r($list_all_users);
	$studentsstr = '';
	foreach($list_all_users as $key=>$val)
	{
		$studentsstr = $studentsstr.'"'.$val->fullname."|".$val->id.'",';
	}
	$studentsstr = "[".$studentsstr."]";
	
	
	
	
	
	
	
	
	
    if(isset($_POST['coursename']) && $_POST['coursename']!='')
    {
        $courseid = $_POST['id'];
    }
    else
    {
        header("Location: enrolled_userslist.php");
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
//echo "SELECT c.id, c.shortname, c.fullname , c.idnumber  
//FROM mdl_course c WHERE c.fullname LIKE '%term ".$_SESSION['term'].", ".$_SESSION['year']."%' OR c.fullname LIKE 
//'%T".$_SESSION['term']."/".$_SESSION['year']."%'";


/*$list_all_users = $DB->get_records_sql("SELECT c.id, c.shortname, c.fullname , c.idnumber  
FROM mdl_course c WHERE c.fullname LIKE '%term ".$_SESSION['term'].", ".$_SESSION['year']."%' OR c.fullname LIKE 
'%T".$_SESSION['term']."/".$_SESSION['year']."%'");
//echo '<pre>';
//print_r($list_all_users);
$studentsstr = '';
foreach($list_all_users as $key=>$val)
{
    $studentsstr = $studentsstr.'"'.$val->fullname."|".$val->id.'",';
}
$studentsstr = "[".$studentsstr."]"; */
?>
<html>    
    <head>
        <title>Enrolled Students List per Course - ACCIT:Moodle</title>
        <link rel="stylesheet" media="all" type="text/css" href="https://code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://accit.edu.mm/moodle/admin/tool/assesmentresults/templates/css/jquery.modal.min.css" />
<style>
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
  left: 0;
  right: 0;
}
.autocomplete-items div {
  padding: 7px;
  cursor: pointer;
  background-color: #fff; 

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
#rcorners4 {
  border-radius: 16px;
  background: #90d19c;
  padding: 20px; 
  width: 200px;
  height: 150px; 
} 
</style>      
    </head>
    <center>
        <body>
            <br>
			<h1>ACCIT :: Course unit search and viewing all enrolled students</h1><br/>
                <?php
            ?>
			<form autocomplete="off" action="http://localhost/accit-moodle/accit/enrolled_userslist.php" method="post">
			<table style="width: 100%;" cellspacing="5" cellpadding="5" border="0" id="rcorners4" >
	<tr><td>Year:</td><td>
	<select name="year[]" id="year[]"  multiple  style="width: 400px; height: 122px;">
	<option value="">Select None</option>
	<option value="2021" <?php if(in_array('2021',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2021</option>
	<option value="2020" <?php if(in_array('2020',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2020</option>
	<option value="2019" <?php if(in_array('2019',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2019</option>
	<option value="2018" <?php if(in_array('2018',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2018</option>
	<option value="2017" <?php if(in_array('2017',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2017</option>
	<option value="2016" <?php if(in_array('2016',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2016</option>
	<option value="2015" <?php if(in_array('2015',@$_POST['year'])==true) { ?> selected="selected" <?php } ?>>2015</option>
	</select><br/>
	<font style="color: blue;">* <i>press and hold CTRL and then click each value for multiple selection</i></font></td></tr>
	
	<tr><td>Term:</td><td>
	<select name="term[]" id="term[]" multiple  style="width: 400px; height: 91px;">
	<option value="">Select None</option>
	<option value="1" <?php if(in_array('1',@$_POST['term'])==true) { ?> selected="selected" <?php } ?>>1</option>
	<option value="2" <?php if(in_array('2',@$_POST['term'])==true) { ?> selected="selected" <?php } ?>>2</option>
	<option value="3" <?php if(in_array('3',@$_POST['term'])==true) { ?> selected="selected" <?php } ?>>3</option>
	<option value="4" <?php if(in_array('4',@$_POST['term'])==true) { ?> selected="selected" <?php } ?>>4</option>
	
	</select><br/><font style="color: blue;">* <i>press and hold CTRL and then click each value for multiple selection</i></font></td></tr>
	
	<tr><td colspan="2" align="center"><br/><input style="height: 30px; width: 191px; font-size: 14px; background-color:#7774d3; color: #fff; border: 1px solid #000;" type="submit" name="search_course" id="search_course" value=" Search Courses / Units " />
        </td></tr></table>
		<input type="hidden" name="search_unit" id="search_unit" value="1" />
	</form>
			<form autocomplete="off" action="http://localhost/accit-moodle/accit/enrolled_userslist.php" method="POST"><div class="autocomplete" style="width:600px;">
   <table style="width: 100%;" cellspacing="5" cellpadding="5" border="0"> <tr><td>Unit Name</td><td><input size="50" id="coursename" type="text" name="coursename" placeholder="Type Course" value="" ></td></tr></table>
	
 <input id="id" type="hidden" name="id" value="<?php echo @$agent_id; ?>" /><br/><br/>
 &nbsp;<input style="height: 30px; width: 191px; font-size: 14px; background-color:#7774d3; color: #fff; border: 1px solid #000;" type="submit" name="search_course" id="search_course" value=" View All Enrolled Students " />
        </div>
		<input type="hidden" name="year" id="year" value="<?php echo $_POST['year']; ?>" />
		<input type="hidden" name="term" id="termm" value="<?php echo $_POST['term']; ?>" />
		</form>';
         <?php   
            if(isset($_POST['id']))
            {
            echo '<p style="font-weight:bold;color:green;">Total '.count($students).' Students Found under</p> <p style="font-weight:bold;color:black;"><a href="http://localhost/accit-moodle/accit/course/view.php?id='.$_POST['id'].'" target="_blank">'.$_POST['coursename'].'</a></p>';
            echo '<br>';
            }
            foreach($students as $key=>$val)
            {       
                echo $val->firstname." ".$val->lastname." [ <a href='mailto: ".$val->email."'>".$val->email."</a> ] <a href='http://localhost/accit-moodle/accit/user/profile.php?id=".$val->id."' target='_blank'><strong>view details</strong></a>";
                echo '<br>';      
                echo '<hr>';
            }
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
coursename_for_search = coursename.toLowerCase();
var pos = coursename_for_search.search(val.toLowerCase());
        /*check if the item starts with the same letters as the text field value:*/
       // if (coursename.substr(0, val.length).toUpperCase() == val.toUpperCase()) { 
		if (coursename_for_search.search(val.toLowerCase())>0) { 
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          //b.innerHTML = "<strong>" + coursename.substr(0, val.length) + "</strong>";
		  //b.innerHTML = "<strong>" + coursename.substr(0, val.length) + "</strong>";
          b.innerHTML += coursename;
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
