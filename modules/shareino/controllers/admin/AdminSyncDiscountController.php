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

require_once(dirname(__FILE__) . '/../../classes/ProductUtiles.php');

class AdminSyncDiscountController extends ModuleAdminController
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
            'id_shareino_sync' => array('title' => 'ID',
                'align' => 'center', 'width' => 25),
            'product_id' => array('title' => 'Product ID',
                'width' => 25, 'search' => true),
            'product_name' => array('title' => 'Name',
                'width' => 300, 'search' => false),
            'status' => array('title' => 'Synced', 'width'
            => 100, 'type' => 'bool'),
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
                'text' => $this->l('Synchronize'), 'confirm' => $this->l('Are you sure?'),
            ),
            'deleteAction' => array(
                'text' => $this->l('Delete From Shareino'), 'confirm' => $this->l('Are you sure?'),
            )
        );

        parent::__construct();
    }


    protected function processBulkSynchronizeAction()
    {
        $productUtiles = new ProductUtiles($this->context);
        $productUtiles->bulkSync($this->boxes);
    }

    protected function processBulkDeleteAction()
    {
        $productUtiles = new ProductUtiles($this->context);
        $sync = new ShareinoSync();
        $productIds = $sync->getProductsIds($this->boxes);

        $productUtiles->deleteProducts($productIds);
        if ($productIds) {
            $sync->changeProductsStatus($this->boxes, 0);
        }
    }


    public function createTemplate($tpl_name)
    {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess()) {

            return $this->context->smarty->createTemplate($this->getTemplatePath() . 'sync_discount.tpl', $this->context->smarty);
        }
        return parent::createTemplate($tpl_name);
    }

    public function processConfiguration()
    {
		
		

        if (Tools::isSubmit('sync_discount')) {
			
         $query = 'SELECT id_product,`from` as start_date, `to` as end_date,from_quantity as quantity, reduction as amount ,reduction_type
                FROM ' . _DB_PREFIX_ . 'specific_price
                WHERE id_product !=0 
                ORDER BY id_specific_price';
            $discounts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

			 // $discounts =json_encode($discounts);
       
		 
		 $productUtiles = new ProductUtiles($this->context);
            $sync = new ShareinoSync();
            $productIds = $sync->getProductsIds(null, true);
$str='';
            if ($productIds) {
                $split_ids = array_chunk($productIds, 75);
                foreach ($split_ids as $pIds) {
					
                    $productUtiles->syncProductDiscount($pIds);

                }
            }

        }
        $this->context->smarty->assign('module_dir', __PS_BASE_URI__ . 'modules/');


        $url = 'index.php?controller=AdminSyncDiscount&configure=shareino';
        $url .= '&token=' . Tools::getAdminTokenLite('AdminSyncDiscount');

        $this->context->smarty->assign('list', $this->renderList());
        $this->context->smarty->assign('url', $url);

        $link = new LinkCore();
        $action = $link->getAdminLink("AdminSynchronize");
        $this->context->smarty->assign('actionUrl', $action);
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

}
