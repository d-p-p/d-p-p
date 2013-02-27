<?php 
if ((!empty($_SERVER['HTTP_ACCEPT_ENCODING'])) && (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'],"gzip")))
{ ob_start("ob_gzhandler"); } else { ob_start(); }

header("Content-Type: text/javascript");
header("Content-Disposition: filename=\"x.js\"");

echo file_get_contents("x_js.js");

?>