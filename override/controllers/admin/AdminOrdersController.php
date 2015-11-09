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

class AdminOrdersController extends AdminOrdersControllerCore
{
    /**
     * Override need to add Bulk Actions for orders
     */
    public function __construct()
    {
        //if Prestashop version is lower that 1.6
        if (self::versionCompare(_PS_VERSION_, 1.6, '<')) {
            self::contruct15();
        } else {
            if (self::versionCompare(_PS_VERSION_, 1.6, '>=') && self::versionCompare(_PS_VERSION_, 1.7, '<')) {
                self::construct16();
            }
        }
    }

    /**
     * Override using to add button to orders top tools bar
     */
    public function initToolbar()
    {
        //if Prestashop version is lower that 1.6
        if (self::versionCompare(_PS_VERSION_, 1.6, '<')) {
            self::initToolbar15();
        } else {
            if (self::versionCompare(_PS_VERSION_, 1.6, '>=') && self::versionCompare(_PS_VERSION_, 1.7, '<')) {
                self::initToolbar16();
            }
        }
    }

    /**
     * Constructor of Prestashop 1.5V
     */
    private function contruct15()
    {
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->addRowAction('view');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();

        $this->_select = '
        a.id_currency,
        a.id_order AS id_pdf,
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
        osl.`name` AS `osname`,
        os.`color`,
        IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new';

        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';

        $statuses_array = array();
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);

        foreach ($statuses as $status) {
            $statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
        'id_order' => array(
            'title' => $this->l('ID'),
            'align' => 'center',
            'width' => 25
        ),
        'reference' => array(
            'title' => $this->l('Reference'),
            'align' => 'center',
            'width' => 65
        ),
        'new' => array(
            'title' => $this->l('New'),
            'width' => 25,
            'align' => 'center',
            'type' => 'bool',
            'tmpTableFilter' => true,
            'icon' => array(
                0 => 'blank.gif',
                1 => array(
                    'src' => 'note.png',
                    'alt' => $this->l('First customer order'),
                )
            ),
            'orderby' => false
        ),
        'customer' => array(
            'title' => $this->l('Customer'),
            'havingFilter' => true,
        ),
        'total_paid_tax_incl' => array(
            'title' => $this->l('Total'),
            'width' => 70,
            'align' => 'right',
            'prefix' => '<b>',
            'suffix' => '</b>',
            'type' => 'price',
            'currency' => true
        ),
        'payment' => array(
            'title' => $this->l('Payment: '),
            'width' => 100
        ),
        'osname' => array(
            'title' => $this->l('Status'),
            'color' => 'color',
            'width' => 280,
            'type' => 'select',
            'list' => $statuses_array,
            'filter_key' => 'os!id_order_state',
            'filter_type' => 'int',
            'order_key' => 'osname'
        ),
        'date_add' => array(
            'title' => $this->l('Date'),
            'width' => 130,
            'align' => 'right',
            'type' => 'datetime',
            'filter_key' => 'a!date_add'
        ),
        'id_pdf' => array(
            'title' => $this->l('PDF'),
            'width' => 35,
            'align' => 'center',
            'callback' => 'printPDFIcons',
            'orderby' => false,
            'search' => false,
            'remove_onclick' => true)
        );

        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_ORDER;

        if (Tools::isSubmit('id_order')) {
            // Save context (in order to apply cart rule)
            $order = new Order((int)Tools::getValue('id_order'));
            if (!Validate::isLoadedObject($order)) {
                throw new PrestaShopException('Cannot load Order object');
            }
            $this->context->cart = new Cart($order->id_cart);
            $this->context->customer = new Customer($order->id_customer);
        }

