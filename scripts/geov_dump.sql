-- mysqldump --databases geov_core geov_moos_cp geov_moos_ctd geov_moos_depth geov_moos_mseas geov_moos_nafcon_target geov_moos_opgrid  --no-data -u sea --password=saline12; echo 'USE `geov_core`;';mysqldump  -u sea --password=saline12 --databases geov_core --tables core_module core_page core_vehicle core_vehicle_default; 
-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: localhost    Database: geov_core
-- ------------------------------------------------------
-- Server version	8.0.41-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `geov_core`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_core` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_core`;

--
-- Table structure for table `core_connected`
--

DROP TABLE IF EXISTS `core_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_ip` char(15) COLLATE utf8mb3_unicode_ci NOT NULL,
  `connected_userid` int unsigned NOT NULL,
  `connected_profileid` int unsigned DEFAULT NULL,
  `connected_lasttime` double DEFAULT NULL COMMENT 'unix epoch',
  `connected_lastdataid` int unsigned DEFAULT '0',
  `connected_client` int NOT NULL COMMENT 'type of connection: 1 = website, 2 = google earth',
  `connected_playback` int DEFAULT NULL COMMENT '0 = stopped, 1 = playing, 2 = pause',
  `connected_playbackcount` double DEFAULT NULL COMMENT 'seconds into the playback',
  `connected_playbackstep` int DEFAULT NULL,
  `connected_message` text COLLATE utf8mb3_unicode_ci,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  `connected_lastrange` int DEFAULT NULL,
  `connected_lasttilt` int DEFAULT NULL,
  PRIMARY KEY (`connected_id`),
  UNIQUE KEY `connected_ip` (`connected_ip`,`connected_client`)
) ENGINE=MyISAM AUTO_INCREMENT=312 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_connected_vehicle`
--

DROP TABLE IF EXISTS `core_connected_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_connected_vehicle` (
  `c_vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `c_vehicle_connectedid` int unsigned NOT NULL,
  `c_vehicle_vehicleid` int unsigned NOT NULL,
  `c_vehicle_lastdid` int DEFAULT NULL,
  `c_vehicle_onscreen` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`c_vehicle_id`),
  UNIQUE KEY `c_vehicle_connectedid` (`c_vehicle_connectedid`,`c_vehicle_vehicleid`)
) ENGINE=MyISAM AUTO_INCREMENT=24278 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_data`
--

DROP TABLE IF EXISTS `core_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_data` (
  `data_id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_time` double DEFAULT NULL COMMENT 'unix epoch timestamp',
  `data_vehicleid` int DEFAULT NULL,
  `data_lat` double DEFAULT NULL COMMENT 'decimal degrees',
  `data_long` double DEFAULT NULL COMMENT 'decimal degrees',
  `data_heading` double DEFAULT NULL,
  `data_speed` double DEFAULT NULL,
  `data_depth` double DEFAULT NULL,
  `data_quality` int NOT NULL DEFAULT '10' COMMENT 'quality number where 0 is the best possible',
  `data_userid` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`data_id`),
  KEY `data_vehicleid` (`data_vehicleid`),
  KEY `data_time` (`data_time`)
) ENGINE=MyISAM AUTO_INCREMENT=3375603 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_imported_alogs`
--

