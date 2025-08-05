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

$res = $conn->query("SELECT * FROM `mdl_qualification_booking` WHERE `status` = '1'");


?>
<!DOCTYPE html>
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
</head>
<body>
	
	<div class="limiter">
		<div class="container-table100">
			<div class="wrap-table100">
					<div class="table">

						<div class="row header">
							<div class="cell">
								Qualification Name
							</div>
								<div class="cell">
								
							</div>
							
						</div>
<?php
while ($row = $res->fetch_array())
    {
		$id_show = $row['id'];
       ?>
						<div class="row">
							<div class="cell" data-title="Full Name">
								<?php echo $row['qualification']; ?>
							</div>
							<div class="cell" data-title="Age">
								<input onclick="javascript: window.location.href='selectunit.php?id=<?php echo $id_show; ?>';" class="btn" type="button" name="sel" id="sel" value="Select & Continue" />
							</div>
							
			</div>
	<?php unset($id_show); } 
	
	$conn->close();
	
	?>

				</div>
			</div>
		</div>
	</div>


	

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
<?php } else { header("Location: https://moodle.accit.nsw.edu.au"); exit(); } ?>