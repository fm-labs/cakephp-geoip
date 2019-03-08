<?php

namespace GeoIp\Exception;

use Cake\Core\Exception\Exception;

class MissingProviderException extends Exception
{
    protected $_messageTemplate = 'GeoIp provider \'%s\' not found';
}
