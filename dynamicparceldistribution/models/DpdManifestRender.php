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

class DpdManifestRender
{
    /**
     * array of messages warning and errors
     *
     * @var array
     */
    public static $errorMessages = array('error' => array(), 'warning' => array());

    /**
     * DPD Requisitions who need to Manifest header
     *
     * @var array
     */
    private static $dpdRequisition = array(
            'LT' => array(
                'name' => 'DPD LIETUVA',
                'pvm' => 'LT1163929217',
                'address' => 'LIEPKALNIO G. 180',
                'tel' => '+370 52106777',
                'fax' => '+370 52106740'),
            'LV' => array(
                'name' => 'DPD LATVIJA',
                'pvm' => 'LV 40003393255',
                'address' => 'URIEKSTES 8A',
                'tel' => '+371 67 385 240',
                'fax' => '+371 67 387 288'),
        );

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Manifest generator
     *
     * @param  array $parameters some parameters by default id order id's
     * @return mix             if something is wrong return false
     */
    public static function getManifest($parameters)
    {
        $availableOrders = self::validateParams($parameters); //select only available orders
        if ($availableOrders === false) {//If something wrong return false
            return false;
        }

        if (0) {//This is for future
            $pdfContent = self::getManifestFromAPI($availableOrders);
        } else {
            $pdfContent = self::generateManifest($availableOrders); //Get Manifest PDF content
        }

        if (count(self::getErrorMessage(false))
            || count(self::getWarningMessage(false))) {
            return false;
        }

        $pathToFile = self::saveFileContent($pdfContent); //file content put to file

        if ($pathToFile !== false) {
            self::processParcelDataSend(); //Send data to DPD WebLabel about printing Manifest
            self::download(self::getManifestFileName(), $pathToFile, 'application/pdf');
        } else {
            return false;
        }
    }

    /**
     * Say to DPD WebLabel API about printing Manifest to need process current parcels
     *
     * @return boolean
     */
    private static function processParcelDataSend()
    {
        $parameters = array(
            'action' => 'parcel_datasend',
            'date' => date('Y-m-d'),
        );
        $api = new API();
        $apiReturn = $api->postData($parameters);
        if ($apiReturn === false) {
            foreach ($api->getErrorMessage() as $errorMessage) {
                self::registerError($errorMessage);
            }
            return false;
        }
    }

    /**
     * This function not full complete because DPD API not have full manifest report
     *
     * @param  array $availableOrders id_orders
     * @return pdf content from DPD API
     */
    private static function getManifestFromAPI($availableOrders)
    {
        $availableOrders;
        self::registerError('If you reality know what you doing so why you leave this message?');
        return false;

        // $parameters = array(
        //     'action' => 'parcel_manifest_print',
        //     'type' => 'manifest', // manifest, manifest_cod, summary_list
        //     'date' => date('Y-m-d'),
        // );

        // $api = new API();
        // $pdfContent = $api->postData($parameters);
        // return $pdfContent;
    }

    /**
     * Save file content to temp folder to not lost data
     *
     * @param  string $fileConent some file content who need to be saved
     * @return string full path to file with file name
     */
    private static function saveFileContent($fileConent)
    {
        $pathToFile = _PS_PDF_DIR_.'dpd_'.time().'_label';
        $writedBytes = file_put_contents($pathToFile, $fileConent);
        if ($writedBytes === false) {
            self::registerError('Error do not have permission to write to this folder: '._PS_PDF_DIR_);
            return false;
        }
        return $pathToFile;
    }

    /**
     * Return generated Manifest file name
     *
     * @param  array  $parameters some order parameters is for future
     * @return string Label file name
     */
    private static function getManifestFileName($parameters = array())
    {
        $parameters;
        $staticString = 'Manifest-';
        $time = date('Ymd_H-i', time());
        return $staticString.$time.'.pdf';
    }

    /**
     * Render Manifest header template
     *
     * @param  array  $variables some manifest header template variables
     * @return string            HTML content of Manifest header
     */
    private static function getManifestHeaderHTML($variables = array())
    {
        $smarty = new Smarty();
        $smarty->setCaching(false);
        $smarty->assign($variables);
        $manifestHeader = self::getPath('').'views/templates/admin/manifest/header.tpl';
        $manifestHeaderContent = $smarty->fetch($manifestHeader);
        return $manifestHeaderContent;
    }

