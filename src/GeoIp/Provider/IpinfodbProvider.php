<?php

namespace GeoIp\GeoIp\Provider;

use GeoIp\Exception\InvalidProviderException;
use GeoIp\GeoIp\Provider;

class IpinfodbProvider extends Provider
{
    const API_BASE_URL = 'http://api.ipinfodb.com/';

    protected $_baseConfig = [
        'apiKey' => ''
    ];

    /**
     * @param array $config Provider config
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if (!preg_match('/^[0-9a-z]{64}$/', $this->_config['apiKey'])) {
            throw new InvalidProviderException([
                'provider' => $this->alias(),
                'message' => __('Invalid IPInfoDB API key')
            ]);
        }
    }

    /**
     * @param string $ip IP address
     * @param array $options Lookup options
     * @return array
     */
    public function lookup($ip, $options = [])
    {
        switch ($options['precision']) {
            case self::PRECISION_CITY:
                $result = $this->getCity($ip);
                break;
            case self::PRECISION_COUNTRY:
            default:
                $result = $this->getCountry($ip);
                break;
        }

        $map = [
            'ipAddress' => 'ip_address',
            'countryCode' => 'country_iso2',
            'countryName' => 'country_name',
            'regionName' => 'region_name',
            'cityName' => 'city_name',
            'zipCode' => 'zip_code',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'timeZone' => 'timezone'
        ];

        return $this->_parseLocation($result, $map);
    }

    /**
     * Geo location lookup with country precision
     *
     * @param string $ip IP address
     * @return array
     */
    protected function getCountry($ip)
    {
        return $this->request($this->buildUrl('v3/ip-country/', $ip));
    }

    /**
     * Geo location lookup with city precision
     *
     * @param string $ip IP address
     * @return array
     */
    protected function getCity($ip)
    {
        return $this->request($this->buildUrl('v3/ip-city/', $ip));
    }

    /**
     * @param string $url Request URI
     * @return array|false Geolocation data or FALSE on error
     */
    protected function request($url = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:63.0) Gecko/20100101 Firefox/63.0');
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        debug($response);
        if ($info['http_code'] != 200) {
            return false;
        }

        /*
         * '{
                "statusCode" : "OK",
                "statusMessage" : "",
                "ipAddress" : "77.119.130.5",
                "countryCode" : "AT",
                "countryName" : "Austria"
            }'
         */
        if (($data = json_decode($response, true)) === null) {
            return false;
        }

        return $data;
    }

    /**
     * Build request URI
     *
     * @param string $path URI path
     * @param string $ip IP Address
     * @return string
     */
    protected function buildUrl($path, $ip)
    {
        return sprintf(
            '%s/%s?key=%s&ip=%s&format=json',
            self::API_BASE_URL,
            $path,
            $this->_config['apiKey'],
            $ip
        );
    }
}
