
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `dpp` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `dpp`;
DROP TABLE IF EXISTS `compilations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compilations` (
  `tag` varchar(25) NOT NULL DEFAULT 'x',
  `name` varchar(25) NOT NULL DEFAULT 'x',
  `pings` bigint(8) unsigned NOT NULL DEFAULT '0',
  `fails` bigint(8) unsigned NOT NULL DEFAULT '0',
  `avg` smallint(5) unsigned NOT NULL DEFAULT '0',
  `std_dev` smallint(5) unsigned NOT NULL DEFAULT '0',
  `meta` varchar(256) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `compilations` WRITE;
/*!40000 ALTER TABLE `compilations` DISABLE KEYS */;
/*!40000 ALTER TABLE `compilations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `rank` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id` char(24) NOT NULL DEFAULT 'xxxxxxxxxxxxxxxxxxxxxxxx',
  `src_ip` varchar(15) NOT NULL DEFAULT '255.255.255.255',
  `dest_ip` varchar(15) NOT NULL DEFAULT '255.255.255.255',
  `time` int(10) unsigned NOT NULL DEFAULT '1111111111',
  `ping_array` varchar(30) NOT NULL DEFAULT 'x,x,x,x,x',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `src_info` varchar(255) NOT NULL DEFAULT 'x',
  PRIMARY KEY (`rank`),
  KEY `ind_events` (`id`,`dest_ip`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `events_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_archive` (
  `rank` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id` char(24) NOT NULL DEFAULT 'xxxxxxxxxxxxxxxxxxxxxxxx',
  `src_ip` varchar(15) NOT NULL DEFAULT '255.255.255.255',
  `dest_ip` varchar(15) NOT NULL DEFAULT '255.255.255.255',
  `time` int(10) unsigned NOT NULL DEFAULT '1111111111',
  `ping_cnt` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ping_avg` smallint(5) NOT NULL DEFAULT '0',
  `ping_stddev` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ping_array` varchar(30) NOT NULL DEFAULT 'x,x,x,x,x',
  `src_lat` float(11,8) NOT NULL DEFAULT '0.00000000',
  `src_lng` float(11,8) NOT NULL DEFAULT '0.00000000',
  `src_country` varchar(4) NOT NULL DEFAULT 'xx',
  `dest_lat` float(11,8) NOT NULL DEFAULT '0.00000000',
  `dest_lng` float(11,8) NOT NULL DEFAULT '0.00000000',
  `dest_country` varchar(4) NOT NULL DEFAULT 'xx',
  `src_info` varchar(255) NOT NULL DEFAULT 'x',
  `archived` int(10) unsigned NOT NULL DEFAULT '1234567890',
  PRIMARY KEY (`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `events_archive` WRITE;
/*!40000 ALTER TABLE `events_archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_archive` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `events_latest`;
/*!50001 DROP VIEW IF EXISTS `events_latest`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `events_latest` (
  `src_ip` varchar(15),
  `dest_ip` varchar(15),
  `time` int(10) unsigned
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sources` (
  `ip` varchar(15) NOT NULL DEFAULT '255.255.255.255',
  `timeouts` tinyint(3) NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL,
  `fails` smallint(5) unsigned NOT NULL DEFAULT '0',
  `geo_lat` varchar(12) NOT NULL DEFAULT '0',
  `geo_lng` varchar(12) NOT NULL DEFAULT '0',
  `country` varchar(4) NOT NULL DEFAULT 'xx',
  `errors` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`ip`),
  KEY `ind_src` (`ip`,`timeouts`,`fails`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sources` WRITE;
/*!40000 ALTER TABLE `sources` DISABLE KEYS */;
/*!40000 ALTER TABLE `sources` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sources_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sources_archive` (
  `rank` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL DEFAULT '255.255.255.255',
  `timeouts` tinyint(3) NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL,
  `fails` smallint(5) unsigned NOT NULL DEFAULT '0',
  `geo_lat` varchar(12) NOT NULL DEFAULT '0',
  `geo_lng` varchar(12) NOT NULL DEFAULT '0',
  `country` varchar(4) NOT NULL DEFAULT 'xx',
  `errors` varchar(256) NOT NULL,
  `archived` int(10) unsigned NOT NULL DEFAULT '1234567890',
  PRIMARY KEY (`rank`),
  KEY `ip_ind` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sources_archive` WRITE;
/*!40000 ALTER TABLE `sources_archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `sources_archive` ENABLE KEYS */;
UNLOCK TABLES;

USE `dpp`;
/*!50001 DROP TABLE IF EXISTS `events_latest`*/;
/*!50001 DROP VIEW IF EXISTS `events_latest`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `events_latest` AS select `events`.`src_ip` AS `src_ip`,`events`.`dest_ip` AS `dest_ip`,max(`events`.`time`) AS `time` from `events` group by `events`.`src_ip`,`events`.`dest_ip` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

