<?php

/**
 * OData Client
 * @category   OData
 * @package    OData\Client
 * @author Victor Vasilev <vasilev@go-rost.ru>
 */
namespace OData\Client;

use GuzzleHttp\Client as RestClient;
use OData\Parser\Adapter\XMLSimpleReaderAdapter;
use OData\Parser\Atom\Reader;

class ConnectionFactory
{
    /** @var Connection[] */
    protected static $_instances = [];

    /**
     * @param string $url
     * @param string $username
     * @param string $password
     * @return Connection
     */
    public static function getInstance($url, $username, $password): Connection
    {
        $key = \md5($url . $username . $password);
        if (!\array_key_exists($key, static::$_instances)) {
            static::$_instances[$key] = new Connection(
                new RestClient(),
                new Reader(new XMLSimpleReaderAdapter()),
                $url,
                $username,
                $password
            );
        }
        return static::$_instances[$key];
    }

}