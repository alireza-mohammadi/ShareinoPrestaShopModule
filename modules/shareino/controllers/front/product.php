<?php

require_once _PS_MODULE_DIR_ . 'shareino/classes/ProductUtiles.php';
require_once _PS_MODULE_DIR_ . 'shareino/classes/dokmeAuth.php';

class ShareinoProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $data = array('status' => true);

        $auth = new dokmeAuth();
        if ($auth->auth()) {

            if (!isset($_GET['id'])) {
                echo Tools::jsonEncode(['status' => false, 'message' => 'خطا! ایدی کالا وارد نشده است.'], true);
                return $this->setTemplate();
            }

            $tblProduct = _DB_PREFIX_ . 'product';
            $id = Db::getInstance()->execute("SELECT `id_product` FROM `$tblProduct` WHERE `$tblProduct`.`active` = 1 AND `id_product`=" . $_GET['id']);

            if (empty($id)) {
                echo Tools::jsonEncode(['status' => false, 'message' => 'کالایی با این ایدی پیدا نشد.'], true);
                return $this->setTemplate();
            }

            if (!$this->checkCategory($id)) {
                echo Tools::jsonEncode(['status' => false, 'message' => 'در دسته بندی انتخاب شده نیست.'], true);
                return $this->setTemplate();
            }

            $productUtiles = new ProductUtiles($this->context);
            $data['data'] = $productUtiles->getProductDetailById($id);
            echo(Tools::jsonEncode($data));

            $time = date('Y-m-d H:i:s');
            $tblSync = _DB_PREFIX_ . 'dokme_synchronize';
            $query = "SELECT `id` FROM $tblSync WHERE `product_id` = $id";
            $result = Db::getInstance()->execute($query);

            if ($result) {
                $query = "UPDATE `$tblSync` SET `date_sync`='$time' WHERE `product_id` = $id";
            } else {
                $query = "INSERT INTO $tblSync(`product_id`, `date_sync`) VALUES ($id,$time)";
            }
            Db::getInstance()->execute($query);
        }

        $this->setTemplate();
    }

    protected function checkCategory($id)
    {
        $category = Configuration::get('SHAREINO_SELECT_CATEGORY');

        if (empty($category)) {
            return false;
        }

        $tblProduct = _DB_PREFIX_ . 'product';
        $tblCategoryProduct = _DB_PREFIX_ . 'category_product';

        $category = implode(',', Tools::jsonDecode($category));

        $query = "SELECT `$tblProduct`.`id_product` FROM `$tblProduct`
                  INNER JOIN `$tblCategoryProduct` ON `$tblCategoryProduct` . `id_product` = `$tblProduct` . `id_product`
                  WHERE `$tblProduct` . `id_product`= $id AND `$tblProduct` . `active` = 1
                  AND `$tblCategoryProduct` . `id_category` IN($category)";

        $id = Db::getInstance()->executeS($query);

        if (empty($id)) {
            return false;
        }

        return true;
    }

}