<?php

require_once _PS_MODULE_DIR_ . 'shareino/classes/dokmeAuth.php';

class ShareinoEmptyModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $auth = new dokmeAuth();

        if ($auth->auth()) {
            $tblSync = _DB_PREFIX_ . 'dokme_synchronize';
            $tblProduct = _DB_PREFIX_ . 'product';

            Db::getInstance()->execute("DELETE FROM `$tblSync`");

            Db::getInstance()->execute("INSERT IGNORE INTO $tblSync(`product_id`) SELECT `id_product` AS `product_id` FROM $tblProduct");

            echo Tools::jsonEncode(['status' => true, 'message' => 'empty db'], true);
        }

        $this->setTemplate();
    }
}