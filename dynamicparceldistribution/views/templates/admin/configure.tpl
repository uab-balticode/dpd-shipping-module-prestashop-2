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
<div id="dynamicparceldistribution">
    {assign var="deliveryLine" value="0"}
    {assign var="packageLine" value="0"}
    <link href="../modules/{$module_name|escape:'htmlall':'UTF-8'}/views/css/dynamicparceldistribution.css" rel="stylesheet" type="text/css"/>
    <img src="../modules/{$module_name|escape:'htmlall':'UTF-8'}/views/img/logo.png" class="dynamicparceldistribution_logo_img"><h2 class="dynamicparceldistribution_inline">{$displayName|escape:'htmlall':'UTF-8'}</h2>
    <form action="{$action|escape:'htmlall':'UTF-8'}" method="{$method|escape:'htmlall':'UTF-8'}" enctype="multipart/form-data">
        <fieldset>
            <legend><img src="../img/admin/contact.gif" alt='' />{l s='Global configuration details' mod='dynamicparceldistribution'}</legend>
                <label for="company">{l s='Enable' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <img src="../img/admin/enabled.gif" alt="Yes" title="Yes">
                    <input name="enabled" id="enable_on"
                    {if $enabled eq '1' }
                        checked="checked"
                    {/if}
                    value="1" type="radio">
                    <label class="t" for="enable_on">{l s='Yes' mod='dynamicparceldistribution'}</label>
                    <img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;">
                    <input name="enabled" id="enable_off"
                    {if $enabled eq '0' }
                        checked="checked"
                    {/if}
                     value="0" type="radio">
                    <label class="t" for="enable_off">{l s='No' mod='dynamicparceldistribution'}</label>
                </div>
                <label for="service_username">{l s='DPD Self service username' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="service_username" value="{$service_username|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="service_userpass">{l s='DPD Self service password' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="password" name="service_userpass" value="{$service_userpass|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="service_userid">{l s='DPD Self service user ID' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="service_userid" value="{$service_userid|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="api_url">{l s='DPD Api URL' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="api_url" value="{$api_url|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_name">{l s='Pickup address name' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_name" value="{$pickup_address_name|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_company">{l s='Pickup address company' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_company" value="{$pickup_address_company|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_email">{l s='Pickup address e-mail' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_email" value="{$pickup_address_email|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_phone">{l s='Pickup address phone' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_phone" value="{$pickup_address_phone|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_street">{l s='Pickup address street' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_street" value="{$pickup_address_street|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_city">{l s='Pickup address city, county' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_city" value="{$pickup_address_city|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_zip">{l s='Pickup address zip code' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_zip" value="{$pickup_address_zip|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_address_country">{l s='Pickup address country' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_address_country" value="{$pickup_address_country|escape:'htmlall':'UTF-8'}"/>
                </div>
                <label for="pickup_vat_code">{l s='VAT code' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="pickup_vat_code" value="{$pickup_vat_code|escape:'htmlall':'UTF-8'}"/>
                </div>
        </fieldset>
        <br>
        <fieldset>
            <legend><img src="../img/admin/contact.gif" alt='' />{l s='Courier configuration details' mod='dynamicparceldistribution'}</legend>
                <label for="carrier_price_calculate">{l s='Price calculate' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <select name="carrier_price_calculate">
                        <option {if $carrier_price_calculate eq '0'} selected {/if} value="0">{l s='Use PrestaShop settings' mod='dynamicparceldistribution'}</option>
                        <option {if $carrier_price_calculate eq '1'} selected {/if} value="1">{l s='Use Custom calculation' mod='dynamicparceldistribution'}</option>
                    </select>
                </div>
                <div class="request_carrier_price_calculate">
                    <label class='' for="carrier_price">{l s='Default Price' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form ">
                        <input type="text" name="carrier_price" value="{$carrier_price|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}"/>
                    </div>
                    <label for="carrier_free_shipping">{l s='Available free shipping' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form">
                        <select name="carrier_free_shipping">
                            <option {if $carrier_free_shipping eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                            <option {if $carrier_free_shipping eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div>
                    <label class="request_carrier_free_shipping" for="carrier_free_from">{l s='Free from' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form request_carrier_free_shipping">
                        <input type="text" name="carrier_free_from" value="{$carrier_free_from|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}"/>
                    </div>
                    <label for="carrier_price_pcode">{l s='Use delivery price by postcode' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form">
                        <select name="carrier_price_pcode">
                            <option {if $carrier_price_pcode eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                            <option {if $carrier_price_pcode eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div>
                    <label class='request_carrier_price_pcode' for="carrier_post_price">{l s='Delivery Price by postcode' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form request_carrier_price_pcode">
                        <input id="carrier_post_price" name="carrier_post_price" type="file">
                        <input type="button" onClick="location.href='{$export_url|escape:'UTF-8'}&export=1&carrier_id={$courierservice_carrier_id|escape:'htmlall':'UTF-8'|escape:'htmlall':'UTF-8'}'" value="{l s='Export existing' mod='dynamicparceldistribution'}">
                    </div>
<!--                     <label for="delivery_time">{l s='Price calculate by' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form">
                        <select name="carrier_price_priority">
                            <option {if $carrier_price_priority eq '0'} selected {/if} value="0">{l s='Dimension' mod='dynamicparceldistribution'}</option>
                            <option {if $carrier_price_priority eq '1'} selected {/if} value="1">{l s='Weight' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div> -->
                </div>
                <label for="allow_courier_pickup">{l s='Allow courier pickup' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <select name="allow_courier_pickup">
                        <option {if $allow_courier_pickup eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                        <option {if $allow_courier_pickup eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                    </select>
                </div>
                <label for="delivery_time">{l s='Show delivery time' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <select name="show_delivery_time">
                        <option {if $show_delivery_time eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                        <option {if $show_delivery_time eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                    </select>
                </div>
                <!--label class="request_show_delivery_time" for="delivery_time">{l s='Set delivery time' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form request_show_delivery_time">
                    <table cellpadding="0" cellspacing="0" class="border">
                        <thead>
                            <tr class="headings" id="heading">
                                <th>{l s='City' mod='dynamicparceldistribution'}</th>
                                <th>{l s='Delivery time' mod='dynamicparceldistribution'}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="dynamicparceldistribution_deliveryTime_body">
                    {if $delivery_times}
                        {foreach from=$delivery_times key=deliveryLine item=block}
                            <tr>
                                <td>
                                    <input type="text" value="{$block.city|escape:'htmlall':'UTF-8'}" name="delivery_time[{$deliveryLine|escape:'htmlall':'UTF-8'}][city]">
                                </td>
                                <td>
                                    <select MULTIPLE="MULTIPLE" name="delivery_time[{$deliveryLine|escape:'htmlall':'UTF-8'}][time][]">
                                        {foreach from=$availible_delivery_time key=id item=title}
                                            <option value="{$id|escape:'htmlall':'UTF-8'}" {if $id|in_array:$block.time|escape:'htmlall':'UTF-8'} selected="selected" {/if}>{$title|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td>
                                    <button onclick="removeBlock(this);" class='' type="button">
                                        <span>{l s='Delete' mod='dynamicparceldistribution'}</span>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                        </tbody>
                        <tfoot>
                            <tr id="addRow_formFieldId ">
                                <td colspan="2"></td>
                                <td >
                                    <button style='' onclick="addBlock('#dynamicparceldistribution_deliveryTime_template', '#dynamicparceldistribution_deliveryTime_body', '#deliveryIncrementTime');" class='' type="button" id="addToEndBtn">
                                        <span>{l s='Add combination' mod='dynamicparceldistribution'}</span>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">* {l s='You can select different times by holding the Ctrl key (or Cmd on Mac)' mod='dynamicparceldistribution'}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div-->
                <div class="request_carrier_price_calculate">
                    <label class="request_carrier_price_calculate" for="carrier_show_size_restr">{l s='Package size restriction' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form request_carrier_price_calculate">
                        <select name="carrier_show_size_restr">
                            <option {if $carrier_show_size_restr eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                            <option {if $carrier_show_size_restr eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div>
                    <div class="request_carrier_show_size_restr">
                        <label for="carrier_size_restr">{l s='Set delivery restriction' mod='dynamicparceldistribution'} :</label>
                        <div class="margin-form">
                            <table cellpadding="0" cellspacing="0" class="border">
                                <thead>
                                    <tr class="headings" id="heading">
                                        <th>{l s='Country' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Base shipping price' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Max package weight' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Max package size' mod='dynamicparceldistribution'}<br>{l s='(Height x Width x Depth)' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Price for oversize package' mod='dynamicparceldistribution'}*</th>
                                        <th>{l s='Price for overweight package' mod='dynamicparceldistribution'}*</th>
                                        <th>{l s='Free shipping from' mod='dynamicparceldistribution'}**</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="dynamicparceldistribution_packageSize_carrier_body">
                            {if $carrier_package_size}
                                {foreach from=$carrier_package_size key=packageLine item=block}
                    <tr>
                        <td>
                            <select name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][country][]" style="width:100px;">
                                {foreach from=$all_countries key=id item=title}
                                    <option value="{$id|escape:'htmlall':'UTF-8'}" {if $id|in_array:$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['country']|escape:'htmlall':'UTF-8'} selected="selected" {/if} >{$title.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <input type="text" value="{$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['base_price']|escape:'htmlall':'UTF-8'}" name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][base_price]">
                        </td>
                        <td>
                            <input type="text" value="{$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['weight']|escape:'htmlall':'UTF-8'}" name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][weight]">
                        </td>
                        <td>
                            <input type="text" value="{$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['dimensions']|escape:'htmlall':'UTF-8'}" name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][dimensions]">
                        </td>
                        <td>
                            <input type="text" value="{$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['oversized_price']|escape:'htmlall':'UTF-8'}" name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][oversized_price]">
                        </td>
                        <td>
                            <input type="text" value="{$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['overweight_price']|escape:'htmlall':'UTF-8'}" name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][overweight_price]">
                        </td>
                        <td>
                            <input type="text" value="{$carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['free_from_price']|escape:'htmlall':'UTF-8'}" name="carrier_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][free_from_price]">
                        </td>
                        <td>
                            <button onclick="removeBlock(this);" class='' type="button">
                                <span>{l s='Delete' mod='dynamicparceldistribution'}</span>
                            </button>
                        </td>
                    </tr>
                                {/foreach}
                            {/if}
                                </tbody>
                                <tfoot>
                                    <tr id="addRow_formFieldId ">
                                        <td colspan="7">
                                            <button style='' onclick="addBlock('#dynamicparceldistribution_packageSize_template_carrier', '#dynamicparceldistribution_packageSize_carrier_body', '#packageIncrementSize');" class='' type="button" id="addToEndBtn">
                                                <span>{l s='Add combination' mod='dynamicparceldistribution'}</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">* {l s='-1 - Oversize / Overweight is not allowed' mod='dynamicparceldistribution'}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">** {l s='-1 - Disabled free shipping 0 - Always free shipping' mod='dynamicparceldistribution'}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
        </fieldset>
        <br>
        <fieldset>
            <legend><img src="../img/admin/contact.gif" alt='' />{l s='Pickup configuration details' mod='dynamicparceldistribution'}</legend>
                <label class="hide" for="courier_price">{l s='Price calculate' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form hide">
                    <select name="pickup_price_calculate">
                        <option {if $pickup_price_calculate eq '0'} selected {/if} value="0">{l s='Use PrestaShop settings' mod='dynamicparceldistribution'}</option>
                        <option {if $pickup_price_calculate eq '1'} selected {/if} value="1">{l s='Use Custom calculation' mod='dynamicparceldistribution'}</option>
                    </select>
                </div>
                <div class="request_pickup_price_calculate">
                    <label class='' for="pickup_price">{l s='Default Price' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form ">
                        <input type="text" name="pickup_price" value="{$pickup_price|escape:'htmlall':'UTF-8'}"/>
                    </div>


                    <label for="pickup_free_shipping">{l s='Available free shipping' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form">
                        <select name="pickup_free_shipping">
                            <option {if $pickup_free_shipping eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                            <option {if $pickup_free_shipping eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div>
                    <label class="request_pickup_free_shipping" for="pickup_free_from">{l s='Free from' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form request_pickup_free_shipping">
                        <input type="text" name="pickup_free_from" value="{$pickup_free_from|escape:'htmlall':'UTF-8'}"/>
                    </div>
                    <!-- <label class='' for="pickup_post_price">{l s='Delivery Price by postcode' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form ">
                        <input type="file" id="pickup_post_price" name="pickup_post_price" >
                        <input type="button" onClick="location.href='{$export_url|escape:'htmlall':'UTF-8'}&export=1&carrier_id={$deliverypoints_carrier_id|escape:'htmlall':'UTF-8'}'" value="{l s='Export existing' mod='dynamicparceldistribution'}">
                    </div> -->
                </div>
<!--                 <label for="delivery_time">{l s='Price calculate by' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form">
                        <select name="pickup_price_priority">
                            <option {if $pickup_price_priority eq '0'} selected {/if} value="0">{l s='Dimension' mod='dynamicparceldistribution'}</option>
                            <option {if $pickup_price_priority eq '1'} selected {/if} value="1">{l s='Weight' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div> -->
                <label for="type_parcel_display">{l s='Type parcel display' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <select name="type_parcel_display">
                        <option {if $type_parcel_display eq 'optgroup'} selected {/if} value="optgroup">{l s='Optgroup' mod='dynamicparceldistribution'}</option>
                        <option {if $type_parcel_display eq 'blocks'} selected {/if} value="blocks">{l s='Block' mod='dynamicparceldistribution'}</option>
                    </select>
                </div>
                <label for="short_office_name">{l s='Show long office names' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <select name="short_office_name">
                        <option {if $short_office_name eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                        <option {if $short_office_name eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                    </select>
                </div>
                <label for="city_priority">{l s='City priority' mod='dynamicparceldistribution'} :</label>
                <div class="margin-form">
                    <input type="text" name="city_priority" value="{$city_priority|escape:'htmlall':'UTF-8'}"/>
                </div>
<!--                 <div class="request_pickup_price_calculate">
                    <label class="request_pickup_price_calculate" for="size_restr">{l s='Package size restriction' mod='dynamicparceldistribution'} :</label>
                    <div class="margin-form request_pickup_price_calculate">
                        <select name="pickup_show_size_restr">
                            <option {if $pickup_show_size_restr eq '0'} selected {/if} value="0">{l s='No' mod='dynamicparceldistribution'}</option>
                            <option {if $pickup_show_size_restr eq '1'} selected {/if} value="1">{l s='Yes' mod='dynamicparceldistribution'}</option>
                        </select>
                    </div>

                    <div class="request_pickup_show_size_restr">
                        <label for="pickup_size_restr">{l s='Set delivery restriction' mod='dynamicparceldistribution'} :</label>
                        <div class="margin-form">
                            <table cellpadding="0" cellspacing="0" class="border">
                                <thead>
                                    <tr class="headings" id="heading">
                                        <th>{l s='Country' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Base shipping price' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Max package weight' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Max package size' mod='dynamicparceldistribution'}<br>{l s='(Height x Width x Depth)' mod='dynamicparceldistribution'}</th>
                                        <th>{l s='Price for oversize package' mod='dynamicparceldistribution'}*</th>
                                        <th>{l s='Price for overweight package' mod='dynamicparceldistribution'}*</th>
                                        <th>{l s='Free shipping from' mod='dynamicparceldistribution'}**</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="dynamicparceldistribution_packageSize_pickup_body">
                            {if $pickup_package_size}
                                {foreach from=$pickup_package_size key=packageLine item=block}
                    <tr>
                        <td>
                            <select name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][country][]" style="width:100px;">
                                {foreach from=$all_countries key=id item=title}
                                    <option value="{$id|escape:'htmlall':'UTF-8'}" {if $id|in_array:$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['country']} selected="selected" {/if} >{$title.name|escape:'htmlall':'UTF-8'}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td>
                            <input type="text" value="{$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['base_price']|escape:'htmlall':'UTF-8'}" name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][base_price]">
                        </td>
                        <td>
                            <input type="text" value="{$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['weight']|escape:'htmlall':'UTF-8'}" name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][weight]">
                        </td>
                        <td>
                            <input type="text" value="{$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['dimensions']|escape:'htmlall':'UTF-8'}" name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][dimensions]">
                        </td>
                        <td>
                            <input type="text" value="{$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['oversized_price']|escape:'htmlall':'UTF-8'}" name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][oversized_price]">
                        </td>
                        <td>
                            <input type="text" value="{$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['overweight_price']|escape:'htmlall':'UTF-8'}" name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][overweight_price]">
                        </td>
                        <td>
                            <input type="text" value="{$pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}]['free_from_price']|escape:'htmlall':'UTF-8'}" name="pickup_package_size[{$packageLine|escape:'htmlall':'UTF-8'}][free_from_price]">
                        </td>
                        <td>
                            <button onclick="removeBlock(this);" class='' type="button">
                                <span>{l s='Delete' mod='dynamicparceldistribution'}</span>
                            </button>
                        </td>
                    </tr>
                                {/foreach}
                            {/if}
                                </tbody>
                                <tfoot>
                                    <tr id="addRow_formFieldId ">
                                        <td colspan="7">
                                            <button style='' onclick="addBlock('#dynamicparceldistribution_packageSize_template_pickup', '#dynamicparceldistribution_packageSize_pickup_body', '#packageIncrementSize');" class='' type="button" id="addToEndBtn">
                                                <span>{l s='Add combination' mod='dynamicparceldistribution'}</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">* {l s='-1 - Oversize / Overweight is not allowed' mod='dynamicparceldistribution'}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="7">** {l s='-1 - Disabled free shipping 0 - Always free shipping' mod='dynamicparceldistribution'}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div> -->
        </fieldset>
        <br>
        <fieldset>
            <div class="margin-form">
                <input class="button" name="btnSubmit" value="{l s='Update settings' mod='dynamicparceldistribution'}" type="submit" />
                <input class="button" name="btnSubmit" value="{l s='Reset' mod='dynamicparceldistribution'}" type="reset" />
            </div>
        </fieldset>
    </form>
    <div class="hide">
        <input type="hidden" id="deliveryIncrementTime" value="{$deliveryLine|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" id="packageIncrementSize" value="{$packageLine|escape:'htmlall':'UTF-8'}"/>
        <script type="text/html" id="dynamicparceldistribution_deliveryTime_template" class="hide">
            <tr>
                <td>
                    <input type="text" value='' name="delivery_time[-0][city]">
                </td>
                <td>
                    <select multiple="multiple" name="delivery_time[-0][time][]">
                        {foreach from=$availible_delivery_time key=id item=title}
                            <option value="{$id|escape:'htmlall':'UTF-8'}" selected="selected" >{$title|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </td>
                <td>
                    <button onclick="removeBlock(this);" class='' type="button">
                        <span>{l s='Delete' mod='dynamicparceldistribution'}</span>
                    </button>
                </td>
            </tr>
        </script>
        <script type="text/html" id="dynamicparceldistribution_packageSize_template_carrier" class="hide">
            <tr>
                <td>
                    <select name="carrier_package_size[-0][country][]" style="width:100px;">
                        {foreach from=$all_countries key=id item=title}
                            <option value="{$id|escape:'htmlall':'UTF-8'}" selected="selected" >{$title.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </td>
                <td>
                    <input type="text" value='' name="carrier_package_size[-0][base_price]">
                </td>
                <td>
                    <input type="text" value='' name="carrier_package_size[-0][dimensions]">
                </td>
                <td>
                    <input type="text" value='' name="carrier_package_size[-0][weight]">
                </td>
                <td>
                    <input type="text" value="-1" name="carrier_package_size[-0][oversized_price]">
                </td>
                <td>
                    <input type="text" value="-1" name="carrier_package_size[-0][overweight_price]">
                </td>
                <td>
                    <input type="text" value="-1" name="carrier_package_size[-0][free_from_price]">
                </td>
                <td>
                    <button onclick="removeBlock(this);" class='' type="button">
                        <span>{l s='Delete' mod='dynamicparceldistribution'}</span>
                    </button>
                </td>
            </tr>
        </script>
        <script type="text/html" id="dynamicparceldistribution_packageSize_template_pickup" class="hide">
            <tr>
                <td>
                    <select name="pickup_package_size[-0][country][]" style="width:100px;">
                        {foreach from=$all_countries key=id item=title}
                            <option value="{$id|escape:'htmlall':'UTF-8'}" selected="selected" >{$title.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </td>
                <td>
                    <input type="text" value='' name="pickup_package_size[-0][base_price]">
                </td>
                <td>
                    <input type="text" value='' name="pickup_package_size[-0][dimensions]">
                </td>
                <td>
                    <input type="text" value='' name="pickup_package_size[-0][weight]">
                </td>
                <td>
                    <input type="text" value="-1" name="pickup_package_size[-0][oversized_price]">
                </td>
                <td>
                    <input type="text" value="-1" name="pickup_package_size[-0][overweight_price]">
                </td>
                <td>
                    <input type="text" value="-1" name="pickup_package_size[-0][free_from_price]">
                </td>
                <td>
                    <button onclick="removeBlock(this);" class='' type="button">
                        <span>{l s='Delete' mod='dynamicparceldistribution'}</span>
                    </button>
                </td>
            </tr>
        </script>
    </div>
</div>