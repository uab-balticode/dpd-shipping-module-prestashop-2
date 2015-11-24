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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/Createdownload.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/Data.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdCarrierOptions.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdCarrierPickUp.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdCarrierCourier.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdDeliveryPoints.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdDeliveryPrice.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdOrderOptions.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdManifestRender.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/models/DpdLabelRender.php');

require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/classes/API.php');
require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/classes/PDFManifestGenerator.php');

if (!class_exists('AdminOrderBulkAction', false)) {
    require_once(_PS_MODULE_DIR_.'dynamicparceldistribution/controllers/admin/AdminOrdersController.php');
}

class DynamicParcelDistribution extends CarrierModule
{
    const CONST_PREFIX = 'DPD_';
    public $id_carrier;
    private $post_errors = array();
    private $html = '';
    public static $module_name = 'dynamicparceldistribution';
    public static $delivery_time = array(    '1' => '8:00 - 14:00',
                                            '2' => '9:00 - 13:00',
                                            '3' => '14:00 - 17:00',
                                            '4' => '14:00 - 18:00',
                                            '5' => '16:00 - 18:00',
                                            '6' => '18:00 - 22:00');

    public static $cod_payment_methods = array(
        'Cash on delivery (COD)' => 'cashondelivery',
        'Balticode COD' => 'cod'
        );
    protected $local_path = __FILE__;
    public $DpdOrderOptions = null;

    private static $defaultValues = array('DELIVERY_TIME' =>
        'a:2:{s:4:"city";a:23:{i:1;s:7:"Vilnius";i:2;s:6:"Kaunas";i:3;s:9:"Klaipėda";i:4;s:9:"Šiauliai";i:5;s:11:"Panevėžys";i:6;s:5:"Utena";i:7;s:8:"Telšiai";i:8;s:8:"Tauragė";i:9;s:6:"Alytus";i:10;s:12:"Marijampolė";i:11;s:5:"Rīga";i:12;s:7:"Jelgava";i:13;s:10:"Jēkabpils";i:14;s:10:"Daugavpils";i:15;s:8:"Rēzekne";i:16;s:6:"Saldus";i:17;s:8:"Liepāja";i:18;s:5:"Talsi";i:19;s:9:"Ventspils";i:20;s:8:"Valmiera";i:21;s:6:"Cēsis";i:22;s:7:"Gulbene";i:23;s:7:"Tallinn";}s:4:"time";a:23:{i:1;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:2;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:3;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:4;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:5;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:6;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:7;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:8;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:9;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:10;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:11;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:12;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:13;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:14;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:15;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:16;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:17;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:18;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:19;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:20;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:21;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:22;a:3:{i:0;s:1:"1";i:1;s:1:"4";i:2;s:1:"6";}i:23;a:3:{i:0;s:1:"2";i:1;s:1:"3";i:2;s:1:"5";}}}'
        );


    /* Constructor */
    public function __construct()
    {
        $this->name = 'dynamicparceldistribution';
        $this->version = '2.0.1';
        $this->tab = 'shipping_logistics';
        $this->author = 'Balticode.com';
        $this->limited_countries = array('lv', 'lt');
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6.9');
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('DPD');
        $this->description = $this->l('Couriers and Parcel delivery in Europe.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module, with module data?');
        $this->_errors = array();
    }

    /* Installer */
    public function install()
    {
        $carrier_config = array(
            0 => array(
                'name' => 'Courier service',
                'type' => 'carrier',
                'id_tax_rules_group' => 0,
                'active' => true,
                'deleted' => false,
                'shipping_handling' => false,
                'range_behavior' => false,
                'shipping_external' => true,
                'need_range' => true,
                'delay' => array(
                    'lv' => 'Saņemiet sūtījumu tieši rokās.',
                    'lt' => 'Gaukite savo siuntą tiesiai į rankas.',
                    Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) => 'Receive your shipment right in your hands.'
                    ),
                'id_zone' => 1,
                'is_module' => true,

                // 'shipping_method'      => Carrier::SHIPPING_METHOD_WEIGHT,
                'external_module_name' => self::$module_name,
                'is_free'              => false,
                'grade'                => false,
                'ranges' => array(
                    array(
                        'delimiter1' => 0.00,
                        'delimiter2' => BalticodeDpdCarrierCourier::$maxWeight,
                        'price'      => 7.99,
                    ),
                ),
                'max_weight' => BalticodeDpdCarrierCourier::$maxWeight,
                'max_width' => BalticodeDpdCarrierCourier::$maxLength,
                'max_height' => BalticodeDpdCarrierCourier::$maxLength,
                'max_depth' => BalticodeDpdCarrierCourier::$maxLength,
            ),
            1 => array(
                'name' => 'Pickup and delivery points',
                'type' => 'pickup',
                'id_tax_rules_group' => 0,
                'active' => true,
                'deleted' => false,
                'shipping_handling' => false,
                'range_behavior' => false,
                'shipping_external' => true,
                'need_range' => true,
                'delay' => array(
                    'lv' => 'Jūs varat izbaudīt sūtījuma saņemšanas tepat aiz stūra ērtību.',
                    'lt' => 'Jūs galite naudotis patogiu pirkinių atsiėmimu Jums patogioje vietoje.',
                    Language::getIsoById(Configuration::get('PS_LANG_DEFAULT')) =>
                        'You can enjoy the convenience of picking up a parcel just around the corner.'
                    ),
                'id_zone' => 1,
                'is_module' => true,

                // 'shipping_method'      => Carrier::SHIPPING_METHOD_WEIGHT,
                'external_module_name' => self::$module_name,
                'is_free'              => false,
                'grade'                => false,
                'ranges' => array(
                    array(
                        'delimiter1' => 0.00,
                        'delimiter2' => BalticodeDpdCarrierPickUp::$maxWeight,
                        'price'      => 2.8,
                    ),
                ),
                'max_weight' => BalticodeDpdCarrierPickUp::$maxWeight,
                'max_width' => BalticodeDpdCarrierPickUp::$maxLength,
                'max_height' => BalticodeDpdCarrierPickUp::$maxLength,
                'max_depth' => BalticodeDpdCarrierPickUp::$maxLength,
            ),
        );

        if (!DpdCarrierOptions::installDb()) {
            return false;
        }

        $id_carrier1 = $this->installExternalCarrier($carrier_config[0]);
        $id_carrier2 = $this->installExternalCarrier($carrier_config[1]);
        Configuration::updateValue('COURIERSERVICE_CARRIER_ID', (int)$id_carrier1);
        Configuration::updateValue('DELIVERYPOINTS_CARRIER_ID', (int)$id_carrier2);

