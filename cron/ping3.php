#!/usr/bin/php -q
<?php

$rep = 3;

require_once("/liz/inc/ping.inc.php");
set_wait($rep);
test_ips($timeout,$tm,$limit);

?>