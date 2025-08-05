<html>
     <title></title>
    <head>
       
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script type="text/javascript">
$(window).load(function() {
    $(".loader").delay('2500').fadeOut("slow");
    var url = "http://moodle.accit.nsw.edu.au/parser/parser1.php";
    $(location).attr('href',url);
});
</script>
<style>
    .loader {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background: url('page-loader.gif') 50% 50% no-repeat rgb(249,249,249);
    opacity: .8;
}
</style>
    </head>
    <center>
        <body>
        <div class="loader"></div>
        </body>
    </center>
</html>