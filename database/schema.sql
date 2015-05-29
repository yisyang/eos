-- MySQL dump 10.13  Distrib 5.1.54
-- ------------------------------------------------------

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

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(24) DEFAULT NULL,
  `password` tinytext,
  `rk` text,
  `ip_current` tinytext,
  `ip_last` tinytext,
  `access_current` datetime DEFAULT NULL,
  `access_last` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `es_applications`
--

DROP TABLE IF EXISTS `es_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `es_applications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `esp_id` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `cover_letter` text NOT NULL,
  `apply_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `esp_id` (`esp_id`),
  KEY `apply_time` (`apply_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1794 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `es_positions`
--

DROP TABLE IF EXISTS `es_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `es_positions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `pay_flat` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bonus_percent` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `duration` int(7) unsigned NOT NULL DEFAULT '0',
  `post_time` datetime NOT NULL,
  `daily_allowance` bigint(20) NOT NULL DEFAULT '-1',
  `ctrl_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_hurry` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_land` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_produce` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_cancel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_build` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_expand` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_price` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_ad` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_build` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_expand` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_res` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_cancel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_hurry` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_build` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_expand` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_discard` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_b2b_buy` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_post` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_hire` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_fire` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `post_time` (`post_time`)
) ENGINE=InnoDB AUTO_INCREMENT=921 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_fact`
--

DROP TABLE IF EXISTS `firm_fact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_fact` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `fact_id` int(10) unsigned NOT NULL,
  `fact_name` varchar(24) NOT NULL DEFAULT 'Factory',
  `size` int(10) unsigned NOT NULL DEFAULT '10',
  `slot` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `fact_id` (`fact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38333 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_news`
--

DROP TABLE IF EXISTS `firm_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `body` text,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=2708565 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_quest`
--

DROP TABLE IF EXISTS `firm_quest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_quest` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `quest_id` int(10) unsigned NOT NULL,
  `gen_target_id` int(10) unsigned DEFAULT NULL,
  `gen_target_n` bigint(20) unsigned DEFAULT NULL,
  `starttime` bigint(20) unsigned DEFAULT NULL,
  `endtime` bigint(20) unsigned DEFAULT NULL,
  `reward_cash` bigint(20) unsigned DEFAULT '0',
  `reward_fame` bigint(20) unsigned DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `failed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quest_id` (`quest_id`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=596923 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_rnd`
--

DROP TABLE IF EXISTS `firm_rnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_rnd` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `rnd_id` int(10) unsigned NOT NULL,
  `rnd_name` varchar(24) NOT NULL DEFAULT 'R&amp;D',
  `size` int(10) unsigned NOT NULL DEFAULT '10',
  `slot` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `rnd_id` (`rnd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10542 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_stock`
--

DROP TABLE IF EXISTS `firm_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_stock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `symbol` varchar(8) NOT NULL,
  `shares_os` bigint(20) unsigned NOT NULL,
  `shares_public` bigint(20) unsigned NOT NULL,
  `share_price` int(10) NOT NULL,
  `share_price_min` int(10) NOT NULL,
  `share_price_max` int(10) NOT NULL,
  `dividend` int(10) unsigned NOT NULL,
  `share_price_open` bigint(20) DEFAULT '100',
  `7de` bigint(20) NOT NULL DEFAULT '0',
  `paid_in_capital` float NOT NULL DEFAULT '0',
  `last_active` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fid` (`fid`),
  UNIQUE KEY `symbol` (`symbol`)
) ENGINE=InnoDB AUTO_INCREMENT=380 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_stock_issuance`
--

DROP TABLE IF EXISTS `firm_stock_issuance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_stock_issuance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `shares` bigint(20) unsigned NOT NULL,
  `price` bigint(20) unsigned NOT NULL,
  `type` enum('IPO','SEO','Buyback','') NOT NULL,
  `starts` datetime NOT NULL,
  `expiration` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`,`type`),
  KEY `expiration` (`expiration`),
  KEY `fid_2` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=446 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_stock_issued_temp`
--

