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

class BalticodeDpdCarrierCourier extends ObjectModel
{
    /**
     * Maximum weight shipping of this carrier
     *
     * @var integer
     */
    public static $maxWeight = 100;

    /**
     * Maximum Length of any side box to shipping of this carrier
     *
     * @var integer
     */
    public static $maxLength = 175; //cm

    /**
     * Maximum scope of box to shipping of this carrier
     *
     * @var integer
     */
    public static $maxScope = 300; //cm

    /**
     * array of Payment methods
     * Disable send data to DPD API if order payment method written in this array
     * For Carrier method
     *
     * @var array
     */
    public static $disable_dpd_delivery_method_on_payments = array();

    /**
     * Get shipping price of current cart and selected carrier of this shop
     *
     * @param  object           $cartObject Cart quote object
     * @param  int | string     $id_carrier Carrier id
     * @param  int | string     $id_shop    Current shop id
     * @return mix                          Boolean false if carrier has been not allowed
     */
    public static function getOrderShippingCost($cartObject, $id_carrier, $id_shop)
    {
        $cartData = BalticodeDpdData::getCartProductsWeight($cartObject);
        $address = new Address($cartObject->id_address_delivery);

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

        //Do we have some restriction by post code
        $post_code = BalticodeDpdData::getPostCode($cartObject);

        //Calculate by postcode restriction
        if (DpdDeliveryPrice::isExistPostCode($post_code, $id_carrier, $id_shop)
            && DpdDeliveryPrice::isEnabled($id_shop)) { //Yes we have restriction by postcode
            return self::getCsvShippingCost($cartObject, $id_carrier, $post_code, $id_shop);
        }

        //Calculate by country restriction
        if (Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'CARRIER_SHOW_SIZE_RESTR')) { //Enabled restrictions
            if (self::isExistRestriction($address->id_country)) {
                return self::getRestrictionCost($cartObject);
            }
        }

