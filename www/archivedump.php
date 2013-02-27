<?php

if ($conn = @mysql_connect("localhost","dpp","dpp")) {
	
	$events = mysql_query("select if(strcmp(src_country,dest_country),src_country,dest_country) country1, if(strcmp(src_country,dest_country),dest_country,src_country) country2, ping_array from dpp.events_archive where src_country != 'xx' and dest_country != 'xx' and  src_country != '' and dest_country != '' order by country1, country2 limit 100",$conn);
	
    echo "country1,country2,ping1,ping2,ping3,ping4,ping5\n";

	for ($i = 0; $i < mysql_num_rows($events); $i++) {
		$event = mysql_fetch_array($events);
		
		echo $event['country1'].','.$event['country2'].','.$event['ping_array']."\n";
	}
}
else {
	echo "error";
}
?>