DROP TABLE IF EXISTS `firm_stock_issued_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_stock_issued_temp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `shares` bigint(20) unsigned NOT NULL,
  `total_price` bigint(20) unsigned NOT NULL,
  `type` enum('IPO','SEO','Buyback') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`,`type`),
  KEY `fid_2` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_store`
--

DROP TABLE IF EXISTS `firm_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_store` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `store_name` varchar(24) NOT NULL DEFAULT 'Store',
  `size` int(10) unsigned NOT NULL,
  `marketing` bigint(20) unsigned NOT NULL,
  `slot` smallint(5) unsigned NOT NULL,
  `is_expanding` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21743 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_store_news`
--

DROP TABLE IF EXISTS `firm_store_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_store_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `body` text,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=15627858 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_store_shelves`
--

DROP TABLE IF EXISTS `firm_store_shelves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_store_shelves` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fsid` bigint(20) unsigned NOT NULL,
  `shelf_slot` tinyint(3) unsigned NOT NULL,
  `wh_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wh_id` (`wh_id`),
  KEY `store_id` (`fsid`)
) ENGINE=InnoDB AUTO_INCREMENT=13060683 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_tech`
--

DROP TABLE IF EXISTS `firm_tech`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_tech` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `quality` int(10) unsigned NOT NULL DEFAULT '10',
  `update_time` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`,`pid`),
  KEY `pid` (`pid`,`quality`)
) ENGINE=InnoDB AUTO_INCREMENT=166337 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firm_wh`
--

DROP TABLE IF EXISTS `firm_wh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firm_wh` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pidq` float unsigned NOT NULL,
  `pidn` bigint(20) unsigned NOT NULL,
  `pidcost` bigint(20) unsigned NOT NULL DEFAULT '0',
  `pidprice` bigint(20) unsigned DEFAULT NULL,
  `pidpartialsale` float NOT NULL DEFAULT '0',
  `no_sell` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`,`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=198678 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firms`
--

DROP TABLE IF EXISTS `firms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(24) DEFAULT NULL,
  `alias` varchar(24) DEFAULT NULL,
  `color` varchar(10) NOT NULL,
  `cash` bigint(20) NOT NULL DEFAULT '0',
  `loan` bigint(20) NOT NULL DEFAULT '0',
  `networth` bigint(20) NOT NULL DEFAULT '0',
  `level` smallint(5) unsigned NOT NULL DEFAULT '0',
  `fame_level` smallint(5) unsigned NOT NULL DEFAULT '0',
  `fame_exp` bigint(20) unsigned NOT NULL DEFAULT '0',
  `wh_size` smallint(5) unsigned NOT NULL DEFAULT '50',
  `max_bldg` smallint(5) unsigned NOT NULL DEFAULT '8',
  `max_fact` smallint(5) unsigned NOT NULL DEFAULT '3',
  `max_store` smallint(5) unsigned NOT NULL DEFAULT '3',
  `max_rnd` smallint(5) unsigned NOT NULL DEFAULT '0',
  `quests_available` smallint(5) unsigned NOT NULL DEFAULT '0',
  `last_login` date DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `vacation_out` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_active` (`last_active`)
) ENGINE=InnoDB AUTO_INCREMENT=44839 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firms_extended`
--

DROP TABLE IF EXISTS `firms_extended`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firms_extended` (
  `id` int(10) unsigned NOT NULL,
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ceo` int(10) unsigned NOT NULL DEFAULT '0',
  `inventory` bigint(20) NOT NULL,
  `property` bigint(20) NOT NULL,
  `intangible` bigint(20) NOT NULL,
  `dividend_flat` int(10) unsigned NOT NULL,
  `auto_repay_loan` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `firms_positions`
--

DROP TABLE IF EXISTS `firms_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firms_positions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `pay_flat` bigint(20) unsigned NOT NULL DEFAULT '0',
  `bonus_percent` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `next_pay_flat` bigint(20) unsigned NOT NULL DEFAULT '0',
  `next_bonus_percent` decimal(5,2) unsigned NOT NULL DEFAULT '0.00',
  `next_accepted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `duration` tinyint(4) NOT NULL DEFAULT '7',
  `daily_allowance` bigint(20) NOT NULL DEFAULT '-1',
  `used_allowance` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ctrl_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_hurry` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_land` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_produce` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_cancel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_build` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_expand` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_price` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_ad` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_build` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_expand` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_res` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_cancel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_hurry` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_build` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_expand` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_discard` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_b2b_buy` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_post` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_hire` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_fire` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_invisible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=45601 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `foreign_companies`