        //Calculate for basic settings
        if (Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'CARRIER_FREE_SHIPPING')) { //Enabled free shipping?
            if (BalticodeDpdData::getOrderPriceToral($cartObject)
                >= (float)Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'CARRIER_FREE_FROM')) {
                return (float)0.0;
            }
        }

        return Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'CARRIER_PRICE');
    }

    /**
     * Grab Shipping CSV price
     *
     * @param  cart (object)
     * @param  string - Id carrier
     * @return mix                 boolean false if price shipping not allowed
     *                             float price of shipping
     */
    public static function getCsvShippingCost($cartObject, $id_carrier, $post_code, $id_shop)
    {
        $currentPrice = false;
        $weight = array();
        //Do we have settings for this postcode?
        if (!DpdDeliveryPrice::isExistPostCode($post_code, $id_carrier, $id_shop)) {
            return false;
        }

        //Grab restrictions by this post code carrier and shop
        $carrier_package_size = DpdDeliveryPrice::getDeliveryPrice($post_code, $id_carrier, $id_shop);

        foreach ($carrier_package_size as $key => $row) {
            $weight[$key] = $row['weight'];
        }
        array_multisort($weight, SORT_DESC, $carrier_package_size);
        //SORTING FROM BIGER TO SMALLER

        $cartData = BalticodeDpdData::getCartProductsWeight($cartObject);
        $totalCartWeight = array_sum($cartData['weight']);
        $cartProductsDimensions = BalticodeDpdData::getCartProductsDimensions($cartObject);

        $correctRestriction = array();
        foreach ($carrier_package_size as $currentRestriction) {
            if ((float)$currentRestriction['weight'] < (float)$totalCartWeight) {
                //This currentRestriction is smaller that we have, so we test it allowed to overweight.
                if ($currentRestriction['overweight_price'] > 0) { //Overweight is allowed?
                    if ((float)$currentRestriction['height'] < (float)max($cartProductsDimensions['height']) //if this restriction is to smaller that we want
                        || (float)$currentRestriction['width'] < (float)max($cartProductsDimensions['width']) //if this restriction is to smaller that we want
                        || (float)$currentRestriction['depth'] < (float)max($cartProductsDimensions['depth'])) { //if this restriction is to smaller that we want)
                        if ((float)$currentRestriction['oversized_price'] > 0) {//Oversize is allowed
                            $correctRestriction = $currentRestriction;
                        } else {
                            return false; //oversize is not available
                        }
                    } else {
                        $correctRestriction = $currentRestriction;
                    }
                }
                continue; //Stop foreach we do not need smaller restrictions
            } else {
                if ((float)$currentRestriction['height'] < (float)max($cartProductsDimensions['height']) //if this restriction is to big that we want
                   || (float)$currentRestriction['width'] < (float)max($cartProductsDimensions['width']) //if this restriction is to big that we want
                   || (float)$currentRestriction['depth'] < (float)max($cartProductsDimensions['depth'])) {//if this restriction is to big that we want
                    if ((float)$currentRestriction['oversized_price'] > 0) { //Oversize is allowed?
                        $correctRestriction = $currentRestriction;
                    } else {
                        return false; //oversize is not available
                    }
                } else {
                    $correctRestriction = $currentRestriction;
                }
            }
        }

        //We no not found any restriction by this cart so this method is not available... return false
        if (!count($correctRestriction)) {
            return false;
        }

        //Now we have one restriction, from where need get price
        if ($correctRestriction['free_from_price'] >= 0) {//If free shipping is allowed
            if (BalticodeDpdData::getOrderPriceToral($cartObject) >= $correctRestriction['free_from_price']) {
                return (float)0.0; //Return free shipping
            }
        }

        $currentPrice = (float)$correctRestriction['price']; //Apply base price

        //Do we have overweight?
        if ((float)$currentRestriction['weight'] < (float)$totalCartWeight) {
            if ($currentRestriction['overweight_price'] > 0) {//Test again we accept overweight?
                $currentPrice += (float)$currentRestriction['overweight_price']; //Plus overweight price
            }
        }

        //Do we have oversize?
        if ((float)$currentRestriction['height'] < (float)max($cartProductsDimensions['height']) //if this restriction is to smaller that we want
               || (float)$currentRestriction['width'] < (float)max($cartProductsDimensions['width']) //if this restriction is to smaller that we want
               || (float)$currentRestriction['depth'] < (float)max($cartProductsDimensions['depth'])) { //if this restriction is to smaller that we want
            if ($currentRestriction['oversized_price'] > 0) { //Test again we accept oversize?
                $currentPrice += (float)$currentRestriction['oversized_price']; //Plus oversize price
            }
        }

        return $currentPrice; //Return final price
    }

    /**
     * Calculate carrier price by restrictions
     *
     * @param  array  $product     Product dimensions array
     * @param  array  $rescriction rescriction who need to be apply
     * @param  float $cartPrice    Total cart price is for free shipping
     * @return mix                 boolean false if price shipping not allowed
     *                             float price of shipping
     */
    public static function getRescrictionPrice($product, $rescriction, $cartPrice = 0)
    {
        $price = array(
                'regular' => (float)0,
                'additional' => (float)0,
            );
        if ($rescriction['free_from_price'] >= 0) { //free shipping is enabled
            if ($cartPrice >= $rescriction['free_from_price']) {
                $price['regular'] = (float)0.0; //Return free shipping
                return $price;
            }
        }

        if ($product['height'] <= $rescriction['dimensions']['height']
            && $product['width'] <= $rescriction['dimensions']['width']
            && $product['depth'] <= $rescriction['dimensions']['depth']) {//Package size is small
                $price['regular'] = (float)$rescriction['base_price']; //Return base shipping price
                return $price;
        } else {//Package size is to high
            if ($rescriction['oversized_price'] == '-1') { //Oversize is not disallowed?
                return false; //Cannot ship this product
            } else {//Oversize is allowed
                if ($rescriction['oversized_price'] >= 0) {//Oversize price is correct?
                    $price = array('regular' => (float)$rescriction['base_price'],
                                'additional' => (float)($rescriction['oversized_price'] * $product['quantity']));
                    return $price;
                } else {
                    DynamicParcelDistribution::log('getRescrictionPrice -> Oversize price is not correct given: '.$rescriction['oversized_price']);
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Has restriction this carrier
     *
     * @param  int | string  $id_country   country id
     * @param  string  $carrier_type carrier type
     * @return boolean
     */
    public static function isExistRestriction($id_country = null, $carrier_type = 'CARRIER')
    {
        if (count(self::getRestrictions($id_country, $carrier_type))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get carrier restrictions of database
     *
     * @param  int | string $id_country   country id
     * @param  string $carrier_type       carrier type
     * @return array                      array of restrictions of set parameters
     */
    public static function getRestrictions($id_country = null, $carrier_type = 'CARRIER')
    {
        $carrier_package_size = array_values(unserialize(Configuration::get(DynamicParcelDistribution::CONST_PREFIX.$carrier_type.'_PACKAGE_SIZE')));
        if ($id_country !== null) {
            foreach ($carrier_package_size as $key => $carrier_package_size_option) {
                if (BalticodeDpdData::recursiveArraySearch($id_country, $carrier_package_size_option) === false) {
                    unset($carrier_package_size[$key]);
                }
            }
        }
        return $carrier_package_size;
    }

    public static function getRestrictionCost(cart $cartObject)
    {
        $address = new Address($cartObject->id_address_delivery);
        $restrictions = self::getRestrictions($address->id_country);
        $cartData = BalticodeDpdData::getCartProductsWeight($cartObject);
        $totalCartWeight = array_sum($cartData['weight']);
        $cartProductsDimensions = BalticodeDpdData::getCartProductsDimensions($cartObject);

        //leave only for this country
        foreach ($restrictions as $key => $carrier_package_size_option) {
            $restrictions[$key]['base_price'] = (float)$carrier_package_size_option['base_price']; //String to float
            $restrictions[$key]['free_from_price'] = (float)$carrier_package_size_option['free_from_price']; //String to float

            $dimension = explode('X', Tools::strtoupper($carrier_package_size_option['dimensions']));
            array_filter($dimension);
            if (count($dimension) !== 3) { //If wrong parsing skip this line
                continue;
            }

            $restrictions[$key]['height']  = trim($dimension[0]);
            $restrictions[$key]['width'] = trim($dimension[1]);
            $restrictions[$key]['depth'] = trim($dimension[2]);

            //Current restriction is for this country? if not, unset current option
            if (BalticodeDpdData::recursiveArraySearch($address->id_country, $carrier_package_size_option) === false) {
                unset($restrictions[$key]);
            }
        }
        $correctRestriction = array();
        foreach ($restrictions as $currentRestriction) {
            if ((float)$currentRestriction['weight'] < (float)$totalCartWeight) {
                //This currentRestriction is smaller that we have, so we test it allowed to overweight.
                if ($currentRestriction['overweight_price'] > -1) {//Overweight is allowed?
                    if ((float)$currentRestriction['height'] < (float)max($cartProductsDimensions['height']) //if this restriction is to smaller that we want
                        || (float)$currentRestriction['width'] < (float)max($cartProductsDimensions['width']) //if this restriction is to smaller that we want
                        || (float)$currentRestriction['depth'] < (float)max($cartProductsDimensions['depth'])) { //if this restriction is to smaller that we want)
                        if ((float)$currentRestriction['oversized_price'] > -1) {//Oversize is allowed
                            $correctRestriction = $currentRestriction;
                        } else {
                            return false; //oversize is not available
                        }
                    } else {
                        $correctRestriction = $currentRestriction;
                    }
                }
                continue; //Stop foreach we do not need smaller restrictions
            } else {
                if ((float)$currentRestriction['height'] < (float)max($cartProductsDimensions['height']) //if this restriction is to big that we want
                   || (float)$currentRestriction['width'] < (float)max($cartProductsDimensions['width']) //if this restriction is to big that we want
                   || (float)$currentRestriction['depth'] < (float)max($cartProductsDimensions['depth'])) { //if this restriction is to big that we want
                    if ((float)$currentRestriction['oversized_price'] > -1) {//Oversize is allowed?
                        $correctRestriction = $currentRestriction;
                    } else {
                        return false; //oversize is not available
                    }
                } else {
                    if ((float)$currentRestriction['height'] >= (float)max($cartProductsDimensions['height']) //if this restriction is to big that we want
                       || (float)$currentRestriction['width'] >= (float)max($cartProductsDimensions['width']) //if this restriction is to big that we want
                       || (float)$currentRestriction['depth'] >= (float)max($cartProductsDimensions['depth'])) { //if this restriction is to big that we want
                        $correctRestriction = $currentRestriction;
                    }
                }
            }
        }
        //We no not found any restriction by this cart so this method is not available... return false
        if (!count($correctRestriction)) {
            return false;
        }

        //Now we have one restriction, from where need get price
        if ($correctRestriction['free_from_price'] >= 0) { //If free shipping is allowed
            if (BalticodeDpdData::getOrderPriceToral($cartObject) >= $correctRestriction['free_from_price']) {
                return (float)0.0; //Return free shipping
            }
        }

        $currentPrice = (float)$correctRestriction['base_price']; //Apply base price

        //Do we have overweight?
        if ((float)$currentRestriction['weight'] < (float)$totalCartWeight) {
            if ($currentRestriction['overweight_price'] > 0) { //Test again we accept overweight?
                $currentPrice += (float)$currentRestriction['overweight_price']; //Plus overweight price
            }
        }

        //Do we have oversize?
        if ((float)$currentRestriction['height'] < (float)max($cartProductsDimensions['height']) //if this restriction is to smaller that we want
               || (float)$currentRestriction['width'] < (float)max($cartProductsDimensions['width']) //if this restriction is to smaller that we want
               || (float)$currentRestriction['depth'] < (float)max($cartProductsDimensions['depth'])) { //if this restriction is to smaller that we want
            if ($currentRestriction['oversized_price'] > 0) {//Test again we accept oversize?
                $currentPrice += (float)$currentRestriction['oversized_price']; //Plus oversize price
            }
        }

        return $currentPrice; //Return final price

    }

    /**
     * Return Available Payment Method for this shipping method
     *
     * @param  string - Payment method name
     * @return boolean
     */
    public static function availablePaymentMethod($paymentMethod)
    {
        $return = !(in_array($paymentMethod, self::$disable_dpd_delivery_method_on_payments));
        return $return;
    }
}
