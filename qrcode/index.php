<html>
<head>
<style>
.button {
  background-color: #4CAF50;
  border: none;
  color: white;
  padding: 10px 17px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
}
</style>
</head>
<title>ACCIT - QR Code Generator</title>
<body style="margin-left:20%; margin-right:20%;">
<?php    
/*
 * PHP QR Code encoder
 *
 * Exemplatory usage
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
    
    echo "<h1>QR Code Generator</h1><hr/>";

    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include "qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    $filename = $PNG_TEMP_DIR.'qrcode.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


    if (isset($_REQUEST['data'])) { 
    
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');
            
        // user data
        $filename = $PNG_TEMP_DIR.'qrcode'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    } else {    
    
        //default data
    //    echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';    
  //      QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    
        
    //display generated file
 if (isset($_REQUEST['data'])) {    echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" />';
	 echo '&nbsp;&nbsp;<br/><a href="'.$PNG_WEB_DIR.basename($filename).'" target="_blank">View and Save</a>';
 echo '<hr/>'; } 
   
    //config form
    echo '<form action="index.php" method="post">
       <br/>
<table><tr><td>
	   URL / Data / Any :&nbsp;</td><td><textarea rows="11" cols="62" name="data" >'.(isset($_REQUEST['data'])?htmlspecialchars($_REQUEST['data']):'').'</textarea>&nbsp;</td>
       <tr>
        <td>Size:</td><td>&nbsp;<select name="size" style="width:100px;">';
        
    for($i=1;$i<=10;$i++)
        echo '<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.$i.'</option>';
        
    echo '</select>&nbsp;</td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
       <tr><td colspan="2"> <input type="submit" value="GENERATE" class="button"></td></tr></table>
		<input type="hidden" value="H" class="button" name="level" id="level" />
		</form><hr/>';
        
    // benchmark
    //QRtools::timeBenchmark();    

    ?>
	</body></html>