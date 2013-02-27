<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: image/gif');

require_once("/liz/inc/vars.inc.php");

if ( !empty($_GET['i']) && ($conn = @mysql_connect("localhost","dpp","dpp")) )
{
	$id = strval($_GET['i']);
	$tm = mktime();
	
	if (!empty($_GET['g']) || !empty($_GET['p']))
	{	
		$send = "";
		
		if (!empty($_GET['p']))
		{
			$p = explode("_",strval($_GET['p']));
			
			$in = mysql_fetch_array(mysql_query("SELECT rank, time FROM dpp.events WHERE id='{$id}' AND dest_ip='{$p[0]}' AND status=0 ORDER BY time DESC LIMIT 1",$conn));
			
			if ($p[1] != -1)
			{	
				$ping_arr_sql = "x,x,x,x,x"; $ping_arr = explode(",",$p[4]); if (count($ping_arr) == intval($p[3])) { $ping_arr_sql = $p[4]; }
				
				mysql_query("UPDATE dpp.events SET time={$tm}, ping_array='{$ping_arr_sql}'"
													.", src_info='".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'],$conn)."'"
													.", status=1 WHERE rank={$in['rank']}",$conn);
			}
			else
			{	mysql_query("UPDATE dpp.sources SET fails=fails+1 WHERE ip='{$p[0]}'",$conn);
				mysql_query("DELETE FROM dpp.events WHERE id='{$id}' AND dest_ip='{$p[0]}' AND status=0",$conn);
			}
			
			$send = " AND ip!='{$p[0]}'";
			
		}
		elseif (!empty($_GET['g']))
		{	mysql_query("UPDATE dpp.sources SET time={$tm} WHERE ip='{$_SERVER['REMOTE_ADDR']}'",$conn);
			if (mysql_affected_rows() == 0) { mysql_query("INSERT INTO dpp.sources SET ip='{$_SERVER['REMOTE_ADDR']}', time={$tm}",$conn); }
		}
	
		$info = mysql_fetch_array(mysql_query("SELECT ip,fails FROM dpp.sources WHERE ip!='{$_SERVER['REMOTE_ADDR']}' AND fails<{$fails} AND timeouts<=-{$limit} AND time>=".($tm-round($days*86400))."{$send} ORDER BY rand() LIMIT 1",$conn));
		
		$subtract_fail = mt_rand(1,3); if (($subtract_fail == 3) && ($info['fails'] > 0)) { mysql_query("UPDATE dpp.sources SET fails=fails-1 WHERE ip='{$info['ip']}'",$conn); }		
		
		mysql_query("INSERT INTO dpp.events SET id='{$id}', src_ip='{$_SERVER['REMOTE_ADDR']}', dest_ip='{$info['ip']}', time={$tm}, src_info='".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'],$conn)."'",$conn);
		
		$ip['dst'] = $info['ip'];
		$rnd = 0;
	}
	
	else
	{ 
		$info = mysql_fetch_array(mysql_query("SELECT dest_ip, time FROM dpp.events WHERE id='{$id}' AND status=0 ORDER BY time DESC LIMIT 1",$conn));
		
		$ip['dst'] = $info['dest_ip'];
	
		$rnd = 1;
	}
	
	$ips = explode(".",$ip['dst']);
	if (count($ips)  == 4)
	{	$snd = array(array($ips[0]+1,$ips[1]+1),array($ips[2]+1,$ips[3]+1));
		$img = ImageCreateTrueColor($snd[$rnd][0],$snd[$rnd][1]);
		ImageFill($img, 0, 0, ImageColorAllocate($img, 220, 220, 220));
		ImageGIF($img);
		ImageDestroy($img);
	}
}

?>