--

DROP TABLE IF EXISTS `foreign_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foreign_companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `country_id` int(10) unsigned NOT NULL,
  `country_name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `foreign_list_goods`
--

DROP TABLE IF EXISTS `foreign_list_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foreign_list_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fcid` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `quality` int(10) unsigned NOT NULL DEFAULT '30',
  `value_to_sell` bigint(20) unsigned NOT NULL,
  `value_sold` bigint(20) unsigned NOT NULL,
  `price_multiplier` decimal(8,2) unsigned NOT NULL DEFAULT '10.00',
  PRIMARY KEY (`id`),
  KEY `fcid` (`fcid`),
  KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `foreign_list_purcs`
--

DROP TABLE IF EXISTS `foreign_list_purcs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foreign_list_purcs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fcid` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `value_to_buy` bigint(20) unsigned NOT NULL,
  `value_bought` bigint(20) unsigned NOT NULL,
  `price_multiplier` decimal(8,2) unsigned NOT NULL DEFAULT '1.50',
  PRIMARY KEY (`id`),
  KEY `fcid` (`fcid`),
  KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `foreign_raw_mat_purc`
--

DROP TABLE IF EXISTS `foreign_raw_mat_purc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foreign_raw_mat_purc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fcid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pidn` bigint(20) unsigned NOT NULL,
  `price` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=514 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grant_applications`
--

DROP TABLE IF EXISTS `grant_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grant_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `grant_id` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `cover_letter` text NOT NULL,
  `status` enum('Pending','Rejected','Approved','') NOT NULL,
  `reviewed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `grant_id` (`grant_id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grants`
--

DROP TABLE IF EXISTS `grants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `cash` bigint(20) unsigned NOT NULL,
  `available` smallint(5) unsigned NOT NULL DEFAULT '1',
  `title` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `nw_min` float NOT NULL,
  `nw_max` float NOT NULL,
  `age_min` smallint(5) unsigned NOT NULL,
  `age_max` smallint(5) unsigned NOT NULL,
  `expiration` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_firms`
--

DROP TABLE IF EXISTS `history_firms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_firms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `networth` bigint(20) NOT NULL,
  `cash` bigint(20) NOT NULL,
  `loan` bigint(20) NOT NULL,
  `total_gains` bigint(20) NOT NULL,
  `total_spending` bigint(20) NOT NULL,
  `production` bigint(20) NOT NULL,
  `store_sales` bigint(20) unsigned NOT NULL,
  `construction` bigint(20) NOT NULL,
  `research` bigint(20) NOT NULL,
  `b2b_sales` bigint(20) NOT NULL,
  `b2b_purchase` bigint(20) NOT NULL,
  `import` bigint(20) unsigned NOT NULL,
  `export` bigint(20) unsigned NOT NULL,
  `maintenance` bigint(20) unsigned NOT NULL,
  `salary` bigint(20) unsigned NOT NULL,
  `paid_in_capital` bigint(20) NOT NULL,
  `inventory` bigint(20) NOT NULL,
  `property` bigint(20) NOT NULL,
  `intangible` bigint(20) NOT NULL,
  `tax` bigint(20) unsigned NOT NULL,
  `interest` bigint(20) unsigned NOT NULL,
  `dividend` bigint(20) unsigned NOT NULL,
  `exec_pay` bigint(20) unsigned NOT NULL,
  `history_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=424583 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_players`
--

DROP TABLE IF EXISTS `history_players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_players` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `networth` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cash` bigint(20) unsigned NOT NULL DEFAULT '0',
  `history_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2289433 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_prod`
--

DROP TABLE IF EXISTS `history_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_prod` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) unsigned NOT NULL,
  `q_avg` float unsigned NOT NULL,
  `price_avg` bigint(20) unsigned NOT NULL,
  `sales_vol` float unsigned NOT NULL,
  `sales_total` float NOT NULL,
  `history_tick` int(10) unsigned NOT NULL,
  `history_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`history_tick`),
  KEY `pid_2` (`pid`,`history_datetime`)
) ENGINE=InnoDB AUTO_INCREMENT=53544668 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_stock`
--

DROP TABLE IF EXISTS `history_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_stock` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `share_price` bigint(20) unsigned NOT NULL,
  `history_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=26873 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_stock_fine`
--

DROP TABLE IF EXISTS `history_stock_fine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_stock_fine` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `share_price` bigint(20) unsigned NOT NULL,
  `history_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=2626782 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_achievements`
--

DROP TABLE IF EXISTS `list_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_achievements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_cat`
--

DROP TABLE IF EXISTS `list_cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_cat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `price_multiplier` decimal(8,2) unsigned NOT NULL DEFAULT '1.00',
  `price_multiplier_target` decimal(8,2) NOT NULL DEFAULT '1.00',
  `va_tc` int(10) unsigned NOT NULL,
  `collectible` tinyint(1) NOT NULL,
  `sellable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_fact`