DROP TABLE IF EXISTS `core_imported_alogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_imported_alogs` (
  `alog_id` int unsigned NOT NULL AUTO_INCREMENT,
  `alog_vehicleid` int NOT NULL,
  `alog_filename` varchar(2000) COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`alog_id`)
) ENGINE=MyISAM AUTO_INCREMENT=481 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_module`
--

DROP TABLE IF EXISTS `core_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_module` (
  `module_id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL,
  `module_file` varchar(1000) NOT NULL,
  `module_ge_viewer` varchar(255) NOT NULL,
  `module_refresh_time` int NOT NULL DEFAULT '5' COMMENT 'refresh time of ge_viewer.php in seconds',
  PRIMARY KEY (`module_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_page`
--

DROP TABLE IF EXISTS `core_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_page` (
  `page_id` int unsigned NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_uri` varchar(255) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_profile`
--

DROP TABLE IF EXISTS `core_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `profile_userid` int NOT NULL,
  `profile_tzone` int NOT NULL DEFAULT '-5',
  `profile_sort` enum('name','type','owner') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'type',
  `profile_createtime` double NOT NULL,
  `profile_mode` enum('realtime','playback','history') COLLATE utf8mb3_unicode_ci NOT NULL,
  `profile_rate` float NOT NULL DEFAULT '1',
  `profile_starttime` double DEFAULT NULL,
  `profile_endtime` double DEFAULT NULL,
  `profile_vfollowid` int DEFAULT NULL,
  `profile_followhdg` tinyint(1) DEFAULT '0',
  `profile_fixedicon` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'use fixed size icons (bool flag)',
  `profile_fixediconsize` int NOT NULL DEFAULT '1000' COMMENT 'size (in meters square) for fixed icon size',
  `profile_simulation` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_profile_module`
--

DROP TABLE IF EXISTS `core_profile_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_profile_module` (
  `p_module_id` int unsigned NOT NULL AUTO_INCREMENT,
  `p_module_profileid` int unsigned NOT NULL,
  `p_module_moduleid` int NOT NULL,
  PRIMARY KEY (`p_module_id`),
  UNIQUE KEY `uni_index` (`p_module_profileid`,`p_module_moduleid`)
) ENGINE=MyISAM AUTO_INCREMENT=106 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_profile_vehicle`
--

DROP TABLE IF EXISTS `core_profile_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_profile_vehicle` (
  `p_vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `p_vehicle_profileid` int unsigned NOT NULL,
  `p_vehicle_vehicleid` int unsigned NOT NULL,
  `p_vehicle_duration` double DEFAULT '600',
  `p_vehicle_scale` double DEFAULT '1',
  `p_vehicle_showimage` tinyint(1) NOT NULL DEFAULT '1',
  `p_vehicle_showtext` tinyint(1) NOT NULL DEFAULT '1',
  `p_vehicle_pt` tinyint(1) DEFAULT NULL,
  `p_vehicle_line` tinyint(1) NOT NULL DEFAULT '1',
  `p_vehicle_color` char(8) COLLATE utf8mb3_unicode_ci DEFAULT 'FFFFFFFF' COMMENT 'AABBGGRR',
  PRIMARY KEY (`p_vehicle_id`),
  UNIQUE KEY `p_vehicle_profileid_2` (`p_vehicle_profileid`,`p_vehicle_vehicleid`),
  KEY `p_vehicle_profileid` (`p_vehicle_profileid`),
  KEY `p_vehicle_vehicleid` (`p_vehicle_vehicleid`)
) ENGINE=MyISAM AUTO_INCREMENT=2292 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_user`
--

DROP TABLE IF EXISTS `core_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_user` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_active_profileid` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_vehicle`
--

DROP TABLE IF EXISTS `core_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_vehicle` (
  `vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `vehicle_name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vehicle_owner` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vehicle_loa` float DEFAULT '0.01' COMMENT 'length overall in meters',
  `vehicle_beam` float DEFAULT '0.01' COMMENT 'beam in meters',
  `vehicle_image` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT 'geov/images/default.png' COMMENT 'file name of image relative to web root',
  `vehicle_disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is the vehicle disabled from view in all profiles',
  PRIMARY KEY (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=672704001 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `core_vehicle_default`
--

DROP TABLE IF EXISTS `core_vehicle_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_vehicle_default` (
  `v_default_id` int unsigned NOT NULL AUTO_INCREMENT,
  `v_default_type` varchar(255) NOT NULL,
  `v_default_owner` varchar(255) NOT NULL,
  `v_default_use_loa` float NOT NULL,
  `v_default_use_beam` float NOT NULL,
  `v_default_use_image` varchar(255) NOT NULL,
  PRIMARY KEY (`v_default_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: `geov_moos_cp`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_moos_cp` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_moos_cp`;

--
-- Table structure for table `moos_cp_connected`
--

DROP TABLE IF EXISTS `moos_cp_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_cp_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`connected_id`)
) ENGINE=MyISAM AUTO_INCREMENT=273 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_cp_data`
--

DROP TABLE IF EXISTS `moos_cp_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_cp_data` (
  `data_id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_time` double NOT NULL COMMENT 'unix epoch timestamp',
  `data_variable` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_userid` int NOT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_time` (`data_time`)
) ENGINE=MyISAM AUTO_INCREMENT=14953 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_cp_profile`
--

DROP TABLE IF EXISTS `moos_cp_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_cp_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_cp_profile_vehicle`
--

DROP TABLE IF EXISTS `moos_cp_profile_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_cp_profile_vehicle` (
  `p_vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `p_vehicle_profileid` int unsigned NOT NULL,
  `p_vehicle_vehicleid` int unsigned NOT NULL,
  `p_vehicle_disp_cp` tinyint(1) NOT NULL,
  PRIMARY KEY (`p_vehicle_id`),
  UNIQUE KEY `p_vehicle_profileid_2` (`p_vehicle_profileid`,`p_vehicle_vehicleid`),
  KEY `p_vehicle_profileid` (`p_vehicle_profileid`),
  KEY `p_vehicle_vehicleid` (`p_vehicle_vehicleid`)
) ENGINE=MyISAM AUTO_INCREMENT=271 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: `geov_moos_ctd`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_moos_ctd` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_moos_ctd`;

--
-- Table structure for table `moos_ctd_connected`
--

DROP TABLE IF EXISTS `moos_ctd_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_ctd_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`connected_id`)
) ENGINE=MyISAM AUTO_INCREMENT=305 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_ctd_data`
--

DROP TABLE IF EXISTS `moos_ctd_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_ctd_data` (
  `data_id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_time` double NOT NULL COMMENT 'unix epoch timestamp',
  `data_variable` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_userid` int NOT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_time` (`data_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1566 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_ctd_profile`
--

DROP TABLE IF EXISTS `moos_ctd_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_ctd_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  `profile_temp_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `profile_temp_opacity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: `geov_moos_depth`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_moos_depth` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_moos_depth`;

--
-- Table structure for table `moos_depth_connected`
--

DROP TABLE IF EXISTS `moos_depth_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_depth_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`connected_id`)
) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_depth_profile`
--

DROP TABLE IF EXISTS `moos_depth_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_depth_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_depth_profile_vehicle`
--

DROP TABLE IF EXISTS `moos_depth_profile_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_depth_profile_vehicle` (
  `p_vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `p_vehicle_profileid` int unsigned NOT NULL,
  `p_vehicle_vehicleid` int unsigned NOT NULL,
  `p_vehicle_disp_depth` tinyint(1) NOT NULL,
  PRIMARY KEY (`p_vehicle_id`),
  KEY `p_vehicle_profileid` (`p_vehicle_profileid`),
  KEY `p_vehicle_vehicleid` (`p_vehicle_vehicleid`)
) ENGINE=MyISAM AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: `geov_moos_mseas`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_moos_mseas` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_moos_mseas`;

--
-- Table structure for table `moos_mseas_connected`
--

DROP TABLE IF EXISTS `moos_mseas_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_mseas_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`connected_id`)
) ENGINE=MyISAM AUTO_INCREMENT=265 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_mseas_data`
--

DROP TABLE IF EXISTS `moos_mseas_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_mseas_data` (
  `data_id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_time` double NOT NULL COMMENT 'unix epoch timestamp',
  `data_variable` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_userid` int NOT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_time` (`data_time`)
) ENGINE=MyISAM AUTO_INCREMENT=14978 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_mseas_profile`
--

DROP TABLE IF EXISTS `moos_mseas_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_mseas_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  `profile_displaymseas` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_mseas_profile_vehicle`
--

DROP TABLE IF EXISTS `moos_mseas_profile_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_mseas_profile_vehicle` (
  `p_vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `p_vehicle_profileid` int unsigned NOT NULL,
  `p_vehicle_vehicleid` int unsigned NOT NULL,
  PRIMARY KEY (`p_vehicle_id`),
  UNIQUE KEY `p_vehicle_profileid_2` (`p_vehicle_profileid`,`p_vehicle_vehicleid`),
  KEY `p_vehicle_profileid` (`p_vehicle_profileid`),
  KEY `p_vehicle_vehicleid` (`p_vehicle_vehicleid`)
) ENGINE=MyISAM AUTO_INCREMENT=274 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: `geov_moos_nafcon_target`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_moos_nafcon_target` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_moos_nafcon_target`;

--
-- Table structure for table `moos_nafcon_target_config`
--

DROP TABLE IF EXISTS `moos_nafcon_target_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_nafcon_target_config` (
  `config_key` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`config_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_nafcon_target_connected`
--

DROP TABLE IF EXISTS `moos_nafcon_target_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_nafcon_target_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`connected_id`)
) ENGINE=MyISAM AUTO_INCREMENT=238 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_nafcon_target_data`
--

DROP TABLE IF EXISTS `moos_nafcon_target_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_nafcon_target_data` (
  `data_id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_time` double NOT NULL COMMENT 'unix epoch timestamp',
  `data_variable` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_userid` int NOT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_time` (`data_time`)
) ENGINE=MyISAM AUTO_INCREMENT=6794 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_nafcon_target_profile`
--

DROP TABLE IF EXISTS `moos_nafcon_target_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_nafcon_target_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  `profile_display_target` tinyint(1) NOT NULL DEFAULT '1',
  `profile_decay` double NOT NULL DEFAULT '300',
  `profile_modemlookup` varchar(512) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'modules/moos_nafcon_target/modemidlookup.txt',
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Current Database: `geov_moos_opgrid`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `geov_moos_opgrid` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `geov_moos_opgrid`;

--
-- Table structure for table `moos_opgrid_connected`
--

DROP TABLE IF EXISTS `moos_opgrid_connected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_opgrid_connected` (
  `connected_id` int unsigned NOT NULL AUTO_INCREMENT,
  `connected_reload` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`connected_id`)
) ENGINE=MyISAM AUTO_INCREMENT=300 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_opgrid_data`
--

