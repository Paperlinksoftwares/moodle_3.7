<?php
require_once('config.php');
include_once('customfunctions.php');
include_once('lib/moodlelib.php');
global $DB;
global $CFG;
if(isset($_POST['send']) && $_POST['send']==1)
{
$to = new stdClass();
$to->id = '12345';
$to->firstname = 'Debraj';

$to->lastname  = 'Acharya';

$to->email     = 'acharya.666@gmail.com';
$to->maildisplay = true;




$from = new stdClass();

$from->firstname = 'Jeeva';

$from->lastname  = 'Samy';

$from->email     = 'jeeva@live.com';

$from->maildisplay = true;



$emailsubject = "TEST EMAIL SUBJECT";

$messageHtml = "<strong> HELLO DEBRAJ </strong>";
$emailmessage = "sdfsdf sdf sd fsdf";

if(email_to_user($to, $from, $emailsubject, $messageHtml, '', '', true))
{
    echo "Successfully sent!";
}
 else 
{
    echo "A Technical Error! Try Again or Contact to the Administrator.";
}
}
?>
<form action="" name="f1" id="f1" autocomplete="off" method="POST">
<input type="submit" name="submit" id="submit" value=" Send Email " />
<input type="hidden" name="send" id="send" value="1" />
</form>