<?php
#-----------installation des tables n�cessaires au module comparateur


$this->startSetup();

//installation des tables necessaires au module
$this->run("
-- --------------------------------------------------------
--
-- Structure de la table `comparer_cron`
--

DROP TABLE IF EXISTS `{$this->getTable('comparer_cron')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('comparer_cron')}` (
    `comparer_cron_id` int(11) NOT NULL AUTO_INCREMENT,
    `comparer_id` int(11) NOT NULL,
    `timing` time NOT NULL,
    `frequency` varchar(2) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `status` int(11) NOT NULL DEFAULT '0',
    `created_at` datetime NOT NULL,
    `execute_at` datetime NOT NULL,
    PRIMARY KEY (`comparer_cron_id`),
    KEY `comparer_id` (`comparer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

--
-- Contraintes pour la table `comparer_cron`
--
ALTER TABLE `{$this->getTable('comparer_cron')}`
  ADD CONSTRAINT `comparer_cron_ibfk_1` FOREIGN KEY (`comparer_id`) REFERENCES `{$this->getTable('comparer')}` (`comparer_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->endSetup();
