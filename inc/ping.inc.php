<?php

require_once("/liz/inc/xml2array.inc.php");
require_once("/liz/inc/vars.inc.php");



function test_ips($timeout,$tm,$limit)
{	if ($conn = @mysql_connect("localhost","dpp","dpp"))
	{	
		
		$start_time = mktime();
		
//		echo " - starting...\n";
		
/*
		$qu = "SELECT * FROM dpp.sources WHERE time<".($tm-$days*86400)." ORDER BY time ASC";
		$qu_exec = mysql_query($qu,$conn);	
		for ($i = 0; $i < mysql_num_rows($qu_exec); $i++) { archive_ip(mysql_fetch_array($qu_exec),$conn); }
*/	

// 		select group to test pings	
		$cnt = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM dpp.sources WHERE timeouts<=".($limit-1)." AND timeouts>=-".($limit-1),$conn));
		
		if ($cnt['cnt'] > 0)
		{	for ($i = 0; $i < round(60/$timeout); $i++)
			{
				$src = mysql_fetch_array(mysql_query("SELECT * FROM dpp.sources WHERE timeouts<=".($limit-1)." AND timeouts>=-".($limit-1)." ORDER BY rand() LIMIT 1",$conn));
			//	echo $src['ip'];
				if (!empty($src['ip']))
				{	$res = test_ip($src['ip'],$timeout);
				//	echo " - error: {$res['err']} - return time: ".round(round($res['tm']/100)/10)."\n";
		
					if ((round(round($res['tm']/100)/10) != $timeout) && (strpos($res['errstr'],": 401") == 0))
					{	
						if (mysql_query("UPDATE dpp.sources SET timeouts=timeouts-1, errors=CONCAT(errors,'*dpp*{$res['errstr']}') WHERE ip='{$src['ip']}'",$conn)) { /*echo " - Promoted (".($src['timeouts']-1).")...\n";*/ }	
					}
					else
					{	if (mysql_query("UPDATE dpp.sources SET timeouts=timeouts+1 WHERE ip='{$src['ip']}'",$conn)) { /*echo " - Timeout ({$res['errstr']}) (".($src['timeouts']+1).")...\n";*/ }
					}
					if (mktime() > ($start_time+55)) { /*echo "\nkilling process...";*/ break; }
				}
			}
		}
		else { /*echo "\nno untested ips were found...";*/ }
		mysql_close($conn);
	}
}

function set_wait($rep)
{	
	$plus_minus = mt_rand(1,3);
	$wait = $rep*5;
	if ($plus_minus == 1) { $wait = $wait-1; } elseif ($plus_minus == 2) {$wait = $wait+1;}
//	echo "waiting {$wait} seconds...";
	for ($i = 0; $i < $wait; $i++) { /*echo " - ".($wait-$i);*/ sleep(1); }
}

function test_ip($ip,$to)
{
	$st = mktime()+microtime();
	
	$curl = curl_init();
	$curl_opt = array( CURLOPT_URL=>$ip, CURLOPT_TIMEOUT=>$to, CURLOPT_FAILONERROR=>1
				, CURLOPT_NOPROGRESS=>true, CURLOPT_RETURNTRANSFER=>true);
	curl_setopt_array($curl, $curl_opt);
	curl_exec($curl);
	$err = curl_errno($curl);
	$errstr = curl_error($curl);
	curl_close($curl);
	$tm = round((mktime()+microtime()-$st)*1000);

	return array('err'=>$err,'tm'=>$tm,'errstr'=>$errstr);
}


function archive_ip($arr,$connection)
{
	$qu = "INSERT INTO dpp.sources_archive SET "
			."ip='{$arr['ip']}'"
			.",timeouts={$arr['timeouts']}"
			.",time={$arr['time']}"
			.",fails={$arr['fails']}"
			.",geo_lat='{$arr['geo_lat']}'"
			.",geo_lng='{$arr['geo_lng']}'"
			.",country='{$arr['country']}'"
			.",archived=".mktime()
			;
	
	if (mysql_query($qu,$connection))
	{	//echo "\n{$arr['ip']}, last check-in: ".date("G:i:s d/m",$arr['time']);
		$qu_empty = "DELETE FROM dpp.sources WHERE ip={$arr['ip']}";
		//echo " - archived";
		if (mysql_query($qu_empty,$connection))
		{	/*echo " - erased from sources";*/ }
	}
}