            $this->bulk_actions['printDpdLabels'] = array('text' => $this->l('Print DPD Labels'), 'icon' => 'icon-print');
            $this->bulk_actions['printDpdLabelsMPS'] = array('text' => $this->l('Print MPS DPD Labels'), 'icon' => 'icon-print');
            $this->bulk_actions['printDpdManifest'] = array('text' => $this->l('Print DPD Manifest'),
                'icon' => 'icon-print',
                'confirm' => $this->l('Are you sure you want to print manifest, because after doing this you wont be able to print labels? '));
        //parent::__construct();
        AdminController::__construct();
    }

    /**
     * Constructor of Prestashop 1.6v
     */
    private function construct16()
    {
        $this->bootstrap = true;
        $this->table = 'order';
        $this->className = 'Order';
        $this->lang = false;
        $this->addRowAction('view');
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();

        $this->_select = '
        a.id_currency,
        a.id_order AS id_pdf,
        CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
        osl.`name` AS `osname`,
        os.`color`,
        IF((SELECT so.id_order FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer AND so.id_order < a.id_order LIMIT 1) > 0, 0, 1) as new,
        country_lang.name as cname,
        IF(a.valid, 1, 0) badge_success';

        $this->_join = '
        LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
        INNER JOIN `'._DB_PREFIX_.'address` address ON address.id_address = a.id_address_delivery
        INNER JOIN `'._DB_PREFIX_.'country` country ON address.id_country = country.id_country
        INNER JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (country.`id_country` = country_lang.`id_country` AND country_lang.`id_lang` = '.(int)$this->context->language->id.')
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
        $this->_orderBy = 'id_order';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = false;

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'reference' => array(
                'title' => $this->l('Reference')
            ),
            'new' => array(
                'title' => $this->l('New client'),
                'align' => 'text-center',
                'type' => 'bool',
                'tmpTableFilter' => true,
                'orderby' => false,
                'callback' => 'printNewCustomer'
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'havingFilter' => true,
            ),
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge($this->fields_list, array(
                'company' => array(
                    'title' => $this->l('Company'),
                    'filter_key' => 'c!company'
                ),
            ));
        }

        $this->fields_list = array_merge($this->fields_list, array(
            'total_paid_tax_incl' => array(
                'title' => $this->l('Total'),
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'callback' => 'setOrderCurrency',
                'badge_success' => true
            ),
            'payment' => array(
                'title' => $this->l('Payment')
            ),
            'osname' => array(
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'align' => 'text-right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
            'id_pdf' => array(
                'title' => $this->l('PDF'),
                'align' => 'text-center',
                'callback' => 'printPDFIcons',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true
            )
        ));

        if (Country::isCurrentlyUsed('country', true)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT DISTINCT c.id_country, cl.`name`
            FROM `'._DB_PREFIX_.'orders` o
            '.Shop::addSqlAssociation('orders', 'o').'
            INNER JOIN `'._DB_PREFIX_.'address` a ON a.id_address = o.id_address_delivery
            INNER JOIN `'._DB_PREFIX_.'country` c ON a.id_country = c.id_country
            INNER JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = '.(int)$this->context->language->id.')
            ORDER BY cl.name ASC');

            $country_array = array();
            foreach ($result as $row) {
                $country_array[$row['id_country']] = $row['name'];
            }

            $part1 = array_slice($this->fields_list, 0, 3);
            $part2 = array_slice($this->fields_list, 3);
            $part1['cname'] = array(
                'title' => $this->l('Delivery'),
                'type' => 'select',
                'list' => $country_array,
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname'
            );
            $this->fields_list = array_merge($part1, $part2);
        }

        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_ORDER;

        if (Tools::isSubmit('id_order')) {
            // Save context (in order to apply cart rule)
            $order = new Order((int)Tools::getValue('id_order'));
            $this->context->cart = new Cart($order->id_cart);
            $this->context->customer = new Customer($order->id_customer);
        }

        $this->bulk_actions = array(
            'updateOrderStatus' => array('text' => $this->l('Change Order Status'), 'icon' => 'icon-refresh')
        );

        if (Module::getInstanceByName('dynamicparceldistribution')->isEnabled('dynamicparceldistribution')) {
            $this->bulk_actions['printDpdLabels'] = array('text' => $this->l('Print DPD Labels'), 'icon' => 'icon-print');
            $this->bulk_actions['printDpdLabelsMPS'] = array('text' => $this->l('Print MPS DPD Labels'), 'icon' => 'icon-print');
            $this->bulk_actions['printDpdManifest'] = array('text' => $this->l('Print DPD Manifest'),
                'icon' => 'icon-print',
                'confirm' => $this->l('Are you sure you want to print manifest, because after doing this you wont be able to print labels? '));
        }

        if (Configuration::get('MULTISHIPPING_ENABLED')) {
            $this->bulk_actions['GenerateMultishippingXML'] = array('text' => $this->l('Generuoti XML'));
        }

        //parent::__construct();
        AdminController::__construct();
        //Version is Bigger that 1.6
    }

    /**
     * Override for Prestashop 1.5v
     */
    private function initToolbar15()
    {
        if ($this->display == 'view') {
            $order = new Order((int)Tools::getValue('id_order'));
            if ($order->hasBeenShipped()) {
                $type = $this->l('Return products');
            } elseif ($order->hasBeenPaid()) {
                $type = $this->l('Standard refund');
            } else {
                $type = $this->l('Cancel products');
            }

            if (!$order->hasBeenShipped() && !$this->lite_display) {
                $this->toolbar_btn['new'] = array(
                    'short' => 'Create',
                    'href' => '#',
                    'desc' => $this->l('Add a product'),
                    'class' => 'add_product'
                );
            }

            if (Configuration::get('PS_ORDER_RETURN') && !$this->lite_display) {
                $this->toolbar_btn['standard_refund'] = array(
                    'short' => 'Create',
                    'href' => '',
                    'desc' => $type,
                    'class' => 'process-icon-standardRefund'
                );
            }

            if ($order->hasInvoice() && !$this->lite_display) {
                $this->toolbar_btn['partial_refund'] = array(
                    'short' => 'Create',
                    'href' => '',
                    'desc' => $this->l('Partial refund'),
                    'class' => 'process-icon-partialRefund'
                );
            }
        }

        if (DynamicParcelDistribution::isEnabled('dynamicparceldistribution')
            && Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'ALLOW_COURIER_PICKUP')) {
            $this->toolbar_btn['call_carrier'] = array(
                'short' => 'Create',
                'href' => '#',
                'desc' => $this->l('Call DPD Carrier'),
                'class' => 'process-icon-partialRefund',
                'js' => 'showCarrierWindow()',
            );
        }
        if (Configuration::get('MULTISHIPPING_ENABLED')) {
            $this->bulk_actions['GenerateMultishippingXML'] = array('text' => $this->l('Generuoti XML'));
        }

        $res = parent::initToolbar();
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && isset($this->toolbar_btn['new']) && Shop::isFeatureActive()) {
            unset($this->toolbar_btn['new']);
        }
        return $res;
    }

    /**
     * Override for Prestashop 16
     */
    private function initToolbar16()
    {
        if ($this->display == 'view') {
            /** @var Order $order */
            $order = $this->loadObject();
            $customer = $this->context->customer;

            if (!Validate::isLoadedObject($order)) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
            }

            $this->toolbar_title[] = sprintf($this->l('Order %1$s from %2$s %3$s'), $order->reference, $customer->firstname, $customer->lastname);
            $this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);

            if ($order->hasBeenShipped()) {
                $type = $this->l('Return products');
            } elseif ($order->hasBeenPaid()) {
                $type = $this->l('Standard refund');
            } else {
                $type = $this->l('Cancel products');
            }

            if (!$order->hasBeenShipped() && !$this->lite_display) {
                $this->toolbar_btn['new'] = array(
                    'short' => 'Create',
                    'href' => '#',
                    'desc' => $this->l('Add a product'),
                    'class' => 'add_product'
                );
            }

            if (Configuration::get('PS_ORDER_RETURN') && !$this->lite_display) {
                $this->toolbar_btn['standard_refund'] = array(
                    'short' => 'Create',
                    'href' => '',
                    'desc' => $type,
                    'class' => 'process-icon-standardRefund'
                );
            }

            if ($order->hasInvoice() && !$this->lite_display) {
                $this->toolbar_btn['partial_refund'] = array(
                    'short' => 'Create',
                    'href' => '',
                    'desc' => $this->l('Partial refund'),
                    'class' => 'process-icon-partialRefund'
                );
            }
        }

        if (class_exists('DynamicParcelDistribution')) {
            if (DynamicParcelDistribution::isEnabled('dynamicparceldistribution')
                && Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'ALLOW_COURIER_PICKUP')) {
                $this->toolbar_btn['call_carrier'] = array(
                    'short' => 'Create',
                    'href' => '#',
                    'desc' => $this->l('Call DPD Carrier'),
                    'class' => 'process-icon-callCarrier',
                    'js' => 'showCarrierWindow()',
                );
            }
        }
        $res = parent::initToolbar();
        if (Context::getContext()->shop->getContext() != Shop::CONTEXT_SHOP && isset($this->toolbar_btn['new']) && Shop::isFeatureActive()) {
            unset($this->toolbar_btn['new']);
        }
        return $res;
    }

    /**
     * Function of Bulk Action to Print DPD Manifest
     * @return Mix
     */
    public function processBulkPrintDpdManifest()
    {
        $manifest = Module::getInstanceByName('dynamicparceldistribution');
        $pathToFile = $manifest->getPdfFile('manifest', Tools::getValue('orderBox'));
        foreach (DpdManifestRender::getWarningMessage() as $message) {
            $this->displayWarning($this->l($message));
        }
        if ($pathToFile === false) {
            foreach (DpdManifestRender::getErrorMessage() as $message) {
                $this->errors[] = $this->l($message);
            }
        } else {
            $manifest->download($pathToFile, 'manifest.pdf', 'application/pdf');
        }
    }

    /**
     * Function of Bulk Action to Print DPD Labels
     * @return mix
     */
    public function processBulkPrintDpdLabels()
    {
        $label = Module::getInstanceByName('dynamicparceldistribution');
        $pathToFile = $label->getPdfFile('label', Tools::getValue('orderBox'), 'join');
        foreach (DpdLabelRender::getWarningMessage() as $message) {
            $this->displayWarning($this->l($message));
        }

        if ($pathToFile === false) {
            foreach (DpdLabelRender::getErrorMessage() as $message) {
                $this->errors[] = $this->l($message);
            }
        } else {
            $label->download($pathToFile, 'label.pdf', 'application/pdf');
        }
        return '';
    }

    public function processBulkPrintDpdLabelsMPS()
    {
        $label = Module::getInstanceByName('dynamicparceldistribution');
        $pathToFile = $label->getPdfFile('label', Tools::getValue('orderBox'), 'split');
        foreach (DpdLabelRender::getWarningMessage() as $message) {
            $this->displayWarning($this->l($message));
        }
        if ($pathToFile === false) {
            foreach (DpdLabelRender::getErrorMessage() as $message) {
                $this->errors[] = $this->l($message);
            }
        } else {
            $label->download($pathToFile, 'label.pdf', 'application/pdf');
        }
        return '';
    }

    /**
     * Function of bulk Action to get Multishipping XML
     * @return XML
     */
    public function processBulkGenerateMultishippingXML()
    {
        $order_ids = Tools::getValue('orderBox');
        $multishipping = Module::getInstanceByName('multishipping');
        $multishipping->createXML($order_ids);
    }

    /**
     * Function to compare versions for Override
     */
    public static function versionCompare($v1, $v2, $operator = '<')
    {
        Tools::alignVersionNumber($v1, $v2);
        return version_compare($v1, $v2, $operator);
    }
}