--

DROP TABLE IF EXISTS `list_fact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_fact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `division_name` varchar(64) NOT NULL,
  `cost` int(10) unsigned NOT NULL DEFAULT '1000000',
  `timecost` int(10) unsigned NOT NULL DEFAULT '1800',
  `firstcost` int(10) unsigned NOT NULL DEFAULT '10000000',
  `firsttimecost` int(10) unsigned NOT NULL DEFAULT '10800',
  `has_image` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_fact_choices`
--

DROP TABLE IF EXISTS `list_fact_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_fact_choices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fact_id` int(10) unsigned NOT NULL,
  `cost` bigint(20) unsigned NOT NULL DEFAULT '10000',
  `timecost` decimal(10,2) unsigned NOT NULL DEFAULT '60.00',
  `ipid1` int(10) unsigned DEFAULT NULL,
  `ipid1n` decimal(9,3) unsigned DEFAULT NULL,
  `ipid1qm` decimal(3,2) unsigned DEFAULT NULL,
  `ipid2` int(10) unsigned DEFAULT NULL,
  `ipid2n` decimal(9,3) unsigned DEFAULT NULL,
  `ipid2qm` decimal(3,2) unsigned DEFAULT NULL,
  `ipid3` int(10) unsigned DEFAULT NULL,
  `ipid3n` decimal(9,3) unsigned DEFAULT NULL,
  `ipid3qm` decimal(3,2) unsigned DEFAULT NULL,
  `ipid4` int(10) unsigned DEFAULT NULL,
  `ipid4n` decimal(9,3) unsigned DEFAULT NULL,
  `ipid4qm` decimal(3,2) unsigned DEFAULT NULL,
  `opid1` int(10) unsigned NOT NULL,
  `opid1usetech` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fact_id` (`fact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=505 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_prod`
--

DROP TABLE IF EXISTS `list_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_prod` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(10) unsigned NOT NULL DEFAULT '1',
  `name` varchar(64) NOT NULL,
  `value` bigint(20) unsigned NOT NULL DEFAULT '1',
  `value_avg` bigint(20) unsigned DEFAULT '100',
  `q_avg` float unsigned NOT NULL DEFAULT '0',
  `tech_avg` float NOT NULL DEFAULT '0',
  `demand` float unsigned NOT NULL DEFAULT '0',
  `demand_met` float unsigned NOT NULL DEFAULT '0',
  `selltime` decimal(10,2) unsigned NOT NULL DEFAULT '60.00',
  `res_cost` int(10) unsigned NOT NULL DEFAULT '10000',
  `res_dep_1` int(10) unsigned DEFAULT NULL,
  `res_dep_2` int(10) unsigned DEFAULT NULL,
  `res_dep_3` int(10) unsigned DEFAULT NULL,
  `has_icon` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=460 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_quest`
--

