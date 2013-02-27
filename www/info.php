<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<title>Distributed Ping Project</title>

<style>
	body { font-family:courier; }
	td { padding:4px; border:solid 1px gray; }
	
</style>
</head> 

<body>
<?php

require_once("/liz/inc/vars.inc.php");

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{
	$ips = mysql_query("SELECT * FROM dpp.sources WHERE timeouts<=-{$limit} and fails<{$fails} AND time>=".($tm-round($days*86400))." ORDER BY time DESC",$conn);


	$cnt = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM dpp.events WHERE status=1 AND time>=".($tm-60*5),$conn));
	
	$cnt_srcs = mysql_query("SELECT DISTINCT id FROM dpp.events WHERE status=1 AND time>=".($tm-60*5),$conn);
	$cnt_ips = mysql_query("SELECT DISTINCT src_ip FROM dpp.events WHERE status=1 AND time>=".($tm-60*5),$conn);

	$cnt_evnts = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM dpp.events WHERE status=1",$conn));
	
	echo "<b>5 min. snapshot:</b>"
		."<br />".round($cnt['cnt']/5)." events/min."
		."<br />".mysql_num_rows($cnt_srcs)." distinct pinging clients"
		."<br />(".mysql_num_rows($cnt_ips)." distinct ips)"
		
		."<br /><br /><b>ip addresses become targets:</b>"
		."<br />eligible after: <b>{$limit}</b> successful ec2 pings"
		."<br />disqualified after: <b>".$fails."</b> client failures <b>(-1/".round(1/$fail_regen).")</b>"
//		."<br />failure decline: <b>1 out of every ".round(1/$fail_regen)."</b> ping attempts"
		."<br />also disqualified after: <b>".(24*$days)." hours</b> w/o check-in"
		
		."<br /><br />events in live events table: {$cnt_evnts['cnt']}"
		
		;
		
		
		
	echo "<br /><br /><table style=\"border:non;\">"
			."<tr><td colspan=\"5\" style=\"border:none;font-weight:bold;\">".mysql_num_rows($ips)." eligible ping targets</td></tr>";
	
	if (empty($_GET['list'])) { echo "<tr><td style=\"border:none;\"><input type=\"button\" value=\"show list of ips\" onClick=\"location='/info?list=1'\" style=\"width:100%;cursor:pointer;\"/></td></tr>"; }
	else
	{	echo "<tr>"
//			."<td>#</td>"
			."<td>ip address</td>"
			."<td>last seen</td>"
			."<td>fails</td>"
			."<td>successes</td>"
			."<td>loc.</td>"
//			."<td>type</td>"
			."</tr>";

	for ($i = 0; $i < mysql_num_rows($ips); $i++)
	{
		$ip = mysql_fetch_array($ips);
	
	//	this query was too intensive and of questionable overall value...
	//	$browser = mysql_fetch_array(mysql_query("SELECT src_info FROM dpp.events WHERE src_ip='{$ip['ip']}' ORDER BY time DESC LIMIT 1",$conn));
		
		$cnt_pings[$i] = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM dpp.events WHERE dest_ip='{$ip['ip']}' AND status=1 AND time>=".($tm-round($days*86400)),$conn));
//		$cnt_pings_arch[$i] = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM dpp.events_archive WHERE dest_ip='{$ip['ip']}' AND time>=".($tm-round($days*86400)),$conn));
		
		$hrs = floor(($tm-$ip['time'])/60/60);
		$mins = floor(($tm-$ip['time']-(3600*$hrs))/60);
		$secs = (($tm-$ip['time'])-(60*$mins)-(3600*$hrs));
		
	//	if ($hrs < 10) { $hrs = "0{$hrs}"; }
		if ($mins < 10) { $mins = "0{$mins}"; }
		if ($secs < 10) { $secs = "0{$secs}"; }
		
		echo "<tr>"
//			."<td>".($i+1).")</td>"
			."<td>{$ip['ip']}</td>"
			."<td>{$hrs}:{$mins}:{$secs}</td>"
			."<td>{$ip['fails']}</td>"
			."<td>".$cnt_pings[$i]['cnt']." events</td>"
			."<td>{$ip['country']}</td>"
	//		."<td>".substr($browser['src_info'],0,50)."</td>"
			."</tr>";
	}
}
	
	echo "</table>";

}

?>
</body>
</html>