<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

$url = explode("/",substr($_SERVER["REQUEST_URI"],1));

$method = "xml"; $ip1 = ""; $ip2 = ""; $time1 = 0; $time2 = 0; $time_qu = ""; $gps_qu = ""; $latest = false; $j = 0;

foreach ($url as $ind=>$val)
{	
	if (strtolower($val) == "json") { $method = "json"; }
	
	elseif (strtolower($val) == "latest") { $latest = true; }
		
	// find ip address(es) to match as source or destination, if they exist
	elseif (preg_match("^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}^",$val))
	{	if (empty($ip1)) { $ip1 = " AND (e.src_ip='{$val}' OR e.dest_ip='{$val}')"; }
		else { $ip2 = " AND (e.src_ip='{$val}' OR e.dest_ip='{$val}')"; }
	}
	// find time ranges in parameters, if they exist
	elseif (intval(strtotime($val)) != 0)
	{	if ($time1 == 0) { $time1 = intval(strtotime($val)); }
		else { $time2 = intval(strtotime($val)); }
	}
	// find geo-coordinate ranges in parameters, if they exist
	elseif ((strpos($val,",") != 0) && (strpos($val,"_") != 0))
	{	$gps = explode("_",str_replace(" ","",$val)); $pos = explode(",",$gps[0]);
		if (strpos($gps[1],",") == 0) { $range = array(floatval($gps[1]),floatval($gps[1])); }
		else { $range = explode(",",$gps[1]); }
		$x_lo = floatval($pos[0])-floatval($range[0]); $x_hi = floatval($pos[0])+floatval($range[0]);
		$y_lo = floatval($pos[1])-floatval($range[1]); $y_hi = floatval($pos[1])+floatval($range[1]);
		$gps_qu = " AND e.src_lat >= {$x_lo} AND e.src_lat <= {$x_hi} AND e.src_lng >= {$y_lo} AND e.src_lng <= {$y_hi}"
				 ." AND e.dest_lat >= {$x_lo} AND e.dest_lat <= {$x_hi} AND e.dest_lng >= {$y_lo} AND e.dest_lng <= {$y_hi}";
	}
}

if (($time1 != 0) && ($time2 == 0)) { $time_qu = " AND e.time >= {$time1}"; }
elseif ( ($time1 != 0) && ($time2 != 0) && ($time1 > $time2) ) { $time_qu = " AND e.time <= {$time1} AND e.time >= {$time2}"; }
elseif ( ($time1 != 0) && ($time2 != 0) && ($time1 < $time2) ) { $time_qu = " AND e.time >= {$time1} AND e.time <= {$time2}"; }

if ($method == "xml") { header('Content-type: text/xml'); echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<dpp>"; }
elseif ($method == "json") { header('Content-type: application/json'); }

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{
	$query = "SELECT"." e.src_ip AS src_ip, e.src_lat AS src_lat, e.src_lng AS src_lng"
					.", e.dest_ip AS dst_ip, e.dest_lat AS dst_lat, e.dest_lng AS dst_lng"
					.", e.time AS time, e.ping_cnt AS ping_cnt, e.ping_avg AS ping_avg, e.ping_stddev AS ping_stddev"
					." FROM dpp.events_archive AS e"
//					." JOIN dpp.events_latest AS el ON e.src_ip = el.src_ip AND e.dest_ip = el.dest_ip AND e.time = el.time"
//					." JOIN dpp.sources AS s ON e.src_ip = s.ip JOIN dpp.sources AS d ON e.dest_ip = d.ip"
					." WHERE e.status=1 AND e.src_lat != 0 AND e.src_lng != 0 AND e.dest_lat != 0 AND e.dest_lng != 0"
					.$ip1.$ip2.$time_qu.$gps_qu;
	if (!$latest) { $query .= " ORDER BY e.time DESC LIMIT 1024"; }
	else { $query .= " ORDER BY e.src_ip, e.dest_ip, e.time DESC LIMIT 10000"; }
					
	$get_locs = mysql_query($query,$conn);	
	for ($i = 0; (($i < mysql_num_rows($get_locs)) && (count($dt_arr) < 1024)); $i++)
	{	$dt[$i] = mysql_fetch_array($get_locs);
		if ((!$latest) || (($i != 0) && ("{$dt[$i]['src_ip']}_{$dt[$i]['dst_ip']}" != "{$dt[$i-1]['src_ip']}_{$dt[$i-1]['dst_ip']}")))
		{	$dt_arr[$i] = array( "src_ip"=>$dt[$i]['src_ip'],"src_geo"=>round($dt[$i]['src_lat'],4).", ".round($dt[$i]['src_lng'],4)
				,"dst_ip"=>$dt[$i]['dst_ip'],"dst_geo"=>round($dt[$i]['dst_lat'],4).", ".round($dt[$i]['dst_lng'],4)
				,"time"=>substr(date("c",$dt[$i]['time']),0,19)
				,"ping_cnt"=>$dt[$i]['ping_cnt'],"ping_avg"=>$dt[$i]['ping_avg'],"ping_stddev"=>$dt[$i]['ping_stddev']
				);
		}		
		if (($method == "xml") && !empty($dt_arr[$i])) { echo "\n<event "; foreach ($dt_arr[$i] as $ind=>$val) { echo " {$ind}=\"{$val}\""; } echo " />"; }
	}
	
	if ($method == "json") { echo json_encode(array("dpp"=>$dt_arr)); } unset($dt_arr);
	mysql_close($conn);
}

//echo str_replace("<","&lt;",str_replace(">","&gt;",$query));

if ($method == "xml") { echo "\n</dpp>"; }