    /**
     * Render Manifest content template
     *
     * @param  array  $variables some manifest content template variables
     * @return string            HTML content of Manifest content
     */
    private static function getManifestContentHTML($variables = array())
    {
        $smarty = new Smarty();
        $smarty->setCaching(false);
        $smarty->assign($variables);
        $manifestContent = self::getPath('').'views/templates/admin/manifest/content.tpl';
        $manifestContentContent = $smarty->fetch($manifestContent);
        return $manifestContentContent;
    }

    /**
     * Render Manifest before footer template
     *
     * @param  array  $variables some extra data template variables
     * @return string            HTML content of Manifest before footer block
     */
    private static function getManifestExtraHTML($variables = array())
    {
        $smarty = new Smarty();
        $smarty->setCaching(false);
        $smarty->assign($variables);
        $manifestExtra = self::getPath('').'views/templates/admin/manifest/extra.tpl';
        $manifestExtraContent = $smarty->fetch($manifestExtra);
        return $manifestExtraContent;
    }

    /**
     * Render Manifest footer template
     *
     * @param  array  $variables some footer template variables
     * @return string            HTML content of Manifest footer
     */
    private static function getManifestFooterHTML($variables = array())
    {
        $smarty = new Smarty();
        $smarty->setCaching(false);
        $smarty->assign($variables);
        $manifestFooter = self::getPath('').'views/templates/admin/manifest/footer.tpl';
        $manifestFooterContent = $smarty->fetch($manifestFooter);
        return $manifestFooterContent;
    }

    /**
     * Return Manifest PDF content
     *
     * @param  array  $availableOrders - Orders id
     * @return string - Manifest content
     */
    private static function generateManifest($availableOrders = array())
    {
        $manifest_nr = Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'MANIFEST_NR');
        if ($manifest_nr <= 0) {
            $manifest_nr = 1;
        }

        $currenctLanguageIso = Country::getIsoById(Context::getContext()->country->id);
        if (!in_array($currenctLanguageIso, array_keys(self::$dpdRequisition))) {
            $currenctLanguageIso = 'LT'; //Default if language is not LT or not LV
        }

