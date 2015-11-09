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

class dynamicparceldistributionSavesettingsModuleFrontController extends ModuleFrontController
{
    /**
     * Save carrier settings to database
     * This runs when in frontend changet some drop down
     */
    public function initContent()
    {
        $DpdOrderOptions = Module::getInstanceByName('dynamicparceldistribution')->getModel('DpdOrderOptions');
        $cartOptions = $this->context->cart;
        $result = $DpdOrderOptions->setOrderOptions(
            $cartOptions->id,
            $cartOptions->id_carrier,
            $cartOptions->id_address_delivery,
            serialize(array(
                'delivery_option' => Tools::getValue('value'),
                'delivery_label' => Tools::getValue('label'),
            ))
        );
        die($result); //This is need because without that return some HOOK error
    }
}
