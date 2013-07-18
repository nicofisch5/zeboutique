<?php
#-----------installation des tables n�cessaires au module comparateur


$this->startSetup();

//installation des tables necessaires au module
$this->run("

SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `{$this->getTable('comparer')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer')}` (
  `comparer_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `comparer_mappage_id` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  `croning_at` datetime DEFAULT NULL,
  `category_ids` text NOT NULL,
  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `stock_param` int(11) NOT NULL DEFAULT '1',
  `store_id` int(11) NOT NULL,
  `product_ids` text NOT NULL,
  `tracking` varchar(255) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY (`comparer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Structure de la table `comparer_mappage`
--

DROP TABLE IF EXISTS `{$this->getTable('comparer_mappage')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer_mappage')}` (
  `comparer_mappage_id` int(11) NOT NULL AUTO_INCREMENT,
  `comparer_mappage_name` varchar(255) NOT NULL,
  `comparer_mappage_header` text,
  `comparer_mappage_separator` varchar(255) NOT NULL DEFAULT ';',
  PRIMARY KEY (`comparer_mappage_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `comparer_mappage`
--

INSERT INTO `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`, `comparer_mappage_name`, `comparer_mappage_header`, `comparer_mappage_separator`) VALUES(2, 'kelkoo', '#type=basic', ';');
INSERT INTO `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`, `comparer_mappage_name`, `comparer_mappage_header`, `comparer_mappage_separator`) VALUES(3, 'Twenga', '', ';');
INSERT INTO `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`, `comparer_mappage_name`, `comparer_mappage_header`, `comparer_mappage_separator`) VALUES(4, 'Ciao', '', ';');
INSERT INTO `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`, `comparer_mappage_name`, `comparer_mappage_header`, `comparer_mappage_separator`) VALUES(5, 'Shopzilla', '', ';');
INSERT INTO `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`, `comparer_mappage_name`, `comparer_mappage_header`, `comparer_mappage_separator`) VALUES(6, 'Leguide', '', ';');
INSERT INTO `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`, `comparer_mappage_name`, `comparer_mappage_header`, `comparer_mappage_separator`) VALUES(7, 'Google Base', '', '".addslashes('\t')."');

-- --------------------------------------------------------

--
-- Structure de la table `comparer_mappage_line`
--

DROP TABLE IF EXISTS `{$this->getTable('comparer_mappage_line')}`;
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;

--
-- Contenu de la table `comparer_mappage_line`
--

INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(7, 2, 'url', 'product_url', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(8, 2, 'Title', 'name', 0, '', 1);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(9, 2, 'offerID', 'product_id', 0, '', 2);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(10, 2, 'description', 'description', 255, '', 3);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(11, 2, 'price', 'price', 0, '', 4);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(12, 2, 'deliverycost', 'none', 0, '0', 5);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(13, 2, 'image', 'image_url', 0, '', 6);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(14, 2, 'availability', 'none', 0, '1 jour', 7);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(15, 2, 'category', 'category', 0, '', 8);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(16, 3, 'product_url', 'product_url', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(17, 3, 'designation', 'name', 0, '', 1);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(18, 3, 'price', 'price', 0, '', 3);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(19, 3, 'brand', 'fabriquant', 0, '', 4);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(20, 3, 'image_url', 'image_url', 0, '', 5);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(21, 3, 'description', 'description', 255, '', 6);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(22, 3, 'category', 'category', 0, '', 7);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(23, 3, 'upc_ean', 'none', 0, '', 8);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(24, 3, 'shipping_cost', 'none', 0, '0', 9);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(25, 3, 'in_stock', 'none', 0, 'Y', 10);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(26, 3, 'stock_detail', 'stock_qty', 0, '', 11);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(27, 3, 'ship_to', 'none', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(28, 4, 'nom', 'name', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(29, 4, 'marque', 'fabriquant', 0, '', 1);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(30, 4, 'MPN/EAN/ISBN', 'none', 0, '', 3);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(31, 4, 'Deeplink/lien URL', 'product_url', 0, '', 4);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(32, 4, 'Image URL', 'image_url', 0, '', 5);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(33, 4, 'prix', 'price', 0, '', 6);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(34, 4, 'Frais de Livraison', 'none', 0, '0', 7);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(35, 4, 'Livraison', 'none', 0, 'en stock', 8);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(36, 4, 'Description', 'description', 255, '', 9);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(37, 4, 'Categorie', 'category', 0, '', 10);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(38, 4, 'Vendor Code', 'none', 0, '', 11);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(39, 4, 'offerID', 'product_id', 0, '', 12);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(40, 4, 'Monnaie', 'currency', 0, '', 13);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(41, 5, 'Cat�gorie', 'category', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(42, 5, 'Fabricant', 'fabriquant', 0, '', 1);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(43, 5, 'Titre', 'name', 0, '', 2);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(44, 5, 'Description', 'description', 255, '', 3);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(45, 5, 'URL du Produit', 'product_url', 0, '', 4);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(46, 5, 'Image URL', 'image_url', 0, '', 5);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(47, 5, 'SKU', 'sku', 0, '', 6);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(48, 5, 'MPID', 'product_id', 0, '', 7);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(49, 5, 'Inventaire', 'none', 0, 'En Stock', 8);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(50, 5, 'Condition', 'none', 0, '', 9);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(51, 5, 'Poids', 'weight', 0, '', 10);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(52, 5, 'Frais de livraison', 'none', 0, '0', 11);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(53, 5, 'Ench�re', 'none', 0, '', 12);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(54, 5, 'ISBN', 'none', 0, '', 13);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(55, 5, 'EAN/UPC', 'none', 0, '', 14);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(56, 5, 'Promo', 'promo_price', 0, '', 15);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(57, 5, 'Prix', 'price', 0, '', 16);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(58, 6, 'categorie', 'category', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(59, 6, 'identifiant_unique', 'product_id', 0, '', 1);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(60, 6, 'titre', 'name', 0, '', 2);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(61, 6, 'description', 'description', 0, '', 3);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(62, 6, 'prix', 'price', 0, '', 4);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(63, 6, 'url_produit', 'product_url', 0, '', 5);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(64, 6, 'URL_image', 'image_url', 0, '', 6);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(65, 6, 'frais_de_port', 'none', 0, '0', 7);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(66, 6, 'disponibilite', 'none', 0, '8 jours', 8);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(67, 6, 'delai_de_livraison', 'none', 0, '1 jjour', 9);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(68, 6, 'garantie', 'none', 0, '', 10);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(69, 6, 'reference_modele', 'none', 0, '', 11);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(70, 6, 'D3E', 'none', 0, '', 12);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(71, 6, 'marque', 'fabriquant', 0, '', 13);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(72, 6, 'ean', 'none', 0, '', 14);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(73, 6, 'prix_barre', 'normal_price', 0, '', 15);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(74, 6, 'type_promotion', 'none', 0, '', 16);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(75, 6, 'devise', 'currency', 0, '', 17);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(76, 6, 'occasion', 'none', 0, '', 18);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(77, 6, 'URL_mobile', 'none', 0, '', 19);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(78, 7, 'id', 'sku', 0, '', 0);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(79, 7, 'title', 'name', 0, '', 1);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(80, 7, 'link', 'product_url', 0, '', 2);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(81, 7, 'price', 'current_price', 0, '', 3);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(82, 7, 'description', 'description', 0, '', 4);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(83, 7, 'condition', 'none', 0, 'neuf', 5);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(84, 7, 'image_link', 'image_url', 0, '', 6);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(85, 7, 'category', 'category', 0, '', 7);
INSERT INTO `{$this->getTable('comparer_mappage_line')}` (`comparer_mappage_line_id`, `comparer_mappage_id`, `csv`, `attribute_code`, `max_size`, `default_value`, `sort_order`) VALUES(86, 7, 'quantity', 'stock_qty', 0, '', 8);
-- --------------------------------------------------------

--
-- Structure de la table `comparer_plan`
--

DROP TABLE IF EXISTS `{$this->getTable('comparer_plan')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer_plan')}` (
  `comparer_plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `start_time` time NOT NULL,
  `frequency` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`comparer_plan_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `comparer_plan`
--

INSERT INTO `{$this->getTable('comparer_plan')}` (`comparer_plan_id`, `start_time`, `frequency`, `email`) VALUES(1, '00:00:00', 'D', 'youremail@domaine.com');

--
-- Contraintes pour la table `comparer_mappage_line`
--
ALTER TABLE `{$this->getTable('comparer_mappage_line')}`
  ADD CONSTRAINT `comparer_mappage_line_ibfk_1` FOREIGN KEY (`comparer_mappage_id`) REFERENCES `{$this->getTable('comparer_mappage')}` (`comparer_mappage_id`) ON DELETE CASCADE ON UPDATE CASCADE;


");

$this->endSetup();