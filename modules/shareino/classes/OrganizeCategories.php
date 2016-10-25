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

class OrganizeCategories extends ObjectModel
{
    public $id_shareino_categories;
    public $cat_id;
    public $ids;
    public $names;
    public $model;
    public static $definition = array(
        'table' => 'shareino_organized', 'primary' => 'id_shareino_organized', 'multilang' => false,
        'fields' => array(
            'cat_id' => array('type' => self::TYPE_INT),
            'ids' => array('type' => self::TYPE_STRING),
            'names' => array('type' => self::TYPE_STRING),
            'model' => array('type' => self::TYPE_STRING),
        )
    );

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds current object to the database and check if its product code is duplicate update fields
     * @param bool $auto_date create time
     * @param bool $null_values
     * @return bool | Insertion result
     */
    public function add($auto_date = true, $null_values = false)
    {
        if (isset($this->id) && !$this->force_id) {
            unset($this->id);
        }

        // @hook actionObject*AddBefore
        Hook::exec('actionObjectAddBefore', array('object' => $this));
        Hook::exec('actionObject' . get_class($this) . 'AddBefore', array('object' => $this));

        // Automatically fill dates
        if ($auto_date && property_exists($this, 'date_add')) {
            $this->date_add = date('Y-m-d H:i:s');
        }
        if ($auto_date && property_exists($this, 'date_upd')) {
            $this->date_upd = date('Y-m-d H:i:s');
        }
        $result = Db::getInstance()->insert($this->def['table'],
            $this->getFields(),
            $null_values,
            true,
            Db::REPLACE
        );

        if (!$result) {
            return false;
        }

        // Get object id in database
        $this->id = Db::getInstance()->Insert_ID();

        if (!$result) {
            return false;
        }
        // @hook actionObject*AddAfter
        Hook::exec('actionObjectAddAfter', array('object' => $this));
        Hook::exec('actionObject' . get_class($this) . 'AddAfter', array('object' => $this));

        return $result;
    }

    public static function getShareinoids($categories)
    {
        $categoriesIds = array();
        $productCategories = array(
            "matching" => array(),
            "notMatching" => array()
        );

        $notmatching = array();
        foreach ($categories as $category) {
            $categoriesIds[] = $category["id_category"];
            $notmatching[$category["id_category"]]
                = array($category["link_rewrite"] => $category["name"]);
        }

        $categoriesIds = implode(",", $categoriesIds);

        $query = "SELECT cat_id,ids from " . _DB_PREFIX_ .
            "shareino_organized WHERE cat_id in ($categoriesIds)";

        $result = Db::getInstance()->executeS($query);

        if ($result) {
            foreach ($result as $item) {
                $productCategories["matching"] = array_merge(
                    $productCategories["matching"],
                    explode(",", $item["ids"])
                );

                unset($notmatching[$item["cat_id"]]);
            }
        }
//        d($notmatching);
        foreach ($notmatching as $item) {
            $key = key($item);
            $productCategories["notMatching"][$key] = $item[$key];
        }
        return $productCategories;
    }

    public static function bulkdelete($id)
    {

        $tbl = _DB_PREFIX_ . self::$definition['table'];

        $sql = "DELETE FROM $tbl WHERE id_shareino_organized in (" . implode(",", $id) . ")";

        return Db::getInstance()->query($sql);
    }
}