DROP TABLE IF EXISTS `list_quest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_quest` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(10) unsigned DEFAULT NULL,
  `level_min` int(10) unsigned NOT NULL DEFAULT '0',
  `level_max` int(10) unsigned NOT NULL DEFAULT '99',
  `value_unit_max` bigint(20) unsigned DEFAULT NULL,
  `value_total` bigint(20) unsigned DEFAULT NULL,
  `target_id` int(10) unsigned DEFAULT NULL,
  `target_type` tinytext,
  `n` bigint(20) DEFAULT NULL,
  `q` int(10) unsigned DEFAULT NULL,
  `cash` bigint(20) DEFAULT NULL,
  `duration` bigint(20) unsigned DEFAULT NULL,
  `quest_giver_id` int(10) unsigned DEFAULT NULL,
  `quest_giver_subject` tinytext,
  `quest_giver_body` text,
  `linked_quest_c` int(10) unsigned DEFAULT NULL,
  `linked_quest_f` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `level_min` (`level_min`,`level_max`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_rnd`
--

DROP TABLE IF EXISTS `list_rnd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_rnd` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `division_name` varchar(64) NOT NULL,
  `cost` int(10) unsigned NOT NULL DEFAULT '5000000',
  `timecost` int(10) unsigned NOT NULL DEFAULT '3600',
  `firstcost` int(10) unsigned NOT NULL DEFAULT '50000000',
  `firsttimecost` int(10) unsigned NOT NULL DEFAULT '18000',
  `has_image` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_rnd_choices`
--

DROP TABLE IF EXISTS `list_rnd_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_rnd_choices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rnd_id` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rnd_id` (`rnd_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_store`
--

DROP TABLE IF EXISTS `list_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `division_name` varchar(64) NOT NULL,
  `cost` int(10) unsigned NOT NULL DEFAULT '1000000',
  `timecost` int(10) unsigned NOT NULL DEFAULT '1800',
  `firstcost` int(10) unsigned NOT NULL DEFAULT '10000000',
  `firsttimecost` int(10) unsigned NOT NULL DEFAULT '10800',
  `multiplier` float NOT NULL DEFAULT '1',
  `has_image` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `list_store_choices`
--

DROP TABLE IF EXISTS `list_store_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `list_store_choices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_firms_sold`
--

DROP TABLE IF EXISTS `log_firms_sold`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_firms_sold` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `fid` bigint(20) unsigned NOT NULL,
  `firm_name` varchar(24) NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41040 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_limited_actions`
--

DROP TABLE IF EXISTS `log_limited_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_limited_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(24) NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `action_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action` (`action`,`actor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1927 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_management`
--

DROP TABLE IF EXISTS `log_management`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_management` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `title` varchar(64) NOT NULL,
  `total_salary` double unsigned NOT NULL DEFAULT '0',
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `ctrl_admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_hurry` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_land` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_bldg_view` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_cancel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_fact_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_ad` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_store_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_res` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_cancel` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_rnd_hurry` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_sell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_wh_discard` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_b2b_buy` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_post` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_hire` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ctrl_hr_fire` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=45601 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_market_prod`
--

DROP TABLE IF EXISTS `log_market_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_market_prod` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sfid` int(10) unsigned NOT NULL,
  `bfid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pidq` float unsigned NOT NULL,
  `pidn` bigint(15) unsigned NOT NULL,
  `cost` bigint(20) unsigned NOT NULL DEFAULT '0',
  `price` bigint(20) unsigned NOT NULL,
  `pricetovalue` decimal(20,2) unsigned NOT NULL,
  `hide` tinyint(1) NOT NULL DEFAULT '0',
  `transaction_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bfid` (`bfid`),
  KEY `sfid` (`sfid`)
) ENGINE=InnoDB AUTO_INCREMENT=3698293 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_player_restarts`
--

DROP TABLE IF EXISTS `log_player_restarts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_player_restarts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `restart_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=809 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_queue_prod`
--

DROP TABLE IF EXISTS `log_queue_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_queue_prod` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `ffid` bigint(20) unsigned NOT NULL,
  `fcid` bigint(20) unsigned NOT NULL,
  `opid1` int(10) unsigned NOT NULL,
  `opid1q` float unsigned NOT NULL,
  `opid1n` bigint(20) unsigned NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `starttime` (`starttime`)
) ENGINE=InnoDB AUTO_INCREMENT=1203036 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_revenue`
--

DROP TABLE IF EXISTS `log_revenue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_revenue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `is_debit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pid` smallint(5) unsigned DEFAULT NULL,
  `pidn` bigint(20) unsigned DEFAULT NULL,
  `pidq` int(10) unsigned DEFAULT NULL,
  `value` bigint(20) unsigned NOT NULL,
  `source` varchar(20) DEFAULT NULL,
  `transaction_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`transaction_time`),
  KEY `transaction_time` (`transaction_time`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=8829273 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_sales`
--

