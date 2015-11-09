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

/**
 * DPD API Class
 * Connections send and receive data from API
 * Connection parameters collect from database where put settings of module backend
 */

class API
{
    /**
     * DPD API URL address
     * LT: https://weblabel.dpd.lt/parcel_interface/
     * LV: https://dpdintegration.lv/parcel_interface/
     *
     * @var string
     */
    private $api_url;

    /**
     * DPD API Connection Name
     * @var string
     */
    private $api_name;

    /**
     * DPD API Connection Password
     * @var string
     */
    private $api_pass;

    /**
     * Flag of has some errors or not
     * @var boolean
     */
    private $error = false;

    /**
     * array or error messages if something has been wrong
     * @var array
     */
    private $error_msg = array();

    /**
     * Country name using for filtring
     * @var string
     */
    private $filtring_country;

    /**
     * City name using for filtring
     * @var string
     */
    private $filtring_city;

    /**
     * Some CONSTANT value grab from main module file
     * Value is Prefix of saved values in database
     */
    const CONST_PREFIX = DynamicParcelDistribution::CONST_PREFIX;

    /**
     * Class constructor
     * Grab some data who need to connect to DPD API
     */
    public function __construct()
    {
        $this->api_url = Configuration::get(self::CONST_PREFIX.'API_URL');
        $this->api_name = Configuration::get(self::CONST_PREFIX.'SERVICE_USERNAME');
        $this->api_pass = Configuration::get(self::CONST_PREFIX.'SERVICE_USERPASS');
    }

    /**
     *
     * @param  boolean $country [description]
     * @param  boolean $city    [description]
     * @return [type]           [description]
     */
    public function getDeliveryPoints($country = false, $city = false)
    {
        $data = Tools::jsonDecode($this->getResource());
        if ($data->status == 'ok') {
            $all_points = $data->parcelshops;
            $correct_points = $this->getFiltredPoints($all_points, $country, $city);
            return $correct_points;
        } else {
            $this->addError($data->errlog);
            return $data;
        }
    }

    /**
     * Delivery points filter, return only what we need
     *
     * @param  array $all_points all delivery points array
     * @param  string $country    some value to filter of country code
     * @param  string $city       some value to filter of city name
     * @return array              filtered delivery points
     */
    private function getFiltredPoints($all_points, $country, $city)
    {
        if ($country) {
            $this->setFiltringCountry($country);
            $all_points = array_filter($all_points, array($this, 'filterByCountry'));
        }
        if ($city) {
            $this->setFiltringCity($city);
            $all_points = array_filter($all_points, array($this, 'filterByCity'));
        }
        return $all_points;
    }

    /**
     * Call DPD API URL to get some data by sending parameters
     *
     * @param  array  $params array about what we need to get
     * @param  string $url    custom URL link if need to test something
     * @return mix            from there we got all what we want, jSon, PDF, String
     */
    private function getResource($params = array('action' => 'parcelshop_info'), $url = null)
    {
        $url_link = (($url === null)?$this->api_url:$url);
        $params['username'] = $this->api_name;
        $params['password'] = $this->api_pass;
        //DynamicParcelDistribution::log(print_r($params, true));
        $api = $url_link.$params['action'].'.php';
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params),
                'timeout' => 10,
        ));
        $context = stream_context_create($options);

        $contents = Tools::file_get_contents($api, false, $context);
        //DynamicParcelDistribution::log(print_r($contents, true));
        if ($contents === false) {
            $message = array('status' => 'err','errlog' => 'Wrong URL: '.$api);
            $contents = Tools::jsonEncode($message);
        }
        return $contents;
    }

    /**
     * Add error message to array
     * Set error flag to true - we have some error
     *
     * @param string $message some message
     */
    private function addError($message)
    {
        $this->error = true;
        $this->error_msg[] = $message;
    }

    /**
     * Get all registered error messages
     *
     * @param  boolean - clear array after read messages
     * @return array - registered error messages
     */
    public function getErrorMessage($clear = true)
    {
        $messages = $this->error_msg;
        if ($clear) {
            $this->error_msg = '';
            $this->error = false;
        }
        return $messages;
    }

    /**
     * Set country name to filter
     *
     * @param string $country some country name
     */
    public function setFiltringCountry($country)
    {
        $this->filtring_country = $country;
        return __CLASS__;
    }

    /**
     * Set city name to filter
     *
     * @param string $city some city name
     */
    public function setFiltringCity($city)
    {
        $this->filtring_city = $city;
        return __CLASS__;
    }

    /**
     * Return true if object country is equal what we need
     *
     * @param  object $obj Object get from DPD API with Delivery points
     * @return boolean     This country is what we need
     */
    private function filterByCountry($obj)
    {
        return ($obj->country == $this->filtring_country)? true : false;
    }

    /**
     * Return true if object city is equal what we need
     *
     * @param  object $obj Object get from DPD API with Delivery points
     * @return boolean     This city is what we need
     */
    private function filterByCity($obj)
    {
        return ($obj->city == $this->filtring_city)? true : false;
    }

    /**
     * DPD API send data, returned data validate
     * if PDF content, return as string
     * if string type is jSon, so decode string
     * if decoded or not has been object, test this if it is error message
     *  register error and return false
     *
     * @param  string $parameters some date who need to sent to DPD APi
     * @return mix
     */
    public function postData($parameters)
    {
        $response = null;
        $response = $this->getResource($parameters);

        if (self::isPdf($response)) { //Is pdf file content?
            return $response;
        }

        //Is string of jSon?
        if (is_string($response) && self::isJson($response)) {
            $response = Tools::jsonDecode($response); //Convert to Object
        }

        if (is_object($response)) { //Is object?
            if ($response->status !== 'ok') { //Is status ok?
                $this->addError($response->errlog);
                return false;
            }
        }

        return $response;
    }

    /**
     * return is pdf content
     *
     * @param  string  $fileContent pdf content
     * @return boolean
     */
    public static function isPdf($fileContent)
    {
        if (is_string($fileContent)) {
            $triggers = chr(37).chr(80).chr(68).chr(70).chr(45); //  %PDF-
            $heder = Tools::substr($fileContent, 0, Tools::strlen($triggers));
            if ($heder == $triggers) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * test string is jSon type
     *
     * @param  string  $string - some string
     * @return boolean
     */
    public static function isJson($string)
    {
        Tools::jsonDecode($string);
        return (json_last_error() == JSON_ERROR_NONE); //It is no errors?
    }
}
