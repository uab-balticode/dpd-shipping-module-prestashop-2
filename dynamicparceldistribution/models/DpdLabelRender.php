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

class DpdLabelRender
{
    /**
     * DPD parcel type is available
     * This list is very big to leaved lines only who need to us
     *
     * @var array
     */
    private static $dpd_available_parcel_types = array(
        array('service_code' => '803',
            'servce_description' => 'Parcel Shop',
            'parcel_type' => 'PS',
            'service_elements' => '601',
            'service_mark' => '',
            'service_text' => 'PS'),

        array('service_code' => '329',
            'servce_description' => 'Normal Parcel, COD, B2C',
            'parcel_type' => 'D-COD-B2C',
            'service_elements' => '001,013,100',
            'service_mark' => '',
            'service_text' => 'D-COD-B2C'),

        array('service_code' => '327',
            'servce_description' => 'Normal Parcel, B2C',
            'parcel_type' => 'D-B2C',
            'service_elements' => '1,013',
            'service_mark' => '',
            'service_text' => 'D-B2C'),
        );

    /**
     * Some array with dynamic messages about errors or warnings
     *
     * @var array
     */
    public static $errorMessages = array('error' => array(), 'warning' => array());

    /**
     * Static text to start comment in Order
     * By this Prefix system search Barcodes, so when change this value, system cant found old barcodes
     * Be careful when edit this line
     *
     * @var string
     */
    public static $messagePrefix = 'DPD: ';

    /**
     * Number is using when client is old and parcel Number calculation by one
     * So set this number to continue count
     *
     * @var integer
     */
    private static $addictional_order_number = 0;

    /**
     * Class constructor
     */
    public function __construct()
    {

    }

    /**
     * Return DPD Label from API if Available
     *
     * @param  array of parameters important is orders id
     * @param  string $action join, or split (join = multi orders get one label, Split = label per different order item)
     * @return mix if false some error is, else go to Barcode PDF content
     */
    public static function getLabel(array $parameters, $action)
    {
        //mb_internal_encoding("UTF-8");

        $availableOrders = self::validateParams($parameters); //select only available orders
        if ($availableOrders === false) {//If something wrong return false
            return false;
        }
        self::generateBarcode($availableOrders, $action); //Generate Barcodes from available orders
        if (count(self::getErrorMessage(false))
            || count(self::getWarningMessage(false))) {
            return false;
        }
        $pdfContent = self::generateLabels($availableOrders); //Get Label PDF content
        if (count(self::getErrorMessage(false))
            || count(self::getWarningMessage(false))) {
            return false;
        }

        $pathToFile = self::saveFileContent($pdfContent);
        if ($pathToFile !== false) {
            self::download(self::getLabelFileName($parameters), $pathToFile, 'application/pdf');
        } else {
            return false;
        }
    }

    /**
     * Save file content to temp folder to not lost data
     *
     * @param  string $fileConent some file content who need to be saved
     * @return string full path to file with file name
     */
    private static function saveFileContent($fileConent)
    {
        $pathToFile = _PS_PDF_DIR_.'dpd_'.time().'_label.pdf';
        $writedBytes = file_put_contents($pathToFile, $fileConent);
        if ($writedBytes === false) {
            self::registerError(self::l('Error do not have permission to write to this folder').': '._PS_PDF_DIR_);
            return false;
        }
        return $pathToFile;
    }

    /**
     * Return generated Labels file name
     *
     * @param  array  $parameters some order parameters is for future
     * @return string Label file name
     */
    private static function getLabelFileName($parameters = array())
    {
        $parameters; //This is for validation;
        $staticString = 'Labels-';
        $time = date('Ymd_H-i', time());
        return $staticString.$time.'.pdf';
    }

