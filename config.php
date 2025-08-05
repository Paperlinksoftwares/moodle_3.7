<?php  /// Moodle Configuration File 

unset($CFG);


$CFG = new stdClass();
$CFG->dbtype    = 'mariadb';
$CFG->dbhost    = '127.0.0.1:3308';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle_new2019';
$CFG->dbpass    = 'fa6MNExR4KehLj1A';
$CFG->dbpersist =  false;
$CFG->prefix    = 'mdl_';

//$CFG->wwwroot   = 'https://moodle.accit.nsw.edu.au';
$CFG->wwwroot   = 'https://localhost/accit';
$CFG->dirroot   = 'D:\xampp-3.3\htdocs\accit';
$CFG->dataroot  = 'D:\xampp-3.3\htdocs\moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 00777;  // try 02777 on a server in Safe Mode
//$CFG->debug = 32767;
$CFG->debugdisplay = true;
//$CFG->directorypermissions = 0777;
$CFG->passwordsaltmain = ']1{YX{X*m-tJ=PUB}ZNfw-vID2C';
$CFG->loginhttps=false;

//safeassign plagrism

$CFG->plagiarism_safeassign_urls = [
    [ 'url' => 'safeassign.blackboard.com', 'type' => 'production']
];


//require_once(__DIR__. '/accountrestriction/restriction.php');
//$CFG->customfrontpageinclude = 'accountrestriction/restriction.php'; // No leading slash.

//echo $_GET['s'];
require_once("$CFG->dirroot/lib/setup.php");
//global $DB;
//global $USER;

// MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,
// RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE  F7tN9I5OjfqVRYQ.
if($_GET['s']!=1)
{
	 
	//require_once("$CFG->dirroot/accountrestriction/restriction.php");
}

//echo __DIR__. '/accountrestriction/restriction.php';

?>