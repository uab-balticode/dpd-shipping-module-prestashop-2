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
<link href="{$css|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css"/>
{if $orders|@count gt 0}
    <table class="orders" border="0">
        <thead>
            <tr class="order_head">
                <th class="label_order_nr">{$label_order_nr|escape:'htmlall':'UTF-8'}</th>
                <th class="label_order_number">{$label_order_number|escape:'htmlall':'UTF-8'}</th>
                <th class="label_order_type">{$label_order_type|escape:'htmlall':'UTF-8'}</th>
                <th class="label_order_arrival">{$label_order_arrival|escape:'htmlall':'UTF-8'}</th>
                <th class="label_order_phone">{$label_order_phone|escape:'htmlall':'UTF-8'}</th>
                <th class="label_order_weight">{$label_order_weight|escape:'htmlall':'UTF-8'}</th>
                <th class="label_order_issn">{$label_order_issn|escape:'htmlall':'UTF-8'}</th>
            </tr>
            <tr>
                <th colspan="7"></th>
            </tr>
        </thead>
        {assign var="nr" value="1"}
        {assign var="value_total" value="0"}
        <tbody>
            {foreach from=$orders item=order}
                <tr class="order">
                    <td class="label_order_nr">{$nr++|escape:'htmlall':'UTF-8'}</td>
                    <td class="label_order_number">
                    {foreach from=$order['tracking_number'] item=barcode}
                        {$barcode|escape:'htmlall':'UTF-8'}
                    {/foreach}</td>
                    <td class="label_order_type">{$order['parcel_type']|escape:'htmlall':'UTF-8'}</td>
                    <td class="label_order_arrival">{$order['shipping_firstname']|escape:'htmlall':'UTF-8'} {$order['shipping_lastname']|escape:'htmlall':'UTF-8'}<br />
                        {$order['shipping_address_1']|escape:'htmlall':'UTF-8'}<br />
                        {$order['shipping_postcode']|escape:'htmlall':'UTF-8'}<br />
                        {$order['shipping_city']|escape:'htmlall':'UTF-8'}
                    </td>
                    <td class="label_order_phone">{$order['telephone']|escape:'htmlall':'UTF-8'}</td>
                    <td class="label_order_weight">{$order['total_weight']|escape:'htmlall':'UTF-8'}</td>
                    <td class="label_order_issn">&nbsp;<table><tr><td class="checkbox"></td></tr></table></td>
                </tr>
                {assign var=value_total value=$value_total+$order['total_weight']|escape:'htmlall':'UTF-8'}
            {/foreach}
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">{$label_total|escape:'htmlall':'UTF-8'}</td>
                <td colspan="3">&nbsp;</td>
                <td class="bold">{$value_total|escape:'htmlall':'UTF-8'}</td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <!--tr>
                <td>{$label_orders_count|escape:'htmlall':'UTF-8'}</td>
                <td colspan="6">{$orders|@count}</td>
            </tr-->
            <tr>
                <td>{$label_packages_count|escape:'htmlall':'UTF-8'}</td>
                <td colspan="6">{$packages_barcodes_count|escape:'htmlall':'UTF-8'}</td>
            </tr>
        </tfoot>
    </table>
{/if}