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
//echo '<pre>';
//print_r($_POST); die;
require_once '../../../config.php';
//require_once $CFG->libdir.'/gradelib.php';

global $USER;
global $CFG;
global $DB;

function getObservationChecklistRating($userid , $course, $assignmentname)
{
	global $DB;
	$percentage = '';
	//echo "hhh".addslashes ($assignmentname); 
	//echo '<hr>';
	$sql_checklist_first = "SELECT count(`id`) as count FROM  `mdl_checklist` WHERE `name` LIKE '%".addslashes($assignmentname)." | Observation Checklist%'";
	$list_count = $DB->get_record_sql($sql_checklist_first);
	
	if($list_count->count>0)
	{
	$sql_checklist = "SELECT mi.`id`, mcl.`id` as assignid , mc.`teachermark` , mcl.`course` , mcl.`name` , mc.`userid` , mc.`item` , mc.`userid`
	, mcm.id as checklistid
	FROM `mdl_checklist_item` as mi 
	LEFT JOIN `mdl_checklist_check` as mc
	ON mc.`item` = mi.`id` 
	LEFT JOIN `mdl_checklist` as mcl 
	ON mi.`checklist` = mcl.`id` 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mc.`userid` = '".$userid."' AND mcl.`course` = '".$course."' AND mcl.`name` LIKE '%".addslashes($assignmentname)." | Observation Checklist%'";

	$list_all_deb = $DB->get_records_sql($sql_checklist);
	
	$arr=array();
	if(count($list_all_deb)>0)
	{
		foreach($list_all_deb as $key=>$val)
		{
			$arr[$val->assignid][]=$val->teachermark;		
		}	
	}
	if(count($arr)>0)
	{
		foreach($arr as $key2=>$val2)
		{
			$pass=0;
			for($k=0;$k<count($val2);$k++)
			{
				if($val2[$k]==1)
				{
					$pass++;
				}
			}
			
		}
		$percentage = (($pass/count($val2))*100);
		$percentage = round($percentage,2);
	}
	}
	else
	{
		$percentage = 'NA';
	}
	return $percentage;
}

