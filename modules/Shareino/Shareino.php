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

class Shareino extends Module
{

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'Shareino';
        $this->tab = 'export';
        $this->version = '1.2.0';
        $this->author = 'Saeed Darvish';
        $this->need_instance = 1;
        $this->module_key = '84e0bc5da856da1c414762d8fdfe9a71';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Shareino');
        $this->description = $this->l('Make Sync Your Product with shareino server');

        $this->confirmUninstall = $this->l('if You unistall it you cant sync to shareino,Are you sure you want to uninstall?');

        $token = Configuration::get('SHAREINO_API_TOKEN');

        if ($token == "" || $token == null) {
            $this->warning = $this->l('Shareino Token hasn\'t been set yet');
        }
    }

    public function install()
    {

        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        //Init
        if (!ConfigurationCore::get("SHAREINO_API_TOKEN"))
            ConfigurationCore::updateValue('SHAREINO_API_TOKEN', '');

        include(dirname(__FILE__) . '/sql/install.php');

        $this->installTabs();
        
        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('actionProductDelete') &&
        $this->registerHook('actionProductSave') &&
        $this->registerHook('actionUpdateQuantity');
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output;
    }

    public function hookActionProductDelete()
    {
        /* Place your code here. */
    }

    public function hookActionProductSave()
    {
        /* Place your code here. */
    }

    public function hookActionUpdateQuantity()
    {
        /* Place your code here. */
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
        $tab->name[$this->context->language->id] = $this->l('ّهمسان سازی محصولات');
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