#!/usr/bin/php -q
<?php

require_once("/liz/inc/vars.inc.php");

$tm_min = (strtotime(substr(date("c"),0,11)."00:00:00-07:00")-(60*60*24));
$tm_max = strtotime(substr(date("c"),0,11)."00:00:00-07:00");

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{
	$qu = "SELECT COUNT(*) AS cnt FROM dpp.events_archive WHERE time>={$tm_min} AND time<{$tm_max}";
	die($qu."\n");
}

?>