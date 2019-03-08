<?php
return [
    'Datasources' => [
        // single provider
        'geo_ip' => [
            'className' => '\GeoIp\Datasource\GeoIpDatasource',
            'provider' => 'GeoIp.Ipinfodb',
            'apiKey' => ''
        ],

        // multiple providers (ordered by priority)
        'geo_ip' => [
            'className' => '\GeoIp\Datasource\GeoIpDatasource',
            'provider' => 'GeoIp.Combined',
            'providers' => [
                'ipconfigdb' => [
                    'provider' => 'GeoIp.Ipinfodb',
                    'apiKey' => ''
                ]
            ]
        ],
    ]
];
