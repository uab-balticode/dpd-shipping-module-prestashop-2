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

class BalticodeDpdCarrierPickUp extends ObjectModel
{
    /**
     * Maximum weight shipping of this carrier
     *
     * @var integer
     */
    public static $maxWeight = 20;

    /**
     * Maximum Length of any side box to shipping of this carrier
     *
     * @var integer
     */
    public static $maxLength = 100; //cm

    /**
     * Maximum scope of box to shipping of this carrier
     *
     * @var integer
     */
    public static $maxScope = 200; //cm

    /**
     * array of Payment methods
     * Disable send data to DPD API if order payment method written in this array
     * For Carrier method
     *
     * @var array
     */
    public static $disable_dpd_delivery_method_on_payments = array('Cash on delivery (COD)' => 'cashondelivery', 'Balticode COD' => 'cod');

    public static function getOrderShippingCost($cartObject)
    {
        $cartData = BalticodeDpdData::getCartProductsWeight($cartObject);

        $totalCartWeight = array_sum($cartData['weight']);
        //Order is to heavy
        if ($totalCartWeight > self::$maxWeight) {
            return false;
        }

        $cartProductDimensions = BalticodeDpdData::getCartProductsDimensions($cartObject);
        $cartHeight = max($cartProductDimensions['height']);
        $cartWidth = max($cartProductDimensions['width']);
        $cartDepth = max($cartProductDimensions['depth']);
        $cartScope = ($cartHeight + $cartWidth) * 2 + $cartDepth;

        //Order is to Big
        if ((max($cartHeight, $cartWidth, $cartDepth) > self::$maxLength)
            || $cartScope > self::$maxScope) {
            return false;
        }

        if (Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_FREE_SHIPPING')) {//Enabled free shipping?
            if (BalticodeDpdData::getOrderPriceToral($cartObject)
                >= (float)Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_FREE_FROM')) {
                return (float)0.0;
            }
        }

        return Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_PRICE');
    }

    /**
     * Return Available Payment Method for this shipping method
     * @param  string - Payment method name
     * @return boolean
     */
    public static function availablePaymentMethod($paymentMethod)
    {
        $return = !(in_array($paymentMethod, self::$disable_dpd_delivery_method_on_payments));
        return $return;
    }
}
