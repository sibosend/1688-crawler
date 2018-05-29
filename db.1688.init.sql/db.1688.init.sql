-- --------------------------------------------------------
-- Host:                         py.caritasem.com
-- Server version:               5.1.73 - Source distribution
-- Server OS:                    redhat-linux-gnu
-- HeidiSQL Version:             9.3.0.5071
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for data_1688
CREATE DATABASE IF NOT EXISTS `data_1688` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `data_1688`;

-- Dumping structure for table data_1688.company_curl_position
CREATE TABLE IF NOT EXISTS `company_curl_position` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `index_nav` varchar(250) NOT NULL DEFAULT '0' COMMENT '1688主页左侧导航栏 对应某市场',
  `index_nav_link` varchar(250) NOT NULL DEFAULT '0',
  `market_nav` varchar(250) NOT NULL DEFAULT '0' COMMENT '某市场导航栏 如女装-连衣裙',
  `market_nav_link` varchar(250) NOT NULL DEFAULT '0',
  `search_page_index` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '分类搜索页页数',
  `status` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '本页是否爬完 0 未完成 1 已完成',
  `distribute_ip` varchar(250) DEFAULT '0' COMMENT '分配给哪台机器',
  `position_code` varchar(250) DEFAULT '0' COMMENT '爬虫抓取情况',
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37005 DEFAULT CHARSET=utf8;