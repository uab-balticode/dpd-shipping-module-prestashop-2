{*
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
*}
<div style="display:none" id="call_dpd_carrier_popup">
    <form action="?controller=AdminCallcarrier&token={$token|escape:'htmlall':'UTF-8'}" method="POST">
    
        <div class="shipment_info">
            <label for="Po_parcel_qty"><input type="text" name="Po_parcel_qty" id="Po_parcel_qty" value="0"
                class="validate-not-negative-number validate-digits"/>{l s='Parcels (≤ 31,5kg)' mod='dynamicparceldistribution'}</label>
            <label for="Po_pallet_qty"><input type="text" name="Po_pallet_qty" id="Po_pallet_qty" value="0"
                class="validate-not-negative-number validate-digits"/>{l s='Pallets' mod='dynamicparceldistribution'}</label>
        </div>
        <br>
        <div class="shipment_comment">
            <textarea name="Po_remark" id="Po_remark" cols="30" rows="3" placeholder="{l s='Comment to courier' mod='dynamicparceldistribution'}"></textarea>
        </div>
        <input type="submit" name="submitCallCarrier" value="{l s='Call carrier' mod='dynamicparceldistribution'}" onClick="callCarrier()">
        <input type="button" value="{l s='Close' mod='dynamicparceldistribution'}" onClick="hideCarrierWindow()">
        <input type="hidden" name="parentToken" value="{$smarty.get.token|escape:'htmlall':'UTF-8'}" />
    </form>
</div>