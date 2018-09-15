<?php

namespace Client;

use OData\Client\Connection;
use OData\Client\Table;
use ODataTest\TestCase;

class TableTest  extends TestCase
{

    public function testCount()
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('count')->willReturn(15);

        $table = new TestTable(['connection' => $connectionMock]);

        $result = $table->count();
        $this->assertSame(15, $result);
    }

    public function testCall()
    {
        $connectionMock = $this->createMock(Connection::class);
        $entries = [
            [
                'Ref_Key'              => '31b2c19f-e241-11e6-8108-005056a77adb',
                'Description'          => 'Машино-часы',
                'ЕдиницаИзмерения_Key' => '02c71dcc-e200-11e6-8108-005056a77adb',
                'Code'                 => '000000001',
            ],
        ];
        $connectionMock->method('call')->willReturn($entries);

        $table = new TestTable(['connection' => $connectionMock]);

        $result = $table->call('Test');
        $this->assertSame($entries, $result);
    }

    public function testSaveInsert()
    {
        $connectionMock = $this->createMock(Connection::class);
        $entry = [
            'Ref_Key'     => '31b2c19f-e241-11e6-8108-005056a77adb',
            'Description' => 'Test',
        ];
        $connectionMock->method('insert')->willReturn($entry);

        $table = new TestTable(['connection' => $connectionMock]);

        $result = $table->save([
            'Description' => 'Test',
        ]);
        $this->assertSame($entry['Ref_Key'], $result);
    }

    public function testSaveUpdate()
    {
        $connectionMock = $this->createMock(Connection::class);
        $entry = [
            'Ref_Key'     => '31b2c19f-e241-11e6-8108-005056a77adb',
            'Description' => 'Test (update)',
        ];
        $connectionMock->method('update')->willReturn($entry);

        $table = new TestTable(['connection' => $connectionMock]);

        $result = $table->save($entry);
        $this->assertSame($entry['Ref_Key'], $result);
    }

    public function testRemove()
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('delete')->willReturn(1);

        $table = new TestTable(['connection' => $connectionMock]);

        $result = $table->delete('31b2c19f-e241-11e6-8108-005056a77adb');
        $this->assertSame(1, $result);
    }

}

class TestTable extends Table
{

    /**
     * @inheritdoc
     */
    protected $_name = 'Catalog_торо_ВидыДефектов';

    /**
     * @inheritdoc
     */
    protected $_primary = 'Ref_Key';

}