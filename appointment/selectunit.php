<!DOCTYPE html>
<?php
require_once('../config.php');
global $USER;
if($USER->id>0) {
$servername = "192.168.1.12";
$username = "moodle_new2019";
$password = "fa6MNExR4KehLj1A";
$db = "moodle";

// Create connection
$conn = new mysqli($servername, $username, $password,$db);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if(isset($_REQUEST['id']) && $_REQUEST['id']>0)
{
	$id=$_REQUEST['id'];
	

$res = $conn->query("SELECT * FROM `mdl_courseunit_booking` WHERE `status` = '1' AND `qualification_id` = '".$id."'");



if(isset($_POST['submitform']))
{
	$servername2 = "192.168.1.12";
$username2 = "appointment";
$password2 = "Accit@123";
$db2 = "accit_appointment";

// Create connection
$conn2 = new mysqli($servername2, $username2, $password2,$db2);

// Check connection
if ($conn2->connect_error) {
  die("Connection failed: " . $conn2->connect_error);
}
	$select1='';
	$select2='';
	$select3='';
	if(count($_POST['select1'])>0)
	{
		foreach($_POST['select1'] as $key=>$val)
		{
			$select1 = $select1.$val.",";
		}
		
	}
	if(count($_POST['select2'])>0)
	{
		foreach($_POST['select2'] as $key=>$val)
		{
			$select2 = $select2.$val.",";
		}
	}
	if(count($_POST['select3'])>0)
	{
		foreach($_POST['select3'] as $key=>$val)
		{
			$select3 = $select3.$val.",";
		}
	}
	$resinsert = $conn->query("INSERT INTO `mdl_booking_unit_request` (`id`, `user_id`, `unit_request`, `date`) VALUES (NULL, '".$_POST['userid']."', '".$select1."|".$select1."|".$select3."', '".@date('Y-m-d h:i:s')."');
");
$resinsert2 = $conn2->query("INSERT INTO `mdl_booking_unit_request` (`id`, `user_id`, `unit_request`, `date`) VALUES (NULL, '".$_POST['userid']."', '".$select1."|".$select1."|".$select3."', '".@date('Y-m-d h:i:s')."');
");
	if($resinsert && $resinsert2)
	{
		header("Location: http://localhost/accit-moodle/accit/appointment/index.php/appointments/index?userid=".$USER->id);
		exit();
	}
}

?>

<html lang="en">
<head>
	<title>ACCIT | Appointment Scheduler</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="custom/images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="custom/vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="custom/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="custom/vendor/animate/animate.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="custom/vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="custom/vendor/perfect-scrollbar/perfect-scrollbar.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="custom/css/util.css">
	<link rel="stylesheet" type="text/css" href="custom/css/main.css">
<!--===============================================================================================-->
<style>
.btn {
height: 18px;
width: 18px;
border: 1px solid #aaa;
  padding: 11px 15px 11px 15px;
  text-decoration: none;
  margin-left: 52px;
}


.sub {
  background: #3498db;
  background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
  background-image: -moz-linear-gradient(top, #3498db, #2980b9);
  background-image: -ms-linear-gradient(top, #3498db, #2980b9);
  background-image: -o-linear-gradient(top, #3498db, #2980b9);
  background-image: linear-gradient(to bottom, #3498db, #2980b9);
  -webkit-border-radius: 11;
  -moz-border-radius: 11;
  border-radius: 11px;
  font-family: Arial;
  color: #ffffff;
  font-size: 17px;
  padding: 11px 15px 11px 15px;
  text-decoration: none;
}

.sub:hover {
  background: #3cb0fd;
  background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
  background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
  background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
  text-decoration: none;
}
</style>
</head>
<body>
<form action="selectunit.php?id=<?php echo $id; ?>" name="f1" id="f1" method="post" >
	<div class="limiter">
		<div class="container-table100">
			<div class="wrap-table100">
			
	   <div class="table">

						<div class="row header">
							<div class="cell">
								Course Unit Name
							</div>
							
								<div class="cell">
								Assessment 1
							</div>
							<div class="cell">
								Assessment 2
							</div>
							<?php if($id==8) { ?>
							<div class="cell">
								Assessment 3
							</div>
							<?php } ?>
						</div>
<?php
while ($row = $res->fetch_array())
    {
		$id_show = $row['id'];
       ?>
	   
						
						<div class="row">
							<div class="cell" data-title="Full Name">
								<?php echo $row['unit_name']; ?>
							</div>
							<div class="cell" data-title="Age">
								<input class="btn" type="checkbox" name="select1[]" id="select1[]" value="<?php echo $row['unit_name']."-Assessment 1"; ?>" />
							</div>
							<div class="cell" data-title="Age">
								<input class="btn" type="checkbox" name="select2[]" id="select2[]" value="<?php echo $row['unit_name']."-Assessment 2"; ?>" />
							</div>
							<?php if($id==8) { ?>
							<div class="cell" data-title="Age">
								<input class="btn" type="checkbox" name="select3[]" id="select3[]" value="<?php echo $row['unit_name']."-Assessment 3"; ?>" />
							</div>
							<?php } ?>
						</div>
						
					<?php unset($id_show); } 
	
	$conn->close();
	
	?>
	

				</div>
				
	   
	 	<input type="submit" name="submit" id="submit" value="Submit" class="sub" />
	
				
			</div>
		</div>
	</div>
	<input type="hidden" name="submitform" id="submitform" value="1" />

	<input type="hidden" name="userid" id="userid" value="<?php echo $USER->id; ?>" />
</form>

	

<!--===============================================================================================-->	
	<script src="custom/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="custom/vendor/bootstrap/js/popper.js"></script>
	<script src="custom/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="custom/js/main.js"></script>

</body>
</html>
<?php 
}
else
{
	header('Location: selectqualification.php');
	exit();
}
 } else { header("Location: https://moodle.accit.nsw.edu.au"); exit(); } ?>
