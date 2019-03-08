<?php

namespace GeoIp\GeoIp\Provider;

use Cake\Core\App;
use GeoIp\GeoIp\Provider;

class CombinedProvider extends Provider
{
    protected $_baseConfig = [
        'providers' => []
    ];

    /**
     * @var \GeoIp\GeoIp\Provider[] Map of geoip datasource objects
     */
    protected $_providers = [];

    /**
     * Constructor
     *
     * @param array $config GeoIP configuraton
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->constructProviders();
    }

    /**
     * Construct provider objects
     *
     * @return void
     */
    public function constructProviders()
    {
        foreach ($this->_config['providers'] as $alias => $config) {
            if (is_string($config)) {
                $config = ['provider' => $config];
            }

            $config += ['provider' => null];
            if (!$config['provider']) {
                throw new \RuntimeException("Provider $alias has no provider configured");
            }

            $class = App::className($config['provider'], 'GeoIp/Provider', 'Provider');
            if (!$class) {
                throw new \RuntimeException("Provider $alias class not found");
            }

            $provider = new $class($config);
            $this->_providers[$alias] = $provider;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($ip, $options = [])
    {
        // lookup ip
        $location = $this->_location;
        foreach ($this->_providers as $provider) {
            $result = $provider->lookup($ip, $options);
            if ($result !== false) {
                foreach ($result as $k => $v) {
                    if ($v && !isset($location[$k])) {
                        $location[$k] = $v;
                    }
                }
            }
        }

        return $location;
    }

}
