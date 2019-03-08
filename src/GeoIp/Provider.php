<?php

namespace GeoIp\GeoIp;

abstract class Provider
{
    const PRECISION_COUNTRY = 'country';
    const PRECISION_CITY = 'city';

    /**
     * @var array Geo location schema
     */
    protected $_location = [
        'ip_address' => null,
        'country_iso2' => null,
        'country_iso3' => null,
        'country_name' => null,
        'region_name' => null,
        'city_name' => null,
        'zip_code' => null,
        'latitude' => null,
        'longitude' => null,
        'timezone' => null
    ];

    /**
     * @var array Default provider configuration
     */
    protected $_baseConfig = [];

    /**
     * Construct
     *
     * @param array $config Provider config
     */
    public function __construct(array $config = [])
    {
        $config += $this->_baseConfig;
        $this->_config = $config;
    }

    /**
     * Get provider alias
     *
     * @return string
     */
    public function alias()
    {
        return substr(get_class($this), 0, -8);
    }

    /**
     * @param string $ip IP address
     * @param array $options Lookup options
     * @return array
     */
    abstract public function lookup($ip, $options = []);

    /**
     * Parse location data from arbitrary result data.
     *
     * @param array $result Provider specific result data
     * @param array $map Map of provider keys to geo location data key
     * @return array
     */
    protected function _parseLocation($result, $map)
    {
        $location = $this->_location;
        foreach ($result as $k => $v) {
            if (isset($map[$k])) {
                $k = $map[$k];
            }
            if ($v && array_key_exists($k, $location) && $location[$k] === null) {
                $location[$k] = $v;
            }
        }

        return $location;
    }

    /**
     * Returns an array that can be used to describe the internal state of this
     * object.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'alias' => $this->alias()
        ];
    }
}