DROP TABLE IF EXISTS `log_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `fsid` int(10) unsigned NOT NULL,
  `pid` smallint(5) unsigned DEFAULT NULL,
  `pidn` bigint(20) unsigned DEFAULT NULL,
  `pidq` int(10) unsigned DEFAULT NULL,
  `value` bigint(20) unsigned NOT NULL,
  `tick` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`,`tick`),
  KEY `fid` (`fid`,`tick`)
) ENGINE=InnoDB AUTO_INCREMENT=719583637 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_sales_tick`
--

DROP TABLE IF EXISTS `log_sales_tick`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_sales_tick` (
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pidn` float unsigned NOT NULL,
  `value` float unsigned NOT NULL,
  PRIMARY KEY (`fid`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_stock`
--

DROP TABLE IF EXISTS `log_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_stock` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `spid` int(10) unsigned NOT NULL DEFAULT '0',
  `bpid` int(10) unsigned NOT NULL DEFAULT '0',
  `shares` bigint(20) unsigned NOT NULL,
  `share_price` bigint(20) NOT NULL,
  `total_price` bigint(20) unsigned NOT NULL,
  `transaction_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spid` (`spid`),
  KEY `bpid` (`bpid`)
) ENGINE=InnoDB AUTO_INCREMENT=4002 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `market_prod`
--

DROP TABLE IF EXISTS `market_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `market_prod` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pidq` float unsigned NOT NULL,
  `pidn` bigint(20) unsigned NOT NULL,
  `pidcost` bigint(20) unsigned NOT NULL DEFAULT '0',
  `price` bigint(20) unsigned NOT NULL,
  `listed` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=74992888 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `market_requests`
--

DROP TABLE IF EXISTS `market_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `market_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pidq` float unsigned NOT NULL,
  `pidn` bigint(20) NOT NULL,
  `price` bigint(20) unsigned NOT NULL,
  `aon` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `requested` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=74766725 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL,
  `recipient` int(10) unsigned NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `recipient_read` tinyint(1) NOT NULL DEFAULT '0',
  `sender_delete` tinyint(1) NOT NULL DEFAULT '0',
  `recipient_delete` tinyint(1) NOT NULL DEFAULT '0',
  `recipient_starred` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `no_delete` tinyint(1) NOT NULL DEFAULT '0',
  `sendtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `recipient` (`recipient`),
  KEY `sender` (`sender`)
) ENGINE=InnoDB AUTO_INCREMENT=16277 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `player_achievements`
--

DROP TABLE IF EXISTS `player_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_achievements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `aid` int(10) unsigned NOT NULL,
  `awarded` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `aid` (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `player_contacts`
--

DROP TABLE IF EXISTS `player_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `u_pid` int(10) unsigned NOT NULL,
  `u_notes` varchar(250) DEFAULT NULL,
  `c_pid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `u_pid` (`u_pid`)
) ENGINE=InnoDB AUTO_INCREMENT=477 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `player_news`
--

DROP TABLE IF EXISTS `player_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `body` text,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB AUTO_INCREMENT=527136 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `player_stock`
--

