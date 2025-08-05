<?php 
/*
PHP script to send SMS messages with the HTTP API of Diafaan SMS Server
 
Created:       26-10-2015
Last modified: 26-10-2015
*/
ini_set('display_errors', '1');
function SendMessage($host, $port, $userName, $password, $number, $message)
{
    /* Create a TCP/IP socket. */
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        return "socket_create() failed: reason: " . socket_strerror(socket_last_error());
    }
 
    /* Make a connection to the Diafaan SMS Server host */
    $result = socket_connect($socket, $host, $port);
    if ($result === false) {
        return "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
    }
 
    /* Create the HTTP API query string */
    $query = '/http/send-message/';
    $query .= '?username='.urlencode($userName);
    $query .= '&password='.urlencode($password);
    $query .= '&to='.urlencode($number);
    $query .= '&message='.urlencode($message);
     
    /* Send the HTTP GET request */
    $in = "GET ".$query." HTTP/1.1\r\n";
    $in .= "Host: www.myhost.com\r\n";
    $in .= "Connection: Close\r\n\r\n";
    $out = '';
    socket_write($socket, $in, strlen($in));
 
    /* Get the HTTP response */
    $out = '';
    while ($buffer = socket_read($socket, 2048)) {
        $out = $out.$buffer;
    }
    socket_close($socket);
 
    /* Extract the last line of the HTTP response to filter out the HTTP header and get the send result*/
    $lines = explode("\n", $out);  
    return end($lines);  
}
if(isset($_POST['submit']) && $_POST['submit']==1)
{
    $host = '14.200.209.30';
    $port = '9001';
    $userName = 'admin';
    $password = 'admin123';
    $number = $_POST['number'];
    $message = $_POST['message'];
    if(SendMessage($host, $port, $userName, $password, $number, $message))
    {
        echo 'Successfully Sent!';
    }
}
?>
<html>
    <head>
        <title>
            SEND SMS
        </title>
    </head>
    <center>
        <body>
            <form action="<?php echo $CFG->wwwroot; ?>/sms/sendsmsnow.php" name="f1" id="f1" method="post">
            <table>
                <tr>
                    <td>Enter number</td>
                    <td><input type="text" name="number" id="number" value="" /></td>
                </tr>
                <tr>
                    <td>Enter Message</td>
                    <td><textarea name="message" id="message" cols="22" rows="5"></textarea></td>
                </tr>
                 <tr>                  
                    <td colspan="2" align="center">&nbsp;</td>
                </tr>
                <tr>                  
                    <td colspan="2" align="center"><input type="submit" name="submitbutton" id="submitbutton" value=" Send " /></td>
                </tr>
            </table>
                <input type="hidden" name="submit" id="submit" value="1" />
            </form>
        </body>
    </center>
</html>