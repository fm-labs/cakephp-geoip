<?php

namespace GeoIp\Datasource;

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Text;
use GeoIp\Exception\MissingProviderException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * GeoIP Datasource
 * @method \Cake\Database\Schema\Collection getSchemaCollection()
 * @method \Cake\Database\Query newQuery()
 * @method \Cake\Database\StatementInterface prepare($sql)
 * @method \Cake\Database\StatementInterface execute($query, $params = [], array $types = [])
 * @method \Cake\Database\StatementInterface query(string $sql)
 * @method string quote($value, $type = null)
 * @method object getDriver()
 */
class GeoIpDatasource implements ConnectionInterface
{
    /**
     * @var array Datasource configuration
     */
    protected $_config = [];

    /**
     * @var array Default datasource configuration
     */
    protected $_baseConfig = [
        'cacheConfig' => 'geo_ip',
        'provider' => null,
        'log' => false,
    ];

    /**
     * @var \GeoIp\GeoIp\Provider
     */
    protected $_provider;

    /**
     * Whether to log queries generated during this connection.
     *
     * @var bool
     */
    protected $_logQueries = false;

    protected $_logger = null;

    /**
     * Constructor
     *
     * @param array $config Datasource configuration
     */
    public function __construct(array $config)
    {
        $config += $this->_baseConfig;
        $this->_config = $config;

        $provider = '';
        if (!empty($config['provider'])) {
            $provider = $config['provider'];
        }
        $this->provider($provider, $config);

        if (!empty($config['log'])) {
            $this->logQueries($config['log']);
        }
    }

    /**
     * Lookup geo location by ip.
     * Options:
     *  - `precision`: Level of geo location precision. Supported values: 'city' or 'country' (Default: country)
     *
     * @param string $ip IP Address
     * @param array $options Lookup options
     * @return string
     */
    public function lookup($ip, $options = [])
    {
        $options += ['precision' => 'country'];

        if (!$this->isValidIpFormat($ip)) {
            throw new \InvalidArgumentException("Invalid IP address");
        }

        if ($this->isLocalAddress($ip) || $this->isPrivateAddress($ip)) {
            return [];
        }

        $cacheKey = Text::slug($ip) . '_' . $options['precision'];
        $result = Cache::read($cacheKey, $this->_config['cacheConfig']);
        if (!$result) {
            if (($result = $this->provider()->lookup($ip, $options))) {
                Cache::write($cacheKey, $result, $this->_config['cacheConfig']);
            }
        }

        return $result;
    }

    /**
     * Retrieve geo location from IP address
     *
     * @param \GeoIp\GeoIp\Provider|string|null $provider Provider name or instance
     * @param array $config Provider config
     * @return \GeoIp\GeoIp\Provider
     */
    public function provider($provider = null, $config = [])
    {
        if ($provider === null) {
            return $this->_provider;
        }
        if (is_string($provider)) {
            $className = App::className($provider, 'GeoIp/Provider', 'Provider');
            if (!$className || !class_exists($className)) {
                throw new MissingProviderException(['provider' => $provider]);
            }
            $provider = new $className($config);
        }

        return $this->_provider = $provider;
    }

