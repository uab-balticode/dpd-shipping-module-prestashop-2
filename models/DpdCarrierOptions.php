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

class DpdCarrierOptions extends ObjectModel
{
    private static $options_table_name = 'carrier_options';

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Return date from database
     *
     * @param  string $db_prefix prefix of database
     * @return array data
     */
    public static function getOptionsTableName($db_prefix = _DB_PREFIX_)
    {
        return Tools::strtolower($db_prefix.DynamicParcelDistribution::CONST_PREFIX.self::$options_table_name);
    }

    /**
     * return full patch to file
     *
     * @param  string $dirname file name patch end
     * @return string full file patch
     */
    private static function getPath($dirname)
    {
        return _PS_MODULE_DIR_.DynamicParcelDistribution::$module_name.'/'.$dirname;
    }

    /**
     * Function run install database SQL script
     *
     * @return boolean true
     */
    public static function installDb()
    {
        $sql_file = self::getPath('install').'/install_carrier_options.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    /**
     * load selected SQL file
     *
     * @param  string $sql_file full patch to file
     * @return mix
     */
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

    /**
     * function run uninstall SQL script
     *
     * @return boolean true
     */
    private static function dropOptionsTable()
    {
        $sql_file = self::getPath('install').'/uninstall_carrier_options.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    /**
     * uninstall current SQL databse
     *
     * @return boolean
     */
    public static function uninstallDb()
    {
        if (!self::dropOptionsTable()) {
            return false;
        }
        return true;
    }

    /**
     * Put data in Database about carriers
     *
     * @param string | int $carrier_id carrier id who using this data
     * @param string | int $reference_id carrier id who is parent
     * @param string $type types: carrier | pickup
     */
    public static function setCarrierOptions($carrier_id, $reference_id, $type)
    {
        $carrierOptions = array(
                'carrier_id' => $carrier_id,
                'reference_id' => $reference_id,
                'type' => $type,
            );

        $table_name = self::getOptionsTableName('');

        if (count(self::getCarierOptions($carrier_id))) {
            $response = Db::getInstance()->update($table_name, $carrierOptions, "carrier_id=$carrier_id", 1);
        } else {
            $response = Db::getInstance()->insert($table_name, $carrierOptions);
        }
        return $response;
    }

    /**
     * Return data from data base about carrier options
     *
     * @param  string | int $carrier_id carrier id who seek
     * @return array data about this carrier id
     */
    public static function getCarierOptions($carrier_id)
    {
        $table_name = self::getOptionsTableName();
        $conditions = '';

        if ($carrier_id) {
            $conditions = " AND `carrier_id` = '".$carrier_id."'";
        }

        $sql = 'SELECT * FROM '.$table_name.' WHERE `id_dpd_carrier_options` > 0 '.$conditions.' LIMIT 1';

        $carrierOprions = Db::getInstance()->ExecuteS($sql);
        return $carrierOprions;
    }

    /**
     * Return type of carrier id
     *
     * @param  string | int $carrier_id
     * @return string type name of carrier or pickup
     */
    public static function getCarrierType($carrier_id)
    {
        $carrierOptions = array_values(self::getCarierOptions($carrier_id));
        if (empty($carrierOptions)) {//if nothing found
            return false;
        }
        if (count($carrierOptions)) {//if found one so grab type
            return $carrierOptions[0]['type'];
        } else {//its array? strange... return array
            return $carrierOptions;
        }
    }

    /**
    *public static function isDpdShippingMethod($id_carrier)
    *{
    *    if ($carrierExternalName !== DynamicParcelDistribution::$module_name.'_COURIERSERVICE'
    *        && $carrierExternalName !== DynamicParcelDistribution::$module_name.'_DELIVERYPOINTS'
    *        && $carrierExternalName !== 'balticode_dpd_parcelstore'
    *        && $carrierExternalName !== 'balticode_dpd_courier')
    *        return true;
    *    else
    *        return false;
    *}
    **/
}
