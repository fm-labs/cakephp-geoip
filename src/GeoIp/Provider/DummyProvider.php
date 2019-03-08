<?php

namespace GeoIp\GeoIp\Provider;

use GeoIp\GeoIp\Provider;

class DummyProvider extends Provider
{
    /**
     * {@inheritDoc}
     */
    public function lookup($ip, $options = [])
    {
        return [
            'country_iso2' => 'AT'
        ];
    }
}
