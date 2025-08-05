<?php
###########################################################
/*
GuestBook Script
Copyright (C) 2012 StivaSoft ltd. All rights Reserved.


This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.html.

For further information visit:
http://www.phpjabbers.com/
info@phpjabbers.com

Version:  1.0
Released: 2012-03-18
*/
###########################################################

/* Define MySQL connection details and database table name */ 
$SETTINGS["hostname"]='192.168.1.12';
$SETTINGS["mysql_user"]='moodle_new2019';
$SETTINGS["mysql_pass"]='fa6MNExR4KehLj1A';
$SETTINGS["mysql_database"]='moodle';
$SETTINGS["data_table"]='mdl_onlineactivity_notification_cancels'; // this is the default database name that we used

/* Connect to MySQLi */

if (!isset($install) or $install != '1') {
	$connection = mysqli_connect($SETTINGS["hostname"], $SETTINGS["mysql_user"], $SETTINGS["mysql_pass"]) or die ('Unable to connect to MySQL server.<br ><br >Please make sure your MySQL login details are correct.');
	$db = mysqli_select_db($connection,$SETTINGS["mysql_database"]) or die ('request "Unable to select database."');
};
?>