<?php

namespace GeoIp\Model\Table;

class GeoIpTable
{
    /**
     * @var \GeoIp\Datasource\GeoIpDatasource
     */
    protected $_connection;

    /**
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'geo_ip';
    }

    /**
     * @param array $config Table config
     */
    public function __construct(array $config)
    {
        if (isset($config['connection'])) {
            $this->_connection = $config['connection'];
        }
    }

    /**
     * @return \GeoIp\Datasource\GeoIpDatasource
     */
    public function connection()
    {
        return $this->_connection;
    }

    /**
     * @param string $ip IP address
     * @param array $options Lookup options
     * @return array
     */
    public function lookup($ip, $options = [])
    {
        return $this->connection()->lookup($ip, $options);
    }
}
