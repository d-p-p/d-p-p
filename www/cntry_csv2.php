#!/usr/bin/php -q
<?php

$c = json_decode(file_get_contents("/liz/data/countries.json"),TRUE);

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{	
	$qu = "SELECT * FROM dpp.compilations WHERE tag='countries' AND meta=''";
	
	$get_empty = mysql_query($qu,$conn);
	
	for ($i = 0; $i < mysql_num_rows($get_empty); $i++)
	{
		$ev = mysql_fetch_array($get_empty);
		
		$abbr_a = substr($ev['name'],0,2);
		$abbr_b = substr($ev['name'],3,2);
		
		$wr = "UPDATE dpp.compilations SET meta='{$c[$abbr_a]['lat']},{$c[$abbr_a]['lng']}_{$c[$abbr_b]['lat']},{$c[$abbr_b]['lng']}' WHERE tag='countries' AND name='{$ev['name']}'";
		
		if (mysql_query($wr,$conn))
		{	echo "\n\n{$abbr_a}: {$c[$abbr_a]['lat']},{$c[$abbr_a]['lng']}"
				."\n{$abbr_b}: {$c[$abbr_b]['lat']},{$c[$abbr_b]['lng']}";
		}
	}	
}

?>