        $headerVariables = array(
                'css' => self::getPath('').'views/css/manifest.css',
                'label_company' => self::$dpdRequisition[$currenctLanguageIso]['name'],
                'value_vat_code' => self::$dpdRequisition[$currenctLanguageIso]['pvm'],
                'value_fax' => self::$dpdRequisition[$currenctLanguageIso]['fax'],
                'value_street' => self::$dpdRequisition[$currenctLanguageIso]['address'],
                'value_phone' => self::$dpdRequisition[$currenctLanguageIso]['tel'],
                'value_logo' => self::getPath('').'views/img/logo.png',

                'label_phone' => self::l('Phone'),
                'label_vat' => self::l('VAT'),
                'label_fax' => self::l('Fax'),
                'label_manifest_nr' => self::l('Manifest no.'),
                'label_client' => self::l('Client'),
                'label_vat_code' => self::l('VAT code'),
                'label_sphone' => self::l('Phone. no.'),
                'label_done_date' => self::l('Closed'),

                'value_manifest_nr' => $manifest_nr,
                'value_done_date' => date('Y m d'),

                'value_client' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_NAME'),
                'value_client_id' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'SERVICE_USERID'),
                'value_client_street' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_STREET'),
                'value_client_vat_code' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_VAT_CODE'),
                'value_client_phone' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_PHONE'),
                'value_client_city' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_CITY'),
                'value_client_post' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_ZIP'),
                'value_client_post' => Configuration::get(DynamicParcelDistribution::CONST_PREFIX.'PICKUP_ADDRESS_ZIP'),
            );
        Configuration::updateValue(DynamicParcelDistribution::CONST_PREFIX.'MANIFEST_NR', $manifest_nr + 1);
        $footerVariables = array(
                'css' => self::getPath('').'views/css/manifest.css',

                'label_additional' => self::l('Additional Services'),
                'label_load' => self::l('Cargo operations to the sender'),
                'label_wait' => self::l('Waiting to sender'),
                'label_smin' => self::l('min.'),
                'text_notification_title' => self::l('Packaging does not guarantee the lot - ISSN'),
                'text_notification' => array(
                    '1' => self::l('UAB DPD Lithuania is not responsible for improperly packed items. Improper packaging - the packaging, which does not protect the contents of the shipment from the normal transportation hazards and can not guarantee that transported the shipment would not hurt other parcels.'),
                    '2' => self::l('(Parcels numbers can be noted ISSN column to the number, or by the box below)'),
                ),
                'text_conditions' => array('1' => self::l('* Check and confirm that the indicated and / or record information is correct'),
                                        '2' => self::l('* I am aware that __ (record amount) package (s) in which the numbers marked ISSN tag and / or above record, the packaging does not guarantee their safety. I agree that these packages are shipped anyway tiokioje packaging'),
                                        '3' => (($currenctLanguageIso!='LV')?(self::l('Packing on my own responsibility')):'')),

                'label_sender' => self::l('Shipper:'),
                'label_courier' => self::l('Courier:'),
                'label_arrived' => self::l('Arrived:'),
                'label_departure' => self::l('Departed:'),

                'label_sender_signature' => self::l('(senders signature)'),
                'label_name_signature' => self::l('(Name and signature)'),
                'label_name_tour_signature' => self::l('(Name, round, signature)'),
                'label_date_time' => self::l('(Date, time)'),
                'label_time' => self::l('(Time)'),
            );
        $contentVariables = array(
                'css' => self::getPath('').'views/css/manifest.css',
                'orders' => array(),
                'label_order_nr' => self::l('Row no.'),
                'label_order_type' => self::l('Prcel type'),
                'label_order_arrival' => self::l('Recipient'),
                'label_order_phone' => self::l('Phone. no.'),
                'label_order_weight' => self::l('Weight'),
                'label_order_number' => self::l('Order no.'),
                'label_order_issn' => self::l('ISSN'),

                'label_total' => self::l('Total:'),
                'label_orders_count' => self::l('Parcel qty.'),
                'label_packages_count' => self::l('Number of packages'),
            );
        self::sortOrders($availableOrders, SORT_ASC);
        $package_barcode_list = array();
        foreach ($availableOrders as $id_order) {
            $orderObject = new Order($id_order);
            $addressObject = new Address((int)$orderObject->id_address_delivery);
            $contentVariables['orders'][] = array(
                    'parcel_type' => DpdLabelRender::getParcelType($id_order),
                    'shipping_firstname' => $addressObject->firstname,
                    'shipping_lastname' => $addressObject->lastname,
                    'shipping_address_1' => $addressObject->address1,
                    'shipping_postcode' => $addressObject->postcode,
                    'shipping_city' => $addressObject->city,
                    'telephone' => ($addressObject->phone !== '')?$addressObject->phone:$addressObject->phone_mobile,
                    'total_weight' => ($orderObject->getTotalWeight())?$orderObject->getTotalWeight():'1',
                    'tracking_number' => DpdLabelRender::getBarcodeFromOrder($id_order),
                );
            foreach (DpdLabelRender::getBarcodeFromOrder($id_order) as $barcode) {
                $package_barcode_list[] = $barcode; //Add barcode to list
            }
        }
        $package_barcode_list = array_unique($package_barcode_list); //Leave only unique barcodes;
        $contentVariables['packages_barcodes_count'] = (float)count($package_barcode_list); //How much barcodes i have?

        $pdf = new PDFManifestGenerator();
        $pdf->createHeader(self::getManifestHeaderHTML($headerVariables)); //Set header info
        $pdf->createFooter(self::getManifestFooterHTML($footerVariables)); //Set Footer info
        $pdf->createContent(self::getManifestContentHTML($contentVariables)); //Set Content info
        $pdf->writePage(); //Write to page
        $manifestContent = $pdf->render(self::getManifestFileName(), 'S');
        return $manifestContent;
    }


    /**
     * Sorder orders
     *
     * @param  pointer array &$availableOrders source array
     * @param  int array argument $direction - SORT_ASC to sort ascendingly or SORT_DESC to sort descendingly
     * @return none, sortered array is set to pointer
     */
    private static function sortOrders(&$availableOrders, $direction = SORT_ASC)
    {
        if (!is_array($availableOrders)) {
            return false;
        }

        $sort_col = array();
        foreach ($availableOrders as $key => $id_order) {
            $sort_col[$key] = DpdLabelRender::getBarcodeFromOrder($id_order);
        }

        array_multisort($sort_col, $direction, $availableOrders);
    }

    /**
     * Get full path to module directory
     *
     * @param  directory extension $dirname some directory in module folder
     * @return string          patch to folder
     */
    private static function getPath($dirname)
    {
        return _PS_MODULE_DIR_.DynamicParcelDistribution::$module_name.'/'.$dirname;
    }

    /**
     * Validating of parameters is all correct to get manifest
     *
     * @param  array $parameters - validating parameters
     * @return array | Boolean - array of available orders id; false - something wrong
     */
    public static function validateParams($parameters)
    {
        if (!is_array($parameters)) {
            return false;
        }

        foreach ($parameters as $key => $param) {
            switch ($key) {
                case 'id_orders':
                    if ($param === false) {
                        self::registerError('Please select Orders!');
                    }
                    if (!is_array($param)) {
                        return false;
                    }
                    $availableOrders = array();

                    //carrier is available
                    foreach ($param as $id_order) {
                        if (self::getAvailableCarrier($id_order)) {
                            $availableOrders[] = $id_order;
                        }
                    }

                    //remove order id without barcodes
                    foreach ($availableOrders as $key => $id_order) {
                        if (!DpdLabelRender::hasBarcode($id_order)) {
                            unset($availableOrders[$key]);
                        }
                    }

                    if (!count($availableOrders)) {
                        self::registerError('Wrong select Orders!');
                    }
                    break;
            }
        }
        if (count(self::getErrorMessage(false))) {
            return false;
        }
        return $availableOrders;
    }

    /**
     * Test carrier by order id, if this is my carrier return true
     *
     * @param  int $id_order order id
     * @return boolean           true if order shipping carrier is DPD method
     */
    public static function getAvailableCarrier($id_order)
    {
        $order = new Order($id_order);
        $orderCarrierId = $order->id_carrier;
        //$orderPaymentId = $order->module;
        unset($order);

        $carrier = new Carrier($orderCarrierId);
        $carrierName = $carrier->external_module_name;
        unset($carrier);

        if ($carrierName !== DynamicParcelDistribution::$module_name
            && $carrierName !== 'balticode_dpd_parcelstore'
            && $carrierName !== 'balticode_dpd_courier') {
            self::registerWarning('Order: '.$id_order.' is not a DPD shipping method');
            return false;
        }
        return true;
    }

    /**
     * Register error message to private array
     *
     * @param  string $errorMessage some text
     */
    public static function registerError($errorMessage)
    {
        self::$errorMessages['error'][] = $errorMessage;
    }

    /**
     * Return error messages from private array
     *
     * @param  boolean $clear if true array has been cleared,
     *                        if false messages has been leaved
     * @return array         array of messages
     */
    public static function getErrorMessage($clear = true)
    {
        $messages = self::$errorMessages['error'];
        if ($clear) {
            self::$errorMessages['error'] = array();
        }
        return $messages;
    }

    /**
     * Register warning message to private array
     *
     * @param  string $warningMessage some text
     */
    public static function registerWarning($warningMessage)
    {
        self::$errorMessages['warning'][] = $warningMessage;
    }

    /**
     * Return Warning messages from private array
     *
     * @param  boolean $clear if true array has been cleared,
     *                        if false messages has been leaved
     * @return array         array of messages
     */
    public static function getWarningMessage($clear = true)
    {
        $messages = self::$errorMessages['warning'];
        if ($clear) {
            self::$errorMessages['warning'] = array();
        }
        return $messages;
    }

    /**
     * File content create to download dialog box
     *
     * @param  string $fileName   - Downloading file name
     * @param  string $pathToFile - Temp file location
     * @param  string $fileType   - mime type of downloading file
     * @return mime header with file content
     */
    private static function download($fileName, $pathToFile, $fileType)
    {
        if (!file_exists($pathToFile)) {
            return false;
        }

        $content = Tools::file_get_contents($pathToFile);
        unlink($pathToFile);
        $cretedownload = new Createdownload($fileName, $fileType);
        $cretedownload->render($content);
    }

    /**
     * Function translate string to another languages
     * First search in local file
     *
     * @param  string $string some text
     * @return string         translated some text
     */
    private static function l($string)
    {
        if (!is_string($string)) {
            return false;
        }
        return Module::getInstanceByName(DynamicParcelDistribution::$module_name)->l($string);
    }
}
