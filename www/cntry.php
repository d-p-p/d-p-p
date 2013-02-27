#!/usr/bin/php -q
<?php


$arr = explode("\n",file_get_contents("cntry.txt"));

$code = 0;
$name = 0;

foreach ($arr as $val)
{
	if (strlen($val) == 2) { $cn[$code]['A2'] = $val; $code++; }
	else { $cn[$name]['NM'] = $val; $name++; }
}

foreach ($cn as $val) { $c[$val["A2"]] = $val["NM"]; }

var_dump($c);

?>