function archive_event($arr,$connection)
{	$geo = array("src"=>array("lat"=>0,"lng"=>0,"ctry"=>"xx"),"dst"=>array("lat"=>0,"lng"=>0,"ctry"=>"xx"));
	
	$info_arr = mysql_fetch_array(mysql_query("SELECT geo_lat, geo_lng, country, ip, time FROM dpp.sources_archive WHERE ip='{$arr['src_ip']}' ORDER BY time DESC LIMIT 1",$connection));
	if (!empty($info_arr['ip']) && ($info_arr['ip'] == $arr['src_ip'])) { $geo['src']['lat'] = $info_arr['geo_lat']; $geo['src']['lng'] = $info_arr['geo_lng']; $geo['src']['ctry'] = $info_arr['country']; }
	unset($info_arr);

	$info_arr = mysql_fetch_array(mysql_query("SELECT geo_lat, geo_lng, country, ip, time FROM dpp.sources WHERE ip='{$arr['src_ip']}' ORDER BY time DESC LIMIT 1",$connection));
	if (!empty($info_arr['ip']) && ($info_arr['ip'] == $arr['src_ip'])) { $geo['src']['lat'] = $info_arr['geo_lat']; $geo['src']['lng'] = $info_arr['geo_lng']; $geo['src']['ctry'] = $info_arr['country']; }
	unset($info_arr);

	$info_arr = mysql_fetch_array(mysql_query("SELECT geo_lat, geo_lng, country, ip, time FROM dpp.sources_archive WHERE ip='{$arr['dest_ip']}' ORDER BY time DESC LIMIT 1",$connection));
	if (!empty($info_arr['ip']) && ($info_arr['ip'] == $arr['dest_ip'])) { $geo['dst']['lat'] = $info_arr['geo_lat']; $geo['dst']['lng'] = $info_arr['geo_lng']; $geo['dst']['ctry'] = $info_arr['country']; }
	unset($info_arr);
	
	$info_arr = mysql_fetch_array(mysql_query("SELECT geo_lat, geo_lng, country, ip, time FROM dpp.sources WHERE ip='{$arr['dest_ip']}' ORDER BY time DESC LIMIT 1",$connection));
	if (!empty($info_arr['ip']) && ($info_arr['ip'] == $arr['dest_ip'])) { $geo['dst']['lat'] = $info_arr['geo_lat']; $geo['dst']['lng'] = $info_arr['geo_lng']; $geo['dst']['ctry'] = $info_arr['country']; }
	unset($info_arr);
	
	$qu = "INSERT INTO dpp.events_archive SET "
			."id='{$arr['id']}'"
			.", src_ip='{$arr['src_ip']}'"
			.", dest_ip='{$arr['dest_ip']}'"
			.", time={$arr['time']}"
//			.", ping_cnt={$arr['ping_cnt']}"
//			.", ping_avg={$arr['ping_avg']}"
//			.", ping_stddev={$arr['ping_stddev']}"
			.", ping_array='{$arr['ping_array']}'"
			.", src_lat='{$geo['src']['lat']}'"
			.", src_lng='{$geo['src']['lng']}'"
			.", src_country='{$geo['src']['ctry']}'"
			.", dest_lat='{$geo['dst']['lat']}'"
			.", dest_lng='{$geo['dst']['lng']}'"
			.", dest_country='{$geo['dst']['ctry']}'"
			.", src_info='{$arr['src_info']}'"
			.", archived=".mktime()
			;

	if (mysql_query($qu,$connection))
	{	
	//	echo " - {$arr['rank']} (".date("G:i:s",$arr['time']).")";
			
		$qu_empty = "DELETE FROM dpp.events WHERE rank={$arr['rank']}";
	//	echo ", arch";
		if (mysql_query($qu_empty,$connection))
		{	/*echo ", rem";*/ }
	}
}

function archive_source($arr,$connection)
{
	$qu = "INSERT INTO dpp.sources_archive SET "
			."ip='{$arr['ip']}'"
			.", timeouts='{$arr['timeouts']}'"
			.", time={$arr['time']}"
			.", fails={$arr['fails']}"
			.", geo_lat={$arr['geo_lat']}"
			.", geo_lng={$arr['geo_lng']}"
			.", country='{$arr['country']}'"
			.", errors='{$arr['errors']}'"
			.", archived=".mktime()
			;
	
	if (mysql_query($qu,$connection))
	{	
		//echo "\nip: {$arr['ip']} last seen: ".date("G:i:s d/m",$arr['time']);
		
		$qu_empty = "DELETE FROM dpp.sources WHERE ip='{$arr['ip']}'";
		//echo " - archived";
		if (mysql_query($qu_empty,$connection))
		{	/*echo " - removed from sources table";*/ }
	}
}

function get_geo($ip,$connection)
{	
	$url = "http://ipinfodb.com/ip_query.php?ip={$ip}&timezone=false";
	$curl = curl_init();
	$curl_opt = array( CURLOPT_URL=>$url, CURLOPT_TIMEOUT=>10, CURLOPT_FAILONERROR=>1
				, CURLOPT_NOPROGRESS=>true, CURLOPT_RETURNTRANSFER=>true);
	curl_setopt_array($curl, $curl_opt);
	$xml_obj = xml2array(curl_exec($curl),$get_attributes=1);
	curl_close($curl);
//	var_dump($xml_obj);
//	if ( ($xml_obj['Response']['Longitude']['value'] == "0") && ($xml_obj['Response']['Latitude']['value'] == "0") )
//	{	$xml_obj['Response']['Longitude']['value'] = "0";
//		$xml_obj['Response']['Latitude']['value'] = "0";	
//	}
	if (mysql_query("UPDATE dpp.sources SET geo_lat='{$xml_obj['Response']['Latitude']['value']}'"
				.", geo_lng='{$xml_obj['Response']['Longitude']['value']}'"
				.", country='{$xml_obj['Response']['CountryCode']['value']}'"
				." WHERE ip='{$ip}'",$connection))
	{ /*echo "\n{$ip} - geo info updated...";*/ }
	unset($xml_obj);

}

?>