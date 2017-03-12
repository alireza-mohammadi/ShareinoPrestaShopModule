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
 *  Tejarat Ejtemaie Eram
 */
class ShareinoSync extends ObjectModel
{
    public $id_shareino_sync;
    public $product_id;
    public $status;
    public $errors;
    public $date_add;
    public $date_upd;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'shareino_sync', 'primary' => 'id_shareino_sync', 'multilang' => false,
        'fields' => array(
            'product_id' => array('type' => self::TYPE_INT, 'required' => true),
            'status' => array('type' => self::TYPE_BOOL),
            'errors' => array('type' => self::TYPE_STRING, 'size' => 500),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'))
    );

    /**
     * Adds current object to the database and check if its product code is duplicate update fields
     * @param bool $auto_date create time
     * @param bool $null_values
     * @return bool | Insertion result
     */
    public function syncLocalField($auto_date = true, $null_values = false)
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

//        $tbl=_DB_PREFIX_.$this->def['table'];

//        $sql = "INSERT INTO $tbl (`product_id`,`status`,`errors`,`date_add`) VALUES ($this->product_id,$this->status,'$this->errors',$this->data_add)
//        ON DUPLICATE KEY UPDATE status=$this->status,errors='$this->errors',date_upd=$this->date_upd";

        if (!$result = Db::getInstance()->insert($this->def['table'], $this->getFields(), $null_values, true, Db::REPLACE)) {
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

    public function deleteProduct($id)
    {

        $tbl = _DB_PREFIX_ . $this->def['table'];

        $sql = "DELETE FROM $tbl WHERE product_id=$id";

        return Db::getInstance()->query($sql);
    }

    public function getProductsIds($ids, $all = false)
    {
        if ($ids != null) {
            $ids = implode(", ", $ids);
        } else {
            $all = true;
        }

        $query = "SELECT `product_id` from " . _DB_PREFIX_ . "shareino_sync";

        $query .= !$all ? " WHERE id_shareino_sync in ($ids)" : "";

        $product_ids = Db::getInstance()->executeS($query);

        $ids = array();

        foreach ($product_ids as $pid) {
            $ids[] = $pid["product_id"];
        }

        return $ids;

    }

    public function changeProductsStatus($ids, $status = 0, $all = false)
    {

        $ids = implode(", ", $ids);
        $query = "UPDATE " . _DB_PREFIX_ . "shareino_sync SET status=$status";

        $query .= !$all ? " WHERE id_shareino_sync in ($ids);" : " WHERE 1;";

        return Db::getInstance()->execute($query);
    }
}
