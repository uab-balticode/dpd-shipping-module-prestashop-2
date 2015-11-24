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

require_once(_PS_MODULE_DIR_.'dynamicparceldistribution'.DIRECTORY_SEPARATOR.'dynamicparceldistribution.php');

class AdminCallcarrierController extends ModuleAdminController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->initContext();
        $this->sendDataToCarrier();
    }

    /**
     * Function send data to DPD API about carrier pickup
     */
    private function sendDataToCarrier()
    {
        $api = new API();

        $parameters = array(
            'action' => 'dpdis/pickupOrdersSave',
            'payerName' =>  Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_NAME'),
            'senderName' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_NAME'),
            'senderAddress' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_STREET'),
            'senderPostalCode' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_ZIP'),
            'senderCountry' => $this->context->language->iso_code, //LT LV
            'senderCity' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_CITY'),
            'senderContact' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_NAME'),
            'senderPhone' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_PHONE'),
            'parcelsCount' => Tools::getValue('Po_parcel_qty'),
            'palletsCount' => Tools::getValue('Po_pallet_qty'),
            'nonStandard' => Tools::getValue('Po_remark'),
        );
        $responce = $api->postData($parameters);
        if (strip_tags($responce) == 'DONE') {
            $this->context->cookie->__set('redirect_success', Tools::displayError($this->l('Call courier success')));
        } else {
            $this->context->cookie->__set('redirect_errors', Tools::displayError($this->l('Call courier error: '.strip_tags($responce))));
        }
        Tools::redirectAdmin('?controller=AdminOrders&token='.Tools::getValue('parentToken'));
    }

    /* Retrocompatibility 1.4/1.5 */
    private function initContext()
    {
        if (class_exists('Context')) {
            $this->context = Context::getContext();
        }
        // else
        // {
        //     global $smarty, $cookie;
        //     $this->context = new StdClass();
        //     $this->context->smarty = $smarty;
        //     $this->context->cookie = $cookie;
        // }
    }
}
