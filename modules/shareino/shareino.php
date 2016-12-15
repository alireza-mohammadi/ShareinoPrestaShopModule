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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/ShareinoSync.php');
require_once(dirname(__FILE__) . '/classes/ProductUtiles.php');

class Shareino extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'shareino';
        $this->tab = 'export';
        $this->version = '1.2.0';
        $this->author = 'Saeed Darvish';
        $this->need_instance = 1;
        $this->module_key = '84e0bc5da856da1c414762d8fdfe9a71';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Shareino');
        $this->description = $this->l('Make Sync Your Product with shareino server');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $token = Configuration::get('SHAREINO_API_TOKEN');

        if ($token == "" || $token == null) {
            $this->warning = $this->l('Shareino Token hasn\'t been set yet');
        }
    }

    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        $this->installTabs();

        return parent::install() &&
        $this->registerHook('actionProductDelete') &&
        $this->registerHook('actionProductSave') &&
        $this->registerHook('actionUpdateQuantity');
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        // Uninstall Tabs
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitShareinoModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitShareinoModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 9,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter Shareino\'s webservice token'),
                        'name' => 'SHAREINO_API_TOKEN',
                        'label' => $this->l('Shareino Token'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SHAREINO_API_TOKEN' => Configuration::get('SHAREINO_API_TOKEN', "")
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookActionProductDelete($params)
    {
        $product_id = $params["id_product"];
        $productUtil = new ProductUtiles($this->context);

        $productUtil->deleteProducts($product_id);

        $sync = new ShareinoSync();
        $sync->deleteProduct($product_id);

    }

    public function hookActionProductSave($params)
    {

        if (!isset($params["product"]))
            return true;

        $product_id = $params["id_product"];

        // When its delete action so call delete hook
        if (!isset($params['product'])) {
            $this->hookActionProductDelete($params);
            return;
        }

        $productUtil = new ProductUtiles($this->context);
        $product = $productUtil->getProductDetailById($product_id);

        if ($product["active"]) {
            $result = $productUtil->sendRequset("products", "POST", Tools::jsonEncode($product));
            $productUtil->parsSyncResult($result, $product_id);
        } else {
            $productUtil->deleteProducts($product_id);
        }

        return $params;
    }

    public function hookActionUpdateQuantity($params)
    {
        $this->hookActionProductSave($params);
    }

    public function hookActionProductUpdate($params)
    {

        $this->hookActionProductSave($params);
    }

    /**
     * Add Shareino tabs to Prestashop main menu
     */
    public function installTabs()
    {
        // Install Tabs
        $parent_tab = new Tab();
        // Need a foreach for the language
        $parent_tab->name[$this->context->language->id] = $this->l('Shareino');
        $parent_tab->id_parent = 0; // Home tab
        $parent_tab->class_name = 'AdminSynchronize';
        $parent_tab->module = $this->name;
        $parent_tab->add();


        $tab = new Tab();
        // Need a foreach for the language
        $tab->name[$this->context->language->id] = $this->l('همسان سازی محصولات');
        $tab->class_name = 'AdminSynchronize';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        $tab = new Tab();

        // Need a foreach for the language
        $tab->name[$this->context->language->id] = $this->l('معادل سازی دسته بندی ها');
        $tab->class_name = 'AdminManageCats';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();
    }
}