DROP TABLE IF EXISTS `moos_opgrid_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_opgrid_data` (
  `data_id` int unsigned NOT NULL AUTO_INCREMENT,
  `data_time` double NOT NULL COMMENT 'unix epoch timestamp',
  `data_variable` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_value` text COLLATE utf8mb3_unicode_ci NOT NULL,
  `data_userid` int NOT NULL,
  PRIMARY KEY (`data_id`),
  KEY `data_time` (`data_time`)
) ENGINE=MyISAM AUTO_INCREMENT=155504 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_opgrid_profile`
--

DROP TABLE IF EXISTS `moos_opgrid_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_opgrid_profile` (
  `profile_id` int unsigned NOT NULL AUTO_INCREMENT,
  `profile_displayop` tinyint(1) NOT NULL DEFAULT '1',
  `profile_opbox` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `profile_opbox_latlong` varchar(512) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `profile_datumlat` double NOT NULL DEFAULT '0',
  `profile_datumlon` double NOT NULL DEFAULT '0',
  `profile_displayxy` tinyint(1) NOT NULL DEFAULT '1',
  `profile_xyspacing` double NOT NULL DEFAULT '1000',
  `profile_xyspacing_sub` double NOT NULL DEFAULT '100',
  `profile_markers` text COLLATE utf8mb3_unicode_ci,
  `profile_polygons` text COLLATE utf8mb3_unicode_ci,
  `profile_reload` tinyint(1) NOT NULL DEFAULT '1',
  `profile_autoshow` tinyint(1) NOT NULL DEFAULT '1',
  `profile_autoshowexpand` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`profile_id`)
) ENGINE=MyISAM AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moos_opgrid_profile_vehicle`
--

DROP TABLE IF EXISTS `moos_opgrid_profile_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moos_opgrid_profile_vehicle` (
  `p_vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `p_vehicle_profileid` int unsigned NOT NULL,
  `p_vehicle_vehicleid` int unsigned NOT NULL,
  `p_vehicle_auto` tinyint(1) NOT NULL DEFAULT '0',
  `p_vehicle_viewpoint` varchar(512) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '1',
  `p_vehicle_viewpolygon` text COLLATE utf8mb3_unicode_ci,
  `p_vehicle_viewcircle` varchar(512) COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`p_vehicle_id`),
  UNIQUE KEY `p_vehicle_profileid_2` (`p_vehicle_profileid`,`p_vehicle_vehicleid`),
  KEY `p_vehicle_profileid` (`p_vehicle_profileid`),
  KEY `p_vehicle_vehicleid` (`p_vehicle_vehicleid`)
) ENGINE=MyISAM AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-10 19:21:10
USE `geov_core`;
-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: localhost    Database: geov_core
-- ------------------------------------------------------
-- Server version	8.0.41-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `core_module`
--

