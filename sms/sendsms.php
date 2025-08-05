<?php
session_start();
ini_set('display_errors', '1');
function SendMessage($host, $port, $userName, $password, $number, $message)
{

    /* Create the HTTP API query string */
    $query = 'http://'.$host.':'.$port;
    $query .= '/http/send-message/';
    $query .= '?username='.urlencode($userName);
    $query .= '&password='.urlencode($password);
    $query .= '&to='.urlencode($number);
    $query .= '&message='.urlencode($message);
      
    /* Send the HTTP API request and return the response */
    return file_get_contents($query);  
}
 
if(isset($_GET['msg']))
{
    $msg= base64_decode($_GET['msg']);
}

if(isset($_GET['phone']) && isset($_GET['name']) && $_GET['phone']!='')
{
    $phone = $_GET['phone'];
    $name = $_GET['name'];
}
if(isset($_GET['phone']) && $_GET['phone']=='')
{
    ?>
	<script>
	alert('Invalid phone number!');
    window.location.href="http://localhost/accit-moodle/accit/grade/report/overview/studentgrades.php#";
</script>
	<?php
}


// Process the form, if it was submitted
 if (isset($_POST['phone']) && $_POST['phone']!='') {
      
	    require_once dirname(__FILE__) . '/securimage.php';
           $securimage = new Securimage();
        $number = '+'.trim($_POST['phone']); 
        //$number = "+919874366355";
        $message = @$_POST['ct_message']; // the message from the form
        $captcha = @$_POST['ct_captcha']; // the user's entry for the captcha code
        $name    = @$_POST['name'];  // limit name to 64 characters

        $_SESSION['ct_message'] = $message;

       
            if (strlen($message) == 0) {
                // message length too short
                $msg = base64_encode('Please enter a message');
                ?>
<script>
    window.location.href="sendsms.php?phone=<?php echo $number; ?>&name=<?php echo $name; ?>&msg=<?php echo $msg; ?>&e=1";
</script>
<?php
            }
        

        // Only try to validate the captcha if the form has no errors
        // This is especially important for ajax calls
        
          

       else if ($securimage->check($captcha) == false) {
                $msg = base64_encode('Incorrect security code entered');
                ?>
<script>
    window.location.href="sendsms.php?phone=<?php echo $number; ?>&name=<?php echo $name; ?>&msg=<?php echo $msg; ?>&e=1";
</script>
<?php
            }
       
else {
    $host = 'sms.accit.nsw.edu.au';
    $port = '9001';
    $userName = 'admin';
    $password = 'admin@123';
    SendMessage($host, $port, $userName, $password, $number, $message); 
    
    if(SendMessage($host, $port, $userName, $password, $number, $message))
    {
        $msg = base64_encode('SMS successfully sent');
        ?>
<script>
    window.location.href="sendsms.php?phone=<?php echo $number; ?>&name=<?php echo $name; ?>&msg=<?php echo $msg; ?>&e=0";
</script>
<?php
    }
      
}
    } // POST

?>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <title>Send SMS - <?php echo $_REQUEST['name']; ?></title>
    <style type="text/css">
    
           @import url('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');

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
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css"></link>
    </head>
<body>

<fieldset style="width: 35%;
    float: left;
    margin-left: 30%;
    margin-top: 30px;
    padding-left: 26px;
    padding-right: 24px;font-family: Arial, Helvetica, sans-serif;">
    <legend><strong>Send SMS - <?php echo @$name; ?></strong></legend>


    <div style="height:5px!important;">&nbsp;</div>
<div <?php if(isset($_GET['e']) && $_GET['e']==0) { ?> class="success-msg" <?php } else { ?> class="error-msg" <?php } if(isset($_GET['msg']) && $_GET['msg']!='') { ?> style="display: display;" <?php } else { ?> style="display: none;" <?php } ?>><?php echo $msg."!"; ?></div>
<div style="height:2px!important;">&nbsp;</div>
<form method="post" action="sendsms.php" id="contact_form" name="contact_form">

 

  <p>
    Message :<br />
    <textarea name="ct_message" rows="8" cols="54"><?php echo @$_SESSION['ct_message']; ?></textarea>
  </p>
<div style="height:4px!important;">&nbsp;</div>
  <p>
    <?php require_once 'securimage.php'; echo Securimage::getCaptchaHtml(array('input_name' => 'ct_captcha')); ?>
  </p>

  <p>
    <br />
    <input name="sub" id="sub" type="submit" class="w3-btn w3-white w3-border w3-border-green w3-round-large" style="font-weight: bold!important;" value=" SEND SMS " />
   
  </p>
  <input type="hidden" name="phone" id="phone" value="<?php echo $_REQUEST['phone']; ?>" />
    <input type="hidden" name="name" id="name" value="<?php echo @$_REQUEST['name']; ?>" />
</form>
</fieldset>

<script src="https://code.jquery.com/jquery-1.10.1.min.js"></script>
<script type="text/javascript">
    $.noConflict();

    function reloadCaptcha()
    {
        jQuery('#siimage').prop('src', './securimage_show.php?sid=' + Math.random());
    }

  
</script>

</body>
</html>

