<?php
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
admin_externalpage_setup('index');

require_login();

global $SESSION;

global $DB;


$rec=$DB->get_records_sql("SELECT a.course , a.duedate , a.id as aid , a.name , a.intro , c.fullname , c.shortname FROM
{assign} as a JOIN {course} as c ON a.course = c.id WHERE a.`id` = '".$_GET['id']."'");
foreach($rec as $rec)
{
    $arr[]=array("name"=>$rec->name,"intro"=>$rec->intro,"coursefullname"=>$rec->fullname,"courseshortname"=>$rec->shortname,"duedate"=>$rec->duedate);
}
?>
<html>
    <head></head>
    <center>
        <?php  if(count($arr)>0) { ?>
        <table cellspacing="7" cellpadding="10" width="100%">
            <tr>
                <td><strong>Name</strong></td>
                <td><?php echo $arr[0]['name']; ?></td>
            </tr>
            <tr>
                <td><strong>Intro</strong></td>
                <td><?php echo $arr[0]['intro']; ?></td>
            </tr>
            <tr>
                <td><strong>Course</strong></td>
                <td><?php echo $arr[0]['coursefullname']; ?></td>
            </tr>
            
        </table>
        <?php } ?>
    </center>
</html>