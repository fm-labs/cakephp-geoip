<?php

namespace GeoIp\Exception;

use Cake\Core\Exception\Exception;

class InvalidProviderException extends Exception
{
    protected $_messageTemplate = 'GeoIp provider \'%s\' invalid: %s';
}
