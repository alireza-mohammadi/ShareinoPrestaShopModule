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

require_once(dirname(__FILE__) . '/../../classes/OrganizeCategories.php');
require_once(dirname(__FILE__) . '/../../classes/ProductUtiles.php');

class AdminManageCatsController extends ModuleAdminController
{

    public function __construct()
    {
        $this->module = 'shareino';
        $this->context = Context::getContext();
        $this->context->controller = $this;
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'shareino_organized';
        $this->context->controller->addJS('js/jquery/jquery-1.11.0.min.js', 'all');
        $this->context->controller->addJS('js/jquery/plugins/select2/jquery.select2.js', 'all');
        $this->context->controller->addJS('js/jquery/plugins/select2/select2_locale_fa.js', 'all');

        $this->fields_list = array(
            'id_shareino_organized' => array('title' => 'ID',
                'align' => 'center', 'width' => 25),
            'cat_id' => array('title' => 'Category ID',
                'width' => 25, 'search' => true),
            'category_name' => array('title' => 'Category Name',
                'width' => 300, 'search' => false),
            'ids' => array('title' => 'Shareino Categories\'s Ids', 'width'
            => 100, 'search' => false),
            'names' => array('title' => 'Shareino Categories\'s name', 'width' => 300, 'search' => false),
        );


        // Add bulk actions
        $this->bulk_actions = array(
            'deleteAction' => array(
                'text' => $this->l('Delete From Shareino'), 'confirm' => $this->l('Are you sure?'),
            )
        );

        // Update the SQL request of the HelperList
        $this->_select = "cat.`name` as category_name";
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cat ON (cat.`id_category` = a.`cat_id` AND cat.`id_lang` = ' . (int)$this->context->language->id . ')';
        parent::__construct();
    }

    protected function processBulkDeleteAction()
    {
        OrganizeCategories::bulkdelete($this->boxes);
    }

    public function createTemplate($tpl_name)
    {
        if (file_exists($this->getTemplatePath() . $tpl_name) && $this->viewAccess()) {

            return $this->context->smarty->createTemplate($this->getTemplatePath() . $tpl_name, $this->context->smarty);
        }
        return parent::createTemplate($tpl_name);
    }

    public function processConfiguration()
    {
        $this->context->smarty->assign('module_dir', __PS_BASE_URI__ . 'modules/');


        // Get All Shareino categories
        $productUtil = new ProductUtiles($this->context);
        $shareinoCategories = $productUtil->sendRequset("categories", "GET");


        $shareinoCategories = Tools::jsonDecode($shareinoCategories, true);


        if (Tools::isSubmit("organize_categories_submit")) {
            $catId = Tools::getValue("store_cat");
            $shareinoCats = Tools::getValue("shareino_cats");

            if (is_numeric($catId) & $catId > 0) {

                $orgCategories = new OrganizeCategories();
                $orgCategories->cat_id = $catId;
                $names = array();
                if (isset($shareinoCategories["categories"])) {
                    try {
                        foreach ($shareinoCats as $id) {
                            $names[] = str_replace("-- ", "", $shareinoCategories["categories"][$id]);
                        }
                    } catch (Exception $e) {
                        //don nothing
                    }
                }
                $orgCategories->ids = implode(",", $shareinoCats);
                $orgCategories->names = implode(", ", $names);
                $orgCategories->names = implode(", ", $names);
                $orgCategories->add();

            }
        }

        // Create List of categories and active options
        $storeCat = CategoryCore::getCategories($this->context->language->id, true, false);


        $this->context->smarty->assign('list', $this->renderList());
        $this->context->smarty->assign('form', $this->renderForm());
        $this->context->smarty->assign('categories', $storeCat);
        $shareinoCategories = isset($shareinoCategories["categories"]) ? $shareinoCategories["categories"] : false;
        $this->context->smarty->assign('shareinoCategories', $shareinoCategories);
        $link = new LinkCore();
        $action = $link->getAdminLink("AdminManageCats");
        $this->context->smarty->assign('url', $action);
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