DROP TABLE IF EXISTS `core_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_module` (
  `module_id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL,
  `module_file` varchar(1000) NOT NULL,
  `module_ge_viewer` varchar(255) NOT NULL,
  `module_refresh_time` int NOT NULL DEFAULT '5' COMMENT 'refresh time of ge_viewer.php in seconds',
  PRIMARY KEY (`module_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_module`
--

LOCK TABLES `core_module` WRITE;
/*!40000 ALTER TABLE `core_module` DISABLE KEYS */;
INSERT INTO `core_module` VALUES (5,'moos_cp','modules/moos_cp/module.php','modules/moos_cp/ge_viewer.php',1),(4,'moos_nafcon_target','modules/moos_nafcon_target/module.php','modules/moos_nafcon_target/ge_viewer.php',5),(3,'moos_opgrid','modules/moos_opgrid/module.php','modules/moos_opgrid/ge_viewer.php',5),(7,'moos_ctd','modules/moos_ctd/module.php','modules/moos_ctd/ge_viewer.php',30),(8,'moos_mseas','modules/moos_mseas/module.php','modules/moos_mseas/ge_viewer.php',5);
/*!40000 ALTER TABLE `core_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_page`
--

DROP TABLE IF EXISTS `core_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_page` (
  `page_id` int unsigned NOT NULL,
  `page_name` varchar(255) NOT NULL,
  `page_uri` varchar(255) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_page`
--

LOCK TABLES `core_page` WRITE;
/*!40000 ALTER TABLE `core_page` DISABLE KEYS */;
INSERT INTO `core_page` VALUES (1,'home','index.php'),(3,'profile manager','profile.php'),(7,'download geov kml','dl_kml.php'),(5,'import .alog','import_alog.php'),(2,'instructions','help.php'),(4,'vehicle config','vehicle.php'),(6,'tools','tool.php');
/*!40000 ALTER TABLE `core_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_vehicle`
--

DROP TABLE IF EXISTS `core_vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_vehicle` (
  `vehicle_id` int unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_type` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `vehicle_name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vehicle_owner` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `vehicle_loa` float DEFAULT '0.01' COMMENT 'length overall in meters',
  `vehicle_beam` float DEFAULT '0.01' COMMENT 'beam in meters',
  `vehicle_image` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT 'geov/images/default.png' COMMENT 'file name of image relative to web root',
  `vehicle_disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'is the vehicle disabled from view in all profiles',
  PRIMARY KEY (`vehicle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=672704001 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_vehicle`
--

LOCK TABLES `core_vehicle` WRITE;
/*!40000 ALTER TABLE `core_vehicle` DISABLE KEYS */;
INSERT INTO `core_vehicle` VALUES (5,'kayak','dee','mit',2,0.5,'geov/images/kayak.png',0),(6,'kayak','bobby','mit',2,0.5,'geov/images/kayak.png',0),(8,'auv','oex','nurc',3,0.5334,'geov/images/auv_oex.png',0),(9,'auv','unicorn','mit',3,0.5,'geov/images/auv.png',0),(10,'kayak','elanor','mit',2,0.5,'geov/images/kayak.png',0),(12,'auv','sea-horse','mit',3,0.5,'geov/images/auv.png',0),(13,'buoy','gateway','mit',0.3,0.3,'geov/images/buoy.png',0),(14,'auv','macrura','mit',3,0.5,'geov/images/auv.png',0),(15,'kayak','xulu','mit',2,0.5,'geov/images/kayak.png',0),(16,'kayak','yolanda','mit',2,0.5,'geov/images/kayak.png',0),(17,'kayak','zero','mit',2,0.5,'geov/images/kayak.png',0),(3,'ship','ptsur','moss landings',41.14,9.75,'geov/images/ship.png',0),(4,'kayak','frankie','mit',2,0.5,'geov/images/kayak.png',0),(18,'auv','hunter1','mit',3,0.8,'geov/images/kayak.png',0),(19,'kayak','hunter2','mit',3,0.8,'geov/images/kayak.png',0),(21,'uuc','uuc','',0.1,0.1,'geov/images/default.png',0),(22,'glider','x-ray','',0.1,0.1,'geov/images/default.png',0),(23,'auv','whitetip','nuwc',1,0.15,'geov/images/auv.png',0),(24,'glider','folaga','',2,0.25,'geov/images/auv.png',0),(25,'track','trk_5_unicorn','',0.01,0.01,'geov/images/default.png',0),(26,'ship','alliance','nurc',93,15.2,'geov/images/ship.png',0),(27,'buoy','whoi_1','whoi',0.3,0.3,'geov/images/buoy.png',0),(28,'auv','hammerhead','nuwc',1,0.2,'geov/images/auv.png',0),(29,'auv','mako','',0.01,0.01,'geov/images/default.png',0),(30,'ship','leonardo','nurc',28,9,'geov/images/ship.png',0),(31,'fixed_node','modem_on_a_rope','',0.3,0.3,'geov/images/buoy.png',0),(32,'track','trk_1_unicorn','',0.01,0.01,'geov/images/default.png',0),(33,'asc','mandarina','nurc',0.01,0.01,'geov/images/default.png',0),(46,'ship_stern','endeavor_stern','',0.01,0.01,'geov/images/default.png',0),(42,'ship','endeavor','nsf',56.4,10,'geov/images/ship.png',0),(47,'target','trk_0_3','',0.01,0.01,'geov/images/default.png',0),(211212500,'ship','rv alliance','',93,14,'geov/images/default.png',0),(247155800,'ship','leonardo','',28,9,'geov/images/default.png',0),(247157500,'ship','sider faioch','',144,23,'geov/images/default.png',0),(247048300,'ship','dianium','',42,8,'geov/images/default.png',0),(247238500,'ship','revenge','',40,8,'geov/images/default.png',0),(311317000,'ship','voyager of the seas','',311,38,'geov/images/default.png',0),(247621000,'ship','isola del giglio','',61,11,'geov/images/default.png',0),(255803270,'ship','grand celebration','',223,30,'geov/images/default.png',0),(247163900,'ship','snav sardegna','',161,30,'geov/images/default.png',0),(247237700,'ship','snav toscana','',169,27,'geov/images/default.png',0),(247552000,'ship','corsica marina ii','',120,20,'geov/images/default.png',0),(247226500,'ship','claudia m','',178,24,'geov/images/default.png',0),(247214200,'ship','gigierre','',38,7,'geov/images/default.png',0),(247322000,'ship','moby love','',114,18,'geov/images/default.png',0),(247015400,'ship','moby wonder','',175,28,'geov/images/default.png',0),(247086300,'ship','sorrento','',186,30,'geov/images/default.png',0),(247046800,'ship','oglasa','',91,16,'geov/images/default.png',0),(247065490,'ship','ulisse','',23,6,'geov/images/default.png',0),(247216500,'ship','signora del vento','',60,10,'geov/images/default.png',0),(48,'auv','e-oex','',0.01,0.01,'geov/images/default.png',0),(247206800,'ship','freccia del gilio 2','',15,6,'geov/images/default.png',0),(247685000,'ship','laura prima','',100,10,'geov/images/default.png',0),(247163100,'ship','snav lazio','',162,29,'geov/images/default.png',0),(247207600,'ship','domizia','',13,4,'geov/images/default.png',0),(247188200,'ship','coraggio','',199,26,'geov/images/default.png',0),(247132400,'ship','moby aki','',175,28,'geov/images/default.png',0),(49,'track','tgt_1_sim','',0.01,0.01,'geov/images/default.png',0),(50,'track','tgt_1_unicorn','',0.01,0.01,'geov/images/default.png',0),(51,'ship','knorr','',0.01,0.01,'geov/images/default.png',0),(52,'ship','resolution','',16,6.67,'geov/images/ship.png',0),(247166600,'ship','rais del golfo','',28,6,'geov/images/default.png',0),(247056100,'ship','la superba','',211,30,'geov/images/default.png',0),(247030100,'ship','strada corsara','',162,18,'geov/images/default.png',0),(249035000,'ship','glory','',225,32,'geov/images/default.png',0),(247285600,'ship','cdry white','',108,18,'geov/images/default.png',0),(311361000,'ship','brilliance ofthe sea','',294,23,'geov/images/default.png',0),(235064391,'ship','watling street','',186,26,'geov/images/default.png',0),(538080007,'ship','excellence 3','',57,11,'geov/images/default.png',0),(247242800,'ship','esprit','',0,0,'geov/images/default.png',0),(247174300,'ship','primo m','',144,23,'geov/images/default.png',0),(247230200,'ship','tenacia','',199,26,'geov/images/default.png',0),(247034500,'ship','moby freedom','',175,27,'geov/images/default.png',0),(247207500,'ship','vieste 2','',0,0,'geov/images/default.png',0),(247136000,'ship','splendid','',213,26,'geov/images/default.png',0),(636091244,'ship','m/t hc elida','',186,27,'geov/images/default.png',0),(247207200,'ship','mar di giava','',37,7,'geov/images/default.png',0),(53,'buoy','mit_gateway','',0.01,0.01,'geov/images/default.png',0),(247185400,'ship','moby otta','',182,26,'geov/images/default.png',0),(538090044,'ship','wehr ottensen','',184,25,'geov/images/default.png',0),(319395000,'ship','zoom zoom zoom','',50,8,'geov/images/default.png',0),(247259100,'ship','onda blu','',133,20,'geov/images/default.png',0),(247219400,'ship','cruise roma','',224,30,'geov/images/default.png',0),(247106500,'ship','nuraghes','',215,75,'geov/images/default.png',0),(377014000,'ship','carrara','',97,14,'geov/images/default.png',0),(247071600,'ship','m/y rose pigre','',30,6,'geov/images/default.png',0),(247245000,'ship','moby fantasy','',141,20,'geov/images/default.png',0),(248481000,'ship','thomson dream','',243,32,'geov/images/default.png',0),(271001018,'ship','vento di bora','',154,24,'geov/images/default.png',0),(357698000,'ship','msc splendida','',333,50,'geov/images/default.png',0),(54,'buoy','micromodem_buoy','',0.01,0.01,'geov/images/default.png',0),(310327000,'ship','grand princess','',289,36,'geov/images/default.png',0),(247001200,'ship','aurelia','',148,23,'geov/images/default.png',0),(247438000,'ship','brucoli','',37,8,'geov/images/default.png',0),(247026000,'ship','eurocargo napoli','',175,25,'geov/images/default.png',0),(247082300,'ship','ursa minor','',205,28,'geov/images/default.png',0),(255969000,'ship','arion','',118,16,'geov/images/default.png',0),(247050400,'ship','strada gigante','',188,20,'geov/images/default.png',0),(355386000,'ship','andrea palladio','',235,43,'geov/images/default.png',0),(247191000,'ship','corsica express ii','',105,16,'geov/images/default.png',0),(310473000,'ship','oceana','',261,40,'geov/images/default.png',0),(1193046,'ship','my a and a','',44,8,'geov/images/default.png',0),(247183900,'ship','clipper karina','',116,20,'geov/images/default.png',0),(1193047,'buoy','micromodem_gateway','',0.01,0.01,'geov/images/default.png',0),(1193069,'buoy','micromodem_','',0.01,0.01,'geov/images/default.png',0),(1193070,'ship','micromodem_gateway','',0.01,0.01,'geov/images/default.png',0),(1193071,'auv','oex_harpo','',3,1,'geov/images/sardine2.png',0),(247240000,'ship','luigi pa','',160,21,'geov/images/default.png',0),(305183000,'ship','dinah borchard','',127,20,'geov/images/default.png',0),(247041900,'ship','g bellini','',0,0,'geov/images/default.png',0),(247235000,'ship','jolly blu','',148,29,'geov/images/default.png',0),(212659000,'ship','primrose','',0,0,'geov/images/default.png',0),(247293800,'ship','eurocargo genova','',200,26,'geov/images/default.png',0),(247458000,'ship','giraglia','',75,14,'geov/images/default.png',0),(235058928,'ship','k wave','',133,19,'geov/images/default.png',0),(247113900,'ship','sider marleen','',104,14,'geov/images/default.png',0),(1193072,'auv','oex_groucho','',0.01,0.01,'geov/images/default.png',0),(1193073,'unicorn','unicorn','',0.01,0.01,'geov/images/default.png',0),(1193074,'buoy','alliance','',0.01,0.01,'geov/images/default.png',0),(247254900,'ship','sider giglio','',131,17,'geov/images/default.png',0),(304010802,'ship','bosporus','',100,13,'geov/images/default.png',0),(247001100,'ship','clodia','',148,25,'geov/images/default.png',0),(212740000,'ship','neuburg','',142,22,'geov/images/default.png',0),(237628000,'ship','el.venizelos','',175,28,'geov/images/default.png',0),(247279000,'ship','excellent','',203,28,'geov/images/default.png',0),(563907000,'ship','altanger','',168,26,'geov/images/default.png',0),(477990800,'ship','gh fortune','',225,32,'geov/images/default.png',0),(240813000,'ship','maria princess','',229,42,'geov/images/default.png',0),(636012253,'ship','ym fountain','',275,40,'geov/images/default.png',0),(311368000,'ship','island escape','',185,30,'geov/images/default.png',0),(538090215,'ship','ecem kalkavan','',148,22,'geov/images/default.png',0),(377618000,'ship','starfire','',54,10,'geov/images/default.png',0),(247186700,'ship','moby tommy','',212,24,'geov/images/default.png',0),(247105900,'ship','florencia','',0,0,'geov/images/default.png',0),(636012824,'ship','elia','',249,44,'geov/images/default.png',0),(304756000,'ship','rita sibum','',132,19,'geov/images/default.png',0),(247816000,'ship','costa allegra','',188,25,'geov/images/default.png',0),(271002353,'ship','seher 2','',74,10,'geov/images/default.png',0),(247108000,'ship','lng portovenere','',216,32,'geov/images/default.png',0),(247036100,'ship','mega express two','',170,24,'geov/images/default.png',0),(247253000,'ship','certamen','',84,20,'geov/images/default.png',0),(247664000,'ship','moby vincent','',115,20,'geov/images/default.png',0),(563496000,'ship','hardanger','',213,31,'geov/images/default.png',0),(247250000,'ship','martina','',125,20,'geov/images/default.png',0),(247046600,'ship','marmorica','',91,16,'geov/images/default.png',0),(247205000,'ship','giuseppe sa','',168,21,'geov/images/default.png',0),(253144000,'ship','norjan','',130,20,'geov/images/default.png',0),(305317000,'ship','mcl alger','',101,15,'geov/images/default.png',0),(636091725,'ship','stella jabbah','',180,28,'geov/images/default.png',0),(314237000,'ship','wilson gaeta','',88,13,'geov/images/default.png',0),(319107000,'ship','reverie','',70,12,'geov/images/default.png',0),(319762000,'ship','saint nicolas','',70,13,'geov/images/default.png',0),(636090869,'ship','virginia','',294,32,'geov/images/default.png',0),(247130700,'ship','sharden','',220,37,'geov/images/default.png',0),(247616000,'ship','moby lally','',118,19,'geov/images/default.png',0),(247052700,'ship','pace','',0,0,'geov/images/default.png',0),(247067400,'ship','valdaosta','',177,27,'geov/images/default.png',0),(247129000,'ship','moby ale','',93,16,'geov/images/default.png',0),(319045000,'ship','lady allison','',45,9,'geov/images/default.png',0),(247273700,'ship','lisca nera m','',180,20,'geov/images/default.png',0),(247049900,'ship','moby baby','',99,18,'geov/images/default.png',0),(247243600,'ship','cruise barcellona','',224,30,'geov/images/default.png',0),(258038000,'ship','canica','',42,8,'geov/images/default.png',0),(247244600,'ship','south','',54,10,'geov/images/default.png',0),(319672000,'ship','nero','',90,12,'geov/images/default.png',0),(255801350,'ship','diamond star','',89,14,'geov/images/default.png',0),(247148600,'ship','ray g','',177,30,'geov/images/default.png',0),(215811000,'ship','seasong','',244,42,'geov/images/default.png',0),(312822000,'ship','aviva','',141,18,'geov/images/default.png',0),(247284200,'ship','giglio','',75,15,'geov/images/default.png',0),(308038000,'ship','coral leader','',176,31,'geov/images/default.png',0),(356505000,'ship','msc esha','',147,23,'geov/images/default.png',0),(247047600,'ship','planasia','',71,14,'geov/images/default.png',0),(356471000,'ship','msc jenny','',244,32,'geov/images/default.png',0),(378001000,'ship','scarena','',30,6,'geov/images/default.png',0),(370204000,'ship','m/v msc ronit','',176,28,'geov/images/default.png',0),(247089500,'ship','mega express three','',212,30,'geov/images/default.png',0),(228200800,'ship','capo nero','',90,13,'geov/images/default.png',0),(247279400,'ship','enzo d','',74,16,'geov/images/default.png',0),(319997000,'ship','m/y wild thyme','',37,8,'geov/images/default.png',0),(235079875,'ship','azteca','',72,13,'geov/images/default.png',0),(319650000,'ship','my alwaeli','',65,12,'geov/images/default.png',0),(319196000,'ship','grace e','',50,10,'geov/images/default.png',0),(319526000,'ship','dilbar','',110,16,'geov/images/default.png',0),(314245000,'ship','selene','',56,6,'geov/images/default.png',0),(235054081,'ship','s/y_julie marie','',30,8,'geov/images/default.png',0),(319361000,'ship','al mirqab','',133,18,'geov/images/default.png',0),(319949000,'ship','pestifer','',18,12,'geov/images/default.png',0),(319004200,'ship','lady lara','',0,0,'geov/images/default.png',0),(319292000,'ship','red dragon','',52,10,'geov/images/default.png',0),(234429000,'ship','velsheda','',39,7,'geov/images/default.png',0),(372668000,'ship','gaz fraternity','',154,26,'geov/images/default.png',0),(232060000,'ship','lady sarya','',76,12,'geov/images/default.png',0),(211464820,'ship','galloper','',19,4,'geov/images/default.png',0),(253381000,'ship','st-pauline','',105,20,'geov/images/default.png',0),(247121200,'ship','squalo iv','',30,7,'geov/images/default.png',0),(357216000,'ship','petra f','',0,0,'geov/images/default.png',0),(258782000,'ship','hoegh treasure','',199,32,'geov/images/default.png',0),(247034200,'ship','bithia','',216,26,'geov/images/default.png',0),(538090141,'ship','vento di nortada','',149,22,'geov/images/default.png',0),(319031500,'ship','gu','',58,10,'geov/images/default.png',0),(247436000,'ship','ichnusa','',90,14,'geov/images/default.png',0),(247045500,'ship','isola di caprera','',0,0,'geov/images/default.png',0),(319002700,'ship','m/y leo fun','',53,10,'geov/images/default.png',0),(240183000,'ship','omega','',81,13,'geov/images/default.png',0),(235053716,'ship','csav rio aysen','',183,32,'geov/images/default.png',0),(247048500,'ship','gennaro ievoli','',171,27,'geov/images/default.png',0),(228081000,'ship','pascal paoli','',175,34,'geov/images/default.png',0),(533051000,'ship','seri bijaksana','',277,46,'geov/images/default.png',0),(244162000,'ship','asiaborg','',141,22,'geov/images/default.png',0),(514166000,'ship','silver1','',80,16,'geov/images/default.png',0),(247313000,'ship','giulio verne','',27,30,'geov/images/default.png',0),(352413000,'ship','msc amy','',157,22,'geov/images/default.png',0),(248129000,'ship','sierra romeo','',42,8,'geov/images/default.png',0),(247314000,'ship','eurocargo salerno','',195,25,'geov/images/default.png',0),(247579000,'ship','eurocargo valencia','',196,25,'geov/images/default.png',0),(248145000,'ship','atlantis alvarado','',92,14,'geov/images/default.png',0),(377714000,'ship','azzurra ii','',45,12,'geov/images/default.png',0),(247039300,'ship','grande italia','',177,31,'geov/images/default.png',0),(247091800,'ship','vesuvio jet','',65,15,'geov/images/default.png',0),(477927000,'ship','heng chang','',181,30,'geov/images/default.png',0),(310094000,'ship','golden shadow','',66,11,'geov/images/default.png',0),(538001964,'ship','cape bacton','',175,31,'geov/images/default.png',0),(215978000,'ship','montauk','',110,17,'geov/images/default.png',0),(226280000,'ship','jean nicoli','',200,26,'geov/images/default.png',0),(249539000,'ship','sovereign','',268,32,'geov/images/default.png',0),(247409000,'ship','gialess','',30,10,'geov/images/default.png',0),(247088200,'ship','costa mediterranea','',292,32,'geov/images/default.png',0),(247115100,'ship','portodiroma','',0,0,'geov/images/default.png',0),(212112000,'ship','cma-cgm colibri','',171,28,'geov/images/default.png',0),(247153900,'ship','san luca primo','',60,13,'geov/images/default.png',0),(432722000,'ship','dionysos leader','',200,32,'geov/images/default.png',0),(538002969,'ship','uct elizabeth','',118,19,'geov/images/default.png',0),(271000662,'ship','und birlik','',193,26,'geov/images/default.png',0),(247298000,'ship','marco m.','',144,18,'geov/images/default.png',0),(235081036,'ship','apoise','',67,14,'geov/images/default.png',0),(215545000,'ship','isabella','',244,42,'geov/images/default.png',0),(271001044,'ship','black sea','',280,70,'geov/images/default.png',0),(310591000,'ship','luna','',115,18,'geov/images/default.png',0),(228083000,'ship','philippine','',95,16,'geov/images/default.png',0),(235076415,'ship','wessex','',193,28,'geov/images/default.png',0),(235069333,'ship','riela','',0,0,'geov/images/default.png',0),(256704000,'ship','frelon','',77,12,'geov/images/default.png',0),(356042000,'ship','msc lirica','',253,32,'geov/images/default.png',0),(246648000,'ship','nieuw amsterdam','',285,36,'geov/images/default.png',0),(240349000,'ship','rm elegant','',72,12,'geov/images/default.png',0),(538003557,'ship','orient queen','',160,23,'geov/images/default.png',0),(247199000,'ship','syn markab','',98,7,'geov/images/default.png',0),(247182200,'ship','sds wind','',108,18,'geov/images/default.png',0),(247170500,'ship','jolly arancione','',0,0,'geov/images/default.png',0),(255996000,'ship','costa magna','',45,8,'geov/images/default.png',0),(245166000,'ship','eemsdijk','',141,23,'geov/images/default.png',0),(308416000,'ship','norwegian jade','',294,32,'geov/images/default.png',0),(357144000,'ship','crown topaz','',152,23,'geov/images/default.png',0),(247550000,'ship','favignana','',54,11,'geov/images/default.png',0),(235059367,'ship','le  yana','',43,9,'geov/images/default.png',0),(249667000,'ship','celebrity equinox','',316,46,'geov/images/default.png',0),(538090362,'ship','cafer dede','',182,27,'geov/images/default.png',0),(354499000,'ship','msc esthi','',337,46,'geov/images/default.png',0),(538080070,'ship','toy-a','',50,9,'geov/images/default.png',0),(247098600,'ship','alicudi m','',176,34,'geov/images/default.png',0),(247238700,'ship','leale','',112,17,'geov/images/default.png',0),(247145900,'ship','big cem','',97,16,'geov/images/default.png',0),(247157300,'ship','m/t xenia','',0,0,'geov/images/default.png',0),(305144000,'ship','libertas-h','',127,20,'geov/images/default.png',0),(311179000,'ship','triton','',225,32,'geov/images/default.png',0),(311622000,'ship','seven seas mariner','',216,28,'geov/images/default.png',0),(304010331,'ship','beatrice','',107,18,'geov/images/default.png',0),(319943000,'ship','larisa','',0,0,'geov/images/default.png',0),(249421000,'ship','hitra','',107,16,'geov/images/default.png',0),(1193077,'broadcast','broadcast','',0.01,0.01,'geov/images/default.png',0),(319854000,'ship','va bene','',50,10,'geov/images/default.png',0),(310538000,'ship','triple seven','',68,12,'geov/images/default.png',0),(210031000,'ship','helas','',89,12,'geov/images/default.png',0),(256753000,'ship','mari mer','',87,0,'geov/images/default.png',0),(319348000,'ship','voyage','',40,7,'geov/images/default.png',0),(247258100,'ship','costa pacifica','',290,42,'geov/images/default.png',0),(247218800,'ship','matrix','',113,17,'geov/images/default.png',0),(235054475,'ship','blue vision','',45,14,'geov/images/default.png',0),(636091905,'ship','vera d','',178,26,'geov/images/default.png',0),(247274800,'ship','chaitali prem','',229,38,'geov/images/default.png',0),(319910000,'ship','tamsen','',52,10,'geov/images/default.png',0),(247002700,'ship','lazio','',0,0,'geov/images/default.png',0),(354204000,'ship','kurobe','',162,28,'geov/images/default.png',0),(247184700,'ship','clipper kate','',117,20,'geov/images/default.png',0),(211391170,'ship','hanjin helsinki','',274,40,'geov/images/default.png',0),(245864000,'ship','kwintebank','',132,16,'geov/images/default.png',0),(247112000,'ship','pasquale della gatta','',225,32,'geov/images/default.png',0),(213888000,'ship','cpt ahmad 1','',93,14,'geov/images/default.png',0),(247083700,'ship','la suprema','',211,30,'geov/images/default.png',0),(235076417,'ship','mercia','',193,26,'geov/images/default.png',0),(310494000,'ship','carmel bio-top','',186,26,'geov/images/default.png',0),(304962000,'ship','sara borchard','',134,22,'geov/images/default.png',0),(310181000,'ship','virginian','',62,10,'geov/images/default.png',0),(227188000,'ship','corse','',145,26,'geov/images/default.png',0),(240836000,'ship','phaethon','',274,48,'geov/images/default.png',0),(244036000,'ship','coral leaf','',108,17,'geov/images/default.png',0),(636091109,'ship','maersk santana','',335,42,'geov/images/default.png',0),(538080034,'ship','daybreak','',46,8,'geov/images/default.png',0),(254450000,'ship','libra star','',40,10,'geov/images/default.png',0),(235077872,'ship','lammouche','',0,0,'geov/images/default.png',0),(247229300,'ship','sichem rio','',121,20,'geov/images/default.png',0),(247580000,'ship','corsica victoria','',146,22,'geov/images/default.png',0),(636090654,'ship','m/v msc vienna','',260,32,'geov/images/default.png',0),(235070013,'ship','roma','',55,11,'geov/images/default.png',0),(319008900,'ship','northern star','',75,42,'geov/images/default.png',0),(236548000,'ship','festivo','',136,16,'geov/images/default.png',0),(247102250,'ship','my ariete primo','',44,8,'geov/images/default.png',0),(672247000,'ship','salammbo7','',162,24,'geov/images/default.png',0),(304753000,'ship','cartagena','',100,18,'geov/images/default.png',0),(247002300,'ship','domiziana','',148,23,'geov/images/default.png',0),(311085000,'ship','seabourn legend','',135,20,'geov/images/default.png',0),(319933000,'ship','stanley','',0,0,'geov/images/default.png',0),(319098000,'ship','aurora','',60,12,'geov/images/default.png',0),(19802,'ship','uos pathfinder','',77,18,'geov/images/default.png',0),(311478000,'ship','navigator ofthe seas','',311,38,'geov/images/default.png',0),(247116600,'ship','grande anversa','',176,31,'geov/images/default.png',0),(310133000,'ship','valencia express','',215,32,'geov/images/default.png',0),(356454000,'ship','libera','',100,15,'geov/images/default.png',0),(271000266,'ship','kazim genc','',93,14,'geov/images/default.png',0),(273454720,'ship','aston_challenger','',88,12,'geov/images/default.png',0),(672704000,'ship','elyssa','',192,26,'geov/images/default.png',0),(309242000,'ship','windsurf','',187,20,'geov/images/default.png',0),(319860000,'ship','s/y andromeda la dea','',48,9,'geov/images/default.png',0),(371581000,'ship','alfa dragon','',80,13,'geov/images/default.png',0),(376002500,'ship','m/v.dp reel','',91,18,'geov/images/default.png',0),(247134000,'ship','jolly grigio','',142,32,'geov/images/default.png',0),(319957000,'ship','alfa nero','',81,12,'geov/images/default.png',0),(240691000,'ship','astrea','',71,18,'geov/images/default.png',0),(310567000,'ship','ruby princess','',289,50,'geov/images/default.png',0),(319492000,'ship','amadeus','',70,12,'geov/images/default.png',0),(310562000,'ship','ventura','',289,50,'geov/images/default.png',0),(403533000,'ship','najran','',276,32,'geov/images/default.png',0),(247242200,'ship','canneto m','',162,23,'geov/images/default.png',0),(247351000,'ship','ievoli speed','',110,18,'geov/images/default.png',0),(226290000,'ship','raymond croze','',107,18,'geov/images/default.png',0),(247094000,'ship','fantastic','',189,26,'geov/images/default.png',0),(236039000,'ship','baltic skipper','',83,12,'geov/images/default.png',0),(247162200,'ship','moby drea','',184,24,'geov/images/default.png',0),(247086200,'ship','athara','',216,26,'geov/images/default.png',0),(247248000,'ship','mariella amoretti','',132,24,'geov/images/default.png',0),(538090090,'ship','clipper emperor','',224,32,'geov/images/default.png',0),(247020600,'ship','cp 273','',26,6,'geov/images/default.png',0),(352675000,'ship','akinada bridge','',285,40,'geov/images/default.png',0),(319560000,'ship','mondango','',52,8,'geov/images/default.png',0),(247602000,'ship','syn zaura','',95,16,'geov/images/default.png',0),(319136000,'ship','princess iolanthe','',30,9,'geov/images/default.png',0),(235007520,'ship','high chaparral','',49,9,'geov/images/default.png',0),(248456000,'ship','princess too','',48,9,'geov/images/default.png',0),(235008140,'ship','southern cross iii','',48,12,'geov/images/default.png',0),(247045600,'ship','isola di s. stefano','',0,0,'geov/images/default.png',0),(319019300,'ship','phoenix2','',86,13,'geov/images/default.png',0),(319840000,'ship','madsummer','',78,14,'geov/images/default.png',0),(428002000,'ship','zim virginia','',294,32,'geov/images/default.png',0),(518399000,'ship','victory','',52,10,'geov/images/default.png',0),(256920040,'ship','gaja','',35,6,'geov/images/default.png',0),(212844000,'ship','argo i','',65,14,'geov/images/default.png',0),(299000008,'ship','madre del carmine','',25,6,'geov/images/default.png',0),(235011270,'ship','hoppetosse','',30,7,'geov/images/default.png',0),(319610000,'ship','amevi','',80,12,'geov/images/default.png',0),(247196000,'ship','jolly indaco','',158,24,'geov/images/default.png',0),(310065000,'ship','golden odyssey','',80,13,'geov/images/default.png',0),(357405000,'ship','msc poh lin','',0,0,'geov/images/default.png',0),(319201000,'ship','lady sheridan','',58,11,'geov/images/default.png',0),(215934000,'ship','moonlight ii','',82,14,'geov/images/default.png',0),(244638000,'ship','vento di maestrale','',148,23,'geov/images/default.png',0),(354729000,'ship','farah 1','',72,13,'geov/images/default.png',0),(247274700,'ship','maria laura','',0,0,'geov/images/default.png',0),(304367000,'ship','bbc portugal','',86,13,'geov/images/default.png',0),(235065022,'ship','hana','',0,0,'geov/images/default.png',0),(247098200,'ship','catania','',186,27,'geov/images/default.png',0),(235681000,'ship','msc colombia','',294,32,'geov/images/default.png',0),(253337000,'ship','firouzeh','',38,8,'geov/images/default.png',0),(375119000,'ship','domani','',35,8,'geov/images/default.png',0),(305032000,'ship','helena sibum','',132,19,'geov/images/default.png',0),(236566000,'ship','eships dana','',116,18,'geov/images/default.png',0),(210038000,'ship','vento di levante','',148,23,'geov/images/default.png',0),(271000433,'ship','mv ilke mete','',94,14,'geov/images/default.png',0),(538090031,'ship','csav maresias','',184,25,'geov/images/default.png',0),(413057000,'ship','xin ning bo','',280,40,'geov/images/default.png',0),(310051000,'ship','talitha','',80,10,'geov/images/default.png',0),(247229600,'ship','luca s','',101,14,'geov/images/default.png',0),(215639000,'ship','berden','',79,30,'geov/images/default.png',0),(247162900,'ship','ginostra m','',121,14,'geov/images/default.png',0),(256049000,'ship','neptune thelisis','',164,25,'geov/images/default.png',0),(370946000,'ship','captain michalis s.','',38,10,'geov/images/default.png',0),(319026000,'ship','harmony','',50,10,'geov/images/default.png',0),(271000647,'ship','vento di aliseo','',156,25,'geov/images/default.png',0),(248618000,'ship','exuma','',48,10,'geov/images/default.png',0),(372973000,'ship','msc pina','',337,46,'geov/images/default.png',0),(563830000,'ship','schackenborg','',161,24,'geov/images/default.png',0),(319669000,'ship','bel abri','',52,9,'geov/images/default.png',0),(249453000,'ship','dumlupinar','',130,19,'geov/images/default.png',0),(247130000,'ship','jolly amaranto','',145,32,'geov/images/default.png',0),(375652000,'ship','onda','',116,13,'geov/images/default.png',0),(255971000,'ship','funchal','',153,19,'geov/images/default.png',0),(235749000,'ship','altitude','',55,10,'geov/images/default.png',0),(304626000,'ship','m/v andromeda','',81,14,'geov/images/default.png',0),(357702000,'ship','msc giovanna','',178,32,'geov/images/default.png',0),(538090286,'ship','cma cgm cortes','',194,28,'geov/images/default.png',0),(1193080,'ship','ttm_237','',0.01,0.01,'geov/images/default.png',0),(1193081,'ship','ttm_234','',0.01,0.01,'geov/images/default.png',0),(1193082,'ship','ttm_241','',0.01,0.01,'geov/images/default.png',0),(1193083,'ship','ttm_244','',0.01,0.01,'geov/images/default.png',0),(1193084,'ship','ttm_245','',0.01,0.01,'geov/images/default.png',0),(1193085,'ship','ttm_246','',0.01,0.01,'geov/images/default.png',0),(1193086,'ship','ttm_243','',0.01,0.01,'geov/images/default.png',0),(1193087,'ship','ttm_239','',0.01,0.01,'geov/images/default.png',0),(1193088,'ship','ttm_247','',0.01,0.01,'geov/images/default.png',0),(1193089,'ship','ttm_248','',0.01,0.01,'geov/images/default.png',0),(1193090,'ship','ttm_249','',0.01,0.01,'geov/images/default.png',0),(1193091,'ship','ttm_250','',0.01,0.01,'geov/images/default.png',0),(1193092,'ship','ttm_251','',0.01,0.01,'geov/images/default.png',0),(1193093,'ship','ttm_252','',0.01,0.01,'geov/images/default.png',0),(1193094,'ship','ttm_261','',0.01,0.01,'geov/images/default.png',0),(1193095,'ship','ttm_258','',0.01,0.01,'geov/images/default.png',0),(1193096,'ship','ttm_260','',0.01,0.01,'geov/images/default.png',0),(1193097,'buoy','hydroid_gateway','',0.5,0.5,'geov/images/buoy.png',0),(1193099,'auv','spermwhale','',0.01,0.01,'geov/images/default.png',0),(1193100,'auv','neptune','',0.01,0.01,'geov/images/default.png',0),(1193101,'auv','macrura_usbl','',0.01,0.01,'geov/images/default.png',0),(1193102,'track','tgt_1_macrura','',0.01,0.01,'geov/images/default.png',0),(1193103,'ship','scarlett','',42.2,10.6,'geov/images/ship.png',0),(1193115,'auv','discovery','',0.01,0.01,'geov/images/default.png',0),(1193117,'buoy','mfa','',0.01,0.01,'geov/images/default.png',0),(1193118,'ship','arabella','',0.01,0.01,'geov/images/default.png',0),(1193119,'auv','broadcast','',0.01,0.01,'geov/images/default.png',0),(1193120,'auv','comms_state_tracker','',0.01,0.01,'geov/images/default.png',0),(1193121,'icecamp','icex_camp_zulu','',0.01,0.01,'geov/images/default.png',0),(1193122,'auv','icex_tracker_even_platform_q1','',0.01,0.01,'geov/images/default.png',0),(1193123,'auv','icex_tracker_even_platform_q2','',0.01,0.01,'geov/images/default.png',0),(1193124,'auv','icex_tracker_odd_platform_q1','',0.01,0.01,'geov/images/default.png',0),(1193125,'auv','icex_tracker_odd_platform_q2','',0.01,0.01,'geov/images/default.png',0),(1193126,'auv','icex_tracker_unit_q1','',0.01,0.01,'geov/images/default.png',0),(1193127,'auv','icex_tracker_unit_q2','',0.01,0.01,'geov/images/default.png',0),(1193128,'icehole','icex_camp_hydrohole','',0.01,0.01,'geov/images/default.png',0),(1193129,'hydrophone','icex_tracking_hydrophone_0','',0.01,0.01,'geov/images/default.png',0),(1193130,'hydrophone','icex_tracking_hydrophone_1','',0.01,0.01,'geov/images/default.png',0),(1193131,'hydrophone','icex_tracking_hydrophone_2','',0.01,0.01,'geov/images/default.png',0),(1193132,'hydrophone','icex_tracking_hydrophone_3','',0.01,0.01,'geov/images/default.png',0),(1193133,'auv','icex_tracker_filter_estimate','',0.01,0.01,'geov/images/default.png',0),(1193134,'auv','nemo','',0.01,0.01,'geov/images/default.png',0),(1193135,'buoy','sbc0','',0.01,0.01,'geov/images/default.png',0),(1193136,'kayak','sbc0','',0.01,0.01,'geov/images/default.png',0);
/*!40000 ALTER TABLE `core_vehicle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_vehicle_default`
--

DROP TABLE IF EXISTS `core_vehicle_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `core_vehicle_default` (
  `v_default_id` int unsigned NOT NULL AUTO_INCREMENT,
  `v_default_type` varchar(255) NOT NULL,
  `v_default_owner` varchar(255) NOT NULL,
  `v_default_use_loa` float NOT NULL,
  `v_default_use_beam` float NOT NULL,
  `v_default_use_image` varchar(255) NOT NULL,
  PRIMARY KEY (`v_default_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_vehicle_default`
--

LOCK TABLES `core_vehicle_default` WRITE;
/*!40000 ALTER TABLE `core_vehicle_default` DISABLE KEYS */;
INSERT INTO `core_vehicle_default` VALUES (1,'kayak','mit',2,0.5,'geov/images/kayak.png'),(2,'kayak','',2,0.5,'geov/images/kayak.png'),(3,'ship','',35,12,'geov/images/ship.png'),(4,'auv','',3,0.5,'geov/images/auv.png');
/*!40000 ALTER TABLE `core_vehicle_default` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-10 19:21:10
