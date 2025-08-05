<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The gradebook overview report
 *
 * @package   gradereport_overview
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function containsNumbers($String){
    return preg_match('/\\d/', $String) > 0;
}
require_once 'config.php';
global $DB;
if(isset($_GET['set']))
{
    $set=$_GET['set'];
}
else
{
    $set=0;
}
$list_all_users = $DB->get_records_sql("SELECT u.id , u.firstname , u.lastname , u.username FROM mdl_user u JOIN mdl_role_assignments ra ON ra.userid = u.id 
JOIN mdl_role r ON r.id = ra.roleid
AND r.shortname =  'student' WHERE 1");
echo 'Total -'.count($list_all_users).' Students';
echo '<br>';
echo '<br>';
foreach($list_all_users as $list_all_users)
{
    if($set==0 || $set==2)
    {
        if(containsNumbers($list_all_users->username)==false)
        {
            echo $list_all_users->firstname." ".$list_all_users->lastname." --------- ".$list_all_users->username;
            echo '<br>';
        }
    }
    else
    {
        echo $list_all_users->firstname." ".$list_all_users->lastname." --------- ".$list_all_users->username;
        echo '<br>';
    }
}
?>
<br>
<br>
<br>
<a href="userslist.php?set=1">View all</a>
<a href="userslist.php?set=2">View Wrong</a>
