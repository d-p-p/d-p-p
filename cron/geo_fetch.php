#!/usr/bin/php -q
<?php

require_once("/liz/inc/ping.inc.php");

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{	
	$cnt = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM dpp.sources WHERE ((geo_lat='0' AND geo_lng='0') OR geo_lat='' OR geo_lng='' OR country='xx') AND country!='A1'",$conn));
	
	if ($cnt['cnt'] < 30) { $reps = $cnt['cnt']; } else { $reps = 30; }
//	echo "\naddresses to be checked in this run: {$reps}";
	for ($i = 0; $i < $reps; $i++)
	{	//echo " - ";
		set_wait(1/5);
		$ev = mysql_fetch_array(mysql_query("SELECT * FROM dpp.sources WHERE ((geo_lat='0' AND geo_lng='0') OR geo_lat='' OR geo_lng='' OR country='xx') AND country!='A1' ORDER BY rand() LIMIT 1",$conn));		
		get_geo($ev['ip'],$conn);
		unset($ev);
	}
	//echo "\n";

}

?>