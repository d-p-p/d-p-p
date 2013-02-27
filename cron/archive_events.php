#!/usr/bin/php -q
<?php

require_once("/liz/inc/ping.inc.php");

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{	
	$qu = "SELECT * FROM dpp.sources WHERE time<".(mktime()-round($days*86400))." ORDER BY time ASC";
	$qu_exec = mysql_query($qu,$conn);
	
	for ($i = 0; $i < mysql_num_rows($qu_exec); $i++)
	{
		$ev = mysql_fetch_array($qu_exec);
		archive_source($ev,$conn);
	}


	$qu = "SELECT * FROM dpp.events WHERE time<".(mktime()-round($days*86400))." AND status=1 ORDER BY time ASC";
	$qu_exec = mysql_query($qu,$conn);
	
	for ($i = 0; $i < mysql_num_rows($qu_exec); $i++)
	{
		$ev = mysql_fetch_array($qu_exec);
		archive_event($ev,$conn);
	}
	
	if ($clear = mysql_query("DELETE FROM dpp.events WHERE time<".(mktime()-round($days*86400))." AND status=0",$conn))
	{	/*echo "\n(".mysql_affected_rows().") incomplete event(s) removed from events table\n";*/ }




	
//	if ($clear = mysql_query("DELETE FROM dpp.sources WHERE time<".(mktime()-round($days*86400)),$conn))
//	{	echo "\n(".mysql_affected_rows().") old source(s) removed from sources table\n"; }
}

?>