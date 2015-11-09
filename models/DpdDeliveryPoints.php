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

class DpdDeliveryPoints extends ObjectModel
{
    public function __construct()
    {
    }

    private static $points_table_name = 'delivery_points';

    public static function getPointsTableName($db_prefix = _DB_PREFIX_)
    {
        return Tools::strtolower($db_prefix.DynamicParcelDistribution::CONST_PREFIX.self::$points_table_name);
    }

    private static function getPath($dirname)
    {
        return _PS_MODULE_DIR_.DynamicParcelDistribution::$module_name.'/'.$dirname;
    }

    public static function installDb()
    {
        $sql_file = self::getPath('install').'/install_delivery_points.sql';
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

    private static function dropPointsTable()
    {
        $sql_file = self::getPath('install').'/uninstall_delivery_points.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    public static function uninstallDb()
    {
        if (!self::dropPointsTable()) {
            return false;
        }
        return true;
    }

    public static function generateDeliveryPoints($country = false, $city = false)
    {
        $response = true;
        $helper = new API();
        $deliverypoints = DynamicParcelDistribution::objectToarray($helper->getDeliveryPoints($country, $city));
        if (!isset($deliverypoints['errlog'])) {
            $table_name = DpdDeliveryPoints::getPointsTableName('');
            Db::getInstance()->execute('TRUNCATE `'._DB_PREFIX_.$table_name.'`');
            foreach ($deliverypoints as $deliverypoint) {
                $deliverypoint['created_time'] = date('Y-m-d H:i:s');
                $deliverypoint['update_time'] = date('Y-m-d H:i:s');
                $response &= Db::getInstance()->insert($table_name, $deliverypoint);
            }
        } else {
            $response = $deliverypoints;
        }

        return $response;
    }

    public static function collectDeliveryPoints($country = false, $city = false, $id = false)
    {
        $table_name = DpdDeliveryPoints::getPointsTableName();
        $conditions = '';
        if ($country) {
            $conditions = " AND `country` = '".$country."'";
        }

        if ($city) {
            $conditions = " AND `city` = '".$city."'";
        }

        if ($id) {
            $conditions = " AND `parcelshop_id` = '".$id."'";
        }

        $sql = 'SELECT * FROM '.$table_name.' WHERE `deleted` = 0 AND `active` = 1'.$conditions;
        $deliverypoints = Db::getInstance()->ExecuteS($sql);
        return $deliverypoints;
    }

    public static function getParcelStore($parcelshop_id, $other = null)
    {
        $conditions = '';
        if ($other !== null) {
            $conditions = $other;
        }
        if (empty($parcelshop_id)) {
            return false;
        }

        $table_name = DpdDeliveryPoints::getPointsTableName();
        $sql = 'SELECT * FROM '.$table_name.' WHERE `deleted` = 0 AND `active` = 1 AND `parcelshop_id` = '.$parcelshop_id.' '.$conditions;
        $parcelshop = Db::getInstance()->ExecuteS($sql);
        return $parcelshop;
    }
}
