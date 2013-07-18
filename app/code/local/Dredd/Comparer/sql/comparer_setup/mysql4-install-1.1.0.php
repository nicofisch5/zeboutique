<?php 
#-----------installation des tables nécessaires au module compara


$this->startSetup();

//installation des tables necessaires au module
$this->run("
	SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

	CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer')}` (
	  `comparer_id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) NOT NULL,
	  `comparer_mappage_id` int(11) NOT NULL,
	  `updated_at` datetime NOT NULL,
	  `category_ids` text NOT NULL,
	  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
	  `store_id` int(11) NOT NULL,
	  `product_ids` text NOT NULL,
	  `tracking` varchar(255) DEFAULT NULL,
	  `filename` varchar(255) NOT NULL,
	  PRIMARY KEY (`comparer_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer_mappage')}` (
	  `comparer_mappage_id` int(11) NOT NULL AUTO_INCREMENT,
	  `comparer_mappage_name` varchar(255) NOT NULL,
	  `comparer_mappage_header` text,
	  `comparer_mappage_separator` varchar(255) NOT NULL DEFAULT ';',
	  PRIMARY KEY (`comparer_mappage_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer_mappage_line')}` (
	  `comparer_mappage_line_id` int(11) NOT NULL AUTO_INCREMENT,
	  `comparer_mappage_id` int(11) NOT NULL DEFAULT '0',
	  `csv` varchar(255) NOT NULL,
	  `attribute_code` varchar(255) NOT NULL,
	  `max_size` int(11) NOT NULL DEFAULT '0',
	  `default_value` varchar(255) DEFAULT '',
	  `sort_order` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`comparer_mappage_line_id`),
	  KEY `comparer_mappage_id` (`comparer_mappage_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

	ALTER TABLE `{$this->getTable('comparer_mappage_line')}`
	  ADD CONSTRAINT `comparer_mappage_line_ibfk_1` FOREIGN KEY (`comparer_mappage_id`) REFERENCES `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`) ON DELETE CASCADE ON UPDATE CASCADE;


");

$this->endSetup();