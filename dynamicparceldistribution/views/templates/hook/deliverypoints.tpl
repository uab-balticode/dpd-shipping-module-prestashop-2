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
<div id="dynamicparceldistribution_terminal" class="dynamicparceldistribution_options">
    <script src="{$modules_dir|escape:'htmlall':'UTF-8'}dynamicparceldistribution/views/js/script.js" type="text/javascript"></script>
    <h3>{l s='Choose a shipping option for this shipping method: %s' mod='dynamicparceldistribution' sprintf=$carrier_name}</h3>
    <div class="delivery_options">
        {if $type_parcel_display eq 'blocks'}
            <div class="blocks">
                <select name="dpd_delivery_city_id" class="city required-entry">
                    <option value='' data-comment=''>{l s='Please select a city' mod='dynamicparceldistribution'}</option>
                    {foreach from=$delivery_terminals key=city item=delivery_terminal_of_city}
                        <option value="{$city|escape:'htmlall':'UTF-8'}" {if $city eq $current_city} selected="selected" {/if}>{$city|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <select name="dpd_delivery_id" class="points required-entry" onchange="setDeliveryOptions('{$delivery_option|escape:'htmlall':'UTF-8'}','{$id_carrier|escape:'htmlall':'UTF-8'}',jQuery(this).val(), jQuery.trim(jQuery(this).find('option:selected').text()));">
                    <option value='' data-comment=''>{l s='Please select a parcel terminal' mod='dynamicparceldistribution'}</option>
                    {foreach from=$delivery_terminals key=city item=delivery_terminal_of_city}
                        <optgroup label="{$city|escape:'htmlall':'UTF-8'}" data-comment="{$city|escape:'htmlall':'UTF-8'}" style="display:none;" >
                            {foreach from=$delivery_terminal_of_city key=option item=delivery_terminal}
                                <option value="{$delivery_terminal.parcelshop_id|escape:'htmlall':'UTF-8'}">
                                {$delivery_terminal['company']|escape:'htmlall':'UTF-8'}
                                {if $short_office_name}
                                    ({$delivery_terminal['street']|escape:'htmlall':'UTF-8'})
                                {/if}
                                </option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        {else}
            <div class="optgroup">
                <select name="dpd_delivery_id" class="required-entry" onchange="setDeliveryOptions('{$delivery_option|escape:'htmlall':'UTF-8'}','{$id_carrier|escape:'htmlall':'UTF-8'}',jQuery(this).val(), jQuery.trim(jQuery(this).find('option:selected').text()));">
                    <option value='' data-comment=''>{l s='Please select a parcel terminal' mod='dynamicparceldistribution'}</option>
                    {foreach from=$delivery_terminals key=city item=delivery_terminal_of_city}
                        <optgroup label="{$city|escape:'htmlall':'UTF-8'}">
                            {foreach from=$delivery_terminal_of_city key=option item=delivery_terminal}
                                <option value="{$delivery_terminal.parcelshop_id|escape:'htmlall':'UTF-8'}">
                                {$delivery_terminal['company']|escape:'htmlall':'UTF-8'}
                                {if $short_office_name}
                                    ({$delivery_terminal['street']|escape:'htmlall':'UTF-8'})
                                {/if}
                                </option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        {/if}
    </div>
</div>