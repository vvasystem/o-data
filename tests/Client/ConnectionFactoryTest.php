<?php

namespace Client;

use OData\Client\Connection;
use OData\Client\ConnectionFactory;
use ODataTest\TestCase;

class ConnectionFactoryTest extends TestCase
{

    public function testGetInstance()
    {
        $connection = ConnectionFactory::getInstance('http://localhost/test/odata/standard.odata', 'test', 'test');
        $this->assertInstanceOf(Connection::class, $connection);

        $connection2 = ConnectionFactory::getInstance('http://localhost/test/odata/standard.odata', 'test', 'test');
        $this->assertSame($connection2, $connection);
    }

}