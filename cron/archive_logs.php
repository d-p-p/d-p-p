#!/usr/bin/php -q
<?php

copy("/liz/httpd/logs/dpp-access_log","/mnt/dpp_log_archive/".date("Y.m.d").".dpp-access.log");
$hd = fopen("/liz/httpd/logs/dpp-access_log","w"); fclose($hd);
copy("/liz/httpd/logs/dpp-error_log","/mnt/dpp_log_archive/".date("Y.m.d").".dpp-error.log");
$hd = fopen("/liz/httpd/logs/dpp-error_log","w"); fclose($hd);

if (!file_exists("/mnt/dpp_log_archive/".date("Y.m").".dpp-access.log"))
{	$hd = fopen("/mnt/dpp_log_archive/".date("Y.m").".dpp-access.log","w"); fclose($hd);
	exec("gzip /mnt/dpp_log_archive/".date("Y.m",mktime()-100000).".dpp-access.log --best");
}

exec("cat"
	." /mnt/dpp_log_archive/".date("Y.m").".dpp-access.log"
	." /mnt/dpp_log_archive/".date("Y.m.d").".dpp-access.log"
	." > /mnt/dpp_log_archive/".date("Y.m").".dpp-access.log.tmp");
rename("/mnt/dpp_log_archive/".date("Y.m").".dpp-access.log.tmp","/mnt/dpp_log_archive/".date("Y.m").".dpp-access.log");
unlink("/mnt/dpp_log_archive/".date("Y.m.d").".dpp-access.log");

if (!file_exists("/mnt/dpp_log_archive/".date("Y.m").".dpp-error.log"))
{ 	$hd = fopen("/mnt/dpp_log_archive/".date("Y.m").".dpp-error.log","w"); fclose($hd);
	exec("gzip /mnt/dpp_log_archive/".date("Y.m",mktime()-100000).".dpp-error.log --best");
}

exec("cat"
	." /mnt/dpp_log_archive/".date("Y.m").".dpp-error.log"
	." /mnt/dpp_log_archive/".date("Y.m.d").".dpp-error.log"
	." > /mnt/dpp_log_archive/".date("Y.m").".dpp-error.log.tmp");
rename("/mnt/dpp_log_archive/".date("Y.m").".dpp-error.log.tmp","/mnt/dpp_log_archive/".date("Y.m").".dpp-error.log");
unlink("/mnt/dpp_log_archive/".date("Y.m.d").".dpp-error.log");


?>