#!/usr/bin/php -q
<?php

require_once("../inc/ping.inc.php");

$archive_before_x_days = 1/4;

$ip = "87.67.142.42";

echo $ip;
var_dump(test_ip($ip,5));

?>