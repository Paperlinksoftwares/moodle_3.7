<?php
require_once('../config.php');
include('class.phpmailer.php');
global $DB;
global $CFG;
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Host = "mail.tpg.com.au";
$mail->Port = '25';
$mail->Username = "accitpaul@tpg.com.au";
$mail->Password = "Kent0495";
$mail->SMTPDebug  = 1;
$mail->SetFrom('info@accit.nsw.edu.au', 'ACCIT Admin');
$sql_user_to_send_email = "SELECT ass.* , user.firstname, user.lastname, user.email , assign.duedate, assign.name  FROM {assign_submission} as ass 
        LEFT JOIN {assign} as assign 
        ON ass.assignment = assign.id 
        LEFT JOIN {user} AS user 
        ON user.id = ass.userid 
        WHERE ass.userid IN (
        SELECT u.id FROM {user} u
        JOIN {assign_submission} a_s on a_s.userid = u.id) 
        AND ass.status != 'submitted' AND FROM_UNIXTIME(assign.duedate,'%Y-%m-%d %h %i %s') >= NOW() AND FROM_UNIXTIME(assign.duedate,'%Y-%m-%d %h %i %s')< NOW() + INTERVAL 1 DAY";
echo $sql_user_to_send_email;
$list = $DB->get_records_sql($sql_user_to_send_email);    
echo count($list);
echo '<pre>';
print_r($list);
die;
if(count($list)>0)
{
    foreach($list as $list)
    {
        $mail->Subject = "Assesment Due | ".$list->name;
        $email_body = '<table width="70%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;" bgcolor="#F2F4F6">
            <tr>
                <td style="height:40px;">&nbsp;</td>
            </tr>
            <tr>
                  <td align="left">
                      <img src="http://www.accit.nsw.edu.au/img/logo.png" border="0"></img> 
                  </td>
            </tr>
            <tr>
                <td style="height:40px;">&nbsp;</td>
            </tr>
            <tr>
            <td align="center" style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
              <table class="email-content" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;">


                <tr>
                  <td colspan="2" class="email-body" width="20%" cellpadding="0" cellspacing="0" style="-premailer-cellpadding: 0; -premailer-cellspacing: 0; border-bottom-color: #EDEFF2; border-bottom-style: solid; border-bottom-width: 1px; border-top-color: #EDEFF2; border-top-style: solid; border-top-width: 1px; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%; word-break: break-word;" bgcolor="#FFFFFF">
                 <h1 style="box-sizing: border-box; color: #2F3133; font-family: Arial, Helvetica, sans-serif; font-size: 19px; margin-top: 0;" align="left">Hi '.$list->firstname." ".$list->lastname.'</h1>
                          <p style="box-sizing: border-box; color: #74787E; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.5em; margin-top: 0;" align="left">Your assesment is due in 7 days. </p>
                           </td>
                           </tr>
                           <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>
                 <tr>

                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                          <strong>Assesment Name -</strong> 
                                          </td>
                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                            '.$list->name.'
                                          </td>
                                        </tr>
                                        <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>
                                        <tr>
                                          <td style="box-sizing: border-box; font-family: Arial,  Helvetica, sans-serif; word-break: break-word;">
                                          <strong>Due Date -</strong>  
                                          </td>
                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                            '.@date('g:i A \o\n l jS F Y',$list->duedate).'
                                          </td>
                                        </tr>
                                        <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr><tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>

                        <tr>

                                          <td colspan="2" style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                            <a href="http://www.yahoo.com" target="_blank">Submit now</a>
                                          </td>
                                        </tr>                 




                                      </table>
                                      </td>
                                      </tr>

                    <tr>
                <td style="height:20px;">&nbsp;</td>
            </tr>


              </table>';
        //echo $email_body;
        $mail->MsgHTML($email_body);   
        if($mail->validateAddress($list->email))
        {
            $mail->AddAddress($list->email, $list->firstname." ".$list->lastname);   
            if(!$mail->Send())
            {
                echo "Error sending: " . $mail->ErrorInfo;
            }
            else
            {
                echo "E-mail sent";
            }
        }
        $mail->ClearAddresses();
        $mail->ClearAttachments();
        echo '<hr>';
    }
}
//AND ass.status != 'submitted' AND (assign.duedate - UNIX_TIMESTAMP())<(1*24*60*60) LIMIT 5";
?>