    /**
     * Return Labels from DPD API by available orders id
     *
     * @param  array  $availableOrders - Orders id
     * @return string - Label content
     */
    private static function generateLabels($availableOrders = array())
    {
        $barcodes = array();
        $barcodes_array = self::getBarcodeByOrderIds($availableOrders);
        foreach ($barcodes_array as $order_barcodes) {
            foreach ($order_barcodes as $single_barcode) {
                $barcodes[] = $single_barcode;
            }
        }

        //Unique barcodes by all orders (MPS has one barcode for multiple orders)
        $barcodes = array_filter(array_unique(array_values($barcodes)));

        $returnDetails = array(
                'action' => 'parcel_print',
                'parcels' => join('|', $barcodes),
            );
        $api = new API();
        $apiReturn = $api->postData($returnDetails);

        if ($apiReturn === false) {
            foreach ($api->getErrorMessage() as $errorMessage) {
                self::registerError($errorMessage);
            }
            return false;
        }

        if (!API::isPdf($apiReturn)) {
            self::registerError(self::l('Error: DPD API return not PDF file format'));
            return false;
        }
        return $apiReturn;
    }

    /**
     * Validating of parameters is all correct to get Labels
     *
     * @param  array $parameters - validating parameters
     * @return array | Boolean - array of available orders id; false - something wrong
     */
    public static function validateParams(array $parameters)
    {
        if (!is_array($parameters)) {
            return false;
        }
        foreach ($parameters as $key => $param) {
            switch ($key) {
                case 'id_orders':
                    if ($param === false) {
                        self::registerError(self::l('Please select Orders!'));
                    }
                    if (!is_array($param)) {
                        return false;
                    }
                    $availableOrders = array();
                    foreach ($param as $id_order) {
                        if (self::getAvailableCarrier($id_order)) { //This order is DPD method?
                            $availableOrders[] = $id_order;
                        }
                    }
                    if (!count($availableOrders)) {
                        self::registerError(self::l('Wrong select Orders!'));
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
     * Return available currier to get some info for e.g. Labels
     * Order currier method is a DPD method?
     *
     * @param  string - Order Id
     * @return Boolean - this method is DPD method?
     */
    public static function getAvailableCarrier($id_order)
    {
        $order = new Order($id_order);
        $orderCarrierId = $order->id_carrier;
        $carrier = new Carrier($orderCarrierId);
        $carrierExternalName = $carrier->external_module_name;
        unset($order);
        unset($carrier);
        //This is a DPD delivery method?
        if ($carrierExternalName !== DynamicParcelDistribution::$module_name
            && $carrierExternalName !== DynamicParcelDistribution::$module_name
            && $carrierExternalName !== 'balticode_dpd_parcelstore'
            && $carrierExternalName !== 'balticode_dpd_courier') {
            self::registerWarning(self::l('Order: ').$id_order.self::l(' is not a DPD shipping method'));
            return false;
        }
        //Available Payment for this delivery payment? (ParcelStore)
        if (($carrierExternalName == 'balticode_dpd_parcelstore'
            || $carrierExternalName == DynamicParcelDistribution::$module_name)
            && !DynamicParcelDistribution::availablePaymentMethod($id_order, $orderCarrierId)) {
            self::registerWarning(self::l('Order: ').$id_order.self::l(' Payment is not available for this delivery method'));
            return false;
        }
        //Available Payment for this delivery payment? (Carrier)
        if (($carrierExternalName == 'balticode_dpd_courier'
            || $carrierExternalName == DynamicParcelDistribution::$module_name)
            && !DynamicParcelDistribution::availablePaymentMethod($id_order, $orderCarrierId)) {
            self::registerWarning(self::l('Order: ').$id_order.self::l(' Payment is not available for this delivery method'));
            return false;
        }
        return true;
    }

    /**
     * Add error text to array
     *
     * @param  string - Error Message
     * @return mix - Self class;
     */
    public static function registerError($errorMessage)
    {
        self::$errorMessages['error'][] = $errorMessage;
        return __CLASS__;
    }

    /**
     * Get all registered error messages
     *
     * @param  boolean - clear array after read messages
     * @return array - registered error messages
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
     * Add Warning text to array
     *
     * @param  string -  Warning Messages
     * @return mix - self class;
     */
    public static function registerWarning($warningMessage)
    {
        self::$errorMessages['warning'][] = $warningMessage;
        return __CLASS__;
    }

    /**
     * Get all registered warning messages
     *
     * @param  boolean - clear array after read messages
     * @return array - registered messages
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
     * Generate of Barcode, Send data about order to DPD API
     * Grab errors or Barcode number jSon format
     *
     * @param  array - all available orders id
     * @param  string $action join, or split (join = multi orders get one label, Split = label per different order item)
     * @return mix
     */
    private static function generateBarcode($availableOrders = array(), $action = 'join')
    {
        if (!count($availableOrders)) {//If no orders id found return false
            return false;
        }

        $differenceOrders = self::groupOrders($availableOrders, $action); //group orders this for MPS method
        $api = new API();
        //Generate Barcodes by single orders
        foreach ($differenceOrders['single'] as $id_delivery => $typed_orders) {
            foreach ($typed_orders as $type_order => $id_order) {
                if (self::hasBarcode($id_order[0])) {
                    continue;
                }

                if ($type_order == 'PS') {
                    $dpdOrderOptions = DpdOrderOptions::getOrderOptions((int)$id_order[0]);
                    $dpdDeliveryOption = unserialize($dpdOrderOptions[0]['delivery_option']);
                    $parcelShop = DpdDeliveryPoints::getParcelStore($dpdDeliveryOption['delivery_option']);
                    $parcelShop = $parcelShop[0];
                    $orderObject = new Order((int)$id_order[0]);
                    $addressObject = new Address($id_delivery);
                    $message = self::getMessageFromOrder((int)$id_order[0], null, false);

                    $returnDetails = array(
                        'name1' => $addressObject->firstname.' '.$addressObject->lastname,
                        'name2' => $parcelShop['company'],
                        'street' => $parcelShop['street'],
                        'pcode' => preg_replace('/\D/', '', $parcelShop['pcode']),
                        'country' => $parcelShop['country'],//Country code in iso2 character code format (Lithuania is LT)
                        'city' => $parcelShop['city'],
                        'weight' => ($orderObject->getTotalWeight())?$orderObject->getTotalWeight():'1',
                        'phone' => ($addressObject->phone !== '')?$addressObject->phone:$addressObject->phone_mobile,
                        'remark' => (isset($message[0])?$message[0]:''),
                        'parcelshop_id' => $dpdDeliveryOption['delivery_option'],
                        'num_of_parcel' => '1',
                        'order_number' => str_pad((int)$id_order[0]+self::$addictional_order_number, 10, '0', STR_PAD_LEFT),
                        'idm' => 'Y', //Parcelshop is required the idm parameters
                        'idm_sms_rule' => 902, //Write the sum amount of the chosen SMS rules:
                                        // 1 – pickup                0b1000000
                                        // 2 – non delivery        0b0100000
                                        // 4 – delivery            0b0010000
                                        // 8 – inbound                0b0001000
                                        // 16 – out for delivery    0b0000100
                        'parcel_type' => (string)$type_order,
                        'action' => 'parcel_import'
                    );
                    $apiReturn = $api->postData($returnDetails);

                    if ($apiReturn === false) {
                        foreach ($api->getErrorMessage() as $errorMessage) {
                            self::registerError($errorMessage);
                            self::addMessageToOrder((int)$id_order[0], $errorMessage, 'DPD ERROR: ');
                        }
                        return false;
                    }
                    if ($apiReturn->status == 'ok') {
                        if (!empty($apiReturn->errlog)) {
                            self::registerWarning($apiReturn->errlog);
                        }
                        foreach ($apiReturn->pl_number as $barcode) {
                            self::setBarcodeToOrder((int)$id_order[0], $barcode);
                        }
                    }
                } else {
                    $dpdOrderOptions = DpdOrderOptions::getOrderOptions((int)$id_order[0]);
                    $dpdDeliveryOption = unserialize($dpdOrderOptions[0]['delivery_option']); //Delivery time ID
                    $message = self::getMessageFromOrder((int)$id_order[0], null, false);

                    $orderObject = new Order((int)$id_order[0]);
                    $addressObject = new Address($id_delivery);
                    $returnDetails = array(
                        'name1' => $addressObject->firstname.' '.$addressObject->lastname,
                        //'name2' => '',
                        'street' => $addressObject->address1,
                        'pcode' => preg_replace('/\D/', '', $addressObject->postcode),
                        'country' => country::getIsoById($addressObject->id_country), //Country code in iso2 character code format (Lithuania is LT)
                        'city' => $addressObject->city,
                        'weight' => ($orderObject->getTotalWeight())?$orderObject->getTotalWeight():'1',
                        'phone' => ($addressObject->phone !== '')?$addressObject->phone:$addressObject->phone_mobile,
                        'remark' => (isset($message[0])?$message[0]:''),
                        'num_of_parcel' => '1',
                        'order_number' => str_pad((int)$id_order[0]+self::$addictional_order_number, 10, '0', STR_PAD_LEFT),
                        'idm' => 'Y', //Parcelshop is required the idm parameters
                        'idm_sms_rule' => 1, //Write the sum amount of the chosen SMS rules:
                                        // 1 – pickup                0b1000000
                                        // 2 – non delivery        0b0100000
                                        // 4 – delivery            0b0010000
                                        // 8 – inbound                0b0001000
                                        // 16 – out for delivery    0b0000100
                                        // 902 (when using PS type, then the value MUST be );
                        'parcel_type' => (string)$type_order,
                        'action' => 'parcel_import'
                    );

                    $paymentMethod = DynamicParcelDistribution::getPaymentMethod((int)$id_order[0]);
                    if (DynamicParcelDistribution::isCodMethod($paymentMethod)) {
                        $returnDetails['cod_amount'] = (float)$orderObject->total_paid;
                    }

                    $delivery_time = DynamicParcelDistribution::getDeliveryTime($dpdDeliveryOption['delivery_option']); //example 8 - 14
                    $delivery_time = array_filter(explode('-', $delivery_time));
                    
                    $timeTo = $timeFrom = null;
                    if ($delivery_time !== null) {//If something is set
                        if (count($delivery_time) !== 2) {//It is two digits?
                            self::registerError(self::l('Error with delivery time stamp found not two digits given:').serialize($delivery_time));
                            return false;
                        } else {
                            $timeFrom = DynamicParcelDistribution::toTimeStamp($delivery_time[0], 'H:i');
                            $timeTo = DynamicParcelDistribution::toTimeStamp($delivery_time[1], 'H:i');
                        }
                    }
                    if ($timeFrom !== null && $timeTo !== null) { //Add time is set
                        $returnDetails['timeframe_from'] = $timeFrom; //Syntax of the value HH24:MI, example 14:00
                        $returnDetails['timeframe_to'] = $timeTo; //Syntax of the value HH24:MI, example 14:00
                    }

                    $apiReturn = $api->postData($returnDetails);
                    if ($apiReturn === false) {
                        foreach ($api->getErrorMessage() as $errorMessage) {
                            self::addMessageToOrder((int)$id_order[0], $errorMessage, 'DPD ERROR: ');
                            self::registerError($errorMessage);
                        }
                        return false;
                    }
                    if ($apiReturn->status == 'ok') {
                        if (!empty($apiReturn->errlog)) {
                            self::registerWarning($apiReturn->errlog);
                        }
                        foreach ($apiReturn->pl_number as $barcode) {
                            self::setBarcodeToOrder((int)$id_order[0], $barcode);
                        }
                    }
                }
            }
        }

        //Generate Barcodes by multiple orders -> MPS (join)
        foreach ($differenceOrders['multi'] as $id_delivery => $typed_orders) {
            $type_order = array_keys($typed_orders);
            $type_order = $type_order[0];
            if ($type_order == 'PS') {
                continue;
            }

            foreach ($typed_orders as $type_order => $id_orders) {
                $cod_amount = 0;
                $weight = (float)0.0;
                foreach ($id_orders as $id_order) {
                    $orderObject = new Order((int)$id_order);
                    $cod_amount += $orderObject->total_paid;
                    $weight += (float)(($orderObject->getTotalWeight())?$orderObject->getTotalWeight():'1');
                    $orderOptions = DpdOrderOptions::getOrderOptions((int)$id_order); //get Order options (Delivery time set)
                    $dpdDeliveryOption = unserialize($orderOptions[0]['delivery_option']); //Delivery time ID
                    $delivery_time[] = DynamicParcelDistribution::getDeliveryTime($dpdDeliveryOption['delivery_option']); //example 8 - 14
                }
            }

            $delivery_time = array_unique($delivery_time); //Get only unique time stamps

            if (count($delivery_time) === 1) { //if we have only one time stamp, correct, all time stamps are same
                $delivery_time = array_filter(explode('-', $delivery_time[0]));
            } else {
                $delivery_time = null;
            }

            //We need just first order address, another else is same address because it is a MPS
            $firstOrderId = $typed_orders[$type_order][0];
            $orderObject = new Order((int)$firstOrderId);
            $addressObject = new Address($id_delivery);
            $message = self::getMessageFromOrder((int)$firstOrderId, null, false);
            $returnDetails = array(
                'name1' => $addressObject->firstname.' '.$addressObject->lastname,
                //'name2' => '',
                'street' => $addressObject->address1,
                'pcode' => preg_replace('/\D/', '', $addressObject->postcode),
                'country' => country::getIsoById($addressObject->id_country), //Country code in iso2 character code format (Lithuania is LT)
                'city' => $addressObject->city,
                'weight' => ($weight)?$weight:'1',
                'phone' => ($addressObject->phone !== '')?$addressObject->phone:$addressObject->phone_mobile,
                'remark' => (isset($message[0]))?$message[0]:'',
                'num_of_parcel' => '1',
                'order_number' => str_pad((int)$firstOrderId+self::$addictional_order_number, 10, '0', STR_PAD_LEFT),
                'idm' => 'Y', //Parcelshop is required the idm parameters
                'idm_sms_rule' => 1, //Write the sum amount of the chosen SMS rules:
                                    // 1 – pickup                0b1000000
                                    // 2 – non delivery        0b0100000
                                    // 4 – delivery            0b0010000
                                    // 8 – inbound                0b0001000
                                    // 16 – out for delivery    0b0000100
                                    // 902 (when using PS type, then the value MUST be );
                'parcel_type' => (string)$type_order,
                'action' => 'parcel_import',
            );
            $paymentMethod = DynamicParcelDistribution::getPaymentMethod((int)$firstOrderId);
            if (DynamicParcelDistribution::isCodMethod($paymentMethod)) {
                $returnDetails['cod_amount'] = $cod_amount;
            }

            $timeFrom = $timeTo = null;
            if ($delivery_time !== null) {//If something is set
                if (count($delivery_time) !== 2) {//It is two digits?
                    self::registerError(self::l('Error with delivery time stamp found not two digits given:').serialize($delivery_time));
                    return false;
                } else {
                    $timeFrom = DynamicParcelDistribution::toTimeStamp($delivery_time[0], 'H:i');
                    $timeTo = DynamicParcelDistribution::toTimeStamp($delivery_time[1], 'H:i');
                }
            }
            if ($timeFrom !== null && $timeTo !== null) { //Add time is set
                $returnDetails['timeframe_from'] = $timeFrom; //Syntax of the value HH24:MI, example 14:00
                $returnDetails['timeframe_to'] = $timeTo; //Syntax of the value HH24:MI, example 14:00
            }

            $apiReturn = $api->postData($returnDetails); //Get data form DPD API
            if ($apiReturn === false) {
                foreach ($api->getErrorMessage() as $errorMessage) {
                    self::addMessageToOrder((int)$id_order[0], $errorMessage, 'DPD ERROR: ');
                    self::registerError($errorMessage);
                }
                return false;
            }
            if ($apiReturn->status == 'ok') {
                if (!empty($apiReturn->errlog)) {
                    self::registerWarning($apiReturn->errlog);
                }

                foreach ($typed_orders as $id_orders) {
                    foreach ($apiReturn->pl_number as $barcode) {
                        foreach ($id_orders as $id_order) {
                            self::setBarcodeToOrder((int)$id_order, $barcode);
                        }
                    }
                }
            }
        }
        //Generate Barcodes by orders -> MPS (split)
        $total_num_of_parcels = 0;
        foreach ($differenceOrders['mps'] as $id_delivery => $typed_orders) {
            $num_of_parcel = 0;
            $weight = (float)0.0;
            $delivery_time = array();
            foreach ($typed_orders as $type_order => $id_orders) {
                $firstOrderId = $id_orders[0];
                $cod_amount = 0;
                foreach ($id_orders as $id_order) {
                    //We need just first order address, another else is same address because it is a MPS
                    $type_order = array_keys($typed_orders);
                    $type_order = $type_order[0];
                    if ($type_order == 'PS') {
                        break;
                    }
                    $orderObject = new Order((int)$id_order);
                    $cod_amount += $orderObject->total_paid;
                    $num_of_parcel += count(BalticodeDpdData::getCartProducts(new Cart((int)$orderObject->id_cart)));
                    $total_num_of_parcels += count(BalticodeDpdData::getCartProducts(new Cart((int)$orderObject->id_cart)));
                    $weight += (float)(($orderObject->getTotalWeight())?$orderObject->getTotalWeight():'1');
                    $orderOptions = DpdOrderOptions::getOrderOptions((int)$id_order); //get Order options (Delivery time set)
                    $dpdDeliveryOption = unserialize($orderOptions[0]['delivery_option']); //Delivery time ID
                    $delivery_time[] = DynamicParcelDistribution::getDeliveryTime($dpdDeliveryOption['delivery_option']); //example 8 - 14
                }
            }

            $delivery_time = array_unique($delivery_time); //Get only unique time stamps
            if (count($delivery_time) === 1) { //if we have only one time stamp, correct, all time stamps are same
                $delivery_time = array_filter(explode('-', $delivery_time[0]));
            } else {
                $delivery_time = null;
            }

            $message = self::getMessageFromOrder((int)$firstOrderId, null, false);
            $addressObject = new Address($id_delivery);
            $returnDetails = array(
                'name1' => $addressObject->firstname.' '.$addressObject->lastname,
                //'name2' => '',
                'street' => $addressObject->address1,
                'pcode' => preg_replace('/\D/', '', $addressObject->postcode),
                'country' => country::getIsoById($addressObject->id_country), //Country code in iso2 character code format (Lithuania is LT)
                'city' => $addressObject->city,
                'weight' => $weight,
                'phone' => ($addressObject->phone !== '')?$addressObject->phone:$addressObject->phone_mobile,
                'remark' => (isset($message[0])?$message[0]:''),
                'num_of_parcel' => $num_of_parcel,

                'order_number' => str_pad((int)$firstOrderId+self::$addictional_order_number, 10, '0', STR_PAD_LEFT),
                'idm' => 'Y', //Parcelshop is required the idm parameters
                'idm_sms_rule' => 1, //Write the sum amount of the chosen SMS rules:
                                    // 1 – pickup                0b1000000
                                    // 2 – non delivery            0b0100000
                                    // 4 – delivery                0b0010000
                                    // 8 – inbound                0b0001000
                                    // 16 – out for delivery    0b0000100
                                    // 902 (when using PS type, then the value MUST be );
                'parcel_type' => (string)$type_order,
                'action' => 'parcel_import',
            );

            $paymentMethod = DynamicParcelDistribution::getPaymentMethod((int)$firstOrderId);
            if (DynamicParcelDistribution::isCodMethod($paymentMethod)) {
                $returnDetails['cod_amount'] = $cod_amount;
            }

            $timeTo = $timeFrom = null;
            if ($delivery_time !== null) {//If something is set
                if (count($delivery_time) !== 2) {//It is two digits?
                    self::registerError(self::l('Error with delivery time stamp found not two digits given:').serialize($delivery_time));
                    return false;
                } else {
                    $timeFrom = DynamicParcelDistribution::toTimeStamp($delivery_time[0], 'H:i');
                    $timeTo = DynamicParcelDistribution::toTimeStamp($delivery_time[1], 'H:i');
                }
            }
            if ($timeFrom !== null && $timeTo !== null) { //Add time is set
                $returnDetails['timeframe_from'] = $timeFrom; //Syntax of the value HH24:MI, example 14:00
                $returnDetails['timeframe_to'] = $timeTo; //Syntax of the value HH24:MI, example 14:00
            }

            $apiReturn = $api->postData($returnDetails);
            if ($apiReturn === false) {
                foreach ($api->getErrorMessage() as $errorMessage) {
                    self::addMessageToOrder((int)$id_order[0], $errorMessage, 'DPD ERROR: ');
                    self::registerError($errorMessage);
                }
                return false;
            }
            if ($apiReturn->status == 'ok') {
                $barcodes = (array)$apiReturn->pl_number;
                if ($total_num_of_parcels == count($barcodes)) {//if i got correct quantity barcodes
                    $i = 0;
                    foreach ($typed_orders as $type_order => $id_orders) {
                        foreach ($id_orders as $id_order) {
                            $orderObject = new Order((int)$id_order);
                            foreach (BalticodeDpdData::getCartProducts(new Cart((int)$orderObject->id_cart)) as $product) {
                                $product;
                                self::setBarcodeToOrder((int)$id_order, $barcodes[$i++]);
                            }
                        }
                    }
                } else {
                    foreach ($apiReturn->pl_number as $barcode) {
                        foreach ($typed_orders as $type_order => $id_orders) {
                            foreach ($id_orders as $id_order) {
                                self::setBarcodeToOrder((int)$id_order, $barcode);
                            }
                        }
                    }
                }
                if (!empty($apiReturn->errlog)) {
                    self::registerWarning($apiReturn->errlog);
                }
            }
        }
    }

    /**
     * Group orders who not have Barcodes
     * This is need for MPS method
     *
     * @param  array - Order ids who need group
     * @param  string $action join, or split (join = multi orders get one label, Split = label per different order item)
     * @return array - Multidimensional array
     *         array(
     *             'single' => array([orders]), //Single order not grouping
     *             'multi' => array(
     *                  'groups'=> array([order])), //Grouped orders
     *             'given' => array([orders]), //order list who already have barcode in messages
     *         )
     */
    public static function groupOrders($id_orders = array(), $action = 'join')
    {
        if (!is_array($id_orders)) {//if not array set
            return false;
        }
        //MPS grouping data
        // MPS available when:
        //  Same Shipping Address;
        //  Same Package Type;
        //  Same Delivery Day;
        //  Order Delivery NOT to ParcelStore

        $groupedOrders = array(
            'single' => array(),
            'given' => array(),
            'multi' => array(),
            'mps' => array()
        );

        $orders = array();
        if (!count($id_orders)) {//if orders id is empty
            return $groupedOrders; //return empty array
        }
        foreach ($id_orders as $id_order) {
            if (self::hasBarcode($id_order)) {//This order already has barcode?
                $groupedOrders['given'][] = $id_order;
                continue;
            }
            $orderData = new Order($id_order); //grab order data
            $orders[$orderData->id_address_delivery][self::getParcelType($id_order)][] = array(
                'order' => $id_order,
                'shipping_address' => $orderData->id_address_delivery, //same shipping address
                'shipping_type' => self::getParcelType($id_order), //same shipping type
                //'delivery_day' => '',
            );
        }

        if ($action == 'split') {//We need to split order by item to create MPS
            foreach ($orders as $id_delivery => $data_delivery) {
                foreach ($data_delivery as $type_order => $data_order) {
                    foreach ($data_order as $order) {
                        $groupedOrders['mps'][$id_delivery][$type_order][] = $order['order'];
                    }
                }
            }
        }

        if ($action == 'join') { //We need to join order to create one label
            foreach ($orders as $id_delivery => $data_delivery) {
                foreach ($data_delivery as $type_order => $data_order) {
                    if (count($data_order) > 1) {
                        foreach ($data_order as $order) {
                            $groupedOrders['multi'][$id_delivery][$type_order][] = $order['order'];
                        }
                    } else {
                        $groupedOrders['single'][$id_delivery][$type_order][] = $data_order[0]['order'];
                    }
                }
            }
        }

        return $groupedOrders;
    }

    /**
     * Return DPD shipping type
     *
     * @param  string - Order id
     * @return string - DPD Shipping type, PS - ParcelStore, B2C - Business To Consumer, D-COD-B2C...
     * for more info view in self::$dpd_available_parcel_types
     */
    public static function getParcelType($id_order)
    {
        $orderOption = DpdOrderOptions::getOrderOptions($id_order);

        if (count($orderOption) <= 0) {//If we do not any settings try to find in global
            $order = new Order($id_order); //Load order to get carrier id
            $id_carrier = $order->id_carrier;
        } else {
            $id_carrier = $orderOption[0]['carrier_id'];
        }

        if (!$id_carrier) {
            self::registerWarning(self::l('Order: ').$id_order.', '.self::l('data about this carrier is damaged'));
            return false;
        }

        if (!DynamicParcelDistribution::availablePaymentMethod($id_order, $id_carrier)) {
            self::registerWarning(self::l('Order: ').$id_order.', '.self::l('not available payment method for this carrier'));
            return false;
        }

        if (DpdCarrierOptions::getCarrierType($id_carrier) === 'carrier') {
            $paymentMethod = DynamicParcelDistribution::getPaymentMethod($id_order);
            if (DynamicParcelDistribution::isCodMethod($paymentMethod)) {
                return 'D-COD-B2C'; //Classic with CashOnDelivery
            } else {
                return 'D-B2C'; //Classic
            }
        }

        if (DpdCarrierOptions::getCarrierType($id_carrier) === 'pickup') {
            return 'PS'; //Parcel Store
        }

        //Something wrong
        self::registerWarning(self::l('Order: ').$id_order.', '.self::l('something is wrong, cant find Parcel Type.'));
        return false;
    }

    /**
     * Add some message to order
     *
     * @param string - Order id
     * @param string - Some Message
     * @param string - Message prefix to indicate it's my message
     * @return boolean
     */
    private static function addMessageToOrder($id_order, $message, $messagePrefix = null)
    {
        if ($messagePrefix == null) {
            $messagePrefix = self::$messagePrefix;
        }

        $param = array(
            'id_order' => $id_order,
            'private' => 1, //1 - Private, 0 - Public (Display to customer)
            'id_customer' => 0,
            'date_add' => date('Y-m-d H:i:s'),
            'message' => $messagePrefix.$message,
        );
        return Db::getInstance()->insert('message', $param);
    }

    /**
     * Return last Message from order where text start with prefix
     *
     * @param  string - Order id
     * @param  string|null - Message prefix if null use global class messageprefix
     * @param  boolean - Message type is only for administrator or and customer
     * @return string - Message, if not fount return ''
     */
    private static function getMessageFromOrder($id_order, $messagePrefix = null, $private = true)
    {
        if ($messagePrefix == null) {
            $messagePrefix = self::$messagePrefix;
        }
        $messageText = array();
        foreach (MessageCore::getMessagesByOrderId($id_order, $private) as $messageBlock) {
            if (strpos($messageBlock['message'], $messagePrefix) !== false) {
                $messageText[] = $messageBlock['message'];
            }
        }
        return $messageText;
    }

    /**
     * Return order has barcode (barcode message starts with some prefix)
     *
     * @param  string - Order id
     * @param  str|null - Message Prefix if null use global class messageprefix
     * @return boolean
     */
    public static function hasBarcode($id_order, $messagePrefix = null)
    {
        if (!is_string($id_order) && !is_int($id_order)) {
            return false;
        }

        $barcode = self::getBarcodeFromOrder($id_order, $messagePrefix);
        if (count($barcode)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return array Barcodes by Order id's
     *
     * @param  array  $id_orders [description]
     * @param  string | null $messagePrefix Message prefix if null use global class messageprefix
     * @return array - orderId => Barcode
     */
    public static function getBarcodeByOrderIds($id_orders = array(), $messagePrefix = null)
    {
        if (!is_array($id_orders)) {
            return false;
        }

        $barcodes = array();
        foreach ($id_orders as $id_order) {
            if (self::hasBarcode($id_order)) {
                $barcodes[$id_order] = self::getBarcodeFromOrder($id_order, $messagePrefix);
            }
        }
        return $barcodes;
    }

    /**
     * Return single Barcode from order messages
     *
     * @param  string - Order id
     * @param  str|null -  Message prefix if null use global class messageprefix
     * @return string - Barcode without messagePrefix
     */
    public static function getBarcodeFromOrder($id_order, $messagePrefix = null)
    {
        $barcode = array();
        if (is_array($id_order) || is_object($id_order)) {
            return false;
        }

        if ($messagePrefix == null) {
            $messagePrefix = self::$messagePrefix;
        }

        foreach (self::getMessageFromOrder($id_order, $messagePrefix) as $messageTest) {
            $barcode[] = ltrim($messageTest, $messagePrefix);
        }

        return $barcode;
    }

    /**
     * Write Message to Order info
     *
     * @param string - Order id
     * @param string - Barcode number
     * @param str|null - Message start with messagePrefix, if null using global class messagePrefix
     * @return Boolean
     */
    private static function setBarcodeToOrder($id_order, $barcode, $messagePrefix = null)
    {
        if ($messagePrefix == null) {
            $messagePrefix = self::$messagePrefix;
        }

        return self::addMessageToOrder($id_order, $barcode, $messagePrefix);
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
