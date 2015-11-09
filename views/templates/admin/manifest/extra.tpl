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
<table class="extra" style="page-break-inside: avoid;">
    <tr>
        <td class="cell first">
            <table border="0">
                <tr>
                    <td colspan="3" class="bold label_additional">{$label_additional|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr>
                    <td colspan="3"><div class="line line_w200"></div></td>
                </tr>
                <tr>
                    <td class="label_load">
                        <div class="checkbox"></div>{$label_load|escape:'htmlall':'UTF-8'}
                    </td>
                    <td class="label_wait"><div class="checkbox"></div>{$label_wait|escape:'htmlall':'UTF-8'}
                    </td>
                    <td class="label_smin">
                        <div class="checkbox_dotted"></div><div class="checkbox_dotted checkbox_middle_box"></div><div class="checkbox_dotted"></div>
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
                    <td class="bold text_notification_title">{$text_notification_title|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr>
                    <td><div class="line line_w350"/></td>
                </tr>
                <?php //foreach ($text_notification as $notification) { ?>
                <tr>
                    <td class="notification">{$notification|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <?php //} ?>
                <tr>
                    <td class="value_issn_nr">
                        <div class="checkbox line_w685 line_h15"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="cell last">
        <table border="0" cellspacing="0" cellpadding="0" width="747px">
            <tr>
                <td class="text_conditions">{$text_conditions['1']|escape:'htmlall':'UTF-8'}</td>
                <td class="label_sender_signature">{$label_sender_signature|escape:'htmlall':'UTF-8'}</td>
            </tr>
            <tr>
                <td class="text_conditions">{$text_conditions['2']|escape:'htmlall':'UTF-8'}</td>
                <td class="bottom line_w150"><div class="line line_w100" /></td>
            </tr>
            <tr>
                <td class="text_conditions" colspan="2">{$text_conditions['3']|escape:'htmlall':'UTF-8'}</td>
            </tr>
        </table>
        </td>
    </tr>
</table>