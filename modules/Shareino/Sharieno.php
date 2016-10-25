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
    
}