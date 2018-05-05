<?php
/**
 * 2015-2018 Dokme
 *
 * NOTICE OF LICENSE
 *
 * This source file is for module that make sync Product With Dokme server
 * https://github.com/SaeedDarvish/ShareinoPrestaShopModule
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Dokme to newer
 * versions in the future. If you wish to customize Dokme for your
 * needs please refer to https://github.com/SaeedDarvish/ShareinoPrestaShopModule for more information.
 *
 * @author    Saeed Darvish <sd.saeed.darvish@gmail.com>
 * @copyright 2015-2018 Dokme Co
 *  Tejarat Ejtemaie Eram
 */

require_once(dirname(__FILE__) . '/../../classes/ProductUtiles.php');

class AdminSynchronizeController extends ModuleAdminController
{

    public function __construct()
    {
        $this->module = 'shareino';
        $this->table = 'shareino_sync';
        $this->context = Context::getContext();
        $this->context->controller = $this;
        $this->bootstrap = true;
        $this->lang = false;

        $this->context->controller->addJS('js/jquery/jquery-1.11.0.min.js', 'all');
        $this->context->controller->addJS('js/jquery/plugins/select2/jquery.select2.js', 'all');
        $this->context->controller->addJS('js/jquery/plugins/select2/select2_locale_fa.js', 'all');

        $this->fields_list = array(
            'id_shareino_sync' => array('title' => 'ID', 'align' => 'center', 'width' => 25),
            'product_id' => array('title' => 'Product ID', 'width' => 25, 'search' => true),
            'product_name' => array('title' => 'Name', 'width' => 300, 'search' => false),
            'status' => array('title' => 'Synced', 'width' => 100, 'type' => 'bool'),
            'errors' => array('title' => 'Errors', 'width' => 300, 'search' => false),
            'date_upd' => array('title' => 'Date Update', 'type' => 'datetime', 'search' => false)
        );


        $this->addRowAction('aa');
        // Update the SQL request of the HelperList
        $this->_select = "pl.`name` as product_name";
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.`id_product` = a.`product_id` AND pl.`id_lang` = ' . (int)$this->context->language->id . ')';

        // Add bulk actions
        $this->bulk_actions = array(
            'synchronizeAction' => array(
                'text' => $this->l('همسان سازی'), 'confirm' => $this->l('Are you sure?'),
            )
        );

        parent::__construct();
    }


    protected function processBulkSynchronizeAction()
    {
        $productUtiles = new ProductUtiles($this->context);
        $productUtiles->bulkSync($this->boxes);
    }

    public function createTemplate($tpl_name)
    {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess()) {

            return $this->context->smarty->createTemplate($this->getTemplatePath() . 'content.tpl', $this->context->smarty);
        }
        return parent::createTemplate($tpl_name);
    }

    public function processConfiguration()
    {
        $this->context->smarty->assign('module_dir', __PS_BASE_URI__ . 'modules/');


        $url = 'index.php?controller=AdminModules&configure=shareino';
        $url .= '&token=' . Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign('list', $this->renderList());
        $this->context->smarty->assign('url', $url);

        $this->context->smarty->assign(array(
            'token' => Tools::getAdminTokenLite('AdminSynchronize')
        ));

        $link = new LinkCore();
        $action = $link->getAdminLink("AdminSynchronize");
        $this->context->smarty->assign('actionUrl', $action);


        $sync = new ShareinoSync();
        $productIds = $sync->getProductsIds(null, true);
        $this->context->smarty->assign('productIDs', $productIds);
    }

    public function renderForm()
    {
        parent::renderForm();
    }

    public function initContent()
    {
        parent::initContent();
        $this->processConfiguration();
        $this->context->smarty;
    }

    function ajaxProcessSyncProducts()
    {
        $productUtiles = new ProductUtiles($this->context);
        $ids = Tools::getValue('ids');
        ob_start();
        echo Tools::jsonEncode($productUtiles->syncProduct($ids));
    }

    public function ajaxProcessSendCats()
    {
        $categories = CategoryCore::getNestedCategories(null, $this->context->language->id);
        $output = array();
        $this->treeCategories($categories, $output);
        $productUtiles = new ProductUtiles($this->context);
        $result = $productUtiles->sendRequset("categories/sync", "POST", Tools::jsonEncode($output));
        if ($result['status']) {
            ConfigurationCore::set("SHAREINO_SENT_CATS", true);
        }
        ob_start();
        echo Tools::jsonEncode($result);
    }

    public function ajaxProcessSyncDiscounts()
    {
        $productUtil = new ProductUtiles($this->context);

        $ids = Tools::getValue('ids');
        ob_start();

        echo Tools::jsonEncode($productUtil->syncProductDiscount($ids));
    }

    public function treeCategories($pcategories, &$outPut)
    {
        foreach ($pcategories as $category) {
            $outPut[] = array("id" => $category["id_category"],
                "parent_id" => $category["id_parent"],
                "name" => $category["name"],
                "link_rewrite" => $category["link_rewrite"],
            );
            if (isset($category) & !empty($category['children']))
                $this->treeCategories($category['children'], $outPut);
        }
    }

}
