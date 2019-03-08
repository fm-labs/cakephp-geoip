<?php

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;

/*
 * Cache config
 */
if (!Cache::config('geo_ip')) {
    Cache::config('geo_ip', [
        'className' => 'File',
        'duration' => '+1 weeks',
        'path' => CACHE . 'geo_ip' . DS,
        'prefix' => ''
    ]);
}

/*
 * Logs
 */
if (!Log::config('geo_ip')) {
    Log::config('geo_ip', [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'geo_ip',
        //'levels' => ['info'],
        'scopes' => ['geo_ip']
    ]);
}

/*
 * Connection
 */
if (!ConnectionManager::config('geo_ip')) {
    if (Configure::check('GeoIp')) {
        $config = Configure::read('GeoIp');
        $config += ['className' => '\GeoIp\Datasource\GeoIpDatasource'];
        ConnectionManager::config('geo_ip', $config);
    }
}