        if (!parent::install() ||
            !Configuration::updateValue('COURIERSERVICE_OVERCOST', '') ||
            !Configuration::updateValue('DELIVERYPOINTS_OVERCOST', '') ||
            !$this->registerHook('updateCarrier') ||
            !$this->registerHook('displayCarrierList') || //Hook to show Delivery Points
            !$this->registerHook('displayBackOfficeHeader') ||
            !$this->registerHook('displayOrderConfirmation') ||
            !DpdDeliveryPoints::installDb() ||
            !DpdOrderOptions::installDb() ||
            !DpdDeliveryPrice::installDb()) {
//          || !$this->addBulkActionButtons() //Bulk Action buttons
//            //Using this only if Overwrite using already other model!
            return false;
        }

        $tab = new Tab();
        $tab->class_name = 'AdminCallcarrier';
        $tab->module = self::$module_name;
        $tab->name[(int)(Configuration::get('PS_LANG_DEFAULT'))] = $this->l('AdminCallcarrier');
        $tab->id_parent = -1;
        $tab->position = 1;
        $tab->save();

        return true;
    }

    public static function installExternalCarrier($config)
    {
        $carrier = new Carrier();
        $carrier->hydrate($config);

        $carrier->name = $config['name'];
        $carrier->id_zone = $config['id_zone'];
        $carrier->active = $config['active'];
        $carrier->deleted = $config['deleted'];
        $carrier->delay = $config['delay'];
        $carrier->shipping_handling = $config['shipping_handling'];
        $carrier->range_behavior = $config['range_behavior'];
        $carrier->is_module = $config['is_module'];
        $carrier->shipping_external = $config['shipping_external'];
        $carrier->external_module_name = $config['external_module_name'];
        $carrier->need_range = $config['need_range'];
        $carrier->setTaxRulesGroup($config['id_tax_rules_group'], true);

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'lv') {
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            }
            if ($language['iso_code'] == 'lt') {
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            }
            if ($language['iso_code'] == Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))) {
                $carrier->delay[(int)$language['id_lang']] = $config['delay'][$language['iso_code']];
            }
        }

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array('id_carrier' => (int)$carrier->id,
                                                                                    'id_group' => (int)$group['id_group']
                                                                            ), 'INSERT');
            }

            // $range_price = new RangePrice();
            // $range_price->id_carrier = $carrier->id;
            // $range_price->delimiter1 = '0';
            // $range_price->delimiter2 = '10000';
            // $range_price->add();

            // $range_weight = new RangeWeight();
            // $range_weight->id_carrier = $carrier->id;
            // $range_weight->delimiter1 = '0';
            // $range_weight->delimiter2 = '10000';
            // $range_weight->add();

            // Add weight ranges to carrier
            $rangePrices = array();
            foreach ($config['ranges'] as $range) {
                $rangeWeight = new RangeWeight();
                $rangeWeight->hydrate(array(
                    'id_carrier' => $carrier->id,
                    'delimiter1' => (float)$range['delimiter1'],
                    'delimiter2' => (float)$range['delimiter2'],
                ));
                $rangeWeight->add();

                // Save range ID and price and set it after the Zones have been added
                $rangePrices[] = array(
                    'id_range_weight' => $rangeWeight->id,
                    'price' => $range['price'],
                );
            }

            // Update prices in delivery table for each range (need IDs)
            foreach ($rangePrices as $rangePrice) {
                $data  = array('price' => $rangePrice['price'],);
                $where = 'id_range_weight = '.$rangePrice['id_range_weight'];
                Db::getInstance()->update('delivery', $data, $where);
            }

            // Add Europe for EVERY carrier range
            // Automatically creates rows in delivery table, price is 0
            $id_zone_europe = Zone::getIdByName('Europe');
            $carrier->addZone($id_zone_europe ? $id_zone_europe : 1);

            // Copy Logo
            if (!Tools::copy(dirname(__FILE__).'/logo.png', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.png')) {
                return false;
            }
            DpdCarrierOptions::setCarrierOptions((int)$carrier->id, (int)$carrier->id, $config['type']);
            // Return ID Carrier
            return (int)$carrier->id;
        }

        return false;
    }

    /* Uninstaller */
    public function uninstall()
    {
        // Uninstall
        if (!parent::uninstall() ||
            !Configuration::deleteByName('COURIERSERVICE_OVERCOST') ||
            !Configuration::deleteByName('DELIVERYPOINTS_OVERCOST') ||
            !$this->unregisterHook('updateCarrier') ||
            !$this->unregisterHook('displayBackOfficeHeader') ||
            !DpdCarrierOptions::uninstallDb() ||
            !DpdDeliveryPoints::uninstallDb() ||
            !DpdOrderOptions::uninstallDb() ||
            !DpdDeliveryPrice::uninstallDb()) {
//          || !$this->removeBulkActionButtons() //Using this only if Overwrite using already other model!
            return false;
        }

        // Delete External Carrier
        $carrier1 = new Carrier((int)Configuration::get('COURIERSERVICE_CARRIER_ID'));
        $carrier2 = new Carrier((int)Configuration::get('DELIVERYPOINTS_CARRIER_ID'));

        // If external carrier is default set other one as default
        if (Configuration::get('PS_CARRIER_DEFAULT') == (int)$carrier1->id || Configuration::get('PS_CARRIER_DEFAULT') == (int)$carrier2->id) {
            $carriers_d = Carrier::getCarriers(Context::getContext()->cookie->id_lang, true, false, false, null, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
            foreach ($carriers_d as $carrier_d) {
                if ($carrier_d['active'] && !$carrier_d['deleted'] && ($carrier_d['name'] != $this->_config['name'])) {
                    Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier_d['id_carrier']);
                }
            }
        }

        // Then delete Carrier
        $carrier1->deleted = 1;
        $carrier2->deleted = 1;
        if (!$carrier1->update() || !$carrier2->update()) {
            return false;
        }

        $settings = array(
                    self::CONST_PREFIX.'ENABLED',
                    self::CONST_PREFIX.'SERVICE_USERNAME',
                    self::CONST_PREFIX.'SERVICE_USERPASS',
                    self::CONST_PREFIX.'SERVICE_USERID',
                    self::CONST_PREFIX.'API_URL',
                    self::CONST_PREFIX.'BTNSUBMIT',
                    self::CONST_PREFIX.'TAB',
                    self::CONST_PREFIX.'USERNAME',
                );
        foreach ($settings as $setting) {
            Configuration::deleteByName($setting);
        }

        $tab = new Tab(Tab::getIdFromClassName('AdminCallcarrier'));
        $tab->delete();
        return true;
    }

    /*
    * Return selected parcel store, or delivery time
    */
    /* Using to: </controllers/admin/AdminOrderController.php> */
    public function displayInfoByCart($id_cart)
    {
        $order = new Order(Order::getOrderByCartId($id_cart));
        $deliveryOptionsSerlialized = DpdOrderOptions::getOrderOptions((int)$order->id);

        if (!count($deliveryOptionsSerlialized)) {
            return false;
        }
        $deliveryOptionsarray = unserialize($deliveryOptionsSerlialized[0]['delivery_option']);

        if ($deliveryOptionsSerlialized[0]['carrier_id'] == (int)Configuration::get('COURIERSERVICE_CARRIER_ID')) {//If time is set
            $deliveyTime = '<br>'.
                $this->l('Selected delivery time:').
                '<br><b>'.$deliveryOptionsarray['delivery_label'].'</b>';

            return $deliveyTime;
        }
        if ($deliveryOptionsSerlialized[0]['carrier_id'] == (int)Configuration::get('DELIVERYPOINTS_CARRIER_ID')) {//if parcel store is set
            $parcelStoresAllInOne = DpdDeliveryPoints::collectDeliveryPoints(false, false, $deliveryOptionsarray['delivery_option']);
            if (isset($parcelStoresAllInOne[0])) {
                $parcelStoresAllInOne = $parcelStoresAllInOne[0];
            }

            $parcelStore = '<br>'.
                $this->l('Selected parcel shop:');

            if (empty($parcelStoresAllInOne)) {
                $parcelStore .= '<br><b>'.$deliveryOptionsarray['delivery_label'].'</b>';
            } else {
                $parcelStore .= '<br><b>'.
                    $parcelStoresAllInOne['company'].'<br>'.
                    $parcelStoresAllInOne['city'].' - '.$parcelStoresAllInOne['street'].' '.$parcelStoresAllInOne['country'].'-'.$parcelStoresAllInOne['pcode'].
                    '</b>';
            }
            return $parcelStore;
        }
        return '';
    }

    public function hookdisplayOrderConfirmation($params)
    {
        DpdOrderOptions::setOrderIdByCartId(
            $params['objOrder']->id_cart, //cart_id
            $params['objOrder']->id, //order_id
            $params['objOrder']->id_carrier, //carrier_id
            $params['objOrder']->id_address_delivery //address_option_id
        );
    }

    public function hookUpdateCarrier($params)
    {
        if ((int)$params['id_carrier'] == (int)Configuration::get('COURIERSERVICE_CARRIER_ID')) {
            DpdCarrierOptions::setCarrierOptions((int)$params['carrier']->id, (int)$params['id_carrier'], 'carrier');
            Configuration::updateValue('COURIERSERVICE_CARRIER_ID', (int)$params['carrier']->id);
        }
        if ((int)$params['id_carrier'] == (int)Configuration::get('DELIVERYPOINTS_CARRIER_ID')) {
            DpdCarrierOptions::setCarrierOptions((int)$params['carrier']->id, (int)$params['id_carrier'], 'pickup');
            Configuration::updateValue('DELIVERYPOINTS_CARRIER_ID', (int)$params['carrier']->id);
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (isset($this->context->cookie->redirect_errors)) {
            $this->context->controller->errors[] = $this->context->cookie->redirect_errors;
            unset($this->context->cookie->redirect_errors);
        }
        if (isset($this->context->cookie->redirect_success)) {
            $this->context->controller->confirmations[] = $this->context->cookie->redirect_success;
            unset($this->context->cookie->redirect_success);
        }

        $controllerName = 'AdminCallcarrier';
        $this->context->smarty->assign(
            array(
                'boxToken' => Tools::getAdminToken($controllerName.(int)Tab::getIdFromClassName($controllerName).(int)$this->context->employee->id),
            )
        );

        $this->context->controller->addjquery();
        $this->context->controller->addCSS($this->_path.'/views/css/callcarrier.css');
        $this->context->controller->addJS($this->_path.'views/js/script.js', 'all');
        //return $this->display(__FILE__, 'views/templates/admin/callcarrier.tpl');
        $output = $this->context->smarty->fetch($this->local_path.'/views/templates/admin/callcarrier.tpl');
        return $output;
    }

    /*
    *
    */
    public function getOrderShippingCost($cartObject, $shipping_cost = 0)
    {
        return $this->getOrderShippingCostExternal($cartObject, $shipping_cost);
    }

    public function getOrderShippingCostExternal($cartObject, $shipping_cost = 0)
    {
        if (!self::isEnabled(self::$module_name)) {
            return false;
        }

        $shipping_cost;
        $carrierType = DpdCarrierOptions::getCarrierType($this->id_carrier);
        if ($carrierType === false) {//Not found this carrier id in dpd_carrier_option db table
            self::log('getOrderShippingCostExternal -> Not found this carrier id in dpd_carrier_option db table'.$this->id_carrier);
            return false;
        }

        if ($carrierType == 'pickup') {
            return BalticodeDpdCarrierPickUp::getOrderShippingCost($cartObject);
        }


        if ($carrierType == 'carrier') {
            if ($this->getPriceCalculation($carrierType) === '0') {//Price is calculation prestaShop?
                return $this->systemPriceCalculate($this->id_carrier, $cartObject->id_shop, $this->context->country->id_zone);
            } else {
                return BalticodeDpdCarrierCourier::getOrderShippingCost(
                    $cartObject,
                    $this->id_carrier,
                    $cartObject->id_shop
                );
            }
        }
    }

    /**
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
     */
    public function getDiagonal($height, $width, $depth)
    {
        $height = trim($height);
        $width = trim($width);
        $depth = trim($depth);
        $dimensions = sqrt(($height * $height) + ($width * $width) + ($depth * $depth));
        return $dimensions;
    }

    /**
     * Grab Shipping default price
     *
     * @param  cart (object)
     * @param  string - Id carrier
     * @return float | boolean; float - price, false - cannot ship
     */
    public function getDefaultShippingCost(cart $cartObject, $id_carrier)
    {
        $carrier_type = DpdCarrierOptions::getCarrierType($id_carrier); // carrier | pickup
        if (!is_string($carrier_type)) {
            $this->log(
                'getDefaultShippingCost -> carrier type is not correct, available (string) given: '
                .gettype($carrier_type)
                .'; carrier id: '
                .$id_carrier
            );
            return false;
        }

        $carrier_type = Tools::strtoupper($carrier_type); // CARRIER | PICKUP
        if ((int)Configuration::get(self::CONST_PREFIX.$carrier_type.'_SHOW_SIZE_RESTR')) {//Enabled Delivery restrictions?
            //Grab all restrictions
            $address = new Address($cartObject->id_address_delivery);
            $restrictions = Configuration::get(self::CONST_PREFIX.$carrier_type.'_PACKAGE_SIZE');
            if ($restrictions) {
                $carrier_package_size = array_values(unserialize(Configuration::get(self::CONST_PREFIX.$carrier_type.'_PACKAGE_SIZE')));
            } else {
                $carrier_package_size = array();
            }
            $productsShippingPrices = array();
            if (Configuration::get(self::CONST_PREFIX.$carrier_type.'_PRICE_PRIORITY')) {
                $cartData = BalticodeDpdData::getCartProductsWeight($cartObject);
                //Current restriction is for this country? if not, unset current option
                foreach ($carrier_package_size as $key => $carrier_package_size_option) {
                    if (BalticodeDpdData::recursiveArraySearch($address->id_country, $carrier_package_size_option) === false) {
                        unset($carrier_package_size[$key]);
                    }
                }

                $current_carrier_package_size = $carrier_package_size;
                foreach ($current_carrier_package_size as $line => $carrier_package_size_option) {
                    if ($carrier_package_size_option['weight'] >= array_sum($cartData['weight'])) {
                        unset($current_carrier_package_size[$line]);
                    }
                }

                if (count($current_carrier_package_size)) {//if one or more
                    $carrier_package_size_max = array();
                    foreach ($current_carrier_package_size as $key => $carrier_package_size_option) {
                        $carrier_package_size_max[$key] = $carrier_package_size_option['weight'];
                    }
                    $id = array_keys($carrier_package_size_max, max($carrier_package_size_max));
                    $restriction = $current_carrier_package_size[$id[0]];
                    if ($restriction['free_from_price'] >= 0) {//free shipping is enabled
                        if ($this->getOrderPriceToral($cartObject) >= $restriction['free_from_price']) {
                            return (float)0.0; //Return free shipping
                        }
                    }

                    if ($restriction['overweight_price'] != '-1') {
                        return $restriction['base_price'] + $restriction['overweight_price'];
                    }
                    //Return price of small weight plus overweight price
                }

                //If we not return price yet so we need to search again for biger weight
                $carrier_package_weight = $carrier_package_size;
                foreach ($carrier_package_weight as $key => $restriction) {
                    if ($restriction['weight'] <= array_sum($cartData['weight'])) {
                        unset($carrier_package_weight[$key]);
                    }
                }

                if (count($carrier_package_weight)) {//Do we find some restrict
                    $carrier_package_size_min = array();
                    foreach ($carrier_package_weight as $key => $carrier_package_size_option) {
                        $carrier_package_size_min[$key] = $carrier_package_size_option['weight'];
                    }
                    $id = array_keys($carrier_package_size_min, min($carrier_package_size_min));
                    $restriction = $carrier_package_weight[$id[0]];

                    if ($restriction['free_from_price'] >= 0) {//free shipping is enabled
                        if ($this->getOrderPriceToral($cartObject) >= $restriction['free_from_price']) {
                            return (float)0.0; //Return free shipping
                        }
                    }
                    return $restriction['base_price'];
                }

                //If nothing return before so we need return false and set this method not available
                return false;
            } else {

                //leave only for this country
                foreach ($carrier_package_size as $key => $carrier_package_size_option) {
                    $carrier_package_size[$key]['base_price'] = (float)$carrier_package_size_option['base_price']; //String to float
                    $carrier_package_size[$key]['free_from_price'] = (float)$carrier_package_size_option['free_from_price']; //String to float

                    $dimension = explode('X', Tools::strtoupper($carrier_package_size_option['dimensions']));
                    array_filter($dimension);
                    if (count($dimension) !== 3) {//If wrong parsing skip this line
                        continue;
                    }

                    $carrier_package_size[$key]['dimensions'] = array(
                            'height' => trim($dimension[0]),
                            'width' => trim($dimension[1]),
                            'depth' => trim($dimension[2]),
                            'diagonal' => $this->getDiagonal(
                                $dimension[0],
                                $dimension[1],
                                $dimension[2]
                            ),
                        );
                    //Current restriction is for this country? if not, unset current option
                    if (BalticodeDpdData::recursiveArraySearch($address->id_country, $carrier_package_size_option) === false) {
                        unset($carrier_package_size[$key]);
                    }
                }

                if (!count($carrier_package_size)) {//Do we not have any settings for this country?
                    return $this->getBaseShippingCost($cartObject, $carrier_type); //Use Base settings
                }
                foreach (BalticodeDpdData::fliparrayList($this->getCartProductsDimensions($cartObject)) as $key => $product) {
                    $current_carrier_package_size = $carrier_package_size;
                    foreach ($current_carrier_package_size as $line => $carrier_package_size_option) {
                        if ($product['height'] < $carrier_package_size_option['dimensions']['height']
                            || $product['width'] < $carrier_package_size_option['dimensions']['width']
                            || $product['depth'] < $carrier_package_size_option['dimensions']['depth']) {
                            unset($current_carrier_package_size[$line]);
                        }
                    }

                    if (count($current_carrier_package_size)) {//if one or more
                        $carrier_package_size_max = array();
                        foreach ($current_carrier_package_size as $key => $carrier_package_size_option) {
                            $carrier_package_size_max[$key] = $carrier_package_size_option['dimensions']['diagonal'];
                        }
                        $id = array_keys($carrier_package_size_max, max($carrier_package_size_max));
                        $restriction = $current_carrier_package_size[$id[0]];
                    } else {//if no found so use first (current product can be 0 dimensions or is very small)
                        $carrier_package_size_min = array();
                        foreach ($carrier_package_size as $key => $carrier_package_size_option) {
                            $carrier_package_size_min[$key] = $carrier_package_size_option['dimensions']['diagonal'];
                        }
                        $id = array_keys($carrier_package_size_min, min($carrier_package_size_min));
                        $restriction = $carrier_package_size[$id[0]];
                    }
                    //Now we have ONE restrict for this product so apply this

                    $productShippingPrice = $this->getRescrictionPrice($product, $restriction, $this->getOrderPriceToral($cartObject));

                    if ($productShippingPrice === false) {//If this product cant be ship disable carrier
                        return false;
                    } else {
                        $productsShippingPrices[] = $productShippingPrice;
                    }
                }

                $productsShippingPrices = BalticodeDpdData::fliparrayList($productsShippingPrices);
                $regularPrice = max($productsShippingPrices['regular']);
                $additionalPrice = array_sum($productsShippingPrices['additional']);
                $finalPrice = $regularPrice + $additionalPrice;

                return $finalPrice;
            }
        } else {
            return $this->getBaseShippingCost($cartObject, $carrier_type); //Use Base settings
        }

        return false;
    }

    /**
     * Return carrier Base shipping price without dimension restrictions
     *
     * @param  cart (object)
     * @param  string carrier type CARRIER | PICKUP
     * @return [type]
     */
    public function getBaseShippingCost(cart $cartObject, $carrier_type)
    {
        if (!is_string($carrier_type)) {
            $this->log(
                'getBaseShippingCost -> carrier type is not correct, available (string) given: '.
                gettype($carrier_type)
            );
            return false;
        }

        $carrier_type = Tools::strtoupper($carrier_type);

        if (Configuration::get(self::CONST_PREFIX.$carrier_type.'_FREE_SHIPPING')) {//Enabled free shipping?
            if (Configuration::get(self::CONST_PREFIX.$carrier_type.'_FREE_FROM') <= $this->getOrderPriceToral($cartObject)) {
                return 0;
            }
        }
        return Configuration::get(self::CONST_PREFIX.$carrier_type.'_PRICE');
    }

    /**
     * Return price calculated by PrestaShop system
     *
     * @param  $id_carrier string | int - carrier id
     * @param  $id_cart string | int - cart id
     * @param  $id_zone string | int - Zone Id 1 -> Global?
     * @return string - Price
     */
    public static function systemPriceCalculate($id_carrier, $id_cart, $id_zone)
    {
        $cart = new Cart($id_cart);
        $total_weight = $cart->getTotalWeight();
        $carrier = new Carrier($id_carrier);
        $price = $carrier->getDeliveryPriceByWeight($total_weight, $id_zone);
        return $price;
    }

    /**
     * DPD carrier price calculation by carrier type
     *
     * @param  string | int - carrier type CARRIER | PICKUP
     * @return string | boolean
     */
    private function getPriceCalculation($carrier_type)
    {
        if (!is_string($carrier_type) && !is_int($carrier_type)) {
            self::log('getPriceCalculation -> carrier type is not correct, available (string) or (int) given: '.gettype($carrier_type));
            return false;
        }
        return Configuration::get(self::CONST_PREFIX.Tools::strtoupper($carrier_type).'_PRICE_CALCULATE');
    }

    private function addBulkActionButtons()
    {
        $array_to_replace = array();
        $array_to_replace[] = array(
            'search' => 'parent::__construct();',
            'replace' => '
            if (Module::getInstanceByName(\''.self::$module_name.'\')->isEnabled(\''.self::$module_name.'\'))
            {
                $this->bulk_actions[\'printDpdLabels\'] = array(\'text\' => $this->l(\'Print DPD Labels\'), \'icon\' => \'icon-print\');
                $this->bulk_actions[\'printDpdManifest\'] = array(\'text\' => $this->l(\'Print DPD Manifest\'),
                    \'icon\' => \'icon-print\',
                    \'confirm\' => $this->l(\'Are you sure you want to print manifest, because after doing this you wont be able to print labels? \'));
            }
         ',
            'action' => 'before',
        );
        $array_to_replace[] = array(
            'search' => 'public function initToolbar()',
            'replace' => 'public function processBulkPrintDpdLabels()
    {
        $label = Module::getInstanceByName(\''.self::$module_name.'\');
        $pathToFile = $label->getPdfFile(\'label\',Tools::getValue(\'orderBox\'));
        foreach (DpdLabelRender::getWarningMessage() as $message)
                    $this->displayWarning($this->l($message));

        if ($pathToFile === false)
            foreach (DpdLabelRender::getErrorMessage() as $message)
                $this->errors[] = $this->l($message);
        else
            $label->download($pathToFile,\'label.pdf\',\'application/pdf\');
        return \'\';
    }

    public function processBulkPrintDpdManifest()
    {
        $manifest = Module::getInstanceByName(\''.self::$module_name.'\');
        $pathToFile = $manifest->getPdfFile(\'manifest\',Tools::getValue(\'orderBox\'));
        foreach (DpdManifestRender::getWarningMessage() as $message)
            $this->displayWarning($this->l($message));
        if ($pathToFile === false)
            foreach (DpdManifestRender::getErrorMessage() as $message)
                $this->errors[] = $this->l($message);
        else
            $manifest->download($pathToFile,\'manifest.pdf\',\'application/pdf\');
    }
    ',
            'action' => 'before',
        );


        //Order list ToolButton
        $array_to_replace[] = array(
            'search' => '$res = parent::initToolbar();',
            'replace' => '
        if (class_exists(\''.__CLASS__.'\'))
            if (DynamicParcelDistribution::isEnabled(\''.self::$module_name.'\'))
                $this->toolbar_btn[\'call_carrier\'] = array(
                    \'short\' => \'Create\',
                    \'href\' => \'#\',
                    \'desc\' => $this->l(\'Call DPD Carrier\'),
                    \'class\' => \'process-icon-partialRefund\',
                    \'js\' => \'showCarrierWindow()\',
                );
         ',
            'action' => 'before',
        );

        Configuration::updateValue(
            self::CONST_PREFIX.Tools::strtoupper('OVERWRITE'),
            serialize($array_to_replace)
        ); //save who we change

        if (!$this->makeOverride($array_to_replace)) {
            return false;
        }
        return true;
    }

    /**
     * Grab PDF of Manifest or Label Content put to file and return path to file
     * @param  $type (string) - 'label' | 'manifest' who need to get
     * @param  $parameters (array) - Some parameters id_order is request
     * @return mix
     */
    public function getPdfFile($type, $parameters = array(), $action = 'join')
    {
        if (!is_string($type)) {
            self::log('getPdfFile -> $type variable type is not correct, available (string) given: '.gettype($type));
            return false;
        }

        switch ($type) {
            case 'label':
                return DpdLabelRender::getLabel(array('id_orders' => $parameters), $action);
            case 'manifest':
                return DpdManifestRender::getManifest(array('id_orders' => $parameters));
            default:
                self::log('getPdfFile -> Type not found  I got: '.$type);
                return false;
        }
    }

    private function removeBulkActionButtons()
    {
        $array_to_replace = array();
        $rows = unserialize(Configuration::get(self::CONST_PREFIX.Tools::strtoupper('OVERWRITE'))); //Read data from database
        foreach ($rows as $row) {
            switch ($row['action']) {
                case 'replace':
                    $array_to_replace[] = array(
                        'search' => $row['replace'],
                        'replace' => $row['search'],
                        'action' => 'replace',
                    );
                    break;
                case 'before':
                    $array_to_replace[] = array(
                        'search' => $row['replace'],
                        'replace' => '',
                        'action' => 'replace',
                    );
                    break;
                case 'after':
                    $array_to_replace[] = array(
                        'search' => $row['replace'],
                        'replace' => '',
                        'action' => 'replace',
                    );
                    break;
            }
        }

        if (!$this->makeOverride($array_to_replace)) {
            return false;
        }
        Configuration::deleteByName(self::CONST_PREFIX.'OVERWRITE'); //If not return false delete what we hange
        return true;
    }

    private function makeOverride($rows)
    {
        $test = new AdminOrderBulkAction();
        foreach ($rows as $row) {
            $test->addToReplace($row['search'], $row['replace'], $row['action']);
        }

        return $test->run();
    }

    public function getContent()
    {
        if (Tools::getValue('export')) {
            $carrier = new Carrier();
            if ($carrier->isLangMultishop()) {
                $id_shop = Context::getContext()->shop->id;
            }
            $pathToFile = DpdDeliveryPrice::exportToCsv(Tools::getValue('carrier_id'), $id_shop);
            $this->download(basename($pathToFile), $pathToFile, 'text/csv');
        }

        $this->context->controller->addJS(($this->_path).'/views/js/script.js', 'all');

        if (Tools::isSubmit('btnSubmit')) {
            $this->postProcess();
            $response = DpdDeliveryPoints::generateDeliveryPoints();
            if (!$response) {
                $this->context->controller->errors[] = Tools::displayError($this->l('Can not write data to the database. Please reinstall the module.'));
            }

            if ($response['errlog'] != '') {
                $this->context->controller->errors[] = Tools::displayError($this->l('API report: '.$response['errlog']));
            }
        }
        $this->context->smarty->assign(
            array(
                'module_name' => $this->name,
                'displayName' => $this->displayName,
                //'action' => Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
                'action' => AdminController::$currentIndex.'&configure='.$this->name
                        .'&save'.$this->name
                        .'&token='.Tools::getAdminTokenLite('AdminModules'),
                'method' => 'POST',
                'enabled' => Configuration::get(self::CONST_PREFIX.'ENABLED'),
                'service_username' => Configuration::get(self::CONST_PREFIX.'SERVICE_USERNAME'),
                'service_userpass' => Configuration::get(self::CONST_PREFIX.'SERVICE_USERPASS'),
                'service_userid' => Configuration::get(self::CONST_PREFIX.'SERVICE_USERID'),
                'api_url' => Configuration::get(self::CONST_PREFIX.'API_URL'),
                'pickup_address_name' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_NAME'),
                'pickup_address_company' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_COMPANY'),
                'pickup_address_email' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_EMAIL'),
                'pickup_address_phone' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_PHONE'),
                'pickup_address_street' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_STREET'),
                'pickup_address_city' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_CITY'),
                'pickup_address_zip' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_ZIP'),
                'pickup_address_country' => Configuration::get(self::CONST_PREFIX.'PICKUP_ADDRESS_COUNTRY'),
                'pickup_vat_code' => Configuration::get(self::CONST_PREFIX.'PICKUP_VAT_CODE'),

                'all_countries' => Country::getCountries($this->context->language->id),
                'export_url' => Tools::safeOutput($_SERVER['REQUEST_URI']),

                //Carrier Options start
                'carrier_price_calculate' => Configuration::get(self::CONST_PREFIX.'CARRIER_PRICE_CALCULATE'),
                'carrier_price' => Configuration::get(self::CONST_PREFIX.'CARRIER_PRICE'),
                'carrier_free_from' => Configuration::get(self::CONST_PREFIX.'CARRIER_FREE_FROM'),
                'carrier_free_shipping' => Configuration::get(self::CONST_PREFIX.'CARRIER_FREE_SHIPPING'),
                'carrier_price_pcode' => Configuration::get(self::CONST_PREFIX.'CARRIER_PRICE_PCODE'),

                'availible_delivery_time' => self::$delivery_time,
                'allow_courier_pickup' => Configuration::get(self::CONST_PREFIX.'ALLOW_COURIER_PICKUP'),
                'show_delivery_time' => Configuration::get(self::CONST_PREFIX.'SHOW_DELIVERY_TIME'),
                //'delivery_times' => array(),

                'delivery_times' => ((Configuration::get(self::CONST_PREFIX.'DELIVERY_TIME') === false)
                    ?array_values(BalticodeDpdData::fliparrayList(unserialize(self::$defaultValues['DELIVERY_TIME'])))
                    :array_values(unserialize(Configuration::get(self::CONST_PREFIX.'DELIVERY_TIME')))),

                'carrier_package_size' => ((Configuration::get(self::CONST_PREFIX.'CARRIER_PACKAGE_SIZE') === false)
                    ?array()
                    :array_values(unserialize(Configuration::get(self::CONST_PREFIX.'CARRIER_PACKAGE_SIZE')))),
                'carrier_show_size_restr' => Configuration::get(self::CONST_PREFIX.'CARRIER_SHOW_SIZE_RESTR'),
                'courierservice_carrier_id' => (int)Configuration::get('COURIERSERVICE_CARRIER_ID'),
                'carrier_price_priority' => Configuration::get(self::CONST_PREFIX.'CARRIER_PRICE_PRIORITY'),
                //Carrier Options end
                //Pickup Options start
                'pickup_price_calculate' => ((Configuration::get(self::CONST_PREFIX.'PICKUP_PRICE_CALCULATE') === false)
                    ? '1'
                    :Configuration::get(self::CONST_PREFIX.'PICKUP_PRICE_CALCULATE')),
                'pickup_price' => Configuration::get(self::CONST_PREFIX.'PICKUP_PRICE'),
                'pickup_free_from' => Configuration::get(self::CONST_PREFIX.'PICKUP_FREE_FROM'),
                'pickup_free_shipping' => Configuration::get(self::CONST_PREFIX.'PICKUP_FREE_SHIPPING'),
                'type_parcel_display' => Configuration::get(self::CONST_PREFIX.'TYPE_PARCEL_DISPLAY'),
                'short_office_name' => Configuration::get(self::CONST_PREFIX.'SHORT_OFFICE_NAME'),
                'city_priority' => Configuration::get(self::CONST_PREFIX.'CITY_PRIORITY'),
                'pickup_package_size' => ((Configuration::get(self::CONST_PREFIX.'PICKUP_PACKAGE_SIZE') === false)
                    ?array()
                    :array_values(unserialize(Configuration::get(self::CONST_PREFIX.'PICKUP_PACKAGE_SIZE')))),
                'pickup_show_size_restr' => ((Configuration::get(self::CONST_PREFIX.'PICKUP_SHOW_SIZE_RESTR') === false)
                    ? '1'
                    :Configuration::get(self::CONST_PREFIX.'PICKUP_SHOW_SIZE_RESTR')),
                'deliverypoints_carrier_id' => (int)Configuration::get('DELIVERYPOINTS_CARRIER_ID'),
                'pickup_price_priority' => Configuration::get(self::CONST_PREFIX.'PICKUP_PRICE_PRIORITY'),

                //Pickup Options end
            )
        );
        //return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
        $output = $this->context->smarty->fetch($this->local_path.'/views/templates/admin/configure.tpl');
        return $output;
    }

    public function hookDisplayCarrierList($params)
    {
        $parcelStoresAllInOne = DpdDeliveryPoints::collectDeliveryPoints(
            country::getIsoById(
                country::getIdByName(
                    null,
                    $params['address']->country
                )
            ),
            false,
            false
        );
        $parcelStoresAllInOne = $this->parcelSort($parcelStoresAllInOne, 'company');
        $parcel_stores_city = array();

        foreach ($parcelStoresAllInOne as $company) {
            $parcel_stores_city[trim($company['city'])][] = $company;
        }

        ksort($parcel_stores_city);
        self::setKeyPriority($parcel_stores_city, self::stringToarray(',', ','.Configuration::get(self::CONST_PREFIX.'CITY_PRIORITY')));
        $carrier_id = (int)$this->context->cart->id_carrier;

        if ($carrier_id == Configuration::get('DELIVERYPOINTS_CARRIER_ID')) {
            $this->context->smarty->assign(array(
                'id_carrier' => $params['cart']->id_carrier,
                'id_address' => $params['address']->id,
                'delivery_terminals' => $parcel_stores_city,
                'delivery_option' => $params['address']->id, //eg delivery_option_15_3
                'current_city' => $params['address']->city,
                'carrier_name' => $this->getCarrierName($carrier_id),
                'type_parcel_display' => Configuration::get(self::CONST_PREFIX.'TYPE_PARCEL_DISPLAY'),
                'short_office_name' => Configuration::get(self::CONST_PREFIX.'SHORT_OFFICE_NAME'),
                //'delivery_time' => self::$delivery_time,
            ));
            //$this->context->controller->addJS(_PS_MODULE_DIR_.'dynamicparceldistribution/views/js/script.js', 'all');
            //$this->context->controller->addJS(_PS_MODULE_DIR_.'dynamicparceldistribution/views/js/script.js');
            //print_r(_PS_MODULE_DIR_.'dynamicparceldistribution/views/js/script.js');
            //$this->context->controller->addCSS($this->_path.'/views/css/mycss.css');
            if (count($parcel_stores_city)) {
                //return $this->display(__FILE__, 'views/templates/hook/deliverypoints.tpl');
                $output = $this->context->smarty->fetch($this->local_path.'views/templates/hook/deliverypoints.tpl');
                return $output;
            } else {
                self::log('hookDisplayCarrierList -> Not found any parcel stores. maybe user and password is not correct?');
            }
            return '';
        }

        if ($carrier_id == Configuration::get('COURIERSERVICE_CARRIER_ID')) {
            if (Configuration::get(self::CONST_PREFIX.'SHOW_DELIVERY_TIME')) {
                $this->getDeliveryTimeAvailable($params['address']->city); //Leave ony available times
                if (empty(self::$delivery_time)) {//Last check for how much available
                    return '';
                }

                $this->context->smarty->assign(array(
                    'delivery_option' => $params['address']->id, //eg delivery_option_15_3
                    'id_carrier' => $params['cart']->id_carrier,
                    'id_address' => $params['address']->id,
                    'carrier_name' => $this->getCarrierName($carrier_id),
                    'delivery_time' => self::$delivery_time,
                ));
                if (count(self::$delivery_time)) {
                    //return $this->display(__FILE__, 'views/templates/hook/courierservice.tpl');
                    $output = $this->context->smarty->fetch($this->local_path.'views/templates/hook/courierservice.tpl');
                    return $output;
                } else {
                    self::log('hookDisplayCarrierList -> Not found any delivery time');
                }
            }
        }
        return '';
    }

    public function getDeliveryTimeAvailable($deliveryShippinCity)
    {
        $deliverySettings = ((Configuration::get(self::CONST_PREFIX.'DELIVERY_TIME') === false)
            ?array_values(BalticodeDpdData::fliparrayList(unserialize(self::$defaultValues['DELIVERY_TIME'])))
            :array_values(unserialize(Configuration::get(self::CONST_PREFIX.'DELIVERY_TIME'))));

        if ($deliverySettings === false) {//Not set any times
            return self::$delivery_time;
        }
        $line = BalticodeDpdData::recursiveArraySearch(trim(Tools::strtolower($deliveryShippinCity)), $this->arrayChangeValueCase($deliverySettings));
        if ($line !== false) {
            self::$delivery_time = array_intersect_key(self::$delivery_time, array_flip($deliverySettings[$line]['time']));
        } else {
            self::$delivery_time = array();
        }
        return $this;
    }

    /**
     * Recursive trim and characters to lower from array values
     *
     * @param  array $arr some array data
     * @return array      return same array just trimmed and lowercase
     */
    public function arrayChangeValueCase($arr)
    {
        if (!is_array($arr)) {
            return array();
        }
        return array_map(
            function ($item) {
                if (is_array($item)) {
                    return self::arrayChangeValueCase($item);
                } else {
                    return trim(Tools::strtolower($item));
                }
            },
            $arr
        );
    }

    private static function stringToarray($separator, $string = '')
    {
        $array = array();
        if ($string != '') {
            $array = explode($separator, $string);
        }
        return array_filter($array);
    }

    private static function setKeyPriority(&$array, $priority = array())
    {
        $new_array = array();
        ksort($priority);
        foreach ($priority as $city) {
            $cityName = trim($city);
            if (isset($array[$cityName])) {
                $new_array[$cityName] = $array[$cityName];
                unset($array[$cityName]);
            }
        }
        $array = $new_array + $array;
    }

    private function getCarrierName($carrier_id)
    {
        $carrier = new Carrier((int)$carrier_id, Context::getContext()->cookie->id_lang);
        return $carrier->name;
    }

    /* Save changes of settings */
    private function postProcess($skip = array('tab', 'btnSubmit'))
    {
        $data = $_POST;
        //Configuration::updateValue(self::CONST_PREFIX.Tools::strtoupper('delivery_time'), self::$defaultValues['DELIVERY_TIME']);
        //This save delivery times to database, if you wanna edit this remove this line and uncomment lines in configure.tpl collector
        foreach ($data as $name => $value) {
            if (in_array($name, $skip)) {
                continue; //Skip not infomative fields
            }
            if (is_array($value)) {
                $value = serialize(Tools::getValue($name));
            } else {
                $value = Tools::getValue($name); //if array so serilizse it
            }
            Configuration::updateValue(self::CONST_PREFIX.Tools::strtoupper($name), $value);
        }

        $carrier = new Carrier();
        $id_shop = null;
        if ($carrier->isLangMultishop()) {
            $id_shop = Context::getContext()->shop->id;
        }

        $file = Tools::fileAttachment('carrier_post_price');

        if ($file !== null) {
            DpdDeliveryPrice::importFromCsv($file, (int)Configuration::get('COURIERSERVICE_CARRIER_ID'), $id_shop);
        }

        $file = Tools::fileAttachment('pickup_post_price');
        if ($file !== null) {
            DpdDeliveryPrice::importFromCsv($file, (int)Configuration::get('DELIVERYPOINTS_CARRIER_ID'), $id_shop);
        }
    }

    /*
    * SORT_DESC
    */
    private function parcelSort(&$parcel_array, $column, $direction = SORT_ASC)
    {
        $sort_col = array();
        foreach ($parcel_array as $key => $row) {
            $sort_col[$key] = $row[$column];
        }

        array_multisort($sort_col, $direction, $parcel_array);
        return $parcel_array;
    }

    public static function objectToarray($obj)
    {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }

        if (is_array($obj)) {
            return array_map([__CLASS__, __METHOD__], $obj);
        } else {
            return $obj;
        }
    }

    public function getModel($moduleName)
    {
        $class = new $moduleName();
        return $class;
    }

    /**
     * File content create to download dialog box
     *
     * @param  string $fileName   - Downloading file name
     * @param  string $pathToFile - Temp file location
     * @param  string $fileType   - mime type of downloading file
     * @return mime header with file content
     */
    public function download($fileName, $pathToFile, $fileType)
    {
        if (!file_exists($pathToFile)) {
            return false;
        }

        $content = Tools::file_get_contents($pathToFile);
        unlink($pathToFile);
        $cretedownload = new Createdownload($fileName, $fileType);
        $cretedownload->render($content);
    }

    /**
     * Put Message to log file
     *
     * @param  $message (string) - message who need put to file
     * @param  $logFileName (string) - file name where put
     * @param  $logFilePath (string) - file location from PS ROOT
     * @return $this (mix) - this class
     */
    public static function log($message, $logFileName = 'dpd.log', $logFilePath = 'log')
    {
        $logger = new FileLogger(0); //0 == debug level, logDebug() won’t work without this.
        $logger->setFilename(_PS_ROOT_DIR_.'/'.$logFilePath.'/'.$logFileName);
        $logger->logDebug(date('Y-m-d H:i:s').':'.$message);
        return __CLASS__;
    }

    /**
     * Shop id is exist
     *
     * @param  $id_shop (string) | (int) - Shop id
     * @return boolean
     */
    public static function shopIsAvailable($id_shop)
    {
        if (!Shop::getShop($id_shop)) {
            self::log('shopIsAvailable -> Shop not found given shop id: '.$id_shop);
            return false;
        }
        return true;
    }

    /**
     * Return Delivery interval
     *
     * @param  string | int - of delivery id
     * @return mix -    string - filtered values;
     *                  array - all values,
     *                  Boolean - not found id
     */
    public static function getDeliveryTime($id_delivery = null)
    {
        $delivery_time = self::$delivery_time;
        if ($id_delivery === null) {
            return $delivery_time;
        } else {
            if ($delivery_time[$id_delivery] !== null
                && (is_string($id_delivery) || is_int($id_delivery))) {
                return $delivery_time[$id_delivery];
            } else {
                return false;
            }
        }
    }

    /**
     * Return date and time by stamp given string
     *
     * @param  string - time, example: 2015/06/25 or 19:55
     * @param  string - format
     * @return string | Boolean if type of variables is not correct
     */
    public static function toTimeStamp($time, $time_stamp = 'Y-m-d H:i:s')
    {
        if (!is_string($time)) {
            return false;
        }
        return date(trim($time_stamp), strtotime($time));
    }

    /**
     * Returm Order PaymentMethod
     *
     * @param  int - order id
     * @return string - order payment method
     */
    public static function getPaymentMethod($id_order)
    {
        $order = new Order($id_order);
        $orderPaymentMethod = $order->module; //Payment method
        return $orderPaymentMethod;
    }

    /**
     * Return Available Payment Method for carrier
     *
     * @param  string | int - Id Order
     * @param  string | int - Id Carrier
     * @return boolean
     */
    public static function availablePaymentMethod($id_order, $id_carrier)
    {
        $carrierTyle = DpdCarrierOptions::getCarrierType($id_carrier); // carrier | pickup
        if ($carrierTyle == 'carrier') {
            return BalticodeDpdCarrierCourier::availablePaymentMethod(self::getPaymentMethod($id_order));
        }

        if ($carrierTyle == 'pickup') {
            return BalticodeDpdCarrierPickUp::availablePaymentMethod(self::getPaymentMethod($id_order));
        }

        return false;
    }

    /**
     * Return Payment Method is CashOnDelivery method
     *
     * @param  string - Payment Method
     * @return boolean
     */
    public static function isCodMethod($paymentMethod)
    {
        return in_array($paymentMethod, self::$cod_payment_methods);
    }

    /**
     * Return module is enabled or disabled
     *
     * @return boolean
     */
    public static function isEnabled($module_name)
    {
        if (!(Configuration::get(self::CONST_PREFIX.'ENABLED'))) {
            return false;
        }
        return parent::isEnabled($module_name);
    }
}
