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
 * Tejarat Ejtemaie Eram
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/ShareinoSync.php');
require_once(dirname(__FILE__) . '/classes/ProductUtiles.php');
require_once(dirname(__FILE__) . '/controllers/admin/AdminSynchronizeController.php');

class Shareino extends Module
{

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'shareino';
        $this->tab = 'export';
        $this->version = '1.3.12';
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

        if (empty(Configuration::get('SELLER_TOKEN'))) {
            Configuration::updateValue('SELLER_TOKEN', bin2hex(static::randomBytes(20)));
        }
    }

    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        $this->installTabs();

        return parent::install() &&
            $this->registerHook('actionProductDelete') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('actionUpdateQuantity') &&
            $this->registerHook('actionCategoryAdd') &&
            $this->registerHook('actionObjectCategoryUpdateAfter') &&
            $this->registerHook('actionCategoryDelete') &&
            $this->registerHook('actionObjectProductUpdateAfter');
    }

    public function uninstall()
    {
        $productUtiles = new ProductUtiles($this->context);
        $productUtiles->sendRequset('status', 'POST', null);

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
                        'desc' => $this->l('توکن دریافتی خود از دکمه را در اینجا وارد کنید.'),
                        'name' => 'SHAREINO_API_TOKEN',
                        'label' => $this->l('توکن دریافتی از دکمه'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-stop"></i>',
                        'desc' => $this->l('درصورت لزوم به شما اطلاع داده میشود که این توکن را برای ما ارسال کنید.'),
                        'name' => 'SELLER_TOKEN',
                        'label' => $this->l(''),
                        'readonly' => true,
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
            'SHAREINO_API_TOKEN' => Configuration::get('SHAREINO_API_TOKEN', ""),
            'SELLER_TOKEN' => Configuration::get('SELLER_TOKEN', "")
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

    public function hookActionCategoryAdd($params)
    {
        if (isset($params['category']) && !empty($params['category'])) {

            $category = array("id" => $params['category']->id,
                "parent_id" => $params['category']->id_parent,
                "name" => $params['category']->name[$this->context->language->id],
            );

            $productUtiles = new ProductUtiles($this->context);
            $productUtiles->sendRequset("categories", "POST", Tools::jsonEncode($category));
        }
    }

    public function hookActionObjectCategoryUpdateAfter($params)
    {
        if (isset($params['object']) && !empty($params['object'])) {
            $category = array("id" => $params['object']->id_category,
                "parent_id" => $params['object']->id_parent,
                "name" => $params['object']->name[$this->context->language->id],
            );
            $productUtiles = new ProductUtiles($this->context);
            $productUtiles->sendRequset("categories", "POST", Tools::jsonEncode($category));
        }
    }

    public function hookActionObjectCategoryDeleteAfter($params)
    {

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
        $product_id = $params["id_product"];

        // When its delete action so call delete hook
        if (!isset($params['product'])) {
            $this->hookActionProductDelete($params);
            return;
        }

        $selectCategory = $this->_checkSelectedCategory($product_id);
        if (!$selectCategory) {
            $this->hookActionProductDelete($params);
            return;
        }

        if (ConfigurationCore::get("SHAREINO_SENT_CATS") !== true) {

            $categories = CategoryCore::getNestedCategories(null, $this->context->language->id);

            $output = array();

            $syncController = new AdminSynchronizeController();
            $syncController->treeCategories($categories, $output);

            $productUtiles = new ProductUtiles($this->context);
            $result = Tools::jsonEncode($productUtiles->sendRequset("categories/sync", "POST", Tools::jsonEncode($output)));
            $result_array = Tools::jsonDecode($result, true);

            if ($result_array['status']) {
                ConfigurationCore::set("SHAREINO_SENT_CATS", true);
            }
        }

        $productUtil = new ProductUtiles($this->context);
        $product = $productUtil->getProductDetailById($product_id);

        if ($product["active"]) {
            $result = $productUtil->sendRequset("products", "POST", Tools::jsonEncode($product));
            if ($result["status"])
                $productUtil->parsSyncResult($result["data"], $product_id);
        }

        return $params;
    }

    public function hookActionUpdateQuantity($params)
    {
        if (!isset($params['id_product']))
            return true;

        $productUtil = new ProductUtiles($this->context);
        $product = $productUtil->getProductDetailById($params['id_product']);

        if ($product["active"]) {
            $result = $productUtil->sendRequset("products", "POST", Tools::jsonEncode($product));
            if ($result["status"])
                $productUtil->parsSyncResult($result["data"], $params['id_product']);
        }
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
        $tab->name[$this->context->language->id] = $this->l('همسان سازی');
        $tab->class_name = 'AdminSynchronize';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();

        $tab = new Tab();
        // Need a foreach for the language
        $tab->name[$this->context->language->id] = $this->l('تنظیمات ارسال کالاها');
        $tab->class_name = 'AdminManageCats';
        $tab->id_parent = $parent_tab->id;
        $tab->module = $this->name;
        $tab->add();
    }

    protected function _checkSelectedCategory($productId)
    {
        $category = json_decode(Configuration::get('SHAREINO_SELECT_CATEGORY'), true);

        if ($category) {
            $category = implode(',', $category);

            $tblProduct = _DB_PREFIX_ . 'product';
            $tblCategoryProduct = _DB_PREFIX_ . 'category_product';

            $query = "SELECT `$tblProduct`.`id_product` FROM `$tblProduct`
                  INNER JOIN `$tblCategoryProduct` ON `$tblCategoryProduct` . `id_product` = `$tblProduct` . `id_product`
                  WHERE `$tblProduct` . `id_product`= $productId AND `$tblProduct` . `active` = 1
                  AND `$tblCategoryProduct` . `id_category` IN($category)";

            $id = Db::getInstance()->executeS($query);

            if (empty($id)) {
                return false;
            }
        }
        return true;
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        $this->hookActionProductSave($params);
    }

    public static function randomBytes($length)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strongSource);
            if (!$strongSource) {
                trigger_error(
                    'openssl was unable to use a strong source of entropy. ' .
                    'Consider updating your system libraries, or ensuring ' .
                    'you have more available entropy.',
                    E_USER_WARNING
                );
            }

            return $bytes;
        }

        trigger_error(
            'You do not have a safe source of random data available. ' .
            'Install either the openssl extension, or paragonie/random_compat. ' .
            'Falling back to an insecure random source.',
            E_USER_WARNING
        );

        return static::insecureRandomBytes($length);
    }

    public static function insecureRandomBytes($length)
    {
        $byteLength = 0;
        $length *= 2;
        $bytes = '';
        while ($byteLength < $length) {
            $bytes .= static::hash(uniqid('-') . uniqid(mt_rand(), true), 'sha512', true);
            $byteLength = strlen($bytes);
        }
        $bytes = substr($bytes, 0, $length);

        return pack('H*', $bytes);
    }

    public static function hash($string, $type = null, $salt = false)
    {
        return hash(strtolower($type), $salt . $string);
    }

}
