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

class DpdDeliveryPrice extends ObjectModel
{
    private static $csvHeader = array('postcode',
        'price',
        'free_from_price',
        'weight',
        'height',
        'width',
        'depth',
        'oversized_price',
        'overweight_price');

    public function __construct()
    {
    }

    private static $price_table_name = 'delivery_price';

    public static function getPriceTableName($db_prefix = _DB_PREFIX_)
    {
        return Tools::strtolower($db_prefix.DynamicParcelDistribution::CONST_PREFIX.self::$price_table_name);
    }

    private static function getPath($dirname)
    {
        return _PS_MODULE_DIR_.DynamicParcelDistribution::$module_name.'/'.$dirname;
    }

    public static function installDb()
    {
        $sql_file = self::getPath('install').'/install_delivery_price.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    public static function isEnabled($id_shop = null)
    {
        $id_shop;
        return Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'CARRIER_PRICE_PCODE');
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

    private static function dropPriceTable()
    {
        $sql_file = self::getPath('install').'/uninstall_delivery_price.sql';
        self::loadSQLFile($sql_file);
        return true;
    }

    public static function uninstallDb()
    {
        if (!self::dropPriceTable()) {
            return false;
        }
        return true;
    }

    public static function setDeliveryPrice(
        $postcode,
        $price,
        $free_from_price,
        $carrier_id,
        $id_shop,
        $weight = '*',
        $height = '*',
        $width = '*',
        $depth = '*',
        $oversized_price = '-1',
        $overweight_price = '-1'
    ) {
        $deliveryPrice = array(
                'postcode' => $postcode,
                'price' => $price,
                'free_from_price' => $free_from_price,
                'carrier_id' => $carrier_id,
                'weight' => $weight,
                'height' => $height,
                'width' => $width,
                'depth' => $depth,
                'id_shop' => $id_shop,
                'oversized_price' => $oversized_price,
                'overweight_price' => $overweight_price
            );

        $table_name = self::getPriceTableName('');
        // if (count(self::getDeliveryPrice($postcode, $carrier_id, $id_shop)))
        //     $response = Db::getInstance()->update($table_name,
        //     $deliveryPrice,
        //     $where = '`postcode` = '.$postcode.' AND `carrier_id` = '.$carrier_id.' AND `id_shop` = '.$id_shop, $limit = 1);
        // else
        $response = Db::getInstance()->insert($table_name, $deliveryPrice);
        return $response;
    }

    public static function getDeliveryPrice($postcode = null, $carrier_id = null, $id_shop = null, $other = null)
    {
        $table_name = self::getPriceTableName();
        $conditions = '';

        if ($postcode) {
            $conditions .= " AND `postcode` = '".preg_replace('/\D/', '', $postcode)."'";
        }

        if ($carrier_id) {
            $conditions .= " AND `carrier_id` = '".$carrier_id."'";
        }

        if (!empty($other)) {
            $conditions .= $other;
        }
        $sql = 'SELECT * FROM '.$table_name.' WHERE `id_dpd_delivery_price` > 0 AND `id_shop` = "'.$id_shop.'" '.$conditions;
        $deliveryPrice = Db::getInstance()->ExecuteS($sql);
        return $deliveryPrice;
    }

    public static function isExistPostCode($post_code, $id_carrier, $id_shop = null)
    {
        if (count(self::getDeliveryPrice($post_code, $id_carrier, $id_shop))) {
            return true;
        } else {
            return false;
        }
    }

    private static function clearDeliveryPrice($carrier_id, $id_shop)
    {
        if (empty($carrier_id)) {
            return false;
        }
        if (empty($id_shop)) {
            return false;
        }
        $table_name = self::getPriceTableName();
        $result = Db::getInstance()->delete($table_name, '`carrier_id` = '.$carrier_id.' AND `id_shop` = '.$id_shop);
        return $result;
    }

    public static function importFromCsv($file, $carrier_id, $id_shop)
    {
        $fileContent = self::csvStringToarray($file['content']);
        $header = $fileContent[0];
        unset($fileContent[0]);
        $content = array();
        foreach ($fileContent as $value) {
            $content[] = array_combine($header, array_pad($value, count($header), null));
        }
        if (count($content)) {
            self::clearDeliveryPrice($carrier_id, $id_shop);
        }

        foreach ($content as $line) {
            if (!empty($line['postcode'])) {
                self::setDeliveryPrice(
                    $line['postcode'],
                    $line['price'],
                    $line['free_from_price'],
                    $carrier_id,
                    $id_shop,
                    $line['weight'],
                    $line['height'],
                    $line['width'],
                    $line['depth'],
                    $line['oversized_price'],
                    $line['overweight_price']
                );
            }
        }
    }

    public static function exportToCsv($carrier_id = '*', $id_shop = null, $file = 'delivery_prices.csv')
    {
        $content = array();
        $header = self::$csvHeader;
        $content[] = $header;
        foreach (self::getDeliveryPrice(null, $carrier_id, $id_shop) as $line) {
            $content[] = array_intersect_key($line, array_flip($header));
        }

        $pathToFile = _PS_MODULE_DIR_.DynamicParcelDistribution::$module_name.'/'.$file;
        $fp = fopen($pathToFile, 'w');
        foreach ($content as $lines) {
            fputcsv($fp, $lines);
        }

        fclose($fp);
        return $pathToFile;
    }

    public static function csvStringToarray($string, $separatorChar = ',', $enclosureChar = '"')
    {
        $newlineChar = array("\n", chr(13));
        // @author: Klemen Nagode

        $array = array();
        $size = Tools::strlen($string);
        $columnIndex = 0;
        $rowIndex = 0;
        $fieldValue = '';
        $isEnclosured = false;
        for ($i = 0; $i < $size; $i++) {
            $char = $string{$i};
            $addChar = '';
            if ($isEnclosured) {
                if ($char == $enclosureChar) {
                    if ($i + 1 < $size && $string{$i + 1} == $enclosureChar) {
                        // escaped char
                        $addChar = $char;
                        $i++; // dont check next char
                    } else {
                        $isEnclosured = false;
                    }
                } else {
                    $addChar = $char;
                }
            } else {
                if ($char == $enclosureChar) {
                    $isEnclosured = true;
                } else {
                    if ($char == $separatorChar) {
                        $array[$rowIndex][$columnIndex] = $fieldValue;
                        $fieldValue = '';
                        $columnIndex++;
                    } elseif (in_array($char, $newlineChar)) {
                        $array[$rowIndex][$columnIndex] = $fieldValue;
                        $fieldValue = '';
                        $columnIndex = 0;
                        $rowIndex++;
                    } else {
                        $addChar = $char;
                    }
                }
            }
            if ($addChar != '') {
                $fieldValue .= $addChar;
            }
        }
        if ($fieldValue) {
            $array[$rowIndex][$columnIndex] = $fieldValue;
        }
        return $array;
    }
}
