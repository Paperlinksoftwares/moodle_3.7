<?php
function randomDate($sStartDate, $sEndDate, $sFormat = 'Y-m-d H:i:s')
{    
    $fMin = strtotime($sStartDate);
    $fMax = strtotime($sEndDate);   
    $fVal = mt_rand($fMin, $fMax);  
    return date($sFormat, $fVal);
}
function randomNumber($min, $max)
{
    return intval(mt_rand($min, $max));
}
function getWeekDatesByDate($date) {
  $ts = strtotime($date);
  $start = strtotime('monday this week', $ts);
  $end = strtotime('sunday this week', $ts);
  return array(date('Y-m-d', $start), date('Y-m-d', $end));
}
function updateSession($logouttime,$logintime,$registerid,$userid)
{
    global $DB;
    $arr = array();
    $duration = ($logouttime - $logintime);
    $u1 = $DB->get_records_sql("SELECT `duration` FROM {attendanceregister_aggregate} WHERE register = '".$registerid."' AND userid = '".$userid."' limit 0 , 1");
    foreach($u1 as  $u1)
    {
        $arr[] = $u1->duration;
    }
    $total_duration = $duration + @$arr[0];
    $u2 = $DB->execute("INSERT INTO {attendanceregister_session} (`id`, `register`, `login`, `logout`, `duration`, `userid`, `onlinesess`, `refcourse`, `comments`, `addedbyuserid`) VALUES (NULL, '".$registerid."', '".$logintime."', '".$logouttime."', '".$duration."', '".$userid."', '1', NULL, '', NULL)");
    if(@$arr[0]=='')
    {
        $u3 = $DB->execute("INSERT INTO {attendanceregister_aggregate} (`id`, `register`, `userid`, `duration`, `onlinesess`, `total`, `grandtotal`, `refcourse`,`lastsessionlogout`) VALUES (NULL, '".$registerid."', '".$userid."', '".$total_duration."' , '1', '1', '0', NULL, '0')");
    
        $u4 = $DB->execute("INSERT INTO {attendanceregister_aggregate} (`id`, `register`, `userid`, `duration`, `onlinesess`, `total`, `grandtotal`, `refcourse`,`lastsessionlogout`) VALUES (NULL, '".$registerid."', '".$userid."', '".$total_duration."' , NULL, '0', '1', NULL, '".$logouttime."')");
    
    }
    else
    {
        $u3 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `duration` = '".$total_duration."' WHERE `register` = '".$registerid."' AND `userid` = '".$userid."'");
        $u5 = $DB->execute("UPDATE {attendanceregister_aggregate} SET `lastsessionlogout` = '".$logouttime."' WHERE `register` = '".$registerid."' AND `userid` = '".$userid."' AND `lastsessionlogout`!='0'");
   }
}
function updateLogs($array)
{
    global $DB;
    $user_string = '';
    if(isset($array['groupid']) && $array['groupid']!='')
    {
        $sql_fetch_group_users = "SELECT `userid` from {groups_members} WHERE groupid = '".$array['groupid']."'"; 
        $groupmemberslist = $DB->get_records_sql($sql_fetch_group_users);
        foreach($groupmemberslist as $groupmemberslist)
        {
            $user_string = $user_string.$groupmemberslist->userid.",";
        }
        $user_string = rtrim($user_string,',');
    }
    $sql_update ="UPDATE {logstore_standard_log} SET `userid` = '".$array['teacherid']."' WHERE `component` = '".$array['module']."'";
    if($user_string!='')
    {
        $sql_update = $sql_update." AND `userid` IN (".$user_string.")";
    }
    if(isset($array['fromdate']) && $array['fromdate']!='')
    {
        $sql_update = $sql_update." AND `timecreated` >= '".strtotime($array['fromdate'])."'";
    }
    if(isset($array['todate']) && $array['todate']!='')
    {
        $sql_update = $sql_update." AND `timecreated` <= '".strtotime($array['todate'])."'";
    }
   // echo $sql_update; die;
    $u = $DB->execute($sql_update);
    //$u = true;
    if($u)
    {
        return true;
    }
    else
    {
        return false;
    }
}
function generateNumberUnique($duration,$divider)
{
    $randomNumber = rand(5,50); 
    $extra = intval($randomNumber/$divider)*60;
    $durationnew = $duration + $extra;
    return $durationnew;
}
function sendEmail($mail,$subject,$toname,$fromname,$to,$from,$contentarray)
{
    $mail->Subject = $subject;
    $mail->SetFrom($from, $fromname);
    $email_body = '<table width="60%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;" bgcolor="#F2F4F6">
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
                 <h1 style="box-sizing: border-box; color: #2F3133; font-family: Arial, Helvetica, sans-serif; font-size: 19px; margin-top: 0;" align="left">Hi '.$toname.'</h1>
                          <p style="box-sizing: border-box; color: #74787E; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.5em; margin-top: 0;" align="left">You have less than 5 hours online attendance for below period.</p>
                           </td>
                           </tr>
                           <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>
                 <tr>

                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                          <strong>Week start date -</strong> 
                                          </td>
                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                            '.$contentarray['weekstartdate'].'
                                          </td>
                                        </tr>
                                        <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>
            <tr>

                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                          <strong>Week start date -</strong> 
                                          </td>
                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                            '.$contentarray['weekfinishdate'].'
                                          </td>
                                        </tr>
                                        <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>
                                        <tr>
                                          <td style="box-sizing: border-box; font-family: Arial,  Helvetica, sans-serif; word-break: break-word;">
                                          <strong>Total Online Hours -</strong>  
                                          </td>
                                          <td style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; word-break: break-word;">
                                            '.$contentarray['duration'].'
                                          </td>
                                        </tr>
                                        <tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr><tr>
                <td colspan="2" style="height:20px;">&nbsp;</td>
            </tr>

                       
                                      </table>
                                      </td>
                                      </tr>

                    <tr>
                <td style="height:20px;">&nbsp;</td>
            </tr>


              </table>';
        echo $email_body; die;
        $mail->MsgHTML($email_body);   
        if($mail->validateAddress($to))
        {
            $mail->AddAddress($to, $toname);   
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