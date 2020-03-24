<?php
return [
    'GeoIp' => [
        // single provider
        'provider' => 'GeoIp.Ipinfodb',
        'apiKey' => '',

        // multiple providers (ordered by priority)
        //'provider' => 'GeoIp.Combined',
        //'providers' => [
        //    'ipconfigdb' => [
        //        'provider' => 'GeoIp.Ipinfodb',
        //        'apiKey' => ''
        //    ]
        //]
    ],
];
