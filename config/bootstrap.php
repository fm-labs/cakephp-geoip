<?php

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;

/*
 * Cache config
 */
if (!Cache::getConfig('geo_ip')) {
    Cache::setConfig('geo_ip', [
        'className' => 'File',
        'duration' => '+1 weeks',
        'path' => CACHE . 'geo_ip' . DS,
        'prefix' => '',
    ]);
}

/*
 * Logs
 */
if (!Log::getConfig('geo_ip')) {
    Log::setConfig('geo_ip', [
        'className' => 'Cake\Log\Engine\FileLog',
        'path' => LOGS,
        'file' => 'geo_ip',
        //'levels' => ['info'],
        'scopes' => ['geo_ip'],
    ]);
}

/*
 * Connection
 */
if (!ConnectionManager::getConfig('geo_ip')) {
    if (Configure::check('GeoIp')) {
        $config = Configure::read('GeoIp');
        $config += ['className' => '\GeoIp\Datasource\GeoIpDatasource'];
        ConnectionManager::setConfig('geo_ip', $config);
    }
}
