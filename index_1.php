<?php if(isset($_GET['current_term'])) { $current_term = $_GET['current_term']; } else { $current_term = 1; }
if(isset($_GET['user_id'])) { $user_id = $_GET['user_id']; } else { $user_id = 1; } ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

	<title>Access Restricted | ACCIT Moodle</title>

	<!-- Google font -->
	<link href="https://fonts.googleapis.com/css?family=Maven+Pro:400,900" rel="stylesheet">

	<!-- Custom stlylesheet -->
	<link type="text/css" rel="stylesheet" href="css/style.css" />

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
<style>
.btn {
  background: #3498db;
  background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
  background-image: -moz-linear-gradient(top, #3498db, #2980b9);
  background-image: -ms-linear-gradient(top, #3498db, #2980b9);
  background-image: -o-linear-gradient(top, #3498db, #2980b9);
  background-image: linear-gradient(to bottom, #3498db, #2980b9);
  -webkit-border-radius: 28;
  -moz-border-radius: 28;
  border-radius: 28px;
  -webkit-box-shadow: 0px 0px 0px #666666;
  -moz-box-shadow: 0px 0px 0px #666666;
  box-shadow: 0px 0px 0px #666666;
  font-family: Arial;
  color: #ffffff;
  font-size: 19px;
  padding: 10px 20px 10px 20px;
  text-decoration: none;
}

</style>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

<script>
$(document).ready(function(){
	var $ = jQuery.noConflict();
  
   
    
	
	$.ajax({
                url: "http://localhost/accit-moodle/accit/nonpayment_account_restricted_view.php?user_id=<?php echo $user_id; ?>&year=<?php echo @date('Y'); ?>&term=<?php echo $current_term; ?>",
                type: "GET",
                cache: false,
                success: function(d) {
                   // alert(d);
                }
            });
	
	
	
	

});
</script>
</head>

<body>

	<div id="notfound">
		<div class="notfound">
			<div class="notfound-404">
				<h1>404</h1>
			</div>
			<h2>Access Restricted!!!</h2>
			<p>You access has been restricted due to the non-payment of fees.

Please get in touch with the admin department to inquire about your dues. Email at <a href="mailto: admin@accit.nsw.edu.au">admin@accit.nsw.edu.au</a> or 
call us at (+ 61 2) 9261 3009</p>

<p>If you have already made payment, please get back to us at <a href="mailto: admin@accit.nsw.edu.au">admin@accit.nsw.edu.au</a>  or call us at (+ 61 2) 9261 3009</p>
<p>&nbsp;</p>
		<p>	<a href="indextemp.php" class="btn">Continue to Moodle</a> </p>
		</div>
	</div>

</body>

</html>