    /**
     * Validate IPv4 address format
     *
     * @param string $ip IPv4 address
     * @return bool
     */
    public function isValidIpFormat($ip)
    {
        return preg_match('/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip);
    }

    /**
     * Returns TRUE if the IP refers to local host address
     *
     * @param string $ip IPv4 address
     * @return bool
     */
    public function isLocalAddress($ip)
    {
        return (in_array($ip, ['127.0.0.1', '::1'])) ? true : false;
    }

    /**
     * Returns TRUE if the IP is in private network address space
     *
     * @param string $ip IPv4 address
     * @return bool
     * @todo The class B network is not entirerly detected
     */
    public function isPrivateAddress($ip)
    {
        if (
            $this->isLocalAddress($ip)
            || preg_match('/^10\./', $ip) // class A 10.0.0.0 – 10.255.255.255, 10.0.0.0/8
            || preg_match('/^172\.16\./', $ip) // class B 172.16.0.0 – 172.31.255.255, 172.16.0.0/12
            || preg_match('/^192\.168\./', $ip) // class C 192.168.0.0 – 192.168.255.255, 192.168.0.0/16
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get the configuration name for this connection.
     *
     * @return string
     */
    public function configName(): string
    {
        return 'geo_ip';
    }

    /**
     * Get the configuration data used to create the connection.
     *
     * @return array
     */
    public function config(): array
    {
        return $this->_config;
    }

    /**
     * Executes a callable function inside a transaction, if any exception occurs
     * while executing the passed callable, the transaction will be rolled back
     * If the result of the callable function is `false`, the transaction will
     * also be rolled back. Otherwise the transaction is committed after executing
     * the callback.
     *
     * The callback will receive the connection instance as its first argument.
     *
     * @param callable $transaction The callback to execute within a transaction.
     * @return mixed The return value of the callback.
     * @throws \Exception Will re-throw any exception raised in $callback after
     *   rolling back the transaction.
     */
    public function transactional(callable $transaction)
    {
        throw new \RuntimeException("Not supported");
    }

    /**
     * Run an operation with constraints disabled.
     *
     * Constraints should be re-enabled after the callback succeeds/fails.
     *
     * @param callable $operation The callback to execute within a transaction.
     * @return mixed The return value of the callback.
     * @throws \Exception Will re-throw any exception raised in $callback after
     *   rolling back the transaction.
     */
    public function disableConstraints(callable $operation)
    {
        throw new \RuntimeException("Not supported");
    }

    /**
     * Enables or disables query logging for this connection.
     *
     * @param bool|null $enable whether to turn logging on or disable it.
     *   Use null to read current value.
     * @return bool
     */
    public function logQueries($enable = null)
    {
        if ($enable === null) {
            return $this->_logQueries;
        }

        //$this->_logQueries = $enable;
    }

    /**
     * Sets the logger object instance. When called with no arguments
     * it returns the currently setup logger instance.
     *
     * @param object|null $instance logger object instance
     * @return object logger instance
     * @deprecated
     */
    public function logger($instance = null)
    {
        if ($instance === null) {
            return $this->_logger;
        }

        return $this->_logger = $instance;
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
            'config' => $this->_config,
            'provider' => $this->_provider,
            //'logQueries' => $this->_logQueries,
            //'logger' => $this->_logger
        ];
    }

    public function getLogger(): LoggerInterface
    {
        return $this->_logger;
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;

        return $this;
    }

    public function supportsDynamicConstraints()
    {
        return false;
    }

    public function enableQueryLogging(bool $value = true)
    {
        $this->_logQueries = $value;

        return $this;
    }

    public function disableQueryLogging()
    {
        $this->_logQueries = false;

        return $this;
    }

    public function disableSavePoints()
    {
        return $this;
    }

    public function isQueryLoggingEnabled(): bool
    {
        return $this->_logQueries;
    }

    function __call($name, $arguments)
    {
        // TODO: Implement @method \Cake\Database\Schema\Collection getSchemaCollection()
        // TODO: Implement @method \Cake\Database\Query newQuery()
        // TODO: Implement @method \Cake\Database\StatementInterface prepare($sql)
        // TODO: Implement @method \Cake\Database\StatementInterface execute($query, $params = [], array $types = [])
        // TODO: Implement @method \Cake\Database\StatementInterface query(string $sql)
        // TODO: Implement @method string quote($value, $type = null)
    }

    /**
     * @inheritDoc
     */
    public function setCacher(CacheInterface $cacher)
    {
        // TODO: Implement setCacher() method.
    }

    /**
     * @inheritDoc
     */
    public function getCacher(): CacheInterface
    {
        // TODO: Implement getCacher() method.
    }
}
