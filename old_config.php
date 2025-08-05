<?php  /// Moodle Configuration File 

unset($CFG);

$CFG = new stdClass();
$CFG->dbtype    = 'mariadb';
$CFG->dbhost    = '192.168.1.12';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle_new2019';
$CFG->dbpass    = 'fa6MNExR4KehLj1A';
$CFG->dbpersist =  false;
$CFG->prefix    = 'mdl_';

$CFG->wwwroot   = 'https://moodle.accit.nsw.edu.au';
$CFG->dirroot   = 'C:\xampp\htdocs';
$CFG->dataroot  = 'C:\xampp\moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 00777;  // try 02777 on a server in Safe Mode
//$CFG->debug = 32767;
$CFG->debugdisplay = false;
//$CFG->directorypermissions = 0777;
$CFG->passwordsaltmain = ']1{YX{X*m-tJ=PUB}ZNfw-vID2C';

require_once("$CFG->dirroot/lib/setup.php");
// MAKE SURE WHEN YOU EDIT THIS FILE THAT THERE ARE NO SPACES, BLANK LINES,
// RETURNS, OR ANYTHING ELSE AFTER THE TWO CHARACTERS ON THE NEXT LINE  F7tN9I5OjfqVRYQ.
?>