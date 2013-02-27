#!/usr/bin/php -q
<?php

$reso = 60; //resolution, in minutes
$strt = 1278054000;
//$fnsh = 1278205200;
$fnsh = 1278658800;

$ht = 1000;
$wd = 2000;

$col = round($wd/(($fnsh-$strt)/($reso*60)+1));

if ($conn = @mysql_connect("localhost","dpp","dpp"))
{	
//	unlink("by_time.htm");
	
	$tm['this'] = $strt;
	for ($i = 1; $tm['this'] < $fnsh; $i++)
	{	$tm['last'] = $tm['this'];
		$tm['this'] = $tm['last']+$reso*60;
	
		$qu = "SELECT ping_array FROM dpp.events_archive WHERE time>={$tm['last']} AND time<{$tm['this']}";
		
		$get_pings = mysql_query($qu,$conn);
		
		$str = ""; $arr = ""; $p[$tm['last']] = array("cnt"=>0, "fls"=>0, "ttl"=>0, "avg"=>0, "sdv"=>0);
		for ($l = 0; $l < mysql_num_rows($get_pings); $l++)
		{	$ev = mysql_fetch_array($get_pings);
			if (!empty($str)) { $str .= ","; } $str .= $ev['ping_array'];
		}
		$arr = explode(",",$str);
		foreach ($arr as $val) { if ($val != "") { $p[$tm['last']]['cnt']++; $p[$tm['last']]['ttl'] = $p[$tm['last']]['ttl']+intval($val); } else { $p[$tm['last']]['fls']++; } }	
		
		$p[$tm['last']]['avg'] = $p[$tm['last']]['ttl']/$p[$tm['last']]['cnt'];
		foreach ($arr as $val) { if ($val != "") { $p[$tm['last']]['sdv'] = $p[$tm['last']]['sdv']+((intval($val)-$p[$tm['last']]['avg'])*(intval($val)-$p[$tm['last']]['avg'])); } }
		$p[$tm['last']]['sdv'] = round(sqrt($p[$tm['last']]['sdv']/($p[$tm['last']]['cnt']-1)));
		
		$qu_ = "INSERT INTO dpp.compilations SET tag='hour', name='".date("c",$tm['last'])."', pings={$p[$tm['last']]['cnt']}, fails={$p[$tm['last']]['fls']}, avg=".round($p[$tm['last']]['avg']).", std_dev={$p[$tm['last']]['sdv']}";
		
		if (mysql_query($qu_,$conn)) { echo "\n".date("c",$tm['last']); }
	}
/*	$p[$tm['last']]['avg']
	$out = ""; $max = 0;
	foreach ($p as $val)
	{
		if (($val['cnt']+$val['fls']) > $max) { $max = $val['cnt']+$val['fls']; }
	}
	
	$div = $max/$ht;
	
	$i = 0;
	
	foreach ($p as $ind=>$val)
	{	
		$out .= "<a title=\"{$val['cnt']}\"><div style=\"height:".round($val['cnt']/$div)."px;width:{$col}px;border:none;background-color:green;position:absolute;top:".(20+$ht-round($val['cnt']/$div))."px;left:".(20+($col+2)*$i)."px;\"></div></a>"
				."<a title=\"{$val['fls']}\"><div style=\"height:".round($val['fls']/$div)."px;width:{$col}px;border:none;background-color:red;position:absolute;top:".(20+$ht-round($val['cnt']/$div)-round($val['fls']/$div)-1)."px;left:".(20+($col+2)*$i)."px;\"></div></a>"
				;
		
		if ($i % 3 == 0)
		{	$out .= "<div style=\"border-left:solid 1px gray;padding-left:4px;position:absolute;font-family:arial;top:".(20+$ht)."px;overflow:visible;font-weight:bold;font-size:12px;left:".(20+($col+2)*$i)."px;\">".date("H:i",$ind)."<br />".date("D",$ind)."</div>"
				;
		}
		
		$i++;
	}
	
		$hn = fopen("by_time.htm","a+");
		fwrite($hn,$out);
		fclose($hn);
	*/
mysql_close($conn);
}

