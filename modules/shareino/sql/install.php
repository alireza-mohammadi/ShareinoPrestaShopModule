<?php
/**
 * 2015-2016 Shareino
 *
 * NOTICE OF LICENSE
 *
 * This source file is for module that make sync Product With shareino server
 * https://github.com/SaeedDarvish/PrestaShopShareinoModule
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Shareino to newer
 * versions in the future. If you wish to customize Shareino for your
 * needs please refer to https://github.com/SaeedDarvish/PrestaShopShareinoModule for more information.
 *
 * @author    Saeed Darvish <sd.saeed.darvish@gmail.com>
 * @copyright 2015-2016 Shareino Co
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  Tejarat Ejtemaie Eram
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS  `'._DB_PREFIX_.'shareino_sync` (
  `id_shareino_sync` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `status` TINYINT NULL,
  `errors` VARCHAR(500) NULL,
  `date_add` DATETIME NULL,
  `date_upd` DATETIME NULL,
  PRIMARY KEY (`id_shareino_sync`),
  UNIQUE INDEX `product_id_UNIQUE` (`product_id` ASC))DEFAULT CHARSET=utf8;';

$sql[] = 'INSERT IGNORE INTO  `'._DB_PREFIX_.'shareino_sync` (`product_id`,`status`)
            SELECT `id_product` AS `product_id`,0 AS `status`
            from  `'._DB_PREFIX_.'product`;';

$sql[] = 'CREATE TABLE IF NOT EXISTS  `'._DB_PREFIX_.'shareino_organized` (
  `id_shareino_organized` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(50) DEFAULT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `ids` varchar(200) NOT NULL,
  `names` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id_shareino_organized`),
  UNIQUE KEY `cat_id_UNIQUE` (`cat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
