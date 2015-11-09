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
<table class="extra" style="page-break-inside: avoid;" cellpadding="2" border="1" >
    <tr>
        <td class="cell first">
            <table border="0">
                <tr>
                    <td colspan="6" class="bold label_additional title">{$label_additional|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr>
                    <td colspan="6" class="line_h1">&nbsp;<hr class="line line_w150"></td>
                </tr>
                <tr>
                    <td class="checkbox_field right">
                        <table class="checkbox" cellspacing="0" cellpadding="0"><tr><td></td></tr></table>
                    </td>
                    <td class="label_load left">
                        {$label_load|escape:'htmlall':'UTF-8'}
                    </td>
                    <td class="checkbox_field right">
                        <table class="checkbox" cellspacing="0" cellpadding="0"><tr><td></td></tr></table>
                    </td>
                    <td class="label_wait left">
                        {$label_wait|escape:'htmlall':'UTF-8'}
                    </td>
                    <td  class="time_field right">
                        <table class="time_input">
                            <tr>
                                <td class="time_input_dotted">&nbsp;</td>
                                <td class="time_input_dotted">&nbsp;</td>
                                <td class="time_input_dotted">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                    <td class="label_smin left">
                        {$label_smin|escape:'htmlall':'UTF-8'}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="cell">
            <table class="issn">
                <tr>
                    <td class="bold text_notification_title title">{$text_notification_title|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr>
                    <td class="line_h1">&nbsp;<hr class="line line_w150"></td>
                </tr>
                {foreach from=$text_notification item=notification}
                <tr>
                    <td class="notification">{$notification|escape:'htmlall':'UTF-8'}</td>
                </tr>
                {/foreach}
                <tr><td class="line_h1"></td></tr>
                <tr>
                    <td>
                        <table style="margin: 10px;"><tr><td class="value_issn_nr left">&nbsp;</td></tr></table>
                    </td>
                </tr>
                <tr><td class="line_h1"></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="cell last">
            <table border="0" cellspacing="0" cellpadding="0" style="width:520px;">
                <tr>
                    <td class="text_conditions">{$text_conditions['1']|escape:'htmlall':'UTF-8'}</td>
                    <td class="conditions_signature"></td>
                </tr>
                <tr>
                    <td class="text_conditions">{$text_conditions['2']|escape:'htmlall':'UTF-8'}</td>
                    <td class="center conditions_signature bottom" style="border-bottom:1px solid #000;"></td>
                </tr>
                <tr>
                    <td class="text_conditions">{$text_conditions['3']|escape:'htmlall':'UTF-8'}</td>
                    <td class="center conditions_signature">{$label_sender_signature|escape:'htmlall':'UTF-8'}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="signature" cellpadding="0" border="0" style="page-break-inside: avoid;">
    <tr>
        <td class="line_w5"></td>
        <td class="line_w134 center">{$label_sender|escape:'htmlall':'UTF-8'}</td>
        <td class="line_w5"></td>
        <td class="line_w134 center">{$label_courier|escape:'htmlall':'UTF-8'}</td>
        <td class="line_w5"></td>
        <td class="line_w134 center">{$label_arrived|escape:'htmlall':'UTF-8'}</td>
        <td class="line_w5"></td>
        <td class="line_w134 center">{$label_departure|escape:'htmlall':'UTF-8'}</td>
        <td class="line_w5"></td>
    </tr>
    <tr style="line_h1">
        <td></td>
        <td class="center line_w134 line"></td>
        <td></td>
        <td class="center line_w134 line"></td>
        <td></td>
        <td class="center line_w134 line"></td>
        <td></td>
        <td class="center line_w134 line"></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td class="center label" >{$label_name_signature|escape:'htmlall':'UTF-8'}</td>
        <td></td>
        <td class="center label" >{$label_name_tour_signature|escape:'htmlall':'UTF-8'}</td>
        <td></td>
        <td class="center label" >{$label_date_time|escape:'htmlall':'UTF-8'}</td>
        <td></td>
        <td class="center label" >{$label_time|escape:'htmlall':'UTF-8'}</td>
        <td></td>
    </tr>
</table>