DROP TABLE IF EXISTS `player_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_stock` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `shares` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=1201 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL,
  `login_id` int(10) unsigned NOT NULL,
  `rk` text NOT NULL,
  `fid` int(10) unsigned DEFAULT NULL,
  `player_name` varchar(32) DEFAULT NULL,
  `player_alias` varchar(24) DEFAULT NULL,
  `player_level` bigint(20) unsigned NOT NULL DEFAULT '0',
  `player_networth` bigint(20) unsigned NOT NULL DEFAULT '0',
  `player_fame_level` smallint(5) unsigned NOT NULL DEFAULT '0',
  `player_fame` bigint(20) unsigned NOT NULL DEFAULT '0',
  `player_cash` bigint(20) unsigned NOT NULL DEFAULT '0',
  `influence` int(10) unsigned NOT NULL DEFAULT '0',
  `vip_level` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `vip_expires` datetime NOT NULL,
  `in_jail` bigint(20) unsigned NOT NULL DEFAULT '0',
  `is_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_flagged` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `avatar_filename` tinytext,
  `show_menu_tooltip` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `narrow_screen` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `queue_countdown` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `b2b_rows_per_page` tinyint(3) unsigned NOT NULL DEFAULT '20',
  `enable_chat` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `new_user` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `is_searchable` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `last_login` date DEFAULT NULL,
  `last_active` datetime DEFAULT NULL,
  `requests` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_alias` (`player_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_extended`
--

DROP TABLE IF EXISTS `players_extended`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_extended` (
  `id` int(10) unsigned NOT NULL,
  `player_created` int(10) unsigned NOT NULL DEFAULT '1330000000',
  `player_desc` text,
  `bot_check` int(10) unsigned DEFAULT NULL,
  `bot_flag` int(10) unsigned NOT NULL DEFAULT '0',
  `voted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `voted_streak` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `player_news_last_read` datetime NOT NULL,
  `world_news_last_read` datetime NOT NULL,
  `system_news_last_read` datetime NOT NULL,
  `special_event_viewed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `muted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chatbox_x` int(11) NOT NULL,
  `chatbox_y` int(11) NOT NULL,
  `chatbox_width` int(10) unsigned NOT NULL,
  `chatbox_height` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue_build`
--

DROP TABLE IF EXISTS `queue_build`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_build` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `building_type` varchar(10) NOT NULL,
  `building_type_id` int(10) unsigned DEFAULT NULL,
  `building_id` bigint(20) unsigned DEFAULT NULL,
  `building_slot` smallint(5) unsigned DEFAULT NULL,
  `newsize` int(10) unsigned NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `endtime` (`endtime`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=423862 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue_prod`
--

DROP TABLE IF EXISTS `queue_prod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_prod` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `ffid` bigint(20) unsigned NOT NULL,
  `fcid` bigint(20) unsigned NOT NULL,
  `opid1` int(10) unsigned NOT NULL,
  `opid1q` float unsigned NOT NULL,
  `opid1n` bigint(20) unsigned NOT NULL,
  `opid1cost` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ipid1q` float unsigned DEFAULT NULL,
  `ipid2q` float unsigned DEFAULT NULL,
  `ipid3q` float unsigned DEFAULT NULL,
  `ipid4q` float unsigned DEFAULT NULL,
  `ipid1cost` bigint(20) unsigned DEFAULT NULL,
  `ipid2cost` bigint(20) unsigned DEFAULT NULL,
  `ipid3cost` bigint(20) unsigned DEFAULT NULL,
  `ipid4cost` bigint(20) unsigned DEFAULT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `endtime` (`endtime`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=1583156 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue_res`
--

DROP TABLE IF EXISTS `queue_res`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue_res` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `frid` bigint(20) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `newlevel` int(10) unsigned NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  `starttime_dep` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `endtime` (`endtime`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=2370271 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_ask`
--

DROP TABLE IF EXISTS `stock_ask`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_ask` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `shares` int(10) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `expiration` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `pid` (`pid`),
  KEY `expiration` (`expiration`)
) ENGINE=InnoDB AUTO_INCREMENT=1148 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_bid`
--

DROP TABLE IF EXISTS `stock_bid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_bid` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `shares` int(10) unsigned NOT NULL,
  `aon` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `price` int(10) unsigned NOT NULL,
  `expiration` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`),
  KEY `pid` (`pid`),
  KEY `expiration` (`expiration`)
) ENGINE=InnoDB AUTO_INCREMENT=3003 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_edit_temp`
--

DROP TABLE IF EXISTS `stock_edit_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_edit_temp` (
  `id` bigint(20) unsigned NOT NULL,
  `type` enum('ask','bid') NOT NULL,
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `shares` int(10) unsigned NOT NULL,
  `aon` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `price` int(10) unsigned NOT NULL,
  `expiration` date NOT NULL,
  PRIMARY KEY (`id`,`type`),
  KEY `expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stock_watchlist`
--

DROP TABLE IF EXISTS `stock_watchlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_watchlist` (
  `fid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fid`,`pid`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_news`
--

DROP TABLE IF EXISTS `system_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` tinytext,
  `body` text,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `world_news`
--

DROP TABLE IF EXISTS `world_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `world_news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` tinytext,
  `body` text,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_created` (`date_created`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `world_var`
--

DROP TABLE IF EXISTS `world_var`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `world_var` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `value` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=502 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-05-28 19:29:16
