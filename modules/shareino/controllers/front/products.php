<?php

require_once _PS_MODULE_DIR_ . 'shareino/classes/ProductUtiles.php';
require_once _PS_MODULE_DIR_ . 'shareino/classes/dokmeAuth.php';

class ShareinoProductsModuleFrontController extends ModuleFrontController
{
    const SIZE = 100;

    public function __construct($response = array())
    {
        parent::__construct($response);
        $this->display_header = false;
        $this->display_header_javascript = false;
        $this->display_footer = false;
    }

    public function initContent()
    {
        parent::initContent();

        $data = array('status' => true);

        $auth = new dokmeAuth();
        if ($auth->auth()) {

            $tblProduct = _DB_PREFIX_ . 'product';
            $tblSync = _DB_PREFIX_ . 'dokme_synchronize';
            $tabCategoryProduct = _DB_PREFIX_ . 'category_product';

            if ($this->getSelectedCategories() === false) {
                $query = "SELECT `id_product` FROM `$tblProduct` LEFT JOIN `$tblSync` ON `$tblProduct`.`date_upd` > `$tblSync`.`date_sync` WHERE `$tblProduct`.`active` = 1 GROUP BY `$tblProduct`.`id_product` limit " . self::SIZE;
            } else {
                $category = implode(',', $this->getSelectedCategories());
                $query = "SELECT `$tabCategoryProduct`.`id_product` FROM `$tabCategoryProduct` LEFT JOIN `$tblProduct` ON `$tblProduct`.`id_product` = `$tabCategoryProduct`.`id_product` AND `$tblProduct`.`active` = 1 WHERE `$tabCategoryProduct`.`id_category` IN($category) GROUP BY `id_product` ASC limit " . self::SIZE;
            }
            $ids = Db::getInstance()->executeS($query);

            if (empty($ids)) {
                echo Tools::jsonEncode(['status' => false, 'message' => 'کالایی با این ایدی پیدا نشد.'], true);
                return $this->setTemplate();
            }

            // get product detail
            $products = [];
            $productUtiles = new ProductUtiles($this->context);
            foreach ($ids as $id) {
                $products[] = $productUtiles->getProductDetailById($id['id_product']);
            }
            $data['data'] = $products;
            echo(Tools::jsonEncode($data));

            // save local
            $time = date('Y-m-d H:i:s');
            $ids = $this->array_pluck($ids, 'id_product');
            $ids = implode(',', $ids);
            $query = "UPDATE `$tblSync` SET `date_sync`='$time' WHERE `product_id` IN ($ids)";
            Db::getInstance()->execute($query);
        }
    }

    protected function array_pluck($array, $column_name)
    {
        if (function_exists('array_column')) {
            return array_column($array, $column_name);
        }

        return array_map(function ($element) use ($column_name) {
            return $element[$column_name];
        }, $array);
    }

    protected function getSelectedCategories()
    {
        $categories = Configuration::get('SHAREINO_SELECT_CATEGORY');
        if (empty($categories)) {
            return false;
        }
        return Tools::jsonDecode($categories);
    }

}
