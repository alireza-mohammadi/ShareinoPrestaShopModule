<?php
/**
 * 2015-2016 Shareino
 *
 * NOTICE OF LICENSE
 *
 * This source file is for module that make sync Product With shareino server
 * https://github.com/SaeedDarvish/ShareinoPrestaShopModule
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Shareino to newer
 * versions in the future. If you wish to customize Shareino for your
 * needs please refer to https://github.com/SaeedDarvish/ShareinoPrestaShopModule for more information.
 *
 * @author    Saeed Darvish <sd.saeed.darvish@gmail.com>
 * @copyright 2015-2016 Shareino Co
 *  Tejarat Ejtemaie Eram
 */

$tables = array();

$tables[] = 'CREATE TABLE IF NOT EXISTS  `' . _DB_PREFIX_ . 'shareino_sync` (
  `id_shareino_sync` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `status` TINYINT NULL,
  `errors` VARCHAR(500) NULL,
  `date_add` DATETIME NULL,
  `date_upd` DATETIME NULL,
  PRIMARY KEY (`id_shareino_sync`),
  UNIQUE INDEX `product_id_UNIQUE` (`product_id` ASC))DEFAULT CHARSET=utf8;';

$tables[] = 'INSERT IGNORE INTO  `' . _DB_PREFIX_ . 'shareino_sync` (`product_id`,`status`)
            SELECT `id_product` AS `product_id`,0 AS `status`
            FROM  `' . _DB_PREFIX_ . 'product`;';

$tables[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dokme_synchronize`(
            `id` BIGINT NOT NULL AUTO_INCREMENT,
            `product_id` BIGINT NOT NULL,
            `date_sync` DATETIME NOT NULL,
             PRIMARY KEY(`id`))';

$tables[] = 'INSERT IGNORE INTO `' . _DB_PREFIX_ . 'dokme_synchronize`(`product_id`) SELECT `id_product` AS `product_id` FROM `' . _DB_PREFIX_ . 'product`';

foreach ($tables as $table) {
    if (Db::getInstance()->execute($table) === false) {
        return false;
    }
}