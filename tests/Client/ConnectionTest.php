<?php

namespace Client;

use GuzzleHttp\ClientInterface;
use OData\Client\ConnectException;
use OData\Client\Connection;
use OData\Parser\Atom\ReaderInterface;
use ODataTest\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ConnectionTest extends TestCase
{

    private function getXML(string $fileName)
    {
        return \file_get_contents(static::TEST_DATA_PATH. '/' . $fileName);
    }

    public function testCall()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn($this->getXML('single_exchange_plan.xml'));
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);
        $entries = [
            [
                'Ref_Key'              => '31b2c19f-e241-11e6-8108-005056a77adb',
                'Description'          => 'Машино-часы',
                'ЕдиницаИзмерения_Key' => '02c71dcc-e200-11e6-8108-005056a77adb',
                'Code'                 => '000000001',
            ],
        ];
        $readerMock->method('getEntries')->willReturn($entries);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $result = $connection->call('', '', 'SelectChanges', [
            'DataExchangePoint' => "http://localhost/test/odata/standard.odata/ExchangePlan_ОбменGoRFID(guid'd043292d-c84e-11e7-be8e-b888e3a9a739')",
            'MessageNo'         => 123,
        ]);
        $this->assertSame($entries, $result);

        $result = $connection->call('Catalog_торо_ВидыДефектов', '0b7be653-ceb8-11e7-be8e-b888e3a9a739', 'Test', []);
        $this->assertSame($entries, $result);
    }

    public function testCount()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn(15);
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $result = $connection->count('Catalog_ПараметрыВыработкиОС');
        $this->assertSame(15, $result);
    }

    public function testInsert()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn($this->getXML('single_entry.xml'));
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);
        $entry = [
            'Ref_Key'     => '31b2c19f-e241-11e6-8108-005056a77adb',
            'Description' => 'Машино-часы',
            'Code'        => '000000001',
        ];
        $readerMock->method('getEntries')->willReturn([$entry]);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $result = $connection->insert('Catalog_ПараметрыВыработкиОС', [
            'Description' => 'Машино-часы',
            'Code'        => '000000001',
        ]);
        $this->assertSame($entry, $result);
    }

    public function testUpdate()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn($this->getXML('single_entry.xml'));
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);
        $entry = [
            'Ref_Key'     => '31b2c19f-e241-11e6-8108-005056a77adb',
            'Description' => 'Машино-часы (update)',
            'Code'        => '000000001',
        ];
        $readerMock->method('getEntries')->willReturn([$entry]);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $result = $connection->update('Catalog_ПараметрыВыработкиОС', [
            'Description' => 'Машино-часы (update)',
            'Code'        => '000000001',
        ], '31b2c19f-e241-11e6-8108-005056a77adb');
        $this->assertSame($entry, $result);
    }

    public function testDelete()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn('');
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $result = $connection->delete('Catalog_ПараметрыВыработкиОС', '31b2c19f-e241-11e6-8108-005056a77adb');
        $this->assertSame(1, $result);
    }

    public function testQuery()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn($this->getXML('single_entry.xml'));
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);
        $entries = [
            [
                'Ref_Key'              => '31b2c19f-e241-11e6-8108-005056a77adb',
                'Description'          => 'Машино-часы',
                'ЕдиницаИзмерения_Key' => '02c71dcc-e200-11e6-8108-005056a77adb',
                'Code'                 => '000000001',
            ],
        ];
        $readerMock->method('getEntries')->willReturn($entries);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $result = $connection->query('/Catalog_ПараметрыВыработкиОС');
        $this->assertSame($entries, $result->fetchAll());
    }

    public function testConnectException()
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(404);
        $bodyMock = $this->createMock(StreamInterface::class);
        $bodyMock->method('getContents')->willReturn(15);
        $responseMock->method('getBody')->willReturn($bodyMock);
        $clientMock->method('request')->willReturn($responseMock);

        $readerMock = $this->createMock(ReaderInterface::class);

        $connection = new Connection($clientMock, $readerMock, 'http://localhost', 'test', 'test');

        $this->expectException(ConnectException::class);
        $connection->count('Catalog_ПараметрыВыработкиОС');
    }

}