if (isset($_POST['type']) && $_POST['type']!='')
{
    $userdetails = $_SESSION['userdetails'];
if($_POST['type']==1)
{
require_once($CFG->libdir.'/pdflib.php');


$pdf = new pdf();
$pdf->SetCreator('ACCIT');
$pdf->SetAuthor('ACCIT');
$pdf->SetTitle('PDF');
$pdf->SetSubject('GRADES');
$pdf->AddPage();




$html_pdf = '<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Print - </title>
<style>
.htmltoimage{
	width:95%;
	margin:auto;
}
.customers {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    font-size: 12px;
}
.info {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

.customers td, #customers th {
    border: 1px solid #aaa;
    padding: 8px;
    font-size: 12px;
}
.info td, .info th {
    border: 1px solid #aaa;
    padding: 8px;
}

customers tr:nth-child(even){background-color: #f2f2f2; font-size: 11px; }

.customers tr:hover {background-color: #ddd; font-size: 11px;}

.customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
    font-size: 12px;
}


.info {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
    font-size: 12px;
}
.alert {
    padding-top: 4px;
    padding-bottom: 4px;
    text-align: left;
    color: black;
    font-size: 11px;
    border: 1px solid red;
    font-weight: bold;
    padding-left: 15px;
}
#myProgress {
  width: 100%;
  background-color: #ddd;
}

#myBar {

  height: 30px;
  background-color: #4CAF50;
  text-align: center;
  padding-top:5px;
}

html,body{font-family: Arial, Helvetica, sans-serif;font-size:12px;}

.w3-image{max-width:100%;height:auto}img{vertical-align:middle}a{color:inherit}
.w3-table,.w3-table-all{border-collapse:collapse;border-spacing:0;width:100%;display:table}.w3-table-all{border:1px solid #ccc}
.w3-bordered tr,.w3-table-all tr{border-bottom:1px solid #ddd}.w3-striped tbody tr:nth-child(even){background-color:#f1f1f1}
.w3-table-all tr:nth-child(odd){background-color:#fff}.w3-table-all tr:nth-child(even){background-color:#f1f1f1}
.w3-hoverable tbody tr:hover,.w3-ul.w3-hoverable li:hover{background-color:#ccc}.w3-centered tr th,.w3-centered tr td{text-align:center}
.w3-table td,.w3-table th,.w3-table-all td,.w3-table-all th{padding:8px 8px;display:table-cell;text-align:left;vertical-align:top}
.w3-table th:first-child,.w3-table td:first-child,.w3-table-all th:first-child,.w3-table-all td:first-child{padding-left:16px}
.w3-btn,.w3-button{border:none;display:inline-block;padding:8px 16px;vertical-align:middle;overflow:hidden;text-decoration:none;color:inherit;background-color:inherit;text-align:center;cursor:pointer;white-space:nowrap}
.w3-btn:hover{box-shadow:0 8px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19)}
.w3-btn,.w3-button{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}   
.w3-disabled,.w3-btn:disabled,.w3-button:disabled{cursor:not-allowed;opacity:0.3}.w3-disabled *,:disabled *{pointer-events:none}
.w3-btn.w3-disabled:hover,.w3-btn:disabled:hover{box-shadow:none}
.w3-badge,.w3-tag{background-color:#000;color:#fff;display:inline-block;padding-left:8px;padding-right:8px;text-align:center}.w3-badge{border-radius:50%}
.w3-ul{list-style-type:none;padding:0;margin:0}.w3-ul li{padding:8px 16px;border-bottom:1px solid #ddd}.w3-ul li:last-child{border-bottom:none}
.w3-tooltip,.w3-display-container{position:relative}.w3-tooltip .w3-text{display:none}.w3-tooltip:hover .w3-text{display:inline-block}
.w3-ripple:active{opacity:0.5}.w3-ripple{transition:opacity 0s}
.w3-input{padding:8px;display:block;border:none;border-bottom:1px solid #ccc;width:100%}
.w3-select{padding:9px 0;width:100%;border:none;border-bottom:1px solid #ccc}
.w3-dropdown-click,.w3-dropdown-hover{position:relative;display:inline-block;cursor:pointer}
.w3-dropdown-hover:hover .w3-dropdown-content{display:block}
.w3-dropdown-hover:first-child,.w3-dropdown-click:hover{background-color:#ccc;color:#000}
.w3-dropdown-hover:hover > .w3-button:first-child,.w3-dropdown-click:hover > .w3-button:first-child{background-color:#ccc;color:#000}
.w3-dropdown-content{cursor:auto;color:#000;background-color:#fff;display:none;position:absolute;min-width:160px;margin:0;padding:0;z-index:1}
.w3-check,.w3-radio{width:24px;height:24px;position:relative;top:6px}
.w3-sidebar{height:100%;width:200px;background-color:#fff;position:fixed!important;z-index:1;overflow:auto}
.w3-bar-block .w3-dropdown-hover,.w3-bar-block .w3-dropdown-click{width:100%}
.w3-bar-block .w3-dropdown-hover .w3-dropdown-content,.w3-bar-block .w3-dropdown-click .w3-dropdown-content{min-width:100%}
.w3-bar-block .w3-dropdown-hover .w3-button,.w3-bar-block .w3-dropdown-click .w3-button{width:100%;text-align:left;padding:8px 16px}
.w3-main,#main{transition:margin-left .4s}
.w3-modal{z-index:3;display:none;padding-top:100px;position:fixed;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4)}
.w3-modal-content{margin:auto;background-color:#fff;position:relative;padding:0;outline:0;width:600px}
.w3-bar{width:100%;overflow:hidden}.w3-center .w3-bar{display:inline-block;width:auto}
.w3-bar .w3-bar-item{padding:8px 16px;float:left;width:auto;border:none;display:block;outline:0}
.w3-bar .w3-dropdown-hover,.w3-bar .w3-dropdown-click{position:static;float:left}
.w3-bar .w3-button{white-space:normal}
.w3-bar-block .w3-bar-item{width:100%;display:block;padding:8px 16px;text-align:left;border:none;white-space:normal;float:none;outline:0}
.w3-bar-block.w3-center .w3-bar-item{text-align:center}.w3-block{display:block;width:100%}
.w3-responsive{display:block;overflow-x:auto}
.w3-container:after,.w3-container:before,.w3-panel:after,.w3-panel:before,.w3-row:after,.w3-row:before,.w3-row-padding:after,.w3-row-padding:before,
.w3-cell-row:before,.w3-cell-row:after,.w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after{content:"";display:table;clear:both}
.w3-col,.w3-half,.w3-third,.w3-twothird,.w3-threequarter,.w3-quarter{float:left;width:100%}
.w3-col.s1{width:8.33333%}.w3-col.s2{width:16.66666%}.w3-col.s3{width:24.99999%}.w3-col.s4{width:33.33333%}
.w3-col.s5{width:41.66666%}.w3-col.s6{width:49.99999%}.w3-col.s7{width:58.33333%}.w3-col.s8{width:66.66666%}
.w3-col.s9{width:74.99999%}.w3-col.s10{width:83.33333%}.w3-col.s11{width:91.66666%}.w3-col.s12{width:99.99999%}
@media (min-width:601px){.w3-col.m1{width:8.33333%}.w3-col.m2{width:16.66666%}.w3-col.m3,.w3-quarter{width:24.99999%}.w3-col.m4,.w3-third{width:33.33333%}
.w3-col.m5{width:41.66666%}.w3-col.m6,.w3-half{width:49.99999%}.w3-col.m7{width:58.33333%}.w3-col.m8,.w3-twothird{width:66.66666%}
.w3-col.m9,.w3-threequarter{width:74.99999%}.w3-col.m10{width:83.33333%}.w3-col.m11{width:91.66666%}.w3-col.m12{width:99.99999%}}
@media (min-width:993px){.w3-col.l1{width:8.33333%}.w3-col.l2{width:16.66666%}.w3-col.l3{width:24.99999%}.w3-col.l4{width:33.33333%}
.w3-col.l5{width:41.66666%}.w3-col.l6{width:49.99999%}.w3-col.l7{width:58.33333%}.w3-col.l8{width:66.66666%}
.w3-col.l9{width:74.99999%}.w3-col.l10{width:83.33333%}.w3-col.l11{width:91.66666%}.w3-col.l12{width:99.99999%}}
.w3-rest{overflow:hidden}.w3-stretch{margin-left:-16px;margin-right:-16px}
.w3-content,.w3-auto{margin-left:auto;margin-right:auto}.w3-content{max-width:980px}.w3-auto{max-width:1140px}
.w3-cell-row{display:table;width:100%}.w3-cell{display:table-cell}
.w3-cell-top{vertical-align:top}.w3-cell-middle{vertical-align:middle}.w3-cell-bottom{vertical-align:bottom}
.w3-hide{display:none!important}.w3-show-block,.w3-show{display:block!important}.w3-show-inline-block{display:inline-block!important}
@media (max-width:1205px){.w3-auto{max-width:95%}}
@media (max-width:600px){.w3-modal-content{margin:0 10px;width:auto!important}.w3-modal{padding-top:30px}
.w3-dropdown-hover.w3-mobile .w3-dropdown-content,.w3-dropdown-click.w3-mobile .w3-dropdown-content{position:relative}	
.w3-hide-small{display:none!important}.w3-mobile{display:block;width:100%!important}.w3-bar-item.w3-mobile,.w3-dropdown-hover.w3-mobile,.w3-dropdown-click.w3-mobile{text-align:center}
.w3-dropdown-hover.w3-mobile,.w3-dropdown-hover.w3-mobile .w3-btn,.w3-dropdown-hover.w3-mobile .w3-button,.w3-dropdown-click.w3-mobile,.w3-dropdown-click.w3-mobile .w3-btn,.w3-dropdown-click.w3-mobile .w3-button{width:100%}}
@media (max-width:768px){.w3-modal-content{width:500px}.w3-modal{padding-top:50px}}
@media (min-width:993px){.w3-modal-content{width:900px}.w3-hide-large{display:none!important}.w3-sidebar.w3-collapse{display:block!important}}
@media (max-width:992px) and (min-width:601px){.w3-hide-medium{display:none!important}}
@media (max-width:992px){.w3-sidebar.w3-collapse{display:none}.w3-main{margin-left:0!important;margin-right:0!important}.w3-auto{max-width:100%}}
.w3-top,.w3-bottom{position:fixed;width:100%;z-index:1}.w3-top{top:0}.w3-bottom{bottom:0}
.w3-overlay{position:fixed;display:none;width:100%;height:100%;top:0;left:0;right:0;bottom:0;background-color:rgba(0,0,0,0.5);z-index:2}
.w3-display-topleft{position:absolute;left:0;top:0}.w3-display-topright{position:absolute;right:0;top:0}
.w3-display-bottomleft{position:absolute;left:0;bottom:0}.w3-display-bottomright{position:absolute;right:0;bottom:0}
.w3-display-middle{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%)}
.w3-display-left{position:absolute;top:50%;left:0%;transform:translate(0%,-50%);-ms-transform:translate(-0%,-50%)}
.w3-display-right{position:absolute;top:50%;right:0%;transform:translate(0%,-50%);-ms-transform:translate(0%,-50%)}
.w3-display-topmiddle{position:absolute;left:50%;top:0;transform:translate(-50%,0%);-ms-transform:translate(-50%,0%)}
.w3-display-bottommiddle{position:absolute;left:50%;bottom:0;transform:translate(-50%,0%);-ms-transform:translate(-50%,0%)}
.w3-display-container:hover .w3-display-hover{display:block}.w3-display-container:hover span.w3-display-hover{display:inline-block}.w3-display-hover{display:none}
.w3-display-position{position:absolute}
.w3-circle{border-radius:50%}
.w3-round-small{border-radius:2px}.w3-round,.w3-round-medium{border-radius:4px}.w3-round-large{border-radius:8px}.w3-round-xlarge{border-radius:16px}.w3-round-xxlarge{border-radius:32px}
.w3-row-padding,.w3-row-padding>.w3-half,.w3-row-padding>.w3-third,.w3-row-padding>.w3-twothird,.w3-row-padding>.w3-threequarter,.w3-row-padding>.w3-quarter,.w3-row-padding>.w3-col{padding:0 8px}
.w3-container,.w3-panel{padding:0.01em 16px}.w3-panel{margin-top:16px;margin-bottom:16px}
.w3-code,.w3-codespan{font-family:Consolas,"courier new";font-size:16px}
.w3-code{width:auto;background-color:#fff;padding:8px 12px;border-left:4px solid #4CAF50;word-wrap:break-word}
.w3-codespan{color:crimson;background-color:#f1f1f1;padding-left:4px;padding-right:4px;font-size:110%}
.w3-card,.w3-card-2{box-shadow:0 2px 5px 0 rgba(0,0,0,0.16),0 2px 10px 0 rgba(0,0,0,0.12)}
.w3-card-4,.w3-hover-shadow:hover{box-shadow:0 4px 10px 0 rgba(0,0,0,0.2),0 4px 20px 0 rgba(0,0,0,0.19)}
.w3-spin{animation:w3-spin 2s infinite linear}@keyframes w3-spin{0%{transform:rotate(0deg)}100%{transform:rotate(359deg)}}
.w3-animate-fading{animation:fading 10s infinite}@keyframes fading{0%{opacity:0}50%{opacity:1}100%{opacity:0}}
.w3-animate-opacity{animation:opac 0.8s}@keyframes opac{from{opacity:0} to{opacity:1}}
.w3-animate-top{position:relative;animation:animatetop 0.4s}@keyframes animatetop{from{top:-300px;opacity:0} to{top:0;opacity:1}}
.w3-animate-left{position:relative;animation:animateleft 0.4s}@keyframes animateleft{from{left:-300px;opacity:0} to{left:0;opacity:1}}
.w3-animate-right{position:relative;animation:animateright 0.4s}@keyframes animateright{from{right:-300px;opacity:0} to{right:0;opacity:1}}
.w3-animate-bottom{position:relative;animation:animatebottom 0.4s}@keyframes animatebottom{from{bottom:-300px;opacity:0} to{bottom:0;opacity:1}}
.w3-animate-zoom {animation:animatezoom 0.6s}@keyframes animatezoom{from{transform:scale(0)} to{transform:scale(1)}}
.w3-animate-input{transition:width 0.4s ease-in-out}.w3-animate-input:focus{width:100%!important}
.w3-opacity,.w3-hover-opacity:hover{opacity:0.60}.w3-opacity-off,.w3-hover-opacity-off:hover{opacity:1}
.w3-opacity-max{opacity:0.25}.w3-opacity-min{opacity:0.75}
.w3-greyscale-max,.w3-grayscale-max,.w3-hover-greyscale:hover,.w3-hover-grayscale:hover{filter:grayscale(100%)}
.w3-greyscale,.w3-grayscale{filter:grayscale(75%)}.w3-greyscale-min,.w3-grayscale-min{filter:grayscale(50%)}
.w3-sepia{filter:sepia(75%)}.w3-sepia-max,.w3-hover-sepia:hover{filter:sepia(100%)}.w3-sepia-min{filter:sepia(50%)}
.w3-tiny{font-size:10px!important}.w3-small{font-size:12px!important}.w3-medium{font-size:15px!important}.w3-large{font-size:18px!important}
.w3-xlarge{font-size:24px!important}.w3-xxlarge{font-size:36px!important}.w3-xxxlarge{font-size:48px!important}.w3-jumbo{font-size:64px!important}
.w3-left-align{text-align:left!important}.w3-right-align{text-align:right!important}.w3-justify{text-align:justify!important}.w3-center{text-align:center!important}
.w3-border-0{border:0!important}.w3-border{border:1px solid #ccc!important}
.w3-border-top{border-top:1px solid #ccc!important}.w3-border-bottom{border-bottom:1px solid #ccc!important}
.w3-border-left{border-left:1px solid #ccc!important}.w3-border-right{border-right:1px solid #ccc!important}
.w3-topbar{border-top:6px solid #ccc!important}.w3-bottombar{border-bottom:6px solid #ccc!important}
.w3-leftbar{border-left:6px solid #ccc!important}.w3-rightbar{border-right:6px solid #ccc!important}
.w3-section,.w3-code{margin-top:16px!important;margin-bottom:16px!important}
.w3-margin{margin:16px!important}.w3-margin-top{margin-top:16px!important}.w3-margin-bottom{margin-bottom:16px!important}
.w3-margin-left{margin-left:16px!important}.w3-margin-right{margin-right:16px!important}
.w3-padding-small{padding:4px 8px!important}.w3-padding{padding:8px 16px!important}.w3-padding-large{padding:12px 24px!important}
.w3-padding-16{padding-top:16px!important;padding-bottom:16px!important}.w3-padding-24{padding-top:24px!important;padding-bottom:24px!important}
.w3-padding-32{padding-top:32px!important;padding-bottom:32px!important}.w3-padding-48{padding-top:48px!important;padding-bottom:48px!important}
.w3-padding-64{padding-top:64px!important;padding-bottom:64px!important}
.w3-left{float:left!important}.w3-right{float:right!important}
.w3-button:hover{color:#000!important;background-color:#ccc!important}
.w3-transparent,.w3-hover-none:hover{background-color:transparent!important}
.w3-hover-none:hover{box-shadow:none!important}
/* Colors */
.w3-amber,.w3-hover-amber:hover{color:#000!important;background-color:#ffc107!important}
.w3-aqua,.w3-hover-aqua:hover{color:#000!important;background-color:#00ffff!important}
.w3-blue,.w3-hover-blue:hover{color:#fff!important;background-color:#2196F3!important}
.w3-light-blue,.w3-hover-light-blue:hover{color:#000!important;background-color:#87CEEB!important}
.w3-brown,.w3-hover-brown:hover{color:#fff!important;background-color:#795548!important}
.w3-cyan,.w3-hover-cyan:hover{color:#000!important;background-color:#00bcd4!important}
.w3-blue-grey,.w3-hover-blue-grey:hover,.w3-blue-gray,.w3-hover-blue-gray:hover{color:#fff!important;background-color:#607d8b!important}
.w3-green,.w3-hover-green:hover{color:#fff!important;background-color:#4CAF50!important}
.w3-light-green,.w3-hover-light-green:hover{color:#000!important;background-color:#8bc34a!important}
.w3-indigo,.w3-hover-indigo:hover{color:#fff!important;background-color:#3f51b5!important}
.w3-khaki,.w3-hover-khaki:hover{color:#000!important;background-color:#f0e68c!important}
.w3-lime,.w3-hover-lime:hover{color:#000!important;background-color:#cddc39!important}
.w3-orange,.w3-hover-orange:hover{color:#000!important;background-color:#ff9800!important}
.w3-deep-orange,.w3-hover-deep-orange:hover{color:#fff!important;background-color:#ff5722!important}
.w3-pink,.w3-hover-pink:hover{color:#fff!important;background-color:#e91e63!important}
.w3-purple,.w3-hover-purple:hover{color:#fff!important;background-color:#9c27b0!important}
.w3-deep-purple,.w3-hover-deep-purple:hover{color:#fff!important;background-color:#673ab7!important}
.w3-red,.w3-hover-red:hover{color:#fff!important;background-color:#f44336!important}
.w3-sand,.w3-hover-sand:hover{color:#000!important;background-color:#fdf5e6!important}
.w3-teal,.w3-hover-teal:hover{color:#fff!important;background-color:#009688!important}
.w3-yellow,.w3-hover-yellow:hover{color:#000!important;background-color:#ffeb3b!important}
.w3-white,.w3-hover-white:hover{color:#000!important;background-color:#fff!important}
.w3-black,.w3-hover-black:hover{color:#fff!important;background-color:#000!important}
.w3-grey,.w3-hover-grey:hover,.w3-gray,.w3-hover-gray:hover{color:#000!important;background-color:#9e9e9e!important}
.w3-light-grey,.w3-hover-light-grey:hover,.w3-light-gray,.w3-hover-light-gray:hover{color:#000!important;background-color:#f1f1f1!important}
.w3-dark-grey,.w3-hover-dark-grey:hover,.w3-dark-gray,.w3-hover-dark-gray:hover{color:#fff!important;background-color:#616161!important}
.w3-pale-red,.w3-hover-pale-red:hover{color:#000!important;background-color:#ffdddd!important}
.w3-pale-green,.w3-hover-pale-green:hover{color:#000!important;background-color:#ddffdd!important}
.w3-pale-yellow,.w3-hover-pale-yellow:hover{color:#000!important;background-color:#ffffcc!important}
.w3-pale-blue,.w3-hover-pale-blue:hover{color:#000!important;background-color:#ddffff!important}
.w3-text-amber,.w3-hover-text-amber:hover{color:#ffc107!important}
.w3-text-aqua,.w3-hover-text-aqua:hover{color:#00ffff!important}
.w3-text-blue,.w3-hover-text-blue:hover{color:#2196F3!important}
.w3-text-light-blue,.w3-hover-text-light-blue:hover{color:#87CEEB!important}
.w3-text-brown,.w3-hover-text-brown:hover{color:#795548!important}
.w3-text-cyan,.w3-hover-text-cyan:hover{color:#00bcd4!important}
.w3-text-blue-grey,.w3-hover-text-blue-grey:hover,.w3-text-blue-gray,.w3-hover-text-blue-gray:hover{color:#607d8b!important}
.w3-text-green,.w3-hover-text-green:hover{color:#4CAF50!important}
.w3-text-light-green,.w3-hover-text-light-green:hover{color:#8bc34a!important}
.w3-text-indigo,.w3-hover-text-indigo:hover{color:#3f51b5!important}
.w3-text-khaki,.w3-hover-text-khaki:hover{color:#b4aa50!important}
.w3-text-lime,.w3-hover-text-lime:hover{color:#cddc39!important}
.w3-text-orange,.w3-hover-text-orange:hover{color:#ff9800!important}
.w3-text-deep-orange,.w3-hover-text-deep-orange:hover{color:#ff5722!important}
.w3-text-pink,.w3-hover-text-pink:hover{color:#e91e63!important}
.w3-text-purple,.w3-hover-text-purple:hover{color:#9c27b0!important}
.w3-text-deep-purple,.w3-hover-text-deep-purple:hover{color:#673ab7!important}
.w3-text-red,.w3-hover-text-red:hover{color:#f44336!important}
.w3-text-sand,.w3-hover-text-sand:hover{color:#fdf5e6!important}
.w3-text-teal,.w3-hover-text-teal:hover{color:#009688!important}
.w3-text-yellow,.w3-hover-text-yellow:hover{color:#d2be0e!important}
.w3-text-white,.w3-hover-text-white:hover{color:#fff!important}
.w3-text-black,.w3-hover-text-black:hover{color:#000!important}
.w3-text-grey,.w3-hover-text-grey:hover,.w3-text-gray,.w3-hover-text-gray:hover{color:#757575!important}
.w3-text-light-grey,.w3-hover-text-light-grey:hover,.w3-text-light-gray,.w3-hover-text-light-gray:hover{color:#f1f1f1!important}
.w3-text-dark-grey,.w3-hover-text-dark-grey:hover,.w3-text-dark-gray,.w3-hover-text-dark-gray:hover{color:#3a3a3a!important}
.w3-border-amber,.w3-hover-border-amber:hover{border-color:#ffc107!important}
.w3-border-aqua,.w3-hover-border-aqua:hover{border-color:#00ffff!important}
.w3-border-blue,.w3-hover-border-blue:hover{border-color:#2196F3!important}
.w3-border-light-blue,.w3-hover-border-light-blue:hover{border-color:#87CEEB!important}
.w3-border-brown,.w3-hover-border-brown:hover{border-color:#795548!important}
.w3-border-cyan,.w3-hover-border-cyan:hover{border-color:#00bcd4!important}
.w3-border-blue-grey,.w3-hover-border-blue-grey:hover,.w3-border-blue-gray,.w3-hover-border-blue-gray:hover{border-color:#607d8b!important}
.w3-border-green,.w3-hover-border-green:hover{border-color:#4CAF50!important}
.w3-border-light-green,.w3-hover-border-light-green:hover{border-color:#8bc34a!important}
.w3-border-indigo,.w3-hover-border-indigo:hover{border-color:#3f51b5!important}
.w3-border-khaki,.w3-hover-border-khaki:hover{border-color:#f0e68c!important}
.w3-border-lime,.w3-hover-border-lime:hover{border-color:#cddc39!important}
.w3-border-orange,.w3-hover-border-orange:hover{border-color:#ff9800!important}
.w3-border-deep-orange,.w3-hover-border-deep-orange:hover{border-color:#ff5722!important}
.w3-border-pink,.w3-hover-border-pink:hover{border-color:#e91e63!important}
.w3-border-purple,.w3-hover-border-purple:hover{border-color:#9c27b0!important}
.w3-border-deep-purple,.w3-hover-border-deep-purple:hover{border-color:#673ab7!important}
.w3-border-red,.w3-hover-border-red:hover{border-color:#f44336!important}
.w3-border-sand,.w3-hover-border-sand:hover{border-color:#fdf5e6!important}
.w3-border-teal,.w3-hover-border-teal:hover{border-color:#009688!important}
.w3-border-yellow,.w3-hover-border-yellow:hover{border-color:#ffeb3b!important}
.w3-border-white,.w3-hover-border-white:hover{border-color:#fff!important}
.w3-border-black,.w3-hover-border-black:hover{border-color:#000!important}
.w3-border-grey,.w3-hover-border-grey:hover,.w3-border-gray,.w3-hover-border-gray:hover{border-color:#9e9e9e!important}
.w3-border-light-grey,.w3-hover-border-light-grey:hover,.w3-border-light-gray,.w3-hover-border-light-gray:hover{border-color:#f1f1f1!important}
.w3-border-dark-grey,.w3-hover-border-dark-grey:hover,.w3-border-dark-gray,.w3-hover-border-dark-gray:hover{border-color:#616161!important}
.w3-border-pale-red,.w3-hover-border-pale-red:hover{border-color:#ffe7e7!important}.w3-border-pale-green,.w3-hover-border-pale-green:hover{border-color:#e7ffe7!important}
.w3-border-pale-yellow,.w3-hover-border-pale-yellow:hover{border-color:#ffffcc!important}.w3-border-pale-blue,.w3-hover-border-pale-blue:hover{border-color:#e7ffff!important}
</style>
</head>
<body>
<div id="htmltoimage"><br>
 <table style="width: 99%; border: 1px solid white; float: left;" cellpadding="1" cellspacing="1">
         <tr>
             <td align="center"> <div style="float: center;"><img src="" boder="0"></div></td>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
    </table>
     <table style="width: 99%;
	border: 1px solid blue;
	padding-left: 2px;
	margin-left: 6px;
	position: relative;
	left: 50%;
	top: 63%;
	transform: translate(-50%,0%);" cellpadding="4" cellspacing="4">
         
         <tr style="background-color: #ddd;">
             <td><strong>Name :</strong></td>
             <td>'; $html_pdf = $html_pdf.$userdetails->firstname." ".$userdetails->lastname; 
             $html_pdf = $html_pdf.'</td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Username :</strong></td>
             <td>'; $html_pdf = $html_pdf.$userdetails->username; $html_pdf=$html_pdf.'</td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Email :</strong></td>
             <td>'; $html_pdf = $html_pdf.$userdetails->email; $html_pdf=$html_pdf.'</td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Phone :</strong></td>
             <td>'; 
if($userdetails->phone1!='') 
    
    { 
        $html_pdf = $html_pdf.$userdetails->phone1; 
    
    } 
else if($userdetails->phone2!='') 
    { 
    $html_pdf = $html_pdf.$userdetails->phone2;
    }
    else { $html_pdf = $html_pdf.'NA'; } 


$html_pdf = $html_pdf.'</td>
         </tr>
     </table><br/>';
$html_pdf = $html_pdf.'<table style="margin-top: 19px; width: 99%; border: 1px solid white; float: left; padding-left: 2px; margin-left: 6px;" cellpadding="4" cellspacing="4">
        <tr>
            <td><div style="float: right; margin-right: 3px;"><i>Generated on '.@date("F j, Y, g:i a").'</i></div></td>
        </tr>
    </table>';
$c=0; 



//ADDED FOR QUALIFICATION GROUPING

foreach($_SESSION['data_all'] as $kk=>$vv)
				{
					
					if(isset($_POST['select_course']) && count($_POST['select_course'])>0 && in_array($vv[2]->id,$_POST['select_course'])==true) 
					{
						
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",$vv[0]);
					
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
				
				}
				else if(count($_POST['select_course'])==0)
				{
					
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",$vv[0]);
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
					
					
				}
				else
				{
				}
				
				}
				
	
	
	
	
	
				
				// END
				
				foreach($data_all_new as $mm=>$pp) 
	{ 
	
	if($mm!='NA') { 
	
		
	$html_pdf = $html_pdf.'<br/><br/><div style="border: 1px solid black;
    border-radius: 9px;
    height: 30px;

    font-size: 19px;
    background-color: orange;
    color: white;
    padding: 2px 6px 6px 6px; margin-top: 7px;">'.$mm.'</div>';
	
 } else {
	$html_pdf = $html_pdf.'<br/><br/><div style="border: 1px solid black;
    border-radius: 9px;
    height: 30px;

    font-size: 19px;
    background-color: red;
    color: white;
    padding: 2px 6px 6px 6px; margin-top: 7px;">
	
	&nbsp;&nbsp;No Qualification Found</div>';
	
	} 
	
	
foreach($pp as $key=>$val)  
{ 

$c++; 
$startdate = $val[2]->startdate;


    if(count($_POST['select_course'])>0 && in_array($val[2]->id,$_POST['select_course'])==false) { $display = 'style="display: none;"'; } else { $display = ''; }
  $html_pdf = $html_pdf.'<table  id="row'.$c.'" style="width: 100%; font-family: Arial, Helvetica, sans-serif!important;"><tr '.$display.'><td><div ';
  
  if($val[2]->category==97 || $val[2]->category==98 || $val[2]->category==104 || $val[2]->category==111 || $val[2]->category==112) { $html_pdf= $html_pdf.' style="color: red!important;font-size: 14px!important;" '; } else { $html_pdf = $html_pdf.' style="color: black; font-size: 14px!important;" '; } $html_pdf= $html_pdf.'>';
  
  $html_pdf = $html_pdf.'<p>'.$val[0].'</p>
</div></td>
            </tr>
            <tr '.$display.'><td>';   
         
         


$contextid = get_context_instance(CONTEXT_COURSE, $val[2]->id);
     
   $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '                      
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
    
    
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}


$list = $DB->get_records_sql($sql);

$arr = array();
//echo '<pre>';
//print_r($list); 
foreach($list as $list)
{
    $sql_module = "SELECT id FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
           $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
    
}


if($list_all_count>0) {
    
    
         
   if(date('Y',$startdate)>2016) {

         $html_pdf = $html_pdf.'<table class="customers" '.$display.'>
  <tr>
    <th>Assignment</th>
    <th>Submission Status</th>
    <th>Last Updated On</th>
    <th>Graded On</th>
    <th>Result</th>
    
   </tr>'; } else {
  
           $html_pdf = $html_pdf.'<table class="customers" '.$display.'>
  <tr>
    <th>Assignment</th>
	
    <th>Submission Status</th>
    
    <th>Result</th>
    
  </tr>';
  
   }
  $cn = 0; foreach($arr as $key=>$val2) { 
        $rate = getObservationChecklistRating($list->userid,$list->courseid,$val2['assignmentname']);
	if($rate!='')
	{
		$rate_percentage = $rate;
	}
	else
	{
		$rate_percentage = '';
	}
      $cn++;
  $html_pdf = $html_pdf.'<tr>
      <td>'.$val2['assignmentname'].'</td>
    <td>';
  if(strtolower($val2['status'])=="new") { $html_pdf = $html_pdf.'No Submission'; } else { $html_pdf=$html_pdf.$val2['status']; } 
  $html_pdf = $html_pdf.'</td>';
  if(date('Y',$startdate)>2016) {
  
    $html_pdf = $html_pdf.'<td>'.$val2['activitydate'].'</td>
    <td>'.$val2['timemodified'].'</td>';
  }
  
 $html_pdf = $html_pdf.'<td>'.$val2['result'].'</td></tr>';
    
  
  $html_pdf = $html_pdf.'<tr><td colspan="7" ><br/><br/><strong>Feedback:</strong>';
   if($val2['feedback']!='') { $html_pdf=$html_pdf.strip_tags($val2['feedback']); } else { $html_pdf=$html_pdf.'No Feedback found!'; }
   $html_pdf = $html_pdf.'<br/></td>
  </tr>';
  
   } 
  
  
  
   if($rate_percentage!='NA') { 
  
  
  $sql_checklist2 = "SELECT mcl.`id` as assignid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` LIKE '%".mysqli_real_escape_string($val2['assignmentname'])." | Observation Checklist%'";

	$list_all_deb2 = $DB->get_record_sql($sql_checklist2);
  

  
  

  $html_pdf = $html_pdf.'<tr>
  <td colspan="7">


  <table style="width:100%!important;">
  <tr>
  <td style="width:20%!important;">
  <strong>Observation checklist</strong></td><td>';
  
  if($rate_percentage!='') { 
  $html_pdf = $html_pdf.'<div id="myProgress" style="border: 1px solid blue!important;">
  <div id="myBar" style="width: '.$rate_percentage.';%!important; color: white!important; font-weight:bold!important;">'.$rate_percentage.'%</div>
  </div>';
  
   } else { 
   
   $html_pdf = $html_pdf.'<div style="text-align:center; color: red!important; font-weight: bold!important;">No attempts yet made</div>';
   
    } 
	$html_pdf = $html_pdf.'</td>
  </tr>
  </table>
  </td>
  </tr>';
   } 
  
  
  
  
  
  
  
  
  
  
  
$html_pdf = $html_pdf.'</table>';
 } else { $html_pdf = $html_pdf.'<div class="alert">  
  You have not viewed your assesments yet!
</div>'; 
 
 }
         
  
     
  $html_pdf = $html_pdf.'</td>
            </tr></table><br/>';
	unset($context);  } }
  
$html_pdf = $html_pdf.'</div>
</body>
</html>';



$pdf->WriteHTML($html_pdf,true, false, true, false, '');
$pdf->lastPage();
$filename = $userdetails->firstname.'-'.$userdetails->lastname.'-'.$userdetails->username.'_'.@date("F j, Y").".pdf";
$pdf->Output($filename, 'D');


}
else if($_POST['type']==2)
{
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Print - <?php echo $userdetails->firstname." ".$userdetails->lastname; ?></title>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
#htmltoimage{
	width:95%;
	margin:auto;
       
}
#customers {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    font-size: 15px;
}
#info {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

#customers td, #customers th {
    border: 1px solid #aaa;
    padding: 8px;
    font-size: 15px;
}
#info td, #info th {
    border: 1px solid #aaa;
    padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2; font-size: 11px; }

#customers tr:hover {background-color: #ddd; font-size: 11px;}

#customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
    font-size: 14px;
}


.info {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
    font-size: 14px;
}
.alert {
    padding-top: 4px;
    padding-bottom: 4px;
    text-align: left;
    color: black;
    font-size: 14px;
    border: 1px solid red;
    font-weight: bold;
    padding-left: 11px;
}
</style>
</head>
<body>
<!--<div id="htmltoimage">
<div class="imgbg"></div>
<h1>Demo page to show example of "How to Create and Download Image of HTML content in webpage Using html2canvas library". Find tutorial page here <a href="http://www.freakyjolly.com/convert-html-document-into-image-jpg-png-from-canvas/" target="_blank">Here</a></h1>. Just click on button below to download Image of this HTML content which is wrapped in an ID named "htmltoimage". Now I am typing some randome stuff, so that image downloaded will have some content to show blah blah blah :P :D <br>
</div>-->


    <table style="width: 99%; border: 1px solid white; float: left;" cellpadding="1" cellspacing="1">
         <tr>
             <td align="center"> <div style="float: center;"><img src="http://localhost/accit-moodle/accit/logo.png" boder="0"></div></td>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
    </table>
     <table style="width: 54%;
	border: 1px solid blue;
	padding-left: 2px;
	margin-left: 6px;
	position: relative;
	left: 50%;
	top: 63%;
	transform: translate(-50%,0%);" cellpadding="4" cellspacing="4">
        
         <tr style="background-color: #ddd;">
             <td><strong>Name :</strong></td>
             <td><?php echo $userdetails->firstname." ".$userdetails->lastname; ?></td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Username :</strong></td>
             <td><?php echo $userdetails->username; ?></td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Email :</strong></td>
             <td><?php echo $userdetails->email; ?></td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Phone :</strong></td>
             <td><?php if($userdetails->phone1!='') 
    
    { 
        echo $userdetails->phone1; 
    
    } 
else if($userdetails->phone2!='') 
    { 
    echo $userdetails->phone2;
    }
    else { echo 'NA'; }  ?></td>
         </tr>
     </table>
    
    <table style="margin-top: 19px; width: 99%; border: 1px solid white; float: left; padding-left: 2px; margin-left: 6px;" cellpadding="4" cellspacing="4">
        <tr>
            <td style="float: right; margin-right: 3px;"><i>Generated on <?php echo @date("F j, Y, g:i a"); ?></i></td>
        </tr>
    </table>
    <div style="height: 30px;"></div>
    <?php $c=0; 
	
	//ADDED OF GROUPING QUALIFICATION
	//echo '<pre>';
	//print_r($_SESSION['data_all']);
	
	foreach($_SESSION['data_all'] as $kk=>$vv)
				{
					if(isset($_POST['select_course']) && count($_POST['select_course'])>0 && in_array($vv[2]->id,$_POST['select_course'])==true) 
					{
					
						
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",$vv[0]);
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
				}
				else if(count($_POST['select_course'])==0)
				{
					
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",$vv[0]);
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
					
					
				}
				else
				{
				}
				
				}
				
	
	
	
	
	
	
	
	
	foreach($data_all_new as $mm=>$pp) 
	{ 
	
	
	if($mm!='NA') { 
	
		
	?>
	<br/>
<br/>
	
	<div style="border: 1px solid black;
    border-radius: 9px;
    height: 49px;

    font-size: 19px;
    background-color: orange;
    color: white;
    padding: 10px 6px 6px 10px; margin-top: 7px;"><?php echo $mm; ?></div><br/>
	
 <?php } else { ?>
 <br/><br/>
	<div style="border: 1px solid black;
    border-radius: 9px;
    height: 49px;

    font-size: 19px;
    background-color: red;
    color: white;
    padding: 2px 6px 6px 6px; margin-top: 7px;">
	
	&nbsp;&nbsp;No Qualification Found</div><br/>
<?php	
	} 
	
	
	$c=0; foreach($pp as $key=>$val)
	{
	$c++; 
	$startdate = $val[2]->startdate; 
	if(isset($_POST['select_course']) && count($_POST['select_course'])>0)
	{
		?>

    <table id="row<?php echo $c; ?>" style="width: 98%; margin-left: 14px; font-family: Arial, Helvetica, sans-serif!important;"><tr><td><div  <?php if($val[2]->category==97 || $val[2]->category==98 || $val[2]->category==104 || $val[2]->category==111 || $val[2]->category==112) { ?> class="w3-panel w3-red w3-round-large" <?php } else { ?> class="w3-panel w3-blue w3-round-large" <?php } ?> style="margin-bottom:0px!important; margin-top:0px!important; font-size: 16px!important;">
  
  <p><?php echo $val[0]; ?></p>
</div></td>
            </tr>
            <tr ><td>     
         
         <?php
         
         
//         $context = context_course::instance($val[2]->id);
//         $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$val[2]->id, 'userid'=>$userid));
//     $report = new grade_report_user($val[2]->id, $gpr, $context, $userid);
//    
//       
//
//        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
//            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
//        } else {
//            if ($report->fill_table()) {
//               // echo '<br />'.$report->print_table(true);
//            }
//        }
         
         
         $contextid = get_context_instance(CONTEXT_COURSE, $val[2]->id);
     
   $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '                      
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
    
    
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}
//echo '<pre>';
//print_r($scale_array);
//echo $sql_all;
//echo '<hr>';
//echo $sql;
//$list_all = $DB->get_records_sql($sql_all);

//$list_all_count = count($list_all);

$list = $DB->get_records_sql($sql);

$arr = array();
//echo '<pre>';
//print_r($list); 
foreach($list as $list)
{
    $sql_module = "SELECT id FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
           $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
    
}

if($list_all_count>0) {
    
    
         ?>
        

         <table id="customers">
  <tr>
    <th>Assignmentsss</th>
    <th>Submission Status</th>
    <?php if(date('Y',$startdate)>2016) { ?>
    <th>Last Updated On</th>
    <th>Graded On</th>
	<?php } ?>
    <th>Result</th>
   
  </tr>
  <?php $cn = 0; $ct = 0; $rate_arr=array(); foreach($arr as $key=>$val2) { 
        $rate = getObservationChecklistRating($list->userid,$list->courseid,$val2['assignmentname']);
	if($rate!='')
	{
		$rate_percentage = $rate;
	}
	else
	{
		$rate_percentage = '';
	}
      $cn++; ?>
  <tr>
      <td><?php echo $val2['assignmentname'].$rate_percentage; ?></td>
    <td><?php if(strtolower($val2['status'])=="new") { echo 'No Submission'; } else { echo $val2['status']; } ?></td>
   <?php if(date('Y',$startdate)>2016) { ?>
    <td><?php echo $val2['activitydate']; ?></td>
    <td><?php echo $val2['timemodified']; ?></td>
	<?php } ?>
    <td><?php echo $val2['result']; ?></td>
	</tr>
	<tr>
    <td colspan="6"><strong>Feedback:</strong>
        
      
          <?php if($val2['feedback']!='') { echo $val2['feedback']; } else { echo 'No Feedback found!'; }
          
         
          ?>
       
 
        
      </td>
  </tr>
  
  
 <?php if($rate_percentage!='NA') { 
  
  
  $sql_checklist2 = "SELECT mcl.`id` as assignid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` LIKE '%".$val2['assignmentname']." | Observation Checklist%'";

	$list_all_deb2 = $DB->get_record_sql($sql_checklist2);
  

  
  
  ?>
  <tr>
  <td colspan="7">


  <table style="width:100%!important;">
  <tr>
  <td style="width:20%!important;">
  <strong>Observation checklist</strong></td><td><?php if($rate_percentage!='') { ?>
  <div id="myProgress" style="border: 1px solid blue!important;">
  <div id="myBar" style="width:<?php echo $rate_percentage; ?>%!important; color: white!important; font-weight:bold!important;"><?php echo $rate_percentage."%"; ?></div>
  </div><?php } else { ?><div style="text-align:center; color: red!important; font-weight: bold!important;">No attempts yet made</div><?php } ?></td>
  </tr>
  </table>
  </td>
  </tr>
  <?php } ?>  

  
  
  
  
  <?php $ct++; unset($rate_arr); unset($rate_percentage); unset($rate); } ?>
  
  
</table>
<?php } else { echo '<div class="alert" style="margin-top:0px!important;">
  
  <p>You have not viewed your assesments yet!</p>
</div>'; }
         
  ?>
     
                    </td>
            </tr></table><br/>
    <?php  unset($context);  }
    
    else if(count($_POST['select_course'])==0)
    {
        
    ?>
            
            
           <table id="row<?php echo $c; ?>" style="width: 98%; margin-left: 14px; font-family: Arial, Helvetica, sans-serif!important;"><tr><td><div  <?php if($val[2]->category==97 || $val[2]->category==98 || $val[2]->category==104 || $val[2]->category==111 || $val[2]->category==112) { ?> class="w3-panel w3-red w3-round-large" <?php } else { ?> class="w3-panel w3-blue w3-round-large" <?php } ?> style="margin-bottom:0px!important; margin-top:0px!important; font-size: 16px!important;">
  
  <p><?php echo $val[0]; ?></p>
</div></td>
            </tr>
            <tr ><td>     
         
         <?php
         
         
//         $context = context_course::instance($val[2]->id);
//         $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'courseid'=>$val[2]->id, 'userid'=>$userid));
//     $report = new grade_report_user($val[2]->id, $gpr, $context, $userid);
//    
//       
//
//        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
//            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
//        } else {
//            if ($report->fill_table()) {
//               // echo '<br />'.$report->print_table(true);
//            }
//        }
         
         
         $contextid = get_context_instance(CONTEXT_COURSE, $val[2]->id);
     
   $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '                      
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
    
    
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}
//echo '<pre>';
//print_r($scale_array);
//echo $sql_all;
//echo '<hr>';
//echo $sql;
//$list_all = $DB->get_records_sql($sql_all);

//$list_all_count = count($list_all);

$list = $DB->get_records_sql($sql);

$arr = array();
//echo '<pre>';
//print_r($list); 
foreach($list as $list)
{
    $sql_module = "SELECT id FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
           $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
    
}

if($list_all_count>0) {
    
    
         ?>
        

         <table id="customers">
  <tr>
    <th>Assignment</th>
    <th>Submission Status</th>
	<?php if(date('Y',$startdate)>2016) { ?>
    <th>Last Updated On</th>
    <th>Graded On</th>
	<?php } ?>
    <th>Result</th>
    
  </tr>
  <?php $cn = 0; $ct = 0; $rate_arr=array(); foreach($arr as $key=>$val2) { 
        $rate = getObservationChecklistRating($list->userid,$list->courseid,$val2['assignmentname']);
	if($rate!='')
	{
		$rate_percentage = $rate;
	}
	else
	{
		$rate_percentage = '';
	}
      $cn++; ?>
  <tr>
      <td><?php echo $val2['assignmentname']; ?></td>
    <td><?php if(strtolower($val2['status'])=="new") { echo 'No Submission'; } else { echo $val2['status']; } ?></td>
  <?php if(date('Y',$startdate)>2016) { ?>
    <td><?php echo $val2['activitydate']; ?></td>
    <td><?php echo $val2['timemodified']; ?></td>
  <?php } ?>
    <td><?php echo $val2['result']; ?></td></tr>
	<tr>
    <td colspan="6"><strong>Feedback:</strong>
        
      
          <?php if($val2['feedback']!='') { echo $val2['feedback']; } else { echo 'No Feedback found!'; }
          
         
          ?>
       
 
       
       </td>
  </tr>
  
  
   
 <?php if($rate_percentage!='NA') { 
  
  
  $sql_checklist2 = "SELECT mcl.`id` as assignid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` LIKE '%".$val2['assignmentname']." | Observation Checklist%'";

	$list_all_deb2 = $DB->get_record_sql($sql_checklist2);
  

  
  
  ?>
  <tr>
  <td colspan="7">


  <table style="width:100%!important;">
  <tr>
  <td style="width:20%!important;">
  <strong>Observation checklist</strong></td><td><?php if($rate_percentage!='') { ?>
  <div id="myProgress" style="border: 1px solid blue!important;">
  <div id="myBar" style="width:<?php echo $rate_percentage; ?>%!important; color: white!important; font-weight:bold!important;"><?php echo $rate_percentage."%"; ?></div>
  </div><?php } else { ?><div style="text-align:center; color: red!important; font-weight: bold!important;">No attempts yet made</div><?php } ?></td>
  </tr>
  </table>
  </td>
  </tr>
  <?php } ?>  
  
  
  
  
  <?php $ct++; unset($rate_arr); unset($rate_percentage); unset($rate); } ?>
  
  
</table>
<?php } else { echo '<div class="alert" style="margin-top:0px!important;">
  
  <p>You have not viewed your assesments yet!</p>
</div>'; }
         
  ?>
     
                    </td>
            </tr></table><br/>
    <?php  unset($context);  } else { } } ?>
  

 
 
            
    <?php
    
} ?>
  <br/>

</body>
</html>
<?php
} 
else  if($_POST['type']==3)
{
    ?>
<script> window.location.href="http://localhost/accit-moodle/accit/sms/sendsms.php?phone=<?php echo $_POST['phone']; ?>&name=<?php echo $_POST['name']; ?>"; </script>
<?php
}
else if($_POST['type']==4)
{
	
	$html_pdf = '<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Print - </title>
<style>
.htmltoimage{
	width:95%;
	margin:auto;
}
.customers {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    font-size: 12px;
}
.info {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 60%;
}

.customers td, #customers th {
    border: 1px solid #aaa;
    padding: 8px;
    font-size: 12px;
}
.info td, .info th {
    border: 1px solid #aaa;
    padding: 8px;
}

customers tr:nth-child(even){background-color: #f2f2f2; font-size: 11px; }

.customers tr:hover {background-color: #ddd; font-size: 11px;}

.customers th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
    font-size: 12px;
}


.info {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
    font-size: 12px;
}
.alert {
    padding-top: 4px;
    padding-bottom: 4px;
    text-align: left;
    color: black;
    font-size: 11px;
    border: 1px solid red;
    font-weight: bold;
    padding-left: 15px;
}
#myProgress {
  width: 100%;
  background-color: #ddd;
}

#myBar {

  height: 30px;
  background-color: #4CAF50;
  text-align: center;
  padding-top:5px;
}

html,body{font-family: Arial, Helvetica, sans-serif;font-size:12px;}

.w3-image{max-width:100%;height:auto}img{vertical-align:middle}a{color:inherit}
.w3-table,.w3-table-all{border-collapse:collapse;border-spacing:0;width:100%;display:table}.w3-table-all{border:1px solid #ccc}
.w3-bordered tr,.w3-table-all tr{border-bottom:1px solid #ddd}.w3-striped tbody tr:nth-child(even){background-color:#f1f1f1}
.w3-table-all tr:nth-child(odd){background-color:#fff}.w3-table-all tr:nth-child(even){background-color:#f1f1f1}
.w3-hoverable tbody tr:hover,.w3-ul.w3-hoverable li:hover{background-color:#ccc}.w3-centered tr th,.w3-centered tr td{text-align:center}
.w3-table td,.w3-table th,.w3-table-all td,.w3-table-all th{padding:8px 8px;display:table-cell;text-align:left;vertical-align:top}
.w3-table th:first-child,.w3-table td:first-child,.w3-table-all th:first-child,.w3-table-all td:first-child{padding-left:16px}
.w3-btn,.w3-button{border:none;display:inline-block;padding:8px 16px;vertical-align:middle;overflow:hidden;text-decoration:none;color:inherit;background-color:inherit;text-align:center;cursor:pointer;white-space:nowrap}
.w3-btn:hover{box-shadow:0 8px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19)}
.w3-btn,.w3-button{-webkit-touch-callout:none;-webkit-user-select:none;-khtml-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}   
.w3-disabled,.w3-btn:disabled,.w3-button:disabled{cursor:not-allowed;opacity:0.3}.w3-disabled *,:disabled *{pointer-events:none}
.w3-btn.w3-disabled:hover,.w3-btn:disabled:hover{box-shadow:none}
.w3-badge,.w3-tag{background-color:#000;color:#fff;display:inline-block;padding-left:8px;padding-right:8px;text-align:center}.w3-badge{border-radius:50%}
.w3-ul{list-style-type:none;padding:0;margin:0}.w3-ul li{padding:8px 16px;border-bottom:1px solid #ddd}.w3-ul li:last-child{border-bottom:none}
.w3-tooltip,.w3-display-container{position:relative}.w3-tooltip .w3-text{display:none}.w3-tooltip:hover .w3-text{display:inline-block}
.w3-ripple:active{opacity:0.5}.w3-ripple{transition:opacity 0s}
.w3-input{padding:8px;display:block;border:none;border-bottom:1px solid #ccc;width:100%}
.w3-select{padding:9px 0;width:100%;border:none;border-bottom:1px solid #ccc}
.w3-dropdown-click,.w3-dropdown-hover{position:relative;display:inline-block;cursor:pointer}
.w3-dropdown-hover:hover .w3-dropdown-content{display:block}
.w3-dropdown-hover:first-child,.w3-dropdown-click:hover{background-color:#ccc;color:#000}
.w3-dropdown-hover:hover > .w3-button:first-child,.w3-dropdown-click:hover > .w3-button:first-child{background-color:#ccc;color:#000}
.w3-dropdown-content{cursor:auto;color:#000;background-color:#fff;display:none;position:absolute;min-width:160px;margin:0;padding:0;z-index:1}
.w3-check,.w3-radio{width:24px;height:24px;position:relative;top:6px}
.w3-sidebar{height:100%;width:200px;background-color:#fff;position:fixed!important;z-index:1;overflow:auto}
.w3-bar-block .w3-dropdown-hover,.w3-bar-block .w3-dropdown-click{width:100%}
.w3-bar-block .w3-dropdown-hover .w3-dropdown-content,.w3-bar-block .w3-dropdown-click .w3-dropdown-content{min-width:100%}
.w3-bar-block .w3-dropdown-hover .w3-button,.w3-bar-block .w3-dropdown-click .w3-button{width:100%;text-align:left;padding:8px 16px}
.w3-main,#main{transition:margin-left .4s}
.w3-modal{z-index:3;display:none;padding-top:100px;position:fixed;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4)}
.w3-modal-content{margin:auto;background-color:#fff;position:relative;padding:0;outline:0;width:600px}
.w3-bar{width:100%;overflow:hidden}.w3-center .w3-bar{display:inline-block;width:auto}
.w3-bar .w3-bar-item{padding:8px 16px;float:left;width:auto;border:none;display:block;outline:0}
.w3-bar .w3-dropdown-hover,.w3-bar .w3-dropdown-click{position:static;float:left}
.w3-bar .w3-button{white-space:normal}
.w3-bar-block .w3-bar-item{width:100%;display:block;padding:8px 16px;text-align:left;border:none;white-space:normal;float:none;outline:0}
.w3-bar-block.w3-center .w3-bar-item{text-align:center}.w3-block{display:block;width:100%}
.w3-responsive{display:block;overflow-x:auto}
.w3-container:after,.w3-container:before,.w3-panel:after,.w3-panel:before,.w3-row:after,.w3-row:before,.w3-row-padding:after,.w3-row-padding:before,
.w3-cell-row:before,.w3-cell-row:after,.w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after{content:"";display:table;clear:both}
.w3-col,.w3-half,.w3-third,.w3-twothird,.w3-threequarter,.w3-quarter{float:left;width:100%}
.w3-col.s1{width:8.33333%}.w3-col.s2{width:16.66666%}.w3-col.s3{width:24.99999%}.w3-col.s4{width:33.33333%}
.w3-col.s5{width:41.66666%}.w3-col.s6{width:49.99999%}.w3-col.s7{width:58.33333%}.w3-col.s8{width:66.66666%}
.w3-col.s9{width:74.99999%}.w3-col.s10{width:83.33333%}.w3-col.s11{width:91.66666%}.w3-col.s12{width:99.99999%}
@media (min-width:601px){.w3-col.m1{width:8.33333%}.w3-col.m2{width:16.66666%}.w3-col.m3,.w3-quarter{width:24.99999%}.w3-col.m4,.w3-third{width:33.33333%}
.w3-col.m5{width:41.66666%}.w3-col.m6,.w3-half{width:49.99999%}.w3-col.m7{width:58.33333%}.w3-col.m8,.w3-twothird{width:66.66666%}
.w3-col.m9,.w3-threequarter{width:74.99999%}.w3-col.m10{width:83.33333%}.w3-col.m11{width:91.66666%}.w3-col.m12{width:99.99999%}}
@media (min-width:993px){.w3-col.l1{width:8.33333%}.w3-col.l2{width:16.66666%}.w3-col.l3{width:24.99999%}.w3-col.l4{width:33.33333%}
.w3-col.l5{width:41.66666%}.w3-col.l6{width:49.99999%}.w3-col.l7{width:58.33333%}.w3-col.l8{width:66.66666%}
.w3-col.l9{width:74.99999%}.w3-col.l10{width:83.33333%}.w3-col.l11{width:91.66666%}.w3-col.l12{width:99.99999%}}
.w3-rest{overflow:hidden}.w3-stretch{margin-left:-16px;margin-right:-16px}
.w3-content,.w3-auto{margin-left:auto;margin-right:auto}.w3-content{max-width:980px}.w3-auto{max-width:1140px}
.w3-cell-row{display:table;width:100%}.w3-cell{display:table-cell}
.w3-cell-top{vertical-align:top}.w3-cell-middle{vertical-align:middle}.w3-cell-bottom{vertical-align:bottom}
.w3-hide{display:none!important}.w3-show-block,.w3-show{display:block!important}.w3-show-inline-block{display:inline-block!important}
@media (max-width:1205px){.w3-auto{max-width:95%}}
@media (max-width:600px){.w3-modal-content{margin:0 10px;width:auto!important}.w3-modal{padding-top:30px}
.w3-dropdown-hover.w3-mobile .w3-dropdown-content,.w3-dropdown-click.w3-mobile .w3-dropdown-content{position:relative}	
.w3-hide-small{display:none!important}.w3-mobile{display:block;width:100%!important}.w3-bar-item.w3-mobile,.w3-dropdown-hover.w3-mobile,.w3-dropdown-click.w3-mobile{text-align:center}
.w3-dropdown-hover.w3-mobile,.w3-dropdown-hover.w3-mobile .w3-btn,.w3-dropdown-hover.w3-mobile .w3-button,.w3-dropdown-click.w3-mobile,.w3-dropdown-click.w3-mobile .w3-btn,.w3-dropdown-click.w3-mobile .w3-button{width:100%}}
@media (max-width:768px){.w3-modal-content{width:500px}.w3-modal{padding-top:50px}}
@media (min-width:993px){.w3-modal-content{width:900px}.w3-hide-large{display:none!important}.w3-sidebar.w3-collapse{display:block!important}}
@media (max-width:992px) and (min-width:601px){.w3-hide-medium{display:none!important}}
@media (max-width:992px){.w3-sidebar.w3-collapse{display:none}.w3-main{margin-left:0!important;margin-right:0!important}.w3-auto{max-width:100%}}
.w3-top,.w3-bottom{position:fixed;width:100%;z-index:1}.w3-top{top:0}.w3-bottom{bottom:0}
.w3-overlay{position:fixed;display:none;width:100%;height:100%;top:0;left:0;right:0;bottom:0;background-color:rgba(0,0,0,0.5);z-index:2}
.w3-display-topleft{position:absolute;left:0;top:0}.w3-display-topright{position:absolute;right:0;top:0}
.w3-display-bottomleft{position:absolute;left:0;bottom:0}.w3-display-bottomright{position:absolute;right:0;bottom:0}
.w3-display-middle{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%)}
.w3-display-left{position:absolute;top:50%;left:0%;transform:translate(0%,-50%);-ms-transform:translate(-0%,-50%)}
.w3-display-right{position:absolute;top:50%;right:0%;transform:translate(0%,-50%);-ms-transform:translate(0%,-50%)}
.w3-display-topmiddle{position:absolute;left:50%;top:0;transform:translate(-50%,0%);-ms-transform:translate(-50%,0%)}
.w3-display-bottommiddle{position:absolute;left:50%;bottom:0;transform:translate(-50%,0%);-ms-transform:translate(-50%,0%)}
.w3-display-container:hover .w3-display-hover{display:block}.w3-display-container:hover span.w3-display-hover{display:inline-block}.w3-display-hover{display:none}
.w3-display-position{position:absolute}
.w3-circle{border-radius:50%}
.w3-round-small{border-radius:2px}.w3-round,.w3-round-medium{border-radius:4px}.w3-round-large{border-radius:8px}.w3-round-xlarge{border-radius:16px}.w3-round-xxlarge{border-radius:32px}
.w3-row-padding,.w3-row-padding>.w3-half,.w3-row-padding>.w3-third,.w3-row-padding>.w3-twothird,.w3-row-padding>.w3-threequarter,.w3-row-padding>.w3-quarter,.w3-row-padding>.w3-col{padding:0 8px}
.w3-container,.w3-panel{padding:0.01em 16px}.w3-panel{margin-top:16px;margin-bottom:16px}
.w3-code,.w3-codespan{font-family:Consolas,"courier new";font-size:16px}
.w3-code{width:auto;background-color:#fff;padding:8px 12px;border-left:4px solid #4CAF50;word-wrap:break-word}
.w3-codespan{color:crimson;background-color:#f1f1f1;padding-left:4px;padding-right:4px;font-size:110%}
.w3-card,.w3-card-2{box-shadow:0 2px 5px 0 rgba(0,0,0,0.16),0 2px 10px 0 rgba(0,0,0,0.12)}
.w3-card-4,.w3-hover-shadow:hover{box-shadow:0 4px 10px 0 rgba(0,0,0,0.2),0 4px 20px 0 rgba(0,0,0,0.19)}
.w3-spin{animation:w3-spin 2s infinite linear}@keyframes w3-spin{0%{transform:rotate(0deg)}100%{transform:rotate(359deg)}}
.w3-animate-fading{animation:fading 10s infinite}@keyframes fading{0%{opacity:0}50%{opacity:1}100%{opacity:0}}
.w3-animate-opacity{animation:opac 0.8s}@keyframes opac{from{opacity:0} to{opacity:1}}
.w3-animate-top{position:relative;animation:animatetop 0.4s}@keyframes animatetop{from{top:-300px;opacity:0} to{top:0;opacity:1}}
.w3-animate-left{position:relative;animation:animateleft 0.4s}@keyframes animateleft{from{left:-300px;opacity:0} to{left:0;opacity:1}}
.w3-animate-right{position:relative;animation:animateright 0.4s}@keyframes animateright{from{right:-300px;opacity:0} to{right:0;opacity:1}}
.w3-animate-bottom{position:relative;animation:animatebottom 0.4s}@keyframes animatebottom{from{bottom:-300px;opacity:0} to{bottom:0;opacity:1}}
.w3-animate-zoom {animation:animatezoom 0.6s}@keyframes animatezoom{from{transform:scale(0)} to{transform:scale(1)}}
.w3-animate-input{transition:width 0.4s ease-in-out}.w3-animate-input:focus{width:100%!important}
.w3-opacity,.w3-hover-opacity:hover{opacity:0.60}.w3-opacity-off,.w3-hover-opacity-off:hover{opacity:1}
.w3-opacity-max{opacity:0.25}.w3-opacity-min{opacity:0.75}
.w3-greyscale-max,.w3-grayscale-max,.w3-hover-greyscale:hover,.w3-hover-grayscale:hover{filter:grayscale(100%)}
.w3-greyscale,.w3-grayscale{filter:grayscale(75%)}.w3-greyscale-min,.w3-grayscale-min{filter:grayscale(50%)}
.w3-sepia{filter:sepia(75%)}.w3-sepia-max,.w3-hover-sepia:hover{filter:sepia(100%)}.w3-sepia-min{filter:sepia(50%)}
.w3-tiny{font-size:10px!important}.w3-small{font-size:12px!important}.w3-medium{font-size:15px!important}.w3-large{font-size:18px!important}
.w3-xlarge{font-size:24px!important}.w3-xxlarge{font-size:36px!important}.w3-xxxlarge{font-size:48px!important}.w3-jumbo{font-size:64px!important}
.w3-left-align{text-align:left!important}.w3-right-align{text-align:right!important}.w3-justify{text-align:justify!important}.w3-center{text-align:center!important}
.w3-border-0{border:0!important}.w3-border{border:1px solid #ccc!important}
.w3-border-top{border-top:1px solid #ccc!important}.w3-border-bottom{border-bottom:1px solid #ccc!important}
.w3-border-left{border-left:1px solid #ccc!important}.w3-border-right{border-right:1px solid #ccc!important}
.w3-topbar{border-top:6px solid #ccc!important}.w3-bottombar{border-bottom:6px solid #ccc!important}
.w3-leftbar{border-left:6px solid #ccc!important}.w3-rightbar{border-right:6px solid #ccc!important}
.w3-section,.w3-code{margin-top:16px!important;margin-bottom:16px!important}
.w3-margin{margin:16px!important}.w3-margin-top{margin-top:16px!important}.w3-margin-bottom{margin-bottom:16px!important}
.w3-margin-left{margin-left:16px!important}.w3-margin-right{margin-right:16px!important}
.w3-padding-small{padding:4px 8px!important}.w3-padding{padding:8px 16px!important}.w3-padding-large{padding:12px 24px!important}
.w3-padding-16{padding-top:16px!important;padding-bottom:16px!important}.w3-padding-24{padding-top:24px!important;padding-bottom:24px!important}
.w3-padding-32{padding-top:32px!important;padding-bottom:32px!important}.w3-padding-48{padding-top:48px!important;padding-bottom:48px!important}
.w3-padding-64{padding-top:64px!important;padding-bottom:64px!important}
.w3-left{float:left!important}.w3-right{float:right!important}
.w3-button:hover{color:#000!important;background-color:#ccc!important}
.w3-transparent,.w3-hover-none:hover{background-color:transparent!important}
.w3-hover-none:hover{box-shadow:none!important}
/* Colors */
.w3-amber,.w3-hover-amber:hover{color:#000!important;background-color:#ffc107!important}
.w3-aqua,.w3-hover-aqua:hover{color:#000!important;background-color:#00ffff!important}
.w3-blue,.w3-hover-blue:hover{color:#fff!important;background-color:#2196F3!important}
.w3-light-blue,.w3-hover-light-blue:hover{color:#000!important;background-color:#87CEEB!important}
.w3-brown,.w3-hover-brown:hover{color:#fff!important;background-color:#795548!important}
.w3-cyan,.w3-hover-cyan:hover{color:#000!important;background-color:#00bcd4!important}
.w3-blue-grey,.w3-hover-blue-grey:hover,.w3-blue-gray,.w3-hover-blue-gray:hover{color:#fff!important;background-color:#607d8b!important}
.w3-green,.w3-hover-green:hover{color:#fff!important;background-color:#4CAF50!important}
.w3-light-green,.w3-hover-light-green:hover{color:#000!important;background-color:#8bc34a!important}
.w3-indigo,.w3-hover-indigo:hover{color:#fff!important;background-color:#3f51b5!important}
.w3-khaki,.w3-hover-khaki:hover{color:#000!important;background-color:#f0e68c!important}
.w3-lime,.w3-hover-lime:hover{color:#000!important;background-color:#cddc39!important}
.w3-orange,.w3-hover-orange:hover{color:#000!important;background-color:#ff9800!important}
.w3-deep-orange,.w3-hover-deep-orange:hover{color:#fff!important;background-color:#ff5722!important}
.w3-pink,.w3-hover-pink:hover{color:#fff!important;background-color:#e91e63!important}
.w3-purple,.w3-hover-purple:hover{color:#fff!important;background-color:#9c27b0!important}
.w3-deep-purple,.w3-hover-deep-purple:hover{color:#fff!important;background-color:#673ab7!important}
.w3-red,.w3-hover-red:hover{color:#fff!important;background-color:#f44336!important}
.w3-sand,.w3-hover-sand:hover{color:#000!important;background-color:#fdf5e6!important}
.w3-teal,.w3-hover-teal:hover{color:#fff!important;background-color:#009688!important}
.w3-yellow,.w3-hover-yellow:hover{color:#000!important;background-color:#ffeb3b!important}
.w3-white,.w3-hover-white:hover{color:#000!important;background-color:#fff!important}
.w3-black,.w3-hover-black:hover{color:#fff!important;background-color:#000!important}
.w3-grey,.w3-hover-grey:hover,.w3-gray,.w3-hover-gray:hover{color:#000!important;background-color:#9e9e9e!important}
.w3-light-grey,.w3-hover-light-grey:hover,.w3-light-gray,.w3-hover-light-gray:hover{color:#000!important;background-color:#f1f1f1!important}
.w3-dark-grey,.w3-hover-dark-grey:hover,.w3-dark-gray,.w3-hover-dark-gray:hover{color:#fff!important;background-color:#616161!important}
.w3-pale-red,.w3-hover-pale-red:hover{color:#000!important;background-color:#ffdddd!important}
.w3-pale-green,.w3-hover-pale-green:hover{color:#000!important;background-color:#ddffdd!important}
.w3-pale-yellow,.w3-hover-pale-yellow:hover{color:#000!important;background-color:#ffffcc!important}
.w3-pale-blue,.w3-hover-pale-blue:hover{color:#000!important;background-color:#ddffff!important}
.w3-text-amber,.w3-hover-text-amber:hover{color:#ffc107!important}
.w3-text-aqua,.w3-hover-text-aqua:hover{color:#00ffff!important}
.w3-text-blue,.w3-hover-text-blue:hover{color:#2196F3!important}
.w3-text-light-blue,.w3-hover-text-light-blue:hover{color:#87CEEB!important}
.w3-text-brown,.w3-hover-text-brown:hover{color:#795548!important}
.w3-text-cyan,.w3-hover-text-cyan:hover{color:#00bcd4!important}
.w3-text-blue-grey,.w3-hover-text-blue-grey:hover,.w3-text-blue-gray,.w3-hover-text-blue-gray:hover{color:#607d8b!important}
.w3-text-green,.w3-hover-text-green:hover{color:#4CAF50!important}
.w3-text-light-green,.w3-hover-text-light-green:hover{color:#8bc34a!important}
.w3-text-indigo,.w3-hover-text-indigo:hover{color:#3f51b5!important}
.w3-text-khaki,.w3-hover-text-khaki:hover{color:#b4aa50!important}
.w3-text-lime,.w3-hover-text-lime:hover{color:#cddc39!important}
.w3-text-orange,.w3-hover-text-orange:hover{color:#ff9800!important}
.w3-text-deep-orange,.w3-hover-text-deep-orange:hover{color:#ff5722!important}
.w3-text-pink,.w3-hover-text-pink:hover{color:#e91e63!important}
.w3-text-purple,.w3-hover-text-purple:hover{color:#9c27b0!important}
.w3-text-deep-purple,.w3-hover-text-deep-purple:hover{color:#673ab7!important}
.w3-text-red,.w3-hover-text-red:hover{color:#f44336!important}
.w3-text-sand,.w3-hover-text-sand:hover{color:#fdf5e6!important}
.w3-text-teal,.w3-hover-text-teal:hover{color:#009688!important}
.w3-text-yellow,.w3-hover-text-yellow:hover{color:#d2be0e!important}
.w3-text-white,.w3-hover-text-white:hover{color:#fff!important}
.w3-text-black,.w3-hover-text-black:hover{color:#000!important}
.w3-text-grey,.w3-hover-text-grey:hover,.w3-text-gray,.w3-hover-text-gray:hover{color:#757575!important}
.w3-text-light-grey,.w3-hover-text-light-grey:hover,.w3-text-light-gray,.w3-hover-text-light-gray:hover{color:#f1f1f1!important}
.w3-text-dark-grey,.w3-hover-text-dark-grey:hover,.w3-text-dark-gray,.w3-hover-text-dark-gray:hover{color:#3a3a3a!important}
.w3-border-amber,.w3-hover-border-amber:hover{border-color:#ffc107!important}
.w3-border-aqua,.w3-hover-border-aqua:hover{border-color:#00ffff!important}
.w3-border-blue,.w3-hover-border-blue:hover{border-color:#2196F3!important}
.w3-border-light-blue,.w3-hover-border-light-blue:hover{border-color:#87CEEB!important}
.w3-border-brown,.w3-hover-border-brown:hover{border-color:#795548!important}
.w3-border-cyan,.w3-hover-border-cyan:hover{border-color:#00bcd4!important}
.w3-border-blue-grey,.w3-hover-border-blue-grey:hover,.w3-border-blue-gray,.w3-hover-border-blue-gray:hover{border-color:#607d8b!important}
.w3-border-green,.w3-hover-border-green:hover{border-color:#4CAF50!important}
.w3-border-light-green,.w3-hover-border-light-green:hover{border-color:#8bc34a!important}
.w3-border-indigo,.w3-hover-border-indigo:hover{border-color:#3f51b5!important}
.w3-border-khaki,.w3-hover-border-khaki:hover{border-color:#f0e68c!important}
.w3-border-lime,.w3-hover-border-lime:hover{border-color:#cddc39!important}
.w3-border-orange,.w3-hover-border-orange:hover{border-color:#ff9800!important}
.w3-border-deep-orange,.w3-hover-border-deep-orange:hover{border-color:#ff5722!important}
.w3-border-pink,.w3-hover-border-pink:hover{border-color:#e91e63!important}
.w3-border-purple,.w3-hover-border-purple:hover{border-color:#9c27b0!important}
.w3-border-deep-purple,.w3-hover-border-deep-purple:hover{border-color:#673ab7!important}
.w3-border-red,.w3-hover-border-red:hover{border-color:#f44336!important}
.w3-border-sand,.w3-hover-border-sand:hover{border-color:#fdf5e6!important}
.w3-border-teal,.w3-hover-border-teal:hover{border-color:#009688!important}
.w3-border-yellow,.w3-hover-border-yellow:hover{border-color:#ffeb3b!important}
.w3-border-white,.w3-hover-border-white:hover{border-color:#fff!important}
.w3-border-black,.w3-hover-border-black:hover{border-color:#000!important}
.w3-border-grey,.w3-hover-border-grey:hover,.w3-border-gray,.w3-hover-border-gray:hover{border-color:#9e9e9e!important}
.w3-border-light-grey,.w3-hover-border-light-grey:hover,.w3-border-light-gray,.w3-hover-border-light-gray:hover{border-color:#f1f1f1!important}
.w3-border-dark-grey,.w3-hover-border-dark-grey:hover,.w3-border-dark-gray,.w3-hover-border-dark-gray:hover{border-color:#616161!important}
.w3-border-pale-red,.w3-hover-border-pale-red:hover{border-color:#ffe7e7!important}.w3-border-pale-green,.w3-hover-border-pale-green:hover{border-color:#e7ffe7!important}
.w3-border-pale-yellow,.w3-hover-border-pale-yellow:hover{border-color:#ffffcc!important}.w3-border-pale-blue,.w3-hover-border-pale-blue:hover{border-color:#e7ffff!important}
</style>
</head>
<body>
<div id="htmltoimage"><br>
 <table style="width: 99%; border: 1px solid white; float: left;" cellpadding="1" cellspacing="1">
         <tr>
             <td align="center"> <div style="float: center;"><img src="" boder="0"></div></td>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
    </table>
     <table style="width: 99%;
	border: 1px solid blue;
	padding-left: 2px;
	margin-left: 6px;
	position: relative;
	left: 50%;
	top: 63%;
	transform: translate(-50%,0%);" cellpadding="4" cellspacing="4">
         
         <tr style="background-color: #ddd;">
             <td><strong>Name :</strong></td>
             <td>'; $html_pdf = $html_pdf.$userdetails->firstname." ".$userdetails->lastname; 
             $html_pdf = $html_pdf.'</td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Username :</strong></td>
             <td>'; $html_pdf = $html_pdf.$userdetails->username; $html_pdf=$html_pdf.'</td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Email :</strong></td>
             <td>'; $html_pdf = $html_pdf.$userdetails->email; $html_pdf=$html_pdf.'</td>
         </tr>
         <tr style="background-color: #ddd;">
             <td><strong>Phone :</strong></td>
             <td>'; 
if($userdetails->phone1!='') 
    
    { 
        $html_pdf = $html_pdf.$userdetails->phone1; 
    
    } 
else if($userdetails->phone2!='') 
    { 
    $html_pdf = $html_pdf.$userdetails->phone2;
    }
    else { $html_pdf = $html_pdf.'NA'; } 


$html_pdf = $html_pdf.'</td>
         </tr>
     </table><br/>';
$html_pdf = $html_pdf.'<table style="margin-top: 19px; width: 99%; border: 1px solid white; float: left; padding-left: 2px; margin-left: 6px;" cellpadding="4" cellspacing="4">
        <tr>
            <td><div style="float: right; margin-right: 3px;"><i>Generated on '.@date("F j, Y, g:i a").'</i></div></td>
        </tr>
    </table>';
$c=0; 



//ADDED FOR QUALIFICATION GROUPING

foreach($_SESSION['data_all'] as $kk=>$vv)
				{
					
					if(isset($_POST['select_course']) && count($_POST['select_course'])>0 && in_array($vv[2]->id,$_POST['select_course'])==true) 
					{
						
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",$vv[0]);
					
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
				
				}
				else if(count($_POST['select_course'])==0)
				{
					
					if(@date('Y',$vv[2]->startdate)>2016)
					{
					$c_name_arr = explode(" ",$vv[0]);
					
					
					if(stristr($c_name_arr[0],'CEB')==true )
					{
						$data_all_new['BSB30220 Certificate III in Entrepreneurship and New Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CB')==true )
					{
						$data_all_new['BSB40120 Certificate IV in Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DB')==true || stristr($c_name_arr[0],'ADB')==true)
					{
						$substr = substr($c_name_arr[0],1,2);
						if($substr=='DB')
						{
							$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
						}
						else
						{
							$data_all_new['BSB50120 Diploma of Business (Business Development)'][]=$vv;
						}
						unset($substr);
					}
					
					 /* if(stristr($c_name_arr[0],'ADB')==true)
					{
						$data_all_new['BSB60120 Advanced Diploma of Business'][]=$vv;
					} */
					
				/*	if(stristr($c_name_arr[0],'DT')==true)
					{
						$data_all_new['DT'][]=$vv;
					} */
					if(stristr($c_name_arr[0],'CPD')==true )
					{
						$data_all_new['CPC30620 Certificate III in Painting and Decorating'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CMB')==true )
					{
						$data_all_new['BSB30315 - Certificate III in Micro Business Operations'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'CIT')==true)
					{
						$data_all_new['BSB41115 Certificate IV in International Trade'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIB')==true)
					{
						$data_all_new['BSB50815 Diploma of International Business'][]=$vv;
					}
					
					if(stristr($c_name_arr[0],'DIT')==true)
					{
						$data_all_new['ICT50220 - Diploma of Information Technology'][]=$vv;
					}
					
					unset($c_name_arr);
				}
				else
					
					{
						$data_all_new['NA'][]=$vv;
					}
					
					
				}
				else
				{
				}
				
				}
				
	
	
	
	
	
				
				// END
				
				foreach($data_all_new as $mm=>$pp) 
	{ 
	
	if($mm!='NA') { 
	
		
	$html_pdf = $html_pdf.'<br/><br/><div style="border: 1px solid black;
    border-radius: 9px;
    height: 30px;

    font-size: 19px;
    background-color: orange;
    color: white;
    padding: 2px 6px 6px 6px; margin-top: 7px;">'.$mm.'</div>';
	
 } else {
	$html_pdf = $html_pdf.'<br><br/><div style="border: 1px solid black;
    border-radius: 9px;
    height: 30px;

    font-size: 19px;
    background-color: red;
    color: white;
    padding: 2px 6px 6px 6px; margin-top: 7px;">
	
	&nbsp;&nbsp;No Qualification Found</div>';
	
	} 
	
	
foreach($pp as $key=>$val)  
{ 

$c++; 
$startdate = $val[2]->startdate;


    if(count($_POST['select_course'])>0 && in_array($val[2]->id,$_POST['select_course'])==false) { $display = 'style="display: none;"'; } else { $display = ''; }
  $html_pdf = $html_pdf.'<table  id="row'.$c.'" style="width: 100%; font-family: Arial, Helvetica, sans-serif!important;"><tr '.$display.'><td><div ';
  
  if($val[2]->category==97 || $val[2]->category==98 || $val[2]->category==104 || $val[2]->category==111 || $val[2]->category==112) { $html_pdf= $html_pdf.' style="color: red!important;font-size: 14px!important;" '; } else { $html_pdf = $html_pdf.' style="color: black; font-size: 14px!important;" '; } $html_pdf= $html_pdf.'>';
  
  $html_pdf = $html_pdf.'<p>'.$val[0].'</p>
</div></td>
            </tr>
            <tr '.$display.'><td>';   
         
         


$contextid = get_context_instance(CONTEXT_COURSE, $val[2]->id);
     
   $sql_all =   'SELECT z.id as rowid , z.status as status , z.timemodified as activitydate , gg.usermodified , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted2 , z.userid , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname, ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademin,gi.grademax,gi.scaleid , y.username , ag.id as itemidother '
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '                      
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
 
   
    $sql =    'SELECT z.id as rowid , z.status as status , gg.usermodified , z.userid , z.timemodified as activitydate , gg.feedback , gg.timecreated as feedbackposted1 , gg.timemodified as feedbackposted1 , y.firstname , y.lastname , ag.grade as recorded_grade , gi.iteminstance as assignmentid , ag.timemodified, ax.name as assignmentname,  ax.id as assignmentnameid , '
            . '  gi.courseid , gi.gradetype, gi.grademax,gi.grademin,gi.scaleid , y.username , ag.id as itemidother'
            . ' FROM {assign_submission} as z '
            . ' LEFT JOIN {grade_items} as gi ON  gi.iteminstance = z.assignment AND gi.itemname IS NOT NULL AND gi.itemmodule = "assign" '
               . ' LEFT JOIN {assign_grades} as ag ON  ag.assignment = z.assignment AND ag.userid = z.userid AND ag.userid = z.userid AND ag.timemodified = (SELECT max(`timemodified`) FROM {assign_grades} WHERE `assignment` = z.assignment AND `userid` = z.userid)'
            .' LEFT JOIN {grade_grades} as gg ON gg.itemid = gi.id AND gg.userid = z.userid  '
           . ' LEFT JOIN {assign} as ax ON  ax.id = z.assignment '
            .' LEFT JOIN {user} as y ON z.userid = y.id '
                 
            .' WHERE z.userid = '.$userdetails->id.' AND ax.course = '.$val[2]->id.' and z.timemodified = (SELECT max(`timemodified`) FROM {assign_submission} WHERE `assignment` = ax.id AND `userid` = gg.userid) GROUP BY z.assignment , z.userid ';
    
    
    if($sql!='' && $sql_all!='')
{
//echo $sql; die;
$sql_scale = "SELECT * FROM {scale}";
$list_scale = $DB->get_records_sql($sql_scale);
$scale_array = array();
foreach($list_scale as $key=>$val)
{
    $scale_explode_array = explode(",",$val->scale);
    for($j=0;$j<count($scale_explode_array);$j++)
    {
        $scale_array[$key][$j+1]=$scale_explode_array[$j];
    }
    unset($scale_explode_array);
}


$list = $DB->get_records_sql($sql);

$arr = array();
//echo '<pre>';
//print_r($list); 
foreach($list as $list)
{
    $sql_module = "SELECT id FROM {course_modules} WHERE course = '".$list->courseid."' AND instance = '".$list->assignmentid."'";
    $list_module = $DB->get_record_sql($sql_module);
    if($list->scaleid>0 && $list->gradetype!=1)
    {
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        
        
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
           $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
            unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            @$scale_text = $scale_array[$list->scaleid][$grade_val];
            if(@$scale_text=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);   
            unset($activitydate);          
    }
}
else if($list->scaleid=='' && $list->gradetype==1)
{
    if(@$contextid->id!='' && @$contextid->id>0)
    {
        $sql_role = "SELECT roleid FROM {role_assignments} WHERE contextid = '".$contextid->id."' AND userid = '".$list->userid."'";
        $list_all_stu = $DB->get_record_sql($sql_role);
        if($list_all_stu->roleid!='' && $list_all_stu->roleid==5)
        {
            $grade_val = intval($list->recorded_grade); 
            
            if($grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $grade_exists = '';
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);
            unset($list_all_stu);
            unset($activitydate);          
        }
    }
    else
    {
            $grade_val = intval($list->recorded_grade); 
            
            if(@$grade_val=='')
            {
                @$scale_text="NA / Not yet graded";
            }
            else
            {
                @$scale_text=$grade_val;
                if($list->feedback!='')
                {
                    @$scale_text = @$scale_text." - ".strip_tags($list->feedback);
                }
            }
            if($list->timemodified=='')
            {
                $timemodified = "NA";
            }
            else
            {
                $timemodified = @date("F j, Y, g:i a",$list->timemodified);
            }
            $activitydate = @date("F j, Y, g:i a",$list->activitydate);
            $grade_exists = '';
            $arr[] = array('baseurl'=>$CFG->wwwroot,'rowid'=>$list->rowid,'userid'=>$list->userid, 'name'=>$list->firstname.' '.$list->lastname, 'username'=>$list->username , 'assignmentname'=>$list->assignmentname,'assignmentid'=>$list->assignmentid,'grademin'=>$list->grademin,'grademax'=>$list->grademax,'timemodified'=>$timemodified,'timemodifiedg'=>'','gradeexists'=>$grade_exists,"result"=>$scale_text,"status"=>$list->status,"feedback"=>$list->feedback,"itemidother"=>$list->itemidother,"feedbackposted1"=>$list->feedbackposted1,"feedbackposted2"=>$list->feedbackposted2,"moduleid"=>$list_module->id,'activitydate'=>$activitydate);
           unset($grade_val);
            unset($scale_text);
            unset($timemodified);           
            unset($activitydate);           
    }
}
else
{
    
}
}
}
if(isset($arr))
{
    $list_all_count = count($arr);
    
}


if($list_all_count>0) {
    
    
         
   if(date('Y',$startdate)>2016) {

         $html_pdf = $html_pdf.'<table class="customers" '.$display.'>
  <tr>
    <th>Assignment</th>
    <th>Submission Status</th>
    <th>Last Updated On</th>
    <th>Graded On</th>
    <th>Result</th>
  
   </tr>'; } else {
  
           $html_pdf = $html_pdf.'<table class="customers" '.$display.'>
  <tr>
    <th>Assignment</th>
	
    <th>Submission Status</th>
    
    <th>Result</th>
    
  </tr>';
  
   }
  $cn = 0; foreach($arr as $key=>$val2) { 
        $rate = getObservationChecklistRating($list->userid,$list->courseid,$val2['assignmentname']);
	if($rate!='')
	{
		$rate_percentage = $rate;
	}
	else
	{
		$rate_percentage = '';
	}
      $cn++;
  $html_pdf = $html_pdf.'<tr>
      <td>'.$val2['assignmentname'].'</td>
    <td>';
  if(strtolower($val2['status'])=="new") { $html_pdf = $html_pdf.'No Submission'; } else { $html_pdf=$html_pdf.$val2['status']; } 
  $html_pdf = $html_pdf.'</td>';
  if(date('Y',$startdate)>2016) {
  
    $html_pdf = $html_pdf.'<td>'.$val2['activitydate'].'</td>
    <td>'.$val2['timemodified'].'</td>';
  }
  
 $html_pdf = $html_pdf.'<td>'.$val2['result'].'</td></tr>
    <tr><td colspan="6"><br/>Feedback:';
        
      
          if($val2['feedback']!='') { $html_pdf=$html_pdf.strip_tags($val2['feedback']); } else { $html_pdf=$html_pdf.'No Feedback found!'; }
          
         
          
       
 
        
       $html_pdf = $html_pdf.'<br/></td>
  </tr>';
   } 
  
  
  
   if($rate_percentage!='NA') { 
  
  
  $sql_checklist2 = "SELECT mcl.`id` as assignid , mcl.`course` , mcl.`name` 
	, mcm.id as checklistid
	FROM `mdl_checklist` as mcl 
	LEFT JOIN `mdl_course_modules` as mcm
	ON mcm.`instance` = mcl.`id` AND mcm.`course` = mcl.`course` AND mcm.`module` = '31'
	WHERE mcl.`course` = '".$list->courseid."' AND mcl.`name` LIKE '%".mysqli_real_escape_string($val2['assignmentname'])." | Observation Checklist%'";

	$list_all_deb2 = $DB->get_record_sql($sql_checklist2);
  

  
  

  $html_pdf = $html_pdf.'<tr>
  <td colspan="7">


  <table style="width:100%!important;">
  <tr>
  <td style="width:20%!important;">
  <strong>Observation checklist</strong></td><td>';
  
  if($rate_percentage!='') { 
  $html_pdf = $html_pdf.'<div id="myProgress" style="border: 1px solid blue!important;">
  <div id="myBar" style="width: '.$rate_percentage.';%!important; color: white!important; font-weight:bold!important;">'.$rate_percentage.'%</div>
  </div>';
  
   } else { 
   
   $html_pdf = $html_pdf.'<div style="text-align:center; color: red!important; font-weight: bold!important;">No attempts yet made</div>';
   
    } 
	$html_pdf = $html_pdf.'</td>
  </tr>
  </table>
  </td>
  </tr>';
   } 
  
  
  
  
  
  
  
  
  
  
  
$html_pdf = $html_pdf.'</table>';
 } else { $html_pdf = $html_pdf.'<div class="alert">  
  You have not viewed your assesments yet!
</div>'; 
 
 }
         
  
     
  $html_pdf = $html_pdf.'</td>
            </tr></table><br/>';
	unset($context);  } }
  
$html_pdf = $html_pdf.'</div>
</body>
</html>';
//echo $html_pdf;
//die;

$filename = $userdetails->firstname.'-'.$userdetails->lastname.".doc"; 
//$filename = 'demo.doc';
header("Content-Type: application/force-download");
header( "Content-Disposition: attachment; filename=".basename($filename));
header( "Content-Description: File Transfer");
@readfile($filename);
ob_clean();
flush();
readfile( $filename );
echo $html_pdf; 

    
}
else
{
}
}
?>