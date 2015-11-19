<?php
/**
 * 2015 UAB BaltiCode
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License available
 * through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@balticode.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to
 * newer versions in the future.
 *
 *  @author    UAB Balticode Kęstutis Kaleckas
 *  @package   Balticode_DPD
 *  @copyright Copyright (c) 2015 UAB Balticode (http://balticode.com/)
 *  @license   http://www.gnu.org/licenses/gpl-3.0.txt  GPLv3
 */

class DpdOrderOptions extends ObjectModel
{
    private static $options_table_name = 'delivery_options';

    public function __construct()
    {
    }

    public static function getOptionsTableName($db_prefix = _DB_PREFIX_)
    {
        return Tools::strtolower($db_prefix.DynamicParcelDistribution::CONST_PREFIX.self::$options_table_name);
    }

    private static function getPath($dirname)
    {
        return _PS_MODULE_DIR_.DynamicParcelDistribution::$module_name.'/'.$dirname;
    }

    public static function installDb()
    {
        $sql_file = self::getPath('install').'/install_order_options.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    private static function loadSQLFile($sql_file)
    {
        $result = true;
        $sql_content = Tools::file_get_contents($sql_file);
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }
        return $result;
    }

    private static function dropOptionsTable()
    {
        $sql_file = self::getPath('install').'/uninstall_order_options.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    public static function uninstallDb()
    {
        if (!self::dropOptionsTable()) {
            return false;
        }
        return true;
    }

    public static function setOrderOptions($id_cart, $carrier_id, $address_id, $option)
    {
        $orderOptions = array(
                'cart_id' => $id_cart,
                'carrier_id' => $carrier_id,
                'address_id' => $address_id,
                'delivery_option' => $option,
            );

        $table_name = DpdOrderOptions::getOptionsTableName('');
        if (self::getCartExist($id_cart)) {
            $response = Db::getInstance()->update($table_name, $orderOptions, "cart_id=$id_cart", 1);
        } else {
            $response = Db::getInstance()->insert($table_name, $orderOptions);
        }
        return $response;
    }

    public static function setOrderIdByCartId($id_cart, $id_order, $id_carrier, $id_address)
    {
        $response = false;
        $table_name = DpdOrderOptions::getOptionsTableName();
        $sql = 'SELECT `id_dpd_delivery_options` FROM `'.$table_name.
        '` WHERE `lock` = 0 AND `cart_id` = '.$id_cart.
        //' AND `carrier_id` = '.$id_carrier.
        ' AND `address_id` = '.$id_address;
        $orderOprions = Db::getInstance()->ExecuteS($sql);

        $orderOptions = array(
                'order_id' => $id_order,
                'lock' => 1,
                'carrier_id' => $id_carrier
            );

        $table_name = DpdOrderOptions::getOptionsTableName('');
        if (count($orderOprions) == 1) {
            $response = Db::getInstance()->update($table_name, $orderOptions, 'id_dpd_delivery_options = '.$orderOprions[0]['id_dpd_delivery_options'], 1);
        }
        return $response;
    }

    public static function getCartExist($id_cart)
    {
        $table_name = DpdOrderOptions::getOptionsTableName();

        $sql = 'SELECT * FROM `'.$table_name.'` WHERE `cart_id` = '.$id_cart.' LIMIT 1';

        $orderOprions = Db::getInstance()->ExecuteS($sql);
        if (count($orderOprions)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getOrderOptions($order_id, $limit = 1)
    {
        $table_name = DpdOrderOptions::getOptionsTableName();
        $conditions = '';

        if ($order_id) {
            $conditions = " AND `order_id` = '".$order_id."'";
        }

        $sql = 'SELECT * FROM `'.$table_name.'` WHERE `id_dpd_delivery_options` > 0 '.$conditions.' LIMIT '.$limit;
        $orderOprions = Db::getInstance()->ExecuteS($sql);
        return $orderOprions;
    }
}
