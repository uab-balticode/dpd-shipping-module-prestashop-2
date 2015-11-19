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

/**
 * Class for calculations of common carrier methods
 * for eg. 'Total Cart Product Weight'
 */

class BalticodeDpdData extends ObjectModel
{
    /**
     * Return cart products with weights
     *
     * @param  cart (object)
     * @return array
     */
    public static function getCartProductsWeight(cart $cartObject)
    {
        $weight = array(
                'id_product' => array(),
                'quantity' => array(),
                'weight' => array(),
            );
        $cartProducts = self::getCartProducts($cartObject);
        foreach ($cartProducts as $value) {
            $weight['id_product'][] = $value['id_product'];
            $weight['quantity'][] = $value['quantity'];
            $weight['weight'][] = $value['weight'];
        }
        return $weight;
    }

    /**
     * Return cart products with dimensions
     *
     * @param  cart (object)
     * @return array
     */
    public static function getCartProductsDimensions(cart $cartObject)
    {
        $dimensions = array(
                'id_product' => array(),
                'quantity' => array(),
                'height' => array(),
                'width' => array(),
                'depth' => array(),
                'diagonal' => array(),
            );
        $cartProducts = self::getCartProducts($cartObject);
        foreach ($cartProducts as $value) {
            $dimensions['id_product'][] = $value['id_product'];
            $dimensions['quantity'][] = $value['quantity'];
            $dimensions['height'][] = (float)$value['height'];
            $dimensions['width'][] = (float)$value['width'];
            $dimensions['depth'][] = (float)$value['depth'];
            $dimensions['diagonal'][] = (float)$value['quantity'] * self::getDiagonal(
                $value['height'],
                $value['width'],
                $value['depth']
            );
        }
        return $dimensions;
    }

    /**
     * Function calculate of box diagonal to get with box is bigger
     *
     *                    Diagonal
     *                       |
     *                  *****|**********
     *                *.\    |       * *
     *              *  . \ <-|     *   *
     *            *    .  \      *     *
     *           ****************      *    <- Height
     *           *     .    \   *      *
     *           *     ......\..*......*
     *           *   .        \ *    *
     *           * .           \*  *    <- Depth
     *           ****************
     *                 Width
     *
     * @param  string $height box height
     * @param  string $width  box width
     * @param  string $depth  box depth
     * @return float          box Diagonal
     */
    public static function getDiagonal($height, $width, $depth)
    {
        $height = trim($height);
        $width = trim($width);
        $depth = trim($depth);
        $dimensions = sqrt(($height * $height) + ($width * $width) + ($depth * $depth));
        return $dimensions;
    }

    /**
     * Return array of cart products
     *
     * @param  cart (object)
     * @return  array of products | or empty
     */
    public static function getCartProducts(cart $cartObject)
    {
        $cart_products = array();
        foreach ($cartObject->getProducts() as $product) {
            $cart_products[] = $product;
        }
        return $cart_products;
    }

    /**
     * Flip DualDimensional array
     *
     * @param array
     * @return  array
     */
    public static function fliparrayList($list)
    {
        $new_array = array();
        if ($list && count($list)) {
            $options = array_keys($list); // get all keys c_name, name, c_post and etc
            $values_count = array_keys($list[$options[0]]); //get how much records availible
            foreach ($values_count as $row) {//get rows
                foreach ($options as $option) {
                    $new_array[$row][$option] = $list[$option][$row];
                }
            }
        }
        return $new_array;
    }

    /**
     * Return cart price
     * If use default function got infinitive loop, so calculate it manual
     *
     * @param  cart (object)
     * @return float - total cart price without shipping
     */
    public static function getOrderPriceToral(cart $cartObject)
    {
        $price = (float)0.0;
        foreach (self::getCartProducts($cartObject) as $product) {
            $price += $product['total_wt'];
        }
        return $price;
    }

    /**
     * Return Post code of cart
     *
     * @param  cart   $cartObject Object of cart
     * @return string             postcode
     */
    public static function getPostCode(cart $cartObject)
    {
        $address = new Address($cartObject->id_address_delivery);
        return $address->postcode;
    }

    /**
    * Search value in array, if found return key
    *
    * @param string $needle - value of searching string
    * @param array $haystack - full array where searching
    * @return string || boolean (string - key name || boolean - false - if not found)
    */
    public static function recursiveArraySearch($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value ||
                (is_array($value) &&
                    self::recursiveArraySearch($needle, $value) !== false)) {
                return $current_key;
            }

        }
        return false;
    }
}
