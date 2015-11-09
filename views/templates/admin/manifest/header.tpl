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
<table class="header_props" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td class="label_company allcaps">{$label_company|escape:'htmlall':'UTF-8'}</td>
        <td class="label_phone allcaps">{$label_phone|escape:'htmlall':'UTF-8'}</td>
        <td class="value_phone allcaps">{$value_phone|escape:'htmlall':'UTF-8'}</td>
        <td colspan="2" rowspan="4" class="logo"><img src="{$value_logo|escape:'htmlall':'UTF-8'}" alt="logo"></td>
    </tr>
    <tr>
        <td class="label_vat props_label_vat_code allcpas">{$label_vat|escape:'htmlall':'UTF-8'} {$value_vat_code|escape:'htmlall':'UTF-8'}</td>
        <td class="label_fax">{$label_fax|escape:'htmlall':'UTF-8'}</td>
        <td class="value_fax">{$value_fax|escape:'htmlall':'UTF-8'}</td>
    </tr>
    <tr>
        <td class="value_street allcaps" colspan="3">{$value_street|escape:'htmlall':'UTF-8'}</td>
    </tr>
        <tr>
        <td colspan="3"></td>
    </tr>
</table>
<table class="header" border="0" cellspacing="1">
    <tr>
        <td class="line_h10 top bold label_manifest_nr"><strong>{$label_manifest_nr|escape:'htmlall':'UTF-8'}</strong></td>
        <td class="line_h10 top value_manifest_nr">{$value_manifest_nr|escape:'htmlall':'UTF-8'}</td>
        <td class="line_h10 top label_client">{$label_client|escape:'htmlall':'UTF-8'}</td>
        <td class="line_h10 top value_client">{$value_client|escape:'htmlall':'UTF-8'}</td>
        <td class="line_h10 top label_vat_code">{$label_vat_code|escape:'htmlall':'UTF-8'}</td>
        <td class="line_h10 top label_sphone">{$label_sphone|escape:'htmlall':'UTF-8'}</td>
    </tr>
    <tr>
        <td class="label_done_date">{$label_done_date|escape:'htmlall':'UTF-8'}</td>
        <td class="value_done_date">{$value_done_date|escape:'htmlall':'UTF-8'}</td>
        <td class="value_client_id">{$value_client_id|escape:'htmlall':'UTF-8'}</td>
        <td class="value_client_street allcaps">{$value_client_street|escape:'htmlall':'UTF-8'}</td>
        <td class="value_client_vat_code allcaps">{$value_client_vat_code|escape:'htmlall':'UTF-8'}</td>
        <td class="value_client_phone">{$value_client_phone|escape:'htmlall':'UTF-8'}</td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td class="allcaps">{$value_client_city|escape:'htmlall':'UTF-8'}</td>
        <td colspan="2"></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td class="allcaps">{$value_client_post|escape:'htmlall':'UTF-8'}</td>
        <td colspan="2"></td>
    </tr>
</table>