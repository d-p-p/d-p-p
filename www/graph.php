#!/usr/bin/php -q
<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

$lim = 3100;
$range = "time>=1278054000 AND time<1278658800";
$srch_by = "country";

//echo strtotime("2010-07-02T00:00:00-07:00")."\n".strtotime("2010-07-09T00:00:00-07:00");

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{
	$qu = "SELECT DISTINCT CONCAT(src_{$srch_by},'_',dest_{$srch_by}) AS grp"
		." FROM dpp.events_archive WHERE {$range}"
		." AND src_country!='xx' AND dest_country!='xx'"
		." AND src_country!='A1' AND dest_country!='A1'"
		." AND src_country!='A2' AND dest_country!='A2'"
		." AND src_country!='EU' AND dest_country!='EU'"
		." AND src_country!='AP' AND dest_country!='AP'"
		." AND src_{$srch_by}!=dest_{$srch_by}"
		." ORDER BY grp ASC"
	//	." LIMIT {$lim}"
		;
		
//	die($qu);	
	
	$get_groups = mysql_query($qu,$conn);
	
	echo mysql_num_rows($get_groups);
	
	for ($i = 0; $i < mysql_num_rows($get_groups); $i++)
	{	
		$dt = mysql_fetch_array($get_groups);
		
		$ips = explode("_",$dt['grp']);
		
		
			
		$qu_ = "SELECT * FROM dpp.events_archive WHERE"
				." ( {$range} AND dest_{$srch_by}='{$ips[0]}' AND src_{$srch_by}='{$ips[1]}' )"
				." OR ( {$range} AND src_{$srch_by}='{$ips[0]}' AND dest_{$srch_by}='{$ips[1]}' )"
				;
		
		$p['str'] = "";
		$p['arr'] = "";
		$p['cnt'] = 0;
		$p['fls'] = 0;
		$p['ttl'] = 0;
		$p['avg'] = 0;
		$p['sdv'] = 0;
		$str = "";
		
		$p['src_country'] = "";
		$p['dest_country'] = "";
		$p['src_geo'] = "";
		$p['dest_geo'] = "";
		
		
		if (empty($ex["{$ips[1]}_{$ips[0]}"]))
		{
			$ex[$dt['grp']] = 1;
						
			$get_events = mysql_query($qu_,$conn);
			for ($j = 0; $j < mysql_num_rows($get_events); $j++)
			{	$ev = mysql_fetch_array($get_events); if (!empty($p['str'])) { $p['str'] .= ","; } $p['str'] .= $ev['ping_array'];
				if (empty($p['src_country'])) { $p['src_country'] = $ev['src_country']; $p['dest_country'] = $ev['dest_country']; }
				if (empty($p['src_geo'])) { $p['src_geo'] = "{$ev['src_lat']}, {$ev['src_lng']}"; $p['dest_geo'] = "{$ev['dest_lat']}, {$ev['dest_lng']}"; }
			}
			$p['arr'] = explode(",",$p['str']);
			foreach ($p['arr'] as $val) { if ($val != "") { $p['cnt']++; $p['ttl'] = $p['ttl']+intval($val); } else { $p['fls']++; } }
			if ($p['cnt'] > 10)
			{	$p['avg'] = $p['ttl']/$p['cnt'];
				foreach ($p['arr'] as $val) { if ($val != "") { $p['sdv'] = $p['sdv']+((intval($val)-$p['avg'])*(intval($val)-$p['avg'])); } }
				$p['sdv'] = round(sqrt($p['sdv']/($p['cnt']-1)));
				$str = "**"
					."{$p['src_country']}-{$p['dest_country']}"
						."*{$p['cnt']}" //succ
					."*{$p['fls']}" //fails
					."*".round($p['avg']) //avg
					."*{$p['sdv']}" //std dev
					;
					
				$qu_cn = mysql_query("SELECT * FROM dpp.compilations WHERE tag='countries' AND (name='{$p['src_country']}_{$p['dest_country']}' OR name='{$p['dest_country']}_{$p['src_country']}')",$conn);
				
				if (mysql_num_rows($qu_cn) == 0)
				{	mysql_query("INSERT INTO dpp.compilations SET tag='countries', name='{$p['src_country']}_{$p['dest_country']}'"
									.", pings={$p['cnt']}, fails={$p['fls']}, avg=".round($p['avg']).", std_dev={$p['sdv']}"
									.", meta=''"
									,$conn);
					echo "\n{$i}) {$str} - added";			
				}
				else
				{	$qu_cn_ = mysql_fetch_array($qu_cn);
					mysql_query("UPDATE dpp.compilations SET"
									." pings={$p['cnt']}, fails={$p['fls']}, avg=".round($p['avg']).", std_dev={$p['sdv']}"
									.", meta=''"
									." WHERE tag='countries' AND name='{$qu_cn_['name']}'"
									,$conn);
					echo "\n{$i}) {$str} - updated";
				}
			
/*				$hn = fopen("data.txt","a+");
				fwrite($hn,$str);
				fclose($hn);
				echo "\n{$i}) {$str}";
*/			}
		}
		else {
			echo "\n{$i}) repeat";
		//	$str = "\n**\n{$ips[1]}_{$ips[0]} already done"; $hn = fopen("data.txt","a+"); fwrite($hn,$str); fclose($hn);
			}
		

	
	}

	mysql_close($conn);
}
