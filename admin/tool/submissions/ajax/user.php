<?php
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
admin_externalpage_setup('index');

require_login();

global $SESSION;

global $DB;


$rec=$DB->get_records_sql("SELECT u.* FROM {user} as  u WHERE u.`id` = '".$_GET['id']."'");
foreach($rec as $rec)
{
    $arr[]=array("name"=>$rec->firstname." ".$rec->lastname,"email"=>$rec->email,"address"=>$rec->address,"city"=>$rec->city,
        "country"=>$rec->country,"phone"=>@$rec->phone1."/".@$rec->phone2 ,"skype"=>$rec->skype,"yahoo"=>$rec->yahoo,"aim"=>$rec->aim,"msn"=>$rec->msn,"icq"=>$rec->icq,"idnumber"=>$rec->idnumber,
        "suspended"=>$rec->suspended,"lastlogin"=>$rec->lastlogin);
}
?>
<html>
    <head></head>
    <center>
        <?php  if(count($arr)>0) { ?>
        <table cellspacing="7" cellpadding="10" width="100%">
            <tr>
                <td><strong>Name</strong></td>
                <td><?php echo ($arr[0]['name'] != '' ? $arr[0]['name'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>Email</strong></td>
                <td><a href="mailto: <?php echo ($arr[0]['email'] != '' ? $arr[0]['email'] : 'NA'); ?>"><?php echo ($arr[0]['email'] != '' ? $arr[0]['email'] : 'NA'); ?></a></td>
            </tr>
            <tr>
                <td><strong>Phone</strong></td>
                <td><?php echo ($arr[0]['phone1'] != '' ? $arr[0]['phone1'] : 'NA')." / ".($arr[0]['phone2'] != '' ? $arr[0]['phone2'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>Address</strong></td>
                <td><?php echo ($arr[0]['address'] != '' ? $arr[0]['address'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>City</strong></td>
                <td><?php echo ($arr[0]['city'] != '' ? $arr[0]['city'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>Country</strong></td>
                <td><?php echo ($arr[0]['country'] != '' ? $arr[0]['country'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>Yahoo</strong></td>
                <td><?php echo ($arr[0]['yahoo'] != '' ? $arr[0]['yahoo'] : 'NA'); ?></td>
            </tr>
             <tr>
                <td><strong>Skype</strong></td>
                <td><?php echo ($arr[0]['skype'] != '' ? $arr[0]['skype'] : 'NA'); ?></td>
            </tr>
             <tr>
                <td><strong>MSN</strong></td>
                <td><?php echo ($arr[0]['msn'] != '' ? $arr[0]['msn'] : 'NA'); ?></td>
            </tr>
             <tr>
                <td><strong>AIM</strong></td>
                <td><?php echo ($arr[0]['aim'] != '' ? $arr[0]['aim'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>ICQ</strong></td>
                <td><?php echo ($arr[0]['icq'] != '' ? $arr[0]['icq'] : 'NA'); ?></td>
            </tr>
            <tr>
                <td><strong>Status</strong></td>
                <td><?php echo ($arr[0]['suspended'] == 1 ? '<div style="color: red;">Suspended</div>' : '<div style="color: green;">Active</div>'); ?></td>
            </tr>
            <tr>
                <td><strong>Last Login</strong></td>
                <td><?php echo date("F j, Y, g:i a",$arr[0]['lastlogin']); ?></td>
            </tr>
        </table>
        <?php } ?>
    </center>
</html>