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
<div id="dynamicparceldistribution_courierservice" class="dynamicparceldistribution_options">
    <script src="{$modules_dir|escape:'htmlall':'UTF-8'}dynamicparceldistribution/views/js/script.js" type="text/javascript"></script>
    <h3>{l s='Choose a shipping option for this shipping method: %s' mod='dynamicparceldistribution' sprintf=$carrier_name|escape:'htmlall':'UTF-8'}</h3>
    <div class="delivery_options">
        <div class="delivery_option">
            <select name="dpd_delivery_strip" class="time-strip required-entry" onchange="setDeliveryOptions('{$delivery_option|escape:'htmlall':'UTF-8'}','{$id_carrier|escape:'htmlall':'UTF-8'}',jQuery(this).val(), jQuery.trim(jQuery(this).find('option:selected').text()));">
                <option value='' data-comment=''>{l s='Please select a delivery time' mod='dynamicparceldistribution'}</option>
                {foreach from=$delivery_time key=line item=time_strip}
                    <option value="{$line|escape:'htmlall':'UTF-8'}">
                        {$time_strip|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
</div>