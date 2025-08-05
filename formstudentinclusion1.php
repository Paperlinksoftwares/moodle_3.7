<?php
require_once('config.php');
include_once('customfunctions.php');
global $DB;
global $CFG;

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
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
  
    
    <div class="row">
      <div class="col-25">
        <label for="year">Select Year</label>
      </div>
     
        <div class="col-75">
          <select name="year" id="year">
		  <?php for($i=@date("Y");$i>2018;$i--) { ?>
		  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
		  <?php } ?>
		  </select>
		  </div>
    </div>
	 <div class="row">
      <div class="col-25">
        <label for="term">Select Term</label>
      </div>
     
        <div class="col-75">
          <select name="term" id="term">
		  <?php for($k=1;$k<5;$k++) { ?>
		  <option value="<?php echo $k; ?>">Term - <?php echo $k; ?></option>
		  <?php } ?>
		  </select>
		  </div>
    </div>
       <div class="row">
      <div class="col-25">
        <label for="year">Select Form</label>
      </div>
     
        <div class="col-75">
          <select name="form_type" id="form_type">
		  
		  <option value="1">Enrolment Form</option>
		   <option value="2">Feedback Form</option>
		  
		  </select>
		  </div>
    </div>
      
       <div class="row">
      &nbsp;
    </div>
    <div class="row">
        <input type="submit" value=" Submit " name="submit" id="submit" />
    </div>
 
</div><input type="hidden" value="1" name="searchstudent" id="searchstudent" />
</form>
    </center>
    